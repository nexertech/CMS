<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\ComplaintSpare;
use App\Models\Spare;
use App\Models\ComplaintCategory;
use App\Models\City;
use App\Models\Sector;
use Illuminate\Support\Facades\Schema;
use App\Models\House;
use App\Models\SpareApprovalPerforma;
use App\Models\SpareApprovalItem;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintLog;
use App\Traits\LocationFilterTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    use LocationFilterTrait;
    public function __construct()
    {
        // Middleware is applied in routes
    }

    /**
     * Display a listing of complaints
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Complaint::with(['assignedEmployee', 'city', 'sector', 'attachments', 'spareParts.spare', 'spareApprovals']);

        // Apply location-based filtering
        $this->filterComplaintsByLocation($query, $user);

        // Filter by House No.
        if ($request->has('house_no') && $request->house_no) {
            $houseNo = trim($request->house_no);
            $query->whereHas('house', function ($q) use ($houseNo) {
                $q->where('house_no', 'like', "%{$houseNo}%");
            });
        }

        // Search functionality - by Name and ID
        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                // Search by house number
                $q->whereHas('house', function ($houseQuery) use ($search) {
                    $houseQuery->where('house_no', 'like', "%{$search}%");
                })
                    // Search by title
                    ->orWhere('title', 'like', "%{$search}%")
                    // Search by description
                    ->orWhere('description', 'like', "%{$search}%");

                // If search is numeric, also search by ID (handles both actual ID and formatted complaint_id)
                if (is_numeric($search)) {
                    $numericSearch = (int) $search;
                    // Search by actual ID
                    $q->orWhere('complaints.id', $numericSearch);
                    // Search by ID modulo 10000 (for formatted complaint_id like 0123)
                    $q->orWhereRaw('(complaints.id % 10000) = ?', [$numericSearch]);
                }
            });
        }

        // Filter by overdue status
        if ($request->has('filter') && $request->filter === 'overdue') {
            $query->overdue();
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $statusValue = $request->status;

            // Handle work_priced_performa and maint_priced_performa filters
            // Prefix with complaints. to avoid ambiguity
            if ($statusValue === 'work_priced_performa') {
                $query->where('complaints.status', 'work_priced_performa');
            } elseif ($statusValue === 'maint_priced_performa') {
                $query->where('complaints.status', 'maint_priced_performa');
            } else {
                // For other statuses, use direct filter
                $query->where('complaints.status', $statusValue);
            }
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('complaints.category_id', $request->category); // Updated to use ID
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority) {
            $query->where('complaints.priority', $request->priority);
        }

        // Filter by assigned employee
        if ($request->has('assigned_employee_id') && $request->assigned_employee_id) {
            $query->where('complaints.assigned_employee_id', $request->assigned_employee_id);
        }



        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('complaints.created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('complaints.created_at', '<=', $request->date_to);
        }

        // Order by ID descending (3, 2, 1...) - newest/highest ID first
        // Clear any existing orders and set explicit descending order
        $query->with(['assignedEmployee', 'house', 'category', 'complaintTitle']) // Added relations
            ->reorder()
            ->orderBy('complaints.id', 'desc');
        $complaints = $query->paginate(12)->withQueryString();

        // Filter employees by location
        $employeesQuery = Employee::where('status', 'active');
        $this->filterEmployeesByLocation($employeesQuery, $user);
        $employees = $employeesQuery->get();

        $categories = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::where('status', 'active')->orderBy('name')->pluck('name', 'id')
            : collect();

        return view('admin.complaints.index', compact('complaints', 'employees', 'categories'));
    }

    /**
     * Show the form for creating a new complaint
     */
    public function create()
    {

        $employeesQuery = Employee::where('status', 'active')->orderBy('name');
        $this->filterEmployeesByLocation($employeesQuery, Auth::user());
        $employees = $employeesQuery->get();
        $categories = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::where('status', 'active')->orderBy('name')->pluck('name', 'id')
            : collect();

        // Get cities and sectors for dropdowns
        $citiesQuery = City::where('status', 'active')->orderBy('id', 'asc');
        $userCityIds = $this->getUserCityIds(Auth::user());
        if ($userCityIds !== null) {
            $citiesQuery->whereIn('id', $userCityIds);
        }
        $cities = Schema::hasTable('cities') ? $citiesQuery->get() : collect();

        $sectors = collect(); // Will be loaded dynamically based on city selection

        // Auto-select GE Group and Node for any user who has them assigned
        $defaultCityId = null;
        $defaultSectorId = null;
        $authUser = Auth::user();
        if ($authUser) {
            $defaultCityId = !empty($authUser->city_ids) ? $authUser->city_ids[0] : null;
            $defaultSectorId = !empty($authUser->sector_ids) ? $authUser->sector_ids[0] : null;
        }

        // Get houses filtered by location
        $housesQuery = House::where('status', 'active')->orderBy('username');
        $this->filterHousesByLocation($housesQuery, $authUser);
        $houses = $housesQuery->get();

        return view('admin.complaints.create', compact('employees', 'categories', 'cities', 'sectors', 'defaultCityId', 'defaultSectorId', 'houses'));
    }

    /**
     * Store a newly created complaint
     */
    /**
     * Store a newly created complaint
     */
    public function store(Request $request)
    {
        // Debug: Log the request data
        Log::info('Complaint creation request', [
            'all_data' => $request->all(),
            'method' => $request->method(),
        ]);

        $data = $request->all();
        if (isset($data['complaint_title_id']) && $data['complaint_title_id'] === 'other') {
            $data['complaint_title_id'] = null;
        }

        $validator = Validator::make($data, [
            'title' => 'nullable|string|max:255', // Now holds custom title or "Other"
            'complaint_title_id' => 'nullable|exists:complaint_titles,id', // Holds selected title ID
            'title_other' => 'nullable|string|max:255',
            'category' => 'required|exists:complaint_categories,id', // Expecting ID now
            'priority' => 'required|in:low,medium,high,urgent,emergency',
            'availability_time' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'assigned_employee_id' => 'required|exists:employees,id',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
            'city_id' => 'nullable|exists:cities,id',
            'sector_id' => 'nullable|exists:sectors,id',
            'house_id' => 'required|exists:houses,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $complaintTitleId = $request->complaint_title_id;
            $customTitle = null;

            if (!$complaintTitleId) {
                $customTitle = $request->title_other ?? $request->title;
                if (strtolower($customTitle) === 'other')
                    $customTitle = null;
            }

            $complaint = Complaint::create([
                'complaint_title_id' => $complaintTitleId,
                'title' => $customTitle,
                'house_id' => $request->house_id ?: null,
                'city_id' => $request->city_id ?: null,
                'sector_id' => $request->sector_id ?: null,
                'category_id' => $request->category,
                'priority' => $request->priority,
                'availability_time' => $request->availability_time,
                'description' => $request->description,
                'assigned_employee_id' => $request->assigned_employee_id ?: null,
                'status' => 'assigned',
            ]);

            // ... (attachments and logs remain same) ...
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('complaint-attachments', $filename, 'public');

                    ComplaintAttachment::create([
                        'complaint_id' => $complaint->id,
                        'filename' => $filename,
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ]);
                }
            }

            $currentEmployee = Employee::first();
            if ($currentEmployee) {
                ComplaintLog::create([
                    'complaint_id' => $complaint->id,
                    'action_by' => $currentEmployee->id,
                    'action' => 'created',
                    'remarks' => 'Complaint created',
                ]);
            }

            DB::commit();

            return redirect()->route('admin.complaints.index')
                ->with('success', 'Complaint created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create complaint: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified complaint
     */
    public function show(Complaint $complaint)
    {
        $complaint->load(['assignedEmployee', 'city', 'sector', 'attachments', 'spareParts.spare', 'spareApprovals', 'logs.actionBy', 'category', 'complaintTitle']);
        return view('admin.complaints.show', compact('complaint'));
    }

    /**
     * Show the form for editing the specified complaint
     */
    public function edit(Complaint $complaint)
    {
        if (in_array($complaint->status, ['resolved', 'closed'])) {
            return redirect()->route('admin.complaints.index')
                ->with('error', 'Resolved or Closed complaints cannot be edited.');
        }
        $complaint->load(['assignedEmployee', 'city', 'sector']);

        $employeesQuery = Employee::where('status', 'active')->orderBy('name');

        $this->filterEmployeesByLocation($employeesQuery, Auth::user());
        $employees = $employeesQuery->get();

        $categories = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::where('status', 'active')->orderBy('name')->pluck('name', 'id')
            : collect();

        // Get cities and sectors for dropdowns
        $citiesQuery = City::where('status', 'active')->orderBy('id', 'asc');
        $userCityIds = $this->getUserCityIds(Auth::user());
        if ($userCityIds !== null) {
            $citiesQuery->whereIn('id', $userCityIds);
        }
        $cities = Schema::hasTable('cities') ? $citiesQuery->get() : collect();

        // Load sectors for the complaint's city (via house if needed)
        $sectors = collect();
        $complaintCityId = $complaint->city_id ?? $complaint->house?->city_id;
        if ($complaintCityId) {
            $sectors = Sector::where('city_id', $complaintCityId)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        }

        $defaultCityId = $complaint->city_id ?? $complaint->house?->city_id ?? (!empty(Auth::user()->city_ids) ? Auth::user()->city_ids[0] : null);
        $defaultSectorId = $complaint->sector_id ?? $complaint->house?->sector_id ?? (!empty(Auth::user()->sector_ids) ? Auth::user()->sector_ids[0] : null);

        // Get houses filtered by location
        $housesQuery = House::where('status', 'active')->orderBy('username');
        $this->filterHousesByLocation($housesQuery, Auth::user());
        $houses = $housesQuery->get();

        return view('admin.complaints.edit', compact('complaint', 'employees', 'categories', 'cities', 'sectors', 'defaultCityId', 'defaultSectorId', 'houses'));
    }

    /**
     * Update the specified complaint
     */
    public function update(Request $request, Complaint $complaint)
    {
        if (in_array($complaint->status, ['resolved', 'closed'])) {
            return redirect()->route('admin.complaints.index')
                ->with('error', 'Resolved or Closed complaints cannot be updated.');
        }
        $data = $request->all();
        if (isset($data['complaint_title_id']) && $data['complaint_title_id'] === 'other') {
            $data['complaint_title_id'] = null;
        }

        $validator = Validator::make($data, [
            'title' => 'nullable|string|max:255',
            'complaint_title_id' => 'nullable|exists:complaint_titles,id',
            'title_other' => 'nullable|string|max:255',
            'category' => 'required|exists:complaint_categories,id', // Expect ID
            'priority' => 'required|in:low,medium,high,urgent,emergency',
            'availability_time' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'assigned_employee_id' => 'required|exists:employees,id',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
            'spare_parts' => 'nullable|array',
            'spare_parts.0.spare_id' => 'nullable|exists:spares,id',
            'spare_parts.0.quantity' => 'nullable|integer|min:1',
            'city_id' => 'nullable|exists:cities,id',
            'sector_id' => 'nullable|exists:sectors,id',
            'house_id' => 'required|exists:houses,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $oldStatus = $complaint->status;
        $oldAssignedTo = $complaint->assigned_employee_id;

        // Title Updating Logic
        $complaintTitleId = $request->complaint_title_id;
        $customTitle = null;

        if (!$complaintTitleId) {
            $customTitle = $request->title_other ?? $request->title;
            if (strtolower($customTitle) === 'other')
                $customTitle = null;
        }

        $newStatus = $complaint->status;
        if ($newStatus === 'new' && $request->assigned_employee_id) {
            $newStatus = 'assigned';
        }

        $complaint->update([
            'complaint_title_id' => $complaintTitleId,
            'title' => $customTitle,
            'house_id' => $request->house_id ?: null,
            'city_id' => $request->city_id ?: null,
            'sector_id' => $request->sector_id ?: null,
            'category_id' => $request->category,
            'priority' => $request->priority,
            'availability_time' => $request->availability_time,
            'description' => $request->description,
            'assigned_employee_id' => $request->assigned_employee_id ?: null,
            'status' => $newStatus,
        ]);


        // Update product (spare) selection only if provided
        if ($request->filled('spare_parts') && isset($request->spare_parts[0]['spare_id']) && $request->spare_parts[0]['spare_id']) {
            try {
                DB::beginTransaction();

                $currentEmployee = Employee::first();

                // Remove existing complaint spares without stock adjustment
                $complaint->spareParts()->delete();

                // Add the new selection (single box form)
                $part = $request->spare_parts[0];
                $spare = Spare::find($part['spare_id']);
                if (!$spare) {
                    throw new \Exception('Selected product not found.');
                }

                ComplaintSpare::create([
                    'complaint_id' => $complaint->id,
                    'spare_id' => $spare->id,
                    'quantity' => (int) ($part['quantity'] ?? 1),
                    'used_by' => $currentEmployee?->id ?? Employee::first()->id,
                    'used_at' => now(),
                ]);

                // Also update the main complaints table for compatibility
                $complaint->update([
                    'spare_id' => $spare->id,
                    'spare_quantity' => (int) ($part['quantity'] ?? 1),
                    'spare_used_by' => $currentEmployee?->id ?? Employee::first()->id,
                    'spare_used_at' => now(),
                ]);

                if ($currentEmployee) {
                    ComplaintLog::create([
                        'complaint_id' => $complaint->id,
                        'action_by' => $currentEmployee->id,
                        'action' => 'spare_parts_updated',
                        'remarks' => "Updated product to {$spare->item_name} (Qty: " . ((int) ($part['quantity'] ?? 1)) . ")",
                    ]);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Failed to update product/quantity: ' . $e->getMessage())->withInput();
            }
        }

        // Handle new file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('complaint-attachments', $filename, 'public');

                ComplaintAttachment::create([
                    'complaint_id' => $complaint->id,
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        // Log status changes
        if ($oldStatus !== $request->status) {
            $currentEmployee = Employee::first();
            if ($currentEmployee) {
                ComplaintLog::create([
                    'complaint_id' => $complaint->id,
                    'action_by' => $currentEmployee->id,
                    'action' => 'status_changed',
                    'remarks' => "Status changed from {$oldStatus} to {$request->status}",
                ]);
            }

            // Send Notification to the House (User) for status change
            if ($complaint->house) {
                try {
                    $complaint->house->notify(new \App\Notifications\ComplaintStatusUpdated($complaint, $request->status));
                } catch (\Exception $e) {
                    Log::error('Notification Failed in update(): ' . $e->getMessage());
                }
            }
        }

        // Log assignment changes
        if ($oldAssignedTo !== $request->assigned_employee_id) {
            $assignedEmployee = $request->assigned_employee_id ? Employee::find($request->assigned_employee_id) : null;
            $assignmentNote = $assignedEmployee
                ? "Assigned to {$assignedEmployee->name}"
                : "Unassigned";

            $currentEmployee = Employee::first();
            if ($currentEmployee) {
                ComplaintLog::create([
                    'complaint_id' => $complaint->id,
                    'action_by' => $currentEmployee->id,
                    'action' => 'assignment_changed',
                    'remarks' => $assignmentNote,
                ]);
            }

            // Send Notification to the House (User) for assignment
            if ($complaint->house) {
                try {
                    $status = $request->assigned_employee_id ? 'assigned' : 'unassigned';
                    $complaint->house->notify(new \App\Notifications\ComplaintStatusUpdated($complaint, $status));
                } catch (\Exception $e) {
                    Log::error('Notification Failed in update() assignment: ' . $e->getMessage());
                }
            }
        }

        if ($request->filled('redirect_to')) {
            return redirect($request->redirect_to)
                ->with('success', 'Complaint updated successfully.');
        }

        return redirect()->route('admin.complaints.index')
            ->with('success', 'Complaint updated successfully.');
    }

    /**
     * Remove the specified complaint
     */
    public function destroy(Complaint $complaint)
    {
        try {
            // Soft delete - no need to check for related records as soft delete preserves them
            // Also no need to manually delete related records as they will be soft deleted too
            $complaint->delete(); // This will now soft delete due to SoftDeletes trait

            return redirect()->route('admin.complaints.index')
                ->with('success', 'Complaint deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting complaint: ' . $e->getMessage());
        }
    }

    /**
     * Assign complaint to employee
     */
    public function assign(Request $request, Complaint $complaint)
    {
        $validator = Validator::make($request->all(), [
            'assigned_employee_id' => 'required|exists:employees,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $employee = Employee::find($request->assigned_employee_id);

        $complaint->update([
            'assigned_employee_id' => $request->assigned_employee_id,
            'status' => 'assigned',
        ]);

        $currentEmployee = Employee::first();
        if ($currentEmployee) {
            ComplaintLog::create([
                'complaint_id' => $complaint->id,
                'action_by' => $currentEmployee->id,
                'action' => 'assigned',
                'remarks' => "Assigned to {$employee->name}. " . ($request->notes ?? ''),
            ]);
        }

        // Send Notification to the House (User)
        if ($complaint->house) {
            try {
                $complaint->house->notify(new \App\Notifications\ComplaintStatusUpdated($complaint, 'assigned'));
            } catch (\Exception $e) {
                Log::error('Notification Failed in assign(): ' . $e->getMessage());
            }
        }

        return redirect()->back()
            ->with('success', 'Complaint assigned successfully.');
    }

    /**
     * Update complaint status
     */
    public function updateStatus(Request $request, Complaint $complaint)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:new,assigned,in_progress,resolved,work_performa,maint_performa,work_priced_performa,maint_priced_performa,product_na,un_authorized,pertains_to_ge_const_isld,barak_damages',
            'notes' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);


        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first() ?: 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator);
        }

        $oldStatus = $complaint->status;

        // Get remarks - prefer remarks field, fallback to notes
        $remarks = $request->input('remarks') ?: $request->input('notes') ?: '';

        // Set closed_at when status becomes 'addressed', but only if it's not already set
        $updateData = [
            'status' => $request->status,
        ];

        if ($request->status === 'resolved' && !$complaint->closed_at) {
            // Get current time in Asia/Karachi timezone
            $nowKarachi = \Carbon\Carbon::now('Asia/Karachi');
            // Convert to UTC for database storage (Asia/Karachi is UTC+5)
            // The time value should represent the same moment, just in UTC
            $updateData['closed_at'] = $nowKarachi->copy()->utc();
        } elseif ($request->status !== 'resolved') {
            // If status is changed from addressed to something else, clear closed_at
            $updateData['closed_at'] = null;
        }

        // Use direct DB update to ensure status is saved correctly
        DB::table('complaints')
            ->where('id', $complaint->id)
            ->update($updateData);

        // Refresh the complaint model to get updated data
        $complaint->refresh();


        $currentEmployee = Employee::first();
        if ($currentEmployee) {
            // Initialize log remarks with status change message
            $statusDisplay = $request->status === 'resolved' ? 'addressed' : $request->status;
            $oldStatusDisplay = $oldStatus === 'resolved' ? 'addressed' : $oldStatus;
            $logRemarks = "Status changed from {$oldStatusDisplay} to {$statusDisplay}";

            if ($remarks) {
                $logRemarks .= ". Remarks: " . $remarks;
            }
            ComplaintLog::create([
                'complaint_id' => $complaint->id,
                'action_by' => $currentEmployee->id,
                'action' => 'status_changed',
                'remarks' => $logRemarks,
            ]);
        }

        // Send Notification to the House (User)
        // Ensure we load the house relationship
        $house = $complaint->house;
        if ($house) {
            try {
                $house->notify(new \App\Notifications\ComplaintStatusUpdated($complaint, $request->status));
            } catch (\Exception $e) {
                // Log error but don't fail the written request
                \Illuminate\Support\Facades\Log::error('Notification Failed: ' . $e->getMessage());
            }
        }

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            // Get the updated status directly from database to ensure accuracy
            $updatedStatus = DB::table('complaints')->where('id', $complaint->id)->value('status');
            $updatedClosedAt = DB::table('complaints')->where('id', $complaint->id)->value('closed_at');

            return response()->json([
                'success' => true,
                'message' => 'Complaint status updated successfully.',
                'complaint' => [
                    'id' => $complaint->id,
                    'status' => $updatedStatus,
                    'old_status' => $oldStatus,
                    'closed_at' => $updatedClosedAt ? \Carbon\Carbon::parse($updatedClosedAt)->format('d-m-Y H:i:s') : null,
                ]
            ]);
        }

        return redirect()->back()
            ->with('success', 'Complaint status updated successfully.');
    }

    /**
     * Add notes to complaint
     */
    public function addNotes(Request $request, Complaint $complaint)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $currentEmployee = Employee::first();
        if ($currentEmployee) {
            ComplaintLog::create([
                'complaint_id' => $complaint->id,
                'action_by' => $currentEmployee->id,
                'action' => 'note_added',
                'remarks' => $request->notes,
            ]);
        }

        return redirect()->back()
            ->with('success', 'Notes added successfully.');
    }

    /**
     * Get complaint statistics
     */
    public function getStatistics(Request $request)
    {
        $period = $request->get('period', '30'); // days

        $stats = [
            'total' => Complaint::where('created_at', '>=', now()->subDays($period))->count(),
            'new' => Complaint::where('created_at', '>=', now()->subDays($period))->where('status', 'new')->count(),
            'assigned' => Complaint::where('created_at', '>=', now()->subDays($period))->where('status', 'assigned')->count(),
            'in_progress' => Complaint::where('created_at', '>=', now()->subDays($period))->where('status', 'in_progress')->count(),
            'addressed' => Complaint::where('created_at', '>=', now()->subDays($period))->where('status', 'resolved')->count(),
            'work_performa' => Complaint::where('created_at', '>=', now()->subDays($period))->where('status', 'work_performa')->count(),
            'maint_performa' => Complaint::where('created_at', '>=', now()->subDays($period))->where('status', 'maint_performa')->count(),
            'work_priced_performa' => Complaint::where('created_at', '>=', now()->subDays($period))->where('status', 'work_priced_performa')->count(),
            'maint_priced_performa' => Complaint::where('created_at', '>=', now()->subDays($period))->where('status', 'maint_priced_performa')->count(),
            'product_na' => Complaint::where('created_at', '>=', now()->subDays($period))->where('status', 'product_na')->count(),
            'un_authorized' => Complaint::where('created_at', '>=', now()->subDays($period))->where('status', 'un_authorized')->count(),
            'pertains_to_ge_const_isld' => Complaint::where('created_at', '>=', now()->subDays($period))->where('status', 'pertains_to_ge_const_isld')->count(),
            'overdue' => Complaint::overdue()->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get complaint chart data
     */
    public function getChartData(Request $request)
    {
        $period = $request->get('period', '30'); // days

        $data = Complaint::where('created_at', '>=', now()->subDays($period))
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        return response()->json($data);
    }

    /**
     * Get complaints by type
     */
    public function getByType(Request $request)
    {
        $period = $request->get('period', '30'); // days

        $data = Complaint::where('created_at', '>=', now()->subDays($period))
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->get();

        return response()->json($data);
    }

    /**
     * Get overdue complaints
     */
    public function getOverdue(Request $request)
    {
        $days = $request->get('days', 7);

        $overdue = Complaint::overdue($days)
            ->with(['house', 'assignedEmployee'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($overdue);
    }

    /**
     * Get employee performance
     */
    public function getEmployeePerformance(Request $request)
    {
        $period = $request->get('period', '30'); // days

        $performance = Complaint::where('created_at', '>=', now()->subDays($period))
            ->whereNotNull('assigned_employee_id')
            ->selectRaw('assigned_employee_id, COUNT(*) as total_complaints, 
                SUM(CASE WHEN status = "resolved" THEN 1 ELSE 0 END) as addressed_complaints,
                SUM(CASE WHEN status = "work_performa" THEN 1 ELSE 0 END) as work_performa_count,
                SUM(CASE WHEN status = "maint_performa" THEN 1 ELSE 0 END) as maint_performa_count,
                SUM(CASE WHEN status = "work_priced_performa" THEN 1 ELSE 0 END) as work_priced_performa_count,
                SUM(CASE WHEN status = "maint_priced_performa" THEN 1 ELSE 0 END) as maint_priced_performa_count,
                SUM(CASE WHEN status = "product_na" THEN 1 ELSE 0 END) as product_na_count,
                SUM(CASE WHEN status = "un_authorized" THEN 1 ELSE 0 END) as un_authorized_count,
                SUM(CASE WHEN status = "pertains_to_ge_const_isld" THEN 1 ELSE 0 END) as pertains_to_ge_const_isld_count,
                AVG(CASE WHEN status = "resolved" THEN TIMESTAMPDIFF(HOUR, created_at, updated_at) ELSE NULL END) as avg_resolution_time')
            ->groupBy('assigned_employee_id')
            ->with('assignedEmployee')
            ->get();

        return response()->json($performance);
    }

    /**
     * Print complaint slip
     */
    public function printSlip(Complaint $complaint)
    {
        $complaint->load(['assignedEmployee.designation', 'attachments', 'house', 'category', 'city', 'sector']);

        return view('admin.complaints.print-slip', compact('complaint'));
    }

    /**
     * Bulk actions on complaints
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:assign,change_status,change_priority,delete',
            'complaint_ids' => 'required|array|min:1',
            'complaint_ids.*' => 'exists:complaints,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $complaintIds = $request->complaint_ids;
        $action = $request->action;

        switch ($action) {
            case 'assign':
                $validator = Validator::make($request->all(), [
                    'assigned_employee_id' => 'required|exists:employees,id',
                ]);

                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator);
                }

                Complaint::whereIn('id', $complaintIds)->update([
                    'assigned_employee_id' => $request->assigned_employee_id,
                    'status' => 'assigned',
                ]);
                $message = 'Selected complaints assigned successfully.';
                break;

            case 'change_status':
                $validator = Validator::make($request->all(), [
                    'status' => 'required|in:new,assigned,in_progress,resolved,work_priced_performa,maint_priced_performa,product_na,un_authorized,pertains_to_ge_const_isld',
                ]);

                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator);
                }

                // Set closed_at when status becomes 'addressed', but only if not already set
                if ($request->status === 'resolved') {
                    $nowKarachi = \Carbon\Carbon::now('Asia/Karachi');
                    Complaint::whereIn('id', $complaintIds)
                        ->whereNull('closed_at')
                        ->update([
                            'status' => $request->status,
                            'closed_at' => $nowKarachi->utc(),
                        ]);

                    // Update status for complaints that already have closed_at
                    Complaint::whereIn('id', $complaintIds)
                        ->whereNotNull('closed_at')
                        ->update([
                            'status' => $request->status,
                        ]);
                } else {
                    // If status is changed from addressed to something else, clear closed_at
                    Complaint::whereIn('id', $complaintIds)->update([
                        'status' => $request->status,
                        'closed_at' => null,
                    ]);
                }
                $message = 'Selected complaints status updated successfully.';
                break;

            case 'change_priority':
                $validator = Validator::make($request->all(), [
                    'priority' => 'required|in:low,medium,high',
                ]);

                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator);
                }

                Complaint::whereIn('id', $complaintIds)->update(['priority' => $request->priority]);
                $message = 'Selected complaints priority updated successfully.';
                break;

            case 'delete':
                // Check for related records
                $complaintsWithRecords = Complaint::whereIn('id', $complaintIds)
                    ->where(function ($q) {
                        $q->whereHas('spareParts')
                            ->orWhereHas('spareApprovals');
                    })
                    ->count();

                if ($complaintsWithRecords > 0) {
                    return redirect()->back()
                        ->with('error', 'Some complaints cannot be deleted due to existing related records.');
                }

                Complaint::whereIn('id', $complaintIds)->delete();
                $message = 'Selected complaints deleted successfully.';
                break;
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Add spare parts to complaint with automatic stock deduction
     */
    public function addSpareParts(Request $request, Complaint $complaint)
    {
        $validator = Validator::make($request->all(), [
            'spare_parts' => 'required|array|min:1',
            'spare_parts.*.spare_id' => 'required|exists:spares,id',
            'spare_parts.*.quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $currentEmployee = Employee::first();
            if (!$currentEmployee) {
                throw new \Exception('Employee record not found');
            }

            $totalCost = 0;
            $usedParts = [];

            foreach ($request->spare_parts as $part) {
                $spare = Spare::find($part['spare_id']);

                if (!$spare) {
                    throw new \Exception("Spare part not found: {$part['spare_id']}");
                }

                // Create complaint spare record
                $complaintSpare = ComplaintSpare::create([
                    'complaint_id' => $complaint->id,
                    'spare_id' => $spare->id,
                    'quantity' => $part['quantity'],
                    'used_by' => $currentEmployee->id,
                    'used_at' => now(),
                ]);

                // Also update the main complaints table if it's the first spare part
                if (!$complaint->spare_id) {
                    $complaint->update([
                        'spare_id' => $spare->id,
                        'spare_quantity' => $part['quantity'],
                        'spare_used_by' => $currentEmployee->id,
                        'spare_used_at' => now(),
                    ]);
                }

                // No stock deduction here; happens on approval

                $totalCost += $spare->unit_price * $part['quantity'];
                $usedParts[] = "{$spare->item_name} (Qty: {$part['quantity']})";
            }

            // Log the spare parts usage
            ComplaintLog::create([
                'complaint_id' => $complaint->id,
                'action_by' => $currentEmployee->id,
                'action' => 'spare_parts_added',
                'remarks' => 'Added spare parts: ' . implode(', ', $usedParts) . '. Total cost: PKR ' . number_format($totalCost, 2) . ($request->remarks ? '. Remarks: ' . $request->remarks : ''),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Spare parts added successfully. Total cost: PKR ' . number_format($totalCost, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to add spare parts: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Export complaints data
     */
    public function export(Request $request)
    {
        $query = Complaint::with(['house', 'assignedEmployee']);

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('complaint_type') && $request->complaint_type) {
            $category = $request->complaint_type;
            if (is_numeric($category)) {
                $query->where('category_id', $category);
            } else {
                $query->whereHas('category', function ($q) use ($category) {
                    $q->where('name', $category);
                });
            }
        }

        $complaints = $query->get();

        // Implementation for export
        return response()->json(['message' => 'Export functionality not implemented yet']);
    }

}
