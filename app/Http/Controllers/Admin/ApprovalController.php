<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpareApprovalPerforma;
use App\Models\SpareApprovalItem;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\Spare;
use App\Models\ComplaintCategory;
use App\Models\StockApprovalData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Traits\LocationFilterTrait;

class ApprovalController extends Controller
{
    use LocationFilterTrait;
    
    public function __construct()
    {
        // Middleware is applied in routes
    }

    /**
     * Display a listing of approval performas
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Automatically create missing approval performas for complaints that don't have them
            // This ensures all complaints appear in the approval modal
            // Only create if approval doesn't already exist to avoid duplicates
            $complaintsWithoutApprovalsQuery = Complaint::whereDoesntHave('spareApprovals');
            $this->filterComplaintsByLocation($complaintsWithoutApprovalsQuery, $user);
            $complaintsWithoutApprovals = $complaintsWithoutApprovalsQuery->get();
            if ($complaintsWithoutApprovals->count() > 0) {
                $defaultEmployee = Employee::first();
                if ($defaultEmployee) {
                    foreach ($complaintsWithoutApprovals as $complaint) {
                        try {
                            // Double check if approval doesn't exist (race condition prevention)
                            $existingApproval = SpareApprovalPerforma::where('complaint_id', $complaint->id)->first();
                            if ($existingApproval) {
                                continue; // Skip if approval already exists
                            }
                            
                            $requestedByEmployee = $complaint->assigned_employee_id 
                                ? Employee::find($complaint->assigned_employee_id)
                                : $defaultEmployee;
                            
                            if (!$requestedByEmployee) {
                                $requestedByEmployee = $defaultEmployee;
                            }
                            
                            SpareApprovalPerforma::create([
                                'complaint_id' => $complaint->id,
                                'requested_by' => $requestedByEmployee->id,
                                'status' => 'pending',
                                'remarks' => 'Auto-created for existing complaint',
                            ]);
                        } catch (\Exception $e) {
                            \Log::warning('Failed to create approval performa for complaint: ' . $complaint->id, [
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }
            }
            
            // Start with base query and join complaints table for filtering/ordering
            // Use distinct to avoid duplicates from joins
            $query = SpareApprovalPerforma::query()
                ->join('complaints', 'spare_approval_performa.complaint_id', '=', 'complaints.id')
                ->join('clients', 'complaints.client_id', '=', 'clients.id')
                ->select('spare_approval_performa.*')
                ->distinct();
            
            // Apply location-based filtering through complaint relationship
            if (!$this->canViewAllData($user)) {
                $query->whereHas('complaint', function($q) use ($user) {
                    $this->filterComplaintsByLocation($q, $user);
                });
            }

            // Search functionality - by Complaint ID only
            if ($request->has('search') && $request->search) {
                $search = trim($request->search);
                if (!empty($search)) {
                    $query->where('complaints.id', 'like', "%{$search}%");
                }
            }

            // Filter by Complaint Registration Date (From Date)
            if ($request->has('complaint_date') && $request->complaint_date) {
                $query->whereDate('complaints.created_at', '>=', $request->complaint_date);
            }

            // Filter by End Date (To Date) - works even when category is not selected
            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('complaints.created_at', '<=', $request->date_to);
            }

            // Filter by Nature (category)
            if ($request->has('category') && $request->category) {
                $query->where('complaints.category', $request->category);
            }

            // Filter by complaint status or performa_type (combined in status filter)
            if ($request->has('status') && $request->status) {
                $statusValue = $request->status;
                // Check if it's a performa_type filter (prefixed with 'performa_')
                if (strpos($statusValue, 'performa_') === 0) {
                    $performaType = str_replace('performa_', '', $statusValue);
                    $query->where('spare_approval_performa.performa_type', $performaType);
                    // Exclude "Addressed" (resolved) complaints when filtering by performa_type
                    $query->where('complaints.status', '!=', 'resolved');
                } elseif ($statusValue === 'work_priced_performa') {
                    // Handle work_priced_performa filter
                    // Only check for direct status match (waiting_for_authority removed)
                    $query->where('complaints.status', 'work_priced_performa');
                } elseif ($statusValue === 'maint_priced_performa') {
                    // Handle maint_priced_performa filter
                    // Only check for direct status match (waiting_for_authority removed)
                    $query->where('complaints.status', 'maint_priced_performa');
                } else {
                    // Regular status filter
                    $query->where('complaints.status', $statusValue);
                }
            }

            // Filter by requester or by complaint's assigned employee (using the same requested_by param)
            if ($request->has('requested_by') && $request->requested_by) {
                $employeeId = $request->requested_by;
                $query->where(function($q) use ($employeeId) {
                    $q->where('spare_approval_performa.requested_by', $employeeId)
                      ->orWhere('complaints.assigned_employee_id', $employeeId);
                });
            }

            // Filter by complaint
            if ($request->has('complaint_id') && $request->complaint_id) {
                $query->where('spare_approval_performa.complaint_id', $request->complaint_id);
            }

            // Filter by date range (for approval creation date) - only if not using complaint date filters
            if ($request->has('date_from') && $request->date_from && !$request->has('complaint_date') && !$request->has('date_to')) {
                $query->whereDate('spare_approval_performa.created_at', '>=', $request->date_from);
            }

            // Order by approval ID (descending) - newest first
            $query->orderBy('spare_approval_performa.id', 'desc');
            
            $approvals = $query->paginate(15);
            
            // Reload relationships after join (join may have affected eager loading)
            $approvals->load([
                'complaint.client',
                'complaint.assignedEmployee',
                'complaint.spareParts.spare',
                'requestedBy',
                'approvedBy',
                'items.spare'
            ]);
            
            // Check if each approval has issued stock
            foreach ($approvals as $approval) {
                $hasIssuedStock = \App\Models\SpareStockLog::where('reference_id', $approval->id)
                    ->where('change_type', 'out')
                    ->exists();
                $approval->has_issued_stock = $hasIssuedStock;
            }
            
            // Get complaints with location filtering
            $complaintsQuery = Complaint::pending()->with('client');
            $this->filterComplaintsByLocation($complaintsQuery, $user);
            $complaints = $complaintsQuery->get();
            
            // Get employees with location filtering
            $employeesQuery = Employee::where('status', 'active');
            $this->filterEmployeesByLocation($employeesQuery, $user);
            $employees = $employeesQuery->get();
            
            // Get categories for Nature filter - get from ComplaintCategory table if exists
            if (Schema::hasTable('complaint_categories')) {
                // Get categories from ComplaintCategory table
                $categories = ComplaintCategory::orderBy('name')->pluck('name');
            } else {
                // Fallback: Get categories from complaints that have approvals with location filtering
                $categoriesQuery = Complaint::join('spare_approval_performa', 'complaints.id', '=', 'spare_approval_performa.complaint_id')
                    ->join('clients', 'complaints.client_id', '=', 'clients.id')
                    ->select('complaints.category')
                    ->distinct()
                    ->whereNotNull('complaints.category');
                
                // Apply location filtering through client table
                if (!$this->canViewAllData($user)) {
                    $roleName = strtolower($user->role->role_name ?? '');
                    if ($roleName === 'garrison_engineer' && $user->city_id && $user->city) {
                        $categoriesQuery->where('clients.city', $user->city->name);
                    } elseif (in_array($roleName, ['complaint_center', 'department_staff']) && $user->sector_id && $user->sector) {
                        $categoriesQuery->where('clients.sector', $user->sector->name);
                    }
                }
                
                $categories = $categoriesQuery->pluck('category')
                    ->unique()
                    ->values();
                
                // If still empty, get from all complaints with location filtering
                if ($categories->isEmpty()) {
                    $categoriesQuery = Complaint::select('category')
                        ->distinct()
                        ->whereNotNull('category');
                    
                    // Apply location filtering
                    $this->filterComplaintsByLocation($categoriesQuery, $user);
                    
                    $categories = $categoriesQuery->pluck('category')
                        ->unique()
                        ->values();
                }
            }

            // Define all possible status labels - show all these in dropdown
            $statusLabels = [
                'assigned' => 'Assigned',
                'in_progress' => 'In-Progress',
                'resolved' => 'Addressed',
                'work_performa' => 'Work Performa',
                'maint_performa' => 'Maintenance Performa',
                'work_priced_performa' => 'Work Performa Priced',
                'maint_priced_performa' => 'Maintenance Performa Priced',
                'product_na' => 'Product N/A',
                'un_authorized' => 'Un-Authorized',
                'pertains_to_ge_const_isld' => 'Pertains to GE(N) Const Isld',
                'barak_damages' => 'Barak Damages',
            ];

            // Define performa type labels
            $performaTypeLabels = [
                'work_performa' => 'Work Performa Required',
                'maint_performa' => 'Maintenance Performa Required',
            ];

            // Build performa types collection - prefix with 'performa_' to distinguish from status values
            $performaTypes = collect($performaTypeLabels)->mapWithKeys(function($label, $type) {
                return ['performa_' . $type => $label];
            });

            // Build statuses collection for FILTER (includes performa options)
            // 1. Assigned, 2. In Progress, 3. Addressed, 
            // 4-5. Work/Maintenance Performa Required (after Addressed),
            // 6+. Rest of the statuses
            $orderedStatusesForFilter = [
                'assigned' => $statusLabels['assigned'],
                'in_progress' => $statusLabels['in_progress'],
                'resolved' => $statusLabels['resolved'],
                // Add performa required after Addressed (for filter only)
                'performa_work_performa' => $performaTypes['performa_work_performa'],
                'performa_maint_performa' => $performaTypes['performa_maint_performa'],
            ];
            
            // Add remaining statuses (excluding already added ones and work_performa/maint_performa from filter)
            foreach ($statusLabels as $key => $label) {
                if (!in_array($key, ['assigned', 'in_progress', 'resolved', 'work_performa', 'maint_performa'])) {
                    $orderedStatusesForFilter[$key] = $label;
                }
            }

            // Convert to collection and filter out any empty/null values (for filter)
            $statusesForFilter = collect($orderedStatusesForFilter)->filter(function($label, $key) {
                return !empty($label) && !empty($key) && $key !== '';
            });

            // Build statuses collection for TABLE ROWS (excludes performa options)
            $orderedStatusesForTable = [
                'assigned' => $statusLabels['assigned'],
                'in_progress' => $statusLabels['in_progress'],
                'resolved' => $statusLabels['resolved'],
            ];
            
            // Add remaining statuses (excluding already added ones)
            foreach ($statusLabels as $key => $label) {
                if (!in_array($key, ['assigned', 'in_progress', 'resolved'])) {
                    $orderedStatusesForTable[$key] = $label;
                }
            }

            // Convert to collection and filter out any empty/null values (for table rows)
            $statuses = collect($orderedStatusesForTable)->filter(function($label, $key) {
                return !empty($label) && !empty($key) && $key !== '';
            });

            // Handle AJAX requests - return only table and pagination
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                try {
                    $html = view('admin.approvals.index', compact('approvals', 'complaints', 'employees', 'categories', 'statuses', 'statusesForFilter'))->render();
                    return response()->json([
                        'success' => true,
                        'html' => $html
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error rendering approvals view for AJAX', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return response()->json([
                        'success' => false,
                        'error' => 'Error loading approvals: ' . $e->getMessage()
                    ], 500);
                }
            }

            return view('admin.approvals.index', compact('approvals', 'complaints', 'employees', 'categories', 'statuses', 'statusesForFilter'));
            
        } catch (\Exception $e) {
            \Log::error('Error in ApprovalController@index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            // Return error response for AJAX
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'error' => 'Error loading approvals: ' . $e->getMessage()
                ], 500);
            }
            
            // Return error view for regular requests
            return redirect()->route('admin.approvals.index')
                ->with('error', 'Error loading approvals: ' . $e->getMessage());
        }
    }


    /**
     * Display the specified approval performa
     */
    public function show(SpareApprovalPerforma $approval)
    {
        // Optimize: Only load necessary relationships for AJAX requests
        $isAjax = request()->ajax() || request()->wantsJson();
        
        if ($isAjax) {
            // For AJAX: Load only what's needed, skip heavy sync operations
            $approval->load([
                'complaint.client',
                'complaint.feedback.enteredBy', // Load feedback relationship
                'requestedBy',
                'approvedBy',
                'items.spare'
            ]);
        } else {
            // For HTML: Load all relationships
            $approval->load([
                'complaint.client',
                'complaint.spareParts.spare',
                'complaint.stockLogs.spare',
                'complaint.logs', // Load logs to trace historical status
                'complaint.feedback.enteredBy', // Load feedback relationship
                'requestedBy',
                'approvedBy',
                'items.spare'
            ]);
        }

        // Only sync approval items for HTML requests or if status is pending (skip for AJAX to speed up)
        if (!$isAjax && $approval->complaint && $approval->status === 'pending') {
            try {
                // Load complaint spare parts with spare relationship
                $approval->complaint->loadMissing(['spareParts.spare']);
                
                if ($approval->complaint->spareParts->count() > 0) {
                    // Get existing approval items by spare_id
                    $existingItemsBySpareId = $approval->items->keyBy('spare_id');
                    
                    foreach ($approval->complaint->spareParts as $sp) {
                        if (!$sp->spare_id) continue;
                        
                        // Check if approval item already exists for this spare
                        $existingItem = $existingItemsBySpareId->get($sp->spare_id);
                        
                        if ($existingItem) {
                            // Update existing item if quantity changed
                            if ($existingItem->quantity_requested != $sp->quantity) {
                                $existingItem->update([
                                    'quantity_requested' => (int)($sp->quantity ?? 1),
                                    'reason' => 'Updated from complaint spare usage',
                                ]);
                            }
                        } else {
                            // Create new approval item if it doesn't exist
                            \App\Models\SpareApprovalItem::create([
                                'performa_id' => $approval->id,
                                'spare_id' => $sp->spare_id,
                                'quantity_requested' => (int)($sp->quantity ?? 1),
                                'quantity_approved' => null,
                                'reason' => 'Auto-imported from complaint spare usage',
                            ]);
                        }
                    }
                    
                    // Remove approval items that are no longer in complaint spare parts
                    $complaintSpareIds = $approval->complaint->spareParts->pluck('spare_id')->filter()->toArray();
                    $approval->items()->whereNotIn('spare_id', $complaintSpareIds)->delete();
                    
                    // Reload items after sync
                    $approval->load(['items.spare']);
                }
            } catch (\Throwable $e) {
                \Log::warning('Failed to sync approval items in show()', [
                    'approval_id' => $approval->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Get previously issued stock from stock logs for this approval (optimized query)
        $issuedStock = [];
        try {
            $stockLogs = \App\Models\SpareStockLog::where('reference_id', $approval->id)
                ->where('change_type', 'out')
                ->with('spare:id,item_name')
                ->orderBy('created_at', 'desc')
                ->get(['id', 'spare_id', 'quantity', 'created_at', 'remarks']);
            
            foreach ($stockLogs as $log) {
                if ($log->spare) {
                    $issuedStock[] = [
                        'spare_id' => $log->spare_id,
                        'spare_name' => $log->spare->item_name ?? 'N/A',
                        'quantity_issued' => (int)$log->quantity,
                        'issued_at' => $log->created_at ? $log->created_at->format('M d, Y H:i') : null,
                        'remarks' => $log->remarks ?? null
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch issued stock logs', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage()
            ]);
        }

        if (request()->get('format') === 'html') {
            return view('admin.approvals.show', compact('approval'));
        }

        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'approval' => [
                    'id' => $approval->id,
                    'status' => $approval->status,
                    'performa_type' => $approval->performa_type,
                    'authority_number' => $approval->authority_number,
                    // waiting_for_authority removed
                    'created_at' => $approval->created_at ? $approval->created_at->format('M d, Y H:i') : null,
                    'approved_at' => $approval->approved_at ? $approval->approved_at->format('M d, Y H:i') : null,
                    'remarks' => $approval->remarks,
                    'complaint_id' => $approval->complaint_id,
                    'complaint' => $approval->complaint ? [
                        'id' => $approval->complaint->id,
                        'category' => $approval->complaint->category ?? null,
                        'title' => $approval->complaint->title ?? 'N/A',
                        'sector' => $approval->complaint->client->sector ?? null,
                        'city' => $approval->complaint->client->city ?? null,
                    ] : null,
                    'client_name' => $approval->complaint->client ? $approval->complaint->client->client_name : 'Deleted Client',
                    'complaint_title' => $approval->complaint->title ?? 'N/A',
                    'requested_by_name' => $approval->requestedBy->name ?? 'N/A',
                    'approved_by_name' => $approval->approvedBy->name ?? null,
                    'items' => $approval->items->map(function($item) {
                        return [
                            'id' => $item->id,
                            'spare_id' => $item->spare_id ?? null,
                            'spare_name' => $item->spare->item_name ?? 'N/A',
                            'category' => $item->spare->category ?? 'N/A',
                            'quantity_requested' => (int)$item->quantity_requested,
                            'quantity_approved' => $item->quantity_approved !== null ? (int)$item->quantity_approved : null,
                            'available_stock' => (int)($item->spare->stock_quantity ?? 0),
                            'unit_price' => $item->spare->unit_price ?? 0
                        ];
                    }),
                    'issued_stock' => $issuedStock
                ]
            ]);
        }

        return view('admin.approvals.show', compact('approval'));
    }

    /**
     * Approve the specified approval performa
     */
    public function approve(Request $request, SpareApprovalPerforma $approval)
    {
        // Load relationships
        $approval->load(['items.spare', 'complaint.client', 'requestedBy']);
        
        $validator = Validator::make($request->all(), [
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator);
        }

        // Map of approved quantities from request (if provided per item)
        $approvedInput = collect($request->input('items', []))
            ->mapWithKeys(function($data, $key) {
                $id = (int)$key;
                $qty = isset($data['quantity_approved']) ? (int)$data['quantity_approved'] : null;
                return [$id => $qty];
            });

        // Check stock availability and adjust quantities if insufficient
        $unavailableItems = [];
        $adjustedItems = [];
        
        foreach ($approval->items as $item) {
            // Get spare directly to avoid relationship issues
            $spare = \App\Models\Spare::find($item->spare_id);
            
            if (!$spare) {
                \Log::error('Spare not found for item ID: ' . $item->id . ', Spare ID: ' . $item->spare_id);
                $unavailableItems[] = 'Unknown Spare (ID: ' . $item->spare_id . ')';
                continue;
            }
            
            $requestedQty = (int)$item->quantity_requested;
            $approvedQty = $approvedInput->get($item->id) !== null
                ? max(0, (int)$approvedInput->get($item->id))
                : $requestedQty; // Use requested if not specified
            
            $availableQty = (int)$spare->stock_quantity;
            
            // Adjust quantity to available stock if insufficient
            if ($availableQty < $approvedQty) {
                if ($availableQty > 0) {
                    // Give available quantity (partial approval)
                    $adjustedItems[] = [
                        'item_name' => $spare->item_name,
                        'requested' => $approvedQty,
                        'available' => $availableQty,
                        'item_id' => $item->id
                    ];
                    // Update approved quantity to available
                    $approvedInput[$item->id] = $availableQty;
                } else {
                    // No stock available at all
                    $unavailableItems[] = $spare->item_name . ' (Requested: ' . $approvedQty . ', Available: 0)';
                }
            }
        }
        
        // If items are completely unavailable (zero stock), show error
        if (!empty($unavailableItems)) {
            $message = 'Cannot approve: ' . implode(', ', $unavailableItems) . ' have zero stock available.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }
            return redirect()->back()->with('error', $message);
        }

        try {
            DB::beginTransaction();

            // Update approval status
            $employee = Employee::first();
            if (!$employee) {
                throw new \Exception('No employee record found');
            }
            
            // Build adjusted items message for remarks
            $adjustmentMessage = '';
            if (!empty($adjustedItems)) {
                $adjustments = [];
                foreach ($adjustedItems as $adj) {
                    $adjustments[] = $adj['item_name'] . ' (Requested: ' . $adj['requested'] . ', Given: ' . $adj['available'] . ')';
                }
                $adjustmentMessage = ' Adjusted quantities: ' . implode(', ', $adjustments) . '.';
            }
            
            // Prepare final remarks - user remarks take priority, remove auto-generated text
            $userRemarks = trim($request->remarks ?? '');
            $finalRemarks = '';
            
            // If user provided remarks, use only user remarks (don't keep auto-generated text)
            if ($userRemarks) {
                $finalRemarks = $userRemarks;
            }
            
            // Add adjustment message if any
            if ($adjustmentMessage) {
                $finalRemarks = ($finalRemarks ? $finalRemarks . ' ' : '') . trim($adjustmentMessage);
            }
            
            // Update approval status and remarks
            $updateData = [
                'status' => 'approved',
                'approved_by' => $employee->id,
                'approved_at' => now(),
            ];
            
            // Update remarks if user provided remarks or if there are adjustments
            // This will replace the auto-generated remarks
            if ($finalRemarks) {
                $updateData['remarks'] = $finalRemarks;
            }
            
            $approval->update($updateData);
            
            // Deduct approved quantities now (no prior deduction at complaint stage)
            foreach ($approval->items as $item) {
                $spare = \App\Models\Spare::find($item->spare_id);
                if ($spare) {
                    $qtyToUse = $approvedInput->get($item->id) !== null
                        ? max(0, (int)$approvedInput->get($item->id))
                        : (int)$item->quantity_requested;
                    
                    $availableQty = (int)$spare->stock_quantity;
                    $requestedQty = (int)$item->quantity_requested;
                    
                    // Update reason if quantity was adjusted
                    $reason = $item->reason;
                    if ($qtyToUse < $requestedQty && $qtyToUse > 0) {
                        $reason = 'Insufficient stock: Requested ' . $requestedQty . ', Approved ' . $qtyToUse . ' (Available: ' . $availableQty . ')';
                    } elseif ($qtyToUse == 0) {
                        $reason = 'Zero stock: Requested ' . $requestedQty . ', Available 0';
                    }

                    // Persist approved quantity and updated reason
                    $item->update([
                        'quantity_approved' => $qtyToUse,
                        'reason' => $reason
                    ]);
                    
                    // Deduct exactly the approved quantity (only if > 0)
                    if ($qtyToUse > 0) {
                        $spare->removeStock(
                            $qtyToUse,
                            "Approved for complaint #{$approval->complaint->getTicketNumberAttribute()}",
                            $approval->complaint_id
                        );
                    }
                }
            }

            DB::commit();

            $message = 'Approval performa approved successfully.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            $message = 'Failed to approve: ' . $e->getMessage();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 500);
            }
            return redirect()->back()->with('error', $message);
        }
    }

    /**
     * Save approval with performa info when waiting for authority
     */
    public function saveWithPerforma(Request $request, SpareApprovalPerforma $approval)
    {
        // Allow null performa_type to clear it - normalize the value first
        $performaType = $request->performa_type;
        if ($performaType === null || $performaType === 'null' || $performaType === '' || $performaType === 'undefined') {
            $performaType = null;
        }
        
        // Build validation rules - if null, just nullable; otherwise check if it's in the allowed list
        $performaTypeRules = 'nullable';
        if ($performaType !== null) {
            $performaTypeRules .= '|in:work_performa,maint_performa,work_priced_performa,maint_priced_performa,product_na';
        }
        
        $validator = Validator::make(array_merge($request->all(), ['performa_type' => $performaType]), [
            'performa_type' => $performaTypeRules,
            // waiting_for_authority removed - no longer needed
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Update approval with performa type and mark as waiting for authority
            $updateData = [];
            
            // Use normalized performa_type value
            $updateData['performa_type'] = $performaType;
            
            // Update status: If performa_type is set, set status to 'approved', otherwise keep as 'pending'
            if ($performaType) {
                $updateData['status'] = 'approved'; // Set status to approved when performa_type is set
            }
            // If performa_type is null, status remains 'pending' (default)
            
            // Extract authority number from remarks if present
            $authorityNumber = null;
            if ($request->has('remarks') && $request->remarks) {
                $updateData['remarks'] = $request->remarks;
                
                // Try to extract authority number from remarks
                // Pattern: "Authority No: AUTH-12345" or "Authority No.: AUTH-12345"
                if (preg_match('/Authority\s*No\.?\s*:\s*([A-Za-z0-9\-]+)/i', $request->remarks, $matches)) {
                    $authorityNumber = trim($matches[1]);
                }
            }
            
            // Save authority number to dedicated column if extracted
            if ($authorityNumber) {
                $updateData['authority_number'] = $authorityNumber;
            }
            
            // Use DB::table to ensure status is updated correctly
            DB::table('spare_approval_performa')
                ->where('id', $approval->id)
                ->update($updateData);
            
            // Refresh the approval model
            $approval->refresh();

            // Update complaint status based on performa type
            // Logic: If performa_type is set, use it directly as status (ID format: work_performa, maint_performa, product_na)
            // If performa_type is null, don't change complaint status (status is managed by status dropdown)
            if ($approval->complaint && $updateData['performa_type']) {
                $complaint = $approval->complaint;
                
                // Use performa_type directly as status (ID format)
                $newStatus = $updateData['performa_type']; // work_performa, maint_performa, or product_na
                
                // Update complaint status if not resolved/closed
                if (!in_array($complaint->status, ['resolved', 'closed'])) {
                    $complaint->status = $newStatus;
                    $complaint->save();
                }
            }
            // If performa_type is null, don't change complaint status - it's managed by status dropdown

            return response()->json([
                'success' => true,
                'message' => 'Approval updated successfully.',
                'approval' => $approval->fresh()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saving approval with performa', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update performa type for approval record
     */
    public function updatePerformaType(Request $request, SpareApprovalPerforma $approval)
    {
        $validator = Validator::make($request->all(), [
            'performa_type' => 'required|in:work_performa,maint_performa,work_priced_performa,maint_priced_performa,product_na',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Use DB::table to ensure status is updated correctly
            DB::table('spare_approval_performa')
                ->where('id', $approval->id)
                ->update([
                    'performa_type' => $request->performa_type,
                    'status' => 'approved', // Set status to approved when performa_type is set
                ]);
            
            // Refresh the approval model
            $approval->refresh();

            // Update complaint status based on performa type
            // Logic: If performa_type is set, use it directly as status (ID format: work_performa, maint_performa, product_na)
            // If performa_type is null, don't change complaint status (status is managed by status dropdown)
            if ($approval->complaint && $request->performa_type) {
                $complaint = $approval->complaint;
                
                // Use performa_type directly as status (ID format)
                $newStatus = $request->performa_type; // work_performa, maint_performa, or product_na
                
                // Update complaint status if not resolved/closed
                if (!in_array($complaint->status, ['resolved', 'closed'])) {
                    $complaint->status = $newStatus;
                    $complaint->save();
                }
            }
            // If performa_type is null, don't change complaint status - it's managed by status dropdown

            return response()->json([
                'success' => true,
                'message' => 'Performa type updated successfully.',
                'approval' => $approval->fresh()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating performa type', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update performa type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update reason for in-process status
     */
    public function updateReason(Request $request, SpareApprovalPerforma $approval)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
            'complaint_id' => 'nullable|exists:complaints,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Update approval remarks with the reason
            $approval->update([
                'remarks' => $request->reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reason updated successfully.',
                'reason' => $request->reason
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating reason: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject the specified approval performa
     */
    public function reject(Request $request, SpareApprovalPerforma $approval)
    {
        // Load relationships
        $approval->load(['items.spare', 'complaint.client', 'requestedBy']);
        
        $validator = Validator::make($request->all(), [
            'remarks' => 'required|string',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator);
        }

        try {
            // Resolve acting employee safely (same pattern as approve())
            $employee = Employee::first();
            if (!$employee) {
                throw new \Exception('No employee record found');
            }

            $approval->update([
                'status' => 'rejected',
                'approved_by' => $employee->id,
                'approved_at' => now(),
                'remarks' => $request->remarks,
            ]);

            $message = 'Approval performa rejected successfully.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            $message = 'Failed to reject: ' . $e->getMessage();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 500);
            }
            return redirect()->back()->with('error', $message);
        }
    }

    /**
     * Get approval statistics
     */
    public function getStatistics(Request $request)
    {
        $period = $request->get('period', '30'); // days

        $stats = [
            'total' => SpareApprovalPerforma::where('created_at', '>=', now()->subDays($period))->count(),
            'pending' => SpareApprovalPerforma::where('created_at', '>=', now()->subDays($period))->where('status', 'pending')->count(),
            'approved' => SpareApprovalPerforma::where('created_at', '>=', now()->subDays($period))->where('status', 'approved')->count(),
            'rejected' => SpareApprovalPerforma::where('created_at', '>=', now()->subDays($period))->where('status', 'rejected')->count(),
            'overdue' => SpareApprovalPerforma::overdue()->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Bulk actions on approvals
     */
    public function bulkAction(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'action' => 'required|in:approve,reject',
                'approval_ids' => 'required|array|min:1',
                'approval_ids.*' => 'exists:spare_approval_performa,id',
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return redirect()->back()->withErrors($validator);
            }

            $approvalIds = $request->approval_ids;
            $action = $request->action;

            DB::beginTransaction();

            switch ($action) {
                case 'approve':
                    $approvals = SpareApprovalPerforma::whereIn('id', $approvalIds)
                        ->where('status', 'pending')
                        ->get();

                    foreach ($approvals as $approval) {
                        // Check availability for each item
                        $canApprove = true;
                        foreach ($approval->items as $item) {
                            if (!$item->isSpareAvailable()) {
                                $canApprove = false;
                                break;
                            }
                        }

                        if ($canApprove) {
                            $employee = Employee::first();
                            if (!$employee) {
                                throw new \Exception('No employee record found');
                            }
                            
                            $approval->update([
                                'status' => 'approved',
                                'approved_by' => $employee->id,
                                'approved_at' => now(),
                                'remarks' => $request->remarks,
                            ]);

                            // Deduct requested quantity as approved in bulk
                            foreach ($approval->items as $item) {
                                $approvedQty = (int)$item->quantity_requested;
                                $item->update(['quantity_approved' => $approvedQty]);
                                $item->spare->removeStock(
                                    $approvedQty,
                                    "Bulk approved for complaint #{$approval->complaint->getTicketNumberAttribute()}",
                                    $approval->complaint_id
                                );
                            }
                        }
                    }
                    $message = 'Selected approvals processed successfully.';
                    break;

                case 'reject':
                    $employee = Employee::first();
                    if (!$employee) {
                        throw new \Exception('No employee record found');
                    }
                    
                    $updated = SpareApprovalPerforma::whereIn('id', $approvalIds)
                        ->where('status', 'pending')
                        ->update([
                            'status' => 'rejected',
                            'approved_by' => $employee->id,
                            'approved_at' => now(),
                            'remarks' => $request->remarks,
                        ]);
                    
                    $message = 'Selected approvals rejected successfully.';
                    break;
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Export approvals data
     */
    public function export(Request $request)
    {
        $query = SpareApprovalPerforma::with([
            'complaint.client',
            'requestedBy',
            'approvedBy',
            'items.spare'
        ]);

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('complaint', function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $approvals = $query->orderBy('created_at', 'desc')->get();

        $format = $request->get('format', 'csv');
        
        if ($format === 'csv') {
            return $this->exportToCsv($approvals);
        } elseif ($format === 'excel') {
            return $this->exportToExcel($approvals);
        } else {
            return response()->json(['message' => 'Unsupported export format']);
        }
    }

    /**
     * Export to CSV
     */
    private function exportToCsv($approvals)
    {
        $filename = 'approvals_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($approvals) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID',
                'Complaint ID',
                'Client Name',
                'Requested By',
                'Status',
                'Total Items',
                'Total Cost',
                'Created At',
                'Approved At',
                'Approved By',
                'Remarks'
            ]);

            // CSV Data
            foreach ($approvals as $approval) {
                fputcsv($file, [
                    $approval->id,
                    $approval->complaint->getTicketNumberAttribute(),
                    $approval->complaint->client ? $approval->complaint->client->client_name : 'Deleted Client',
                    $approval->requestedBy->name ?? 'N/A',
                    ucfirst($approval->status),
                    $approval->items->count(),
                    'PKR ' . number_format($approval->getTotalEstimatedCostAttribute(), 2),
                    $approval->created_at->format('Y-m-d H:i:s'),
                    $approval->approved_at ? $approval->approved_at->format('Y-m-d H:i:s') : 'N/A',
                    $approval->approvedBy->name ?? 'N/A',
                    $approval->remarks ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel (placeholder)
     */
    private function exportToExcel($approvals)
    {
        // This would require Laravel Excel package
        return response()->json(['message' => 'Excel export requires Laravel Excel package']);
    }

    /**
     * Update complaint status from approvals view
     */
    public function updateComplaintStatus(Request $request, $complaintId)
    {
        // Get complaint by ID directly to avoid route model binding issues
        $complaint = Complaint::find($complaintId);
        
        if (!$complaint) {
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Complaint not found with ID: ' . $complaintId
                ], 404);
            }
            return redirect()->back()
                ->with('error', 'Complaint not found.');
        }
        
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

        $oldStatus = $complaint->status ?? 'new';
        
        // Get remarks - prefer remarks field, fallback to notes
        $remarks = $request->input('remarks') ?: $request->input('notes') ?: '';

        // Get current employee for logging
        $currentEmployee = Employee::first();
        
        // Logic: Check if approval has performa_type set
        // If performa_type is set, use it to determine status (ID format: work_performa, maint_performa, product_na)
        // If performa_type is null/empty, use status from request (status dropdown value)
        // Exception: resolved, work_priced_performa and maint_priced_performa should always use the requested status
        $approval = $complaint->spareApprovals()->first() ?? null;
        $statusToUse = $request->status;
        
        // If status is resolved, work_priced_performa, maint_priced_performa, un_authorized, or pertains_to_ge_const_isld, 
        // use it directly without checking performa_type
        if (!in_array($request->status, ['resolved', 'work_priced_performa', 'maint_priced_performa', 'un_authorized', 'pertains_to_ge_const_isld'])) {
            if ($approval && $approval->performa_type) {
                // If performa_type is set, use it to determine status (ID format)
                if ($approval->performa_type === 'work_performa') {
                    $statusToUse = 'work_performa';
                } elseif ($approval->performa_type === 'maint_performa') {
                    $statusToUse = 'maint_performa';
                } elseif ($approval->performa_type === 'product_na') {
                    $statusToUse = 'product_na';
                }
            }
        }
        // If performa_type is null/empty, use status from request (status dropdown value - already in ID format)

        // Set closed_at when status becomes 'addressed', but only if it's not already set
        $updateData = [
            'status' => $statusToUse, // Use statusToUse instead of request->status
        ];
        
        if ($statusToUse === 'resolved' && !$complaint->closed_at) {
            // Get current time in Asia/Karachi timezone and convert to UTC for database storage
            $nowKarachi = \Carbon\Carbon::now('Asia/Karachi');
            $updateData['closed_at'] = $nowKarachi->copy()->utc();
        } elseif ($statusToUse !== 'resolved') {
            // If status is changed from addressed to something else, clear closed_at
            $updateData['closed_at'] = null;
        }

        // Normalize old status for comparison (handle null/empty)
        $normalizedOldStatus = $oldStatus ?: 'new';
        
        // Check if status is actually changing
        if ($normalizedOldStatus === $statusToUse && $complaint->closed_at === ($updateData['closed_at'] ?? null)) {
            // Status is already set to the requested value, consider it success
            $updated = true;
        } else {
            // Use transaction to ensure status is saved correctly
            try {
                DB::beginTransaction();
                
                // Check if complaint exists first
                $complaintExists = DB::table('complaints')
                    ->where('id', $complaint->id)
                    ->exists();
                
                if (!$complaintExists) {
                    throw new \Exception('Complaint not found with ID: ' . $complaint->id);
                }
                
                // Get current status from DB before update
                $currentDbStatus = DB::table('complaints')
                    ->where('id', $complaint->id)
                    ->value('status');
                
                // Use raw SQL update to ensure status is saved correctly
                // Use affectingStatement to get the number of affected rows
                $updated = DB::affectingStatement(
                    "UPDATE complaints SET status = ?, closed_at = ? WHERE id = ?",
                    [
                        $updateData['status'],
                        $updateData['closed_at'] ?? null,
                        $complaint->id
                    ]
                );
                
                // Verify the update was successful by checking the actual status in DB
                $actualStatus = DB::table('complaints')
                    ->where('id', $complaint->id)
                    ->value('status');
                
                // Check if the update was successful
                if ($actualStatus !== $statusToUse) {
                    // Update didn't work, throw error
                    throw new \Exception('Status update failed. Current status: ' . ($currentDbStatus ?: 'null') . ', Requested: ' . $statusToUse . ', Actual in DB: ' . ($actualStatus ?: 'null') . ', Rows updated: ' . $updated);
                }
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                
                if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to update status: ' . $e->getMessage()
                    ], 500);
                }
                return redirect()->back()
                    ->with('error', 'Failed to update status: ' . $e->getMessage());
            }
        }
        
        // Refresh the complaint model to get updated data
        $complaint->refresh();

        if ($currentEmployee) {
            // Initialize log remarks with status change message
            $statusDisplay = $statusToUse === 'resolved' ? 'addressed' : $statusToUse;
            $oldStatusDisplay = $oldStatus === 'resolved' ? 'addressed' : $oldStatus;
            $logRemarks = "Status changed from {$oldStatusDisplay} to {$statusDisplay}";
            
            if ($remarks) {
                $logRemarks .= ". Remarks: " . $remarks;
            }
            \App\Models\ComplaintLog::create([
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
            
            // Format closed_at in Asia/Karachi timezone for display
            $formattedClosedAt = null;
            if ($updatedClosedAt) {
                $closedAtCarbon = \Carbon\Carbon::parse($updatedClosedAt)->setTimezone('Asia/Karachi');
                $formattedClosedAt = $closedAtCarbon->format('d-m-Y H:i:s');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Complaint status updated successfully.',
                'complaint' => [
                    'id' => $complaint->id,
                    'status' => $updatedStatus,
                    'old_status' => $oldStatus,
                    'closed_at' => $formattedClosedAt,
                ]
            ]);
        }

        return redirect()->back()
            ->with('success', 'Complaint status updated successfully.');
    }

}
