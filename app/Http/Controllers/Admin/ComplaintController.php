<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Client;
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
        $query = Complaint::with(['client', 'assignedEmployee', 'city', 'sector', 'attachments', 'spareParts.spare', 'spareApprovals']);

        // Apply location-based filtering
        $this->filterComplaintsByLocation($query, $user);

        // Search functionality - by Name and ID
        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                // Search by client name
                $q->whereHas('client', function ($clientQuery) use ($search) {
                    $clientQuery->where('client_name', 'like', "%{$search}%");
                })
                    // Search by title
                    ->orWhere('title', 'like', "%{$search}%")
                    // Search by description
                    ->orWhere('description', 'like', "%{$search}%");

                // If search is numeric, also search by ID (handles both actual ID and formatted complaint_id)
                if (is_numeric($search)) {
                    $numericSearch = (int) $search;
                    // Search by actual ID
                    $q->orWhere('id', $numericSearch);
                    // Search by ID modulo 10000 (for formatted complaint_id like 0123)
                    $q->orWhereRaw('(id % 10000) = ?', [$numericSearch]);
                }
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $statusValue = $request->status;

            // Handle work_priced_performa and maint_priced_performa filters
            // waiting_for_authority removed - only check direct status match
            if ($statusValue === 'work_priced_performa') {
                $query->where('status', 'work_priced_performa');
            } elseif ($statusValue === 'maint_priced_performa') {
                $query->where('status', 'maint_priced_performa');
            } else {
                // For other statuses, use direct filter
                $query->where('status', $statusValue);
            }
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        // Filter by assigned employee
        if ($request->has('assigned_employee_id') && $request->assigned_employee_id) {
            $query->where('assigned_employee_id', $request->assigned_employee_id);
        }

        // Filter by client (apply location filter)
        if ($request->has('client_id') && $request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Order by ID descending (3, 2, 1...) - newest/highest ID first
        // Clear any existing orders and set explicit descending order
        $query->with(['client', 'assignedEmployee', 'house'])
              ->reorder()
              ->orderBy('id', 'desc');
        $complaints = $query->paginate(15);

        // Filter employees by location
        $employeesQuery = Employee::where('status', 'active');
        $this->filterEmployeesByLocation($employeesQuery, $user);
        $employees = $employeesQuery->get();

        $categories = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::orderBy('name')->pluck('name')
            : collect();

        return view('admin.complaints.index', compact('complaints', 'employees', 'categories'));
    }

    /**
     * Show the form for creating a new complaint
     */
    public function create()
    {
        // Clear any old input data to ensure clean form
        request()->session()->forget('_old_input');

        $employeesQuery = Employee::where('status', 'active')->orderBy('name');
        $this->filterEmployeesByLocation($employeesQuery, Auth::user());
        $employees = $employeesQuery->get();
        $categories = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::orderBy('name')->pluck('name')
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
            $defaultCityId = $authUser->city_id;
            $defaultSectorId = $authUser->sector_id;
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
    public function store(Request $request)
    {
        // Debug: Log the request data
        Log::info('Complaint creation request', [
            'all_data' => $request->all(),
            'title' => $request->title,
            'title_other' => $request->title_other,
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type')
        ]);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'title_other' => 'nullable|string|max:255',
            'client_name' => 'nullable|string|max:255',
            // Allow any category string (column changed to VARCHAR)
            'category' => 'required|string|max:100',
            'priority' => 'required|in:low,medium,high,urgent,emergency',
            'availability_time' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'assigned_employee_id' => 'nullable|exists:employees,id',
            // Status removed from form - will be managed in approvals view, default to 'new'
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240', // 10MB max
            'city_id' => 'nullable|exists:cities,id',
            'sector_id' => 'nullable|exists:sectors,id',
            'house_id' => 'required|exists:houses,id',
            'address' => 'nullable|string|max:500',
            'email' => 'nullable|string|max:150',
            'phone' => 'nullable|string|min:11|max:50',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Start database transaction
        DB::beginTransaction();

        try {
            // Get city and sector names from IDs if provided (for client table)
            $cityName = null;
            $sectorName = null;

            if ($request->city_id) {
                $city = City::find($request->city_id);
                $cityName = $city ? $city->name : null;
            }

            if ($request->sector_id) {
                $sector = Sector::find($request->sector_id);
                $sectorName = $sector ? $sector->name : null;
            }

            // Determine client name - if provided use it, otherwise use house username
            $clientName = trim($request->client_name);
            if (empty($clientName) && $request->house_id) {
                $house = House::find($request->house_id);
                $clientName = $house ? $house->username : 'Unknown';
            } elseif (empty($clientName)) {
                $clientName = 'Unknown';
            }

            // Find or create client by name
            $client = Client::firstOrCreate(
                ['client_name' => $clientName],
                [
                    'contact_person' => $request->input('contact_person') ?: $clientName,
                    'email' => $request->input('email', ''),
                    'phone' => $request->input('phone', '') ?: 'N/A',
                    'city' => $cityName ?? '',
                    'sector' => $sectorName ?? '',
                    'address' => $request->input('address'),
                    'status' => 'active',
                ]
            );

            // Handle custom title from "Other" option
            // JavaScript sets title_other value in hidden input with name="title"
            // But if title is still "other", use title_other field
            $finalTitle = $request->title;

            // If title is "other", check for title_other field
            if ($finalTitle === 'other' || strtolower($finalTitle) === 'other') {
                if ($request->has('title_other') && !empty(trim($request->title_other))) {
                    $finalTitle = trim($request->title_other);
                } elseif ($request->has('title') && $request->title !== 'other') {
                    // JavaScript might have already set custom title in title field
                    $finalTitle = trim($request->title);
                }
            }

            // Final fallback - if still "other", try to get from title_other
            if (empty($finalTitle) || strtolower($finalTitle) === 'other') {
                $finalTitle = $request->input('title_other') ? trim($request->input('title_other')) : 'other';
            }

            Log::info('Final title being saved', [
                'final_title' => $finalTitle,
                'original_title' => $request->title,
                'title_other' => $request->title_other
            ]);

            $complaint = Complaint::create([
                'title' => $finalTitle,
                'client_id' => $client->id,
                'house_id' => $request->house_id ?: null,
                'city_id' => $request->city_id ?: null,
                'sector_id' => $request->sector_id ?: null,
                'category' => $request->category,
                'priority' => $request->priority,
                'availability_time' => $request->availability_time,
                'description' => $request->description,
                'assigned_employee_id' => $request->assigned_employee_id ?: null,
                'status' => 'assigned', // Default to 'assigned' - no performa type selected initially
            ]);

            // Handle file attachments
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

            // Log the complaint creation
            // Find the employee associated with the current user
            $currentEmployee = Employee::first();

            if ($currentEmployee) {
                ComplaintLog::create([
                    'complaint_id' => $complaint->id,
                    'action_by' => $currentEmployee->id,
                    'action' => 'created',
                    'remarks' => 'Complaint created',
                ]);
            }

            // Note: Approval performa is automatically created by Complaint model's boot() method
            // No need to create it here to avoid duplicates

            // Commit the transaction
            DB::commit();

            return redirect()->route('admin.complaints.index')
                ->with('success', 'Complaint created successfully.');

        } catch (\Exception $e) {
            // Rollback the transaction on any error
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
        try {
            $complaint->load([
                'client',
                'assignedEmployee',
                'city',
                'sector',
                'attachments',
                'house',
                'logs.actionBy',
                'spareParts.spare',
                'spareParts.usedBy',
                'spareApprovals.items.spare',
                'stockLogs.spare',
                'feedback.enteredBy'
            ]);

            // Check if format=html is requested, return HTML even for AJAX
            if (request()->get('format') === 'html') {
                return view('admin.complaints.show', compact('complaint'));
            }

            if (request()->ajax() || request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
                // Exclude state from client data in JSON response for modal
                $complaintData = $complaint->toArray();
                if (isset($complaintData['client']) && is_array($complaintData['client']) && isset($complaintData['client']['state'])) {
                    unset($complaintData['client']['state']);
                }

                return response()->json([
                    'success' => true,
                    'complaint' => $complaintData
                ]);
            }

            return view('admin.complaints.show', compact('complaint'));
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error in ComplaintController@show: ' . $e->getMessage(), [
                'complaint_id' => $complaint->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            // Check if format=html is requested, return HTML error
            if (request()->get('format') === 'html') {
                return response('<div class="text-center py-5 text-danger">Error loading complaint details: ' . htmlspecialchars($e->getMessage()) . '. Please try again.</div>', 500);
            }

            // Return JSON error for AJAX requests
            if (request()->ajax() || request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Error loading complaint details: ' . $e->getMessage()
                ], 500);
            }

            // For regular requests, redirect back with error
            return redirect()->back()->with('error', 'Error loading complaint details: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the complaint
     */
    public function edit(Complaint $complaint)
    {
        $complaint->load(['spareParts.spare']);

        $employeesQuery = Employee::where('status', 'active')->orderBy('name');
        $this->filterEmployeesByLocation($employeesQuery, Auth::user());
        $employees = $employeesQuery->get();
        $categories = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::orderBy('name')->pluck('name')
            : collect();

        // Provide cities/sectors for dropdowns (match create() UX)
        $citiesQuery = City::where('status', 'active')->orderBy('id', 'asc');
        $userCityIds = $this->getUserCityIds(Auth::user());
        if ($userCityIds !== null) {
            $citiesQuery->whereIn('id', $userCityIds);
        }
        $cities = Schema::hasTable('cities') ? $citiesQuery->get() : collect();

        // Get default city_id and sector_id from complaint
        $defaultCityId = $complaint->city_id;
        $defaultSectorId = $complaint->sector_id;
        $sectors = collect();

        // Load sectors for the selected city
        if ($defaultCityId && Schema::hasTable('sectors')) {
            $sectors = Sector::where('city_id', $defaultCityId)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        }

        // Get houses filtered by location
        $housesQuery = House::where('status', 'active')->orderBy('username');
        $this->filterHousesByLocation($housesQuery, Auth::user());
        $houses = $housesQuery->get();

        return view('admin.complaints.edit', compact(
            'complaint',
            'employees',
            'categories',
            'cities',
            'sectors',
            'defaultCityId',
            'defaultSectorId',
            'houses'
        ));
    }

    /**
     * Update the specified complaint
     */
    public function update(Request $request, Complaint $complaint)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'title_other' => 'nullable|string|max:255',
            'client_name' => 'nullable|string|max:255',
            // Allow any category string (column changed to VARCHAR)
            'category' => 'required|string|max:100',
            'priority' => 'required|in:low,medium,high,urgent,emergency',
            'availability_time' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'assigned_employee_id' => 'nullable|exists:employees,id',
            // Status removed from form - will be managed in approvals view, keep existing status
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
            // Product (spare) optional now
            'spare_parts' => 'nullable|array',
            'spare_parts.0.spare_id' => 'nullable|exists:spares,id',
            'spare_parts.0.quantity' => 'nullable|integer|min:1',
            'city_id' => 'nullable|exists:cities,id',
            'sector_id' => 'nullable|exists:sectors,id',
            'house_id' => 'required|exists:houses,id',
            'address' => 'nullable|string|max:500',
            'email' => 'nullable|string|max:150',
            'phone' => 'nullable|string|min:11|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Resolve city/sector names from IDs if provided (dropdowns)
        $cityName = null;
        $sectorName = null;
        if ($request->city_id) {
            $city = City::find($request->city_id);
            $cityName = $city?->name;
        }
        if ($request->sector_id) {
            $sector = Sector::find($request->sector_id);
            $sectorName = $sector?->name;
        }

        // Fallback to text inputs if present
        $cityName = $cityName ?? ($request->city ?: null);
        $sectorName = $sectorName ?? ($request->sector ?: null);

        // Determine client name - if provided use it, otherwise use house username
        $clientName = trim($request->client_name);
        if (empty($clientName) && $request->house_id) {
            $house = House::find($request->house_id);
            $clientName = $house ? $house->username : 'Unknown';
        } elseif (empty($clientName)) {
            $clientName = 'Unknown';
        }

        // Find or create client by name and update details
        $client = Client::firstOrCreate(
            ['client_name' => $clientName],
            [
                'contact_person' => $request->input('contact_person') ?: $clientName,
                'email' => $request->input('email', ''),
                'phone' => $request->input('phone', ''),
                'address' => $request->input('address'),
                'city' => $cityName,
                'sector' => $sectorName,
                'status' => 'active',
            ]
        );
        // Update existing client with provided fields
        $client->fill([
            'contact_person' => $request->input('contact_person') ?: $clientName,
            'email' => $request->input('email', $client->email),
            'phone' => $request->input('phone', $client->phone),
            'address' => $request->input('address', $client->address),
            'city' => $cityName ?? $client->city,
            'sector' => $sectorName ?? $client->sector,
        ])->save();

        $oldStatus = $complaint->status;
        $oldAssignedTo = $complaint->assigned_employee_id;

        // Use title_other if title is "other", otherwise use title
        // Check both title_other field and if title itself is "other"
        if ($request->title === 'other') {
            $finalTitle = $request->title_other ? trim($request->title_other) : 'other';
        } else {
            $finalTitle = $request->title;
        }

        // If finalTitle is still "other" or empty, use title_other if available
        if (empty($finalTitle) || $finalTitle === 'other') {
            $finalTitle = $request->title_other ? trim($request->title_other) : 'other';
        }

        $complaint->update([
            'title' => $finalTitle,
            'client_id' => $client->id,
            'house_id' => $request->house_id ?: null,
            'city_id' => $request->city_id ?: null,
            'sector_id' => $request->sector_id ?: null,
            'category' => $request->category,
            'priority' => $request->priority,
            'availability_time' => $request->availability_time,
            'description' => $request->description,
            'assigned_employee_id' => $request->assigned_employee_id ?: null,
            // Status not updated here - will be managed in approvals view
            // Keep existing status and closed_at
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
            ->with(['client', 'assignedEmployee'])
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
        $complaint->load(['client', 'assignedEmployee', 'attachments', 'house']);

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
        $query = Complaint::with(['client', 'assignedEmployee']);

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
            $query->where('category', $request->complaint_type);
        }

        $complaints = $query->get();

        // Implementation for export
        return response()->json(['message' => 'Export functionality not implemented yet']);
    }

}
