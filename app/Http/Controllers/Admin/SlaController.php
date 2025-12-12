<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlaRule;
use App\Models\User;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Traits\LocationFilterTrait;

class SlaController extends Controller
{
    use LocationFilterTrait;

    public function __construct()
    {
        // Middleware is applied in routes
    }

    /**
     * Display a listing of SLA rules
     */
    public function index(Request $request)
    {
        $query = SlaRule::with('notifyTo');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('complaint_type', 'like', "%{$search}%");
            });
        }

        // Filter by complaint type
        if ($request->has('complaint_type') && $request->complaint_type) {
            $query->where('complaint_type', $request->complaint_type);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $slaRules = $query->orderBy('created_at', 'desc')->paginate(15);
        $users = User::where('status', 'active')->get();

        return view('admin.sla.index', compact('slaRules', 'users'));
    }

    /**
     * Show the form for creating a new SLA rule
     */
    public function create()
    {
        $loggedInUser = Auth::user();
        $usersQuery = User::where('status', 'active')->with('role');
        
        // Apply location filtering for users
        if ($loggedInUser && $loggedInUser->role) {
            $roleName = strtolower($loggedInUser->role->role_name ?? '');
            
            // Director and Admin can see all users
            if (!in_array($roleName, ['director', 'admin'])) {
                // Garrison Engineer: Show users from their city
                if ($roleName === 'garrison_engineer' && $loggedInUser->city_id) {
                    $usersQuery->where('city_id', $loggedInUser->city_id);
                }
                // Complaint Center and Department Staff: Show users from their sector
                elseif (in_array($roleName, ['complaint_center', 'department_staff']) && $loggedInUser->sector_id) {
                    $usersQuery->where('sector_id', $loggedInUser->sector_id);
                }
                // Other roles: Filter by city_id if available, otherwise by sector_id
                else {
                    if ($loggedInUser->sector_id) {
                        $usersQuery->where('sector_id', $loggedInUser->sector_id);
                    } elseif ($loggedInUser->city_id) {
                        $usersQuery->where('city_id', $loggedInUser->city_id);
                    } else {
                        // If no location assigned, show no users
                        $usersQuery->whereRaw('1 = 0');
                    }
                }
            }
        }
        
        $users = $usersQuery->orderBy('username')->get();
        
        // Fetch categories from database (ComplaintCategory table)
        $complaintTypes = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::orderBy('name')->pluck('name', 'name')->toArray()
            : [];

        return view('admin.sla.create', compact('users', 'complaintTypes'));
    }

    /**
     * Store a newly created SLA rule
     */
    public function store(Request $request)
    {
        // Get categories from database
        $categories = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::orderBy('name')->pluck('name')->toArray()
            : [];
        
        $categoryRule = 'required|string';
        if (!empty($categories)) {
            $categoryRule .= '|in:' . implode(',', $categories);
        }
        
        $validator = Validator::make($request->all(), [
            'complaint_type' => $categoryRule,
            'priority' => 'required|in:low,medium,high,urgent',
            'max_response_time' => 'required|integer|min:1',
            'max_resolution_time' => 'required|integer|min:1',
            'notify_to' => 'required|exists:users,id',
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $slaRule = SlaRule::create($request->all());

        return redirect()->route('admin.sla.index')
            ->with('success', 'SLA rule created successfully.');
    }

    /**
     * Display the specified SLA rule
     */
    public function show(SlaRule $sla)
    {
        $sla->load('notifyTo');

        // Get recent complaints for this SLA rule
        $recentComplaints = Complaint::where('category', $sla->complaint_type)
            ->with(['client', 'assignedEmployee'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.sla.show', compact('sla', 'recentComplaints'));
    }

    /**
     * Show the form for editing the SLA rule
     */
    public function edit(SlaRule $sla)
    {
        $loggedInUser = Auth::user();
        $usersQuery = User::where('status', 'active')->with('role');
        
        // Apply location filtering for users
        if ($loggedInUser && $loggedInUser->role) {
            $roleName = strtolower($loggedInUser->role->role_name ?? '');
            
            // Director and Admin can see all users
            if (!in_array($roleName, ['director', 'admin'])) {
                // Garrison Engineer: Show users from their city
                if ($roleName === 'garrison_engineer' && $loggedInUser->city_id) {
                    $usersQuery->where('city_id', $loggedInUser->city_id);
                }
                // Complaint Center and Department Staff: Show users from their sector
                elseif (in_array($roleName, ['complaint_center', 'department_staff']) && $loggedInUser->sector_id) {
                    $usersQuery->where('sector_id', $loggedInUser->sector_id);
                }
                // Other roles: Filter by city_id if available, otherwise by sector_id
                else {
                    if ($loggedInUser->sector_id) {
                        $usersQuery->where('sector_id', $loggedInUser->sector_id);
                    } elseif ($loggedInUser->city_id) {
                        $usersQuery->where('city_id', $loggedInUser->city_id);
                    } else {
                        // If no location assigned, show no users
                        $usersQuery->whereRaw('1 = 0');
                    }
                }
            }
        }
        
        $users = $usersQuery->orderBy('username')->get();
        
        // Fetch categories from database (ComplaintCategory table)
        $complaintTypes = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::orderBy('name')->pluck('name', 'name')->toArray()
            : [];

        return view('admin.sla.edit', compact('sla', 'users', 'complaintTypes'));
    }

    /**
     * Update the specified SLA rule
     */
    public function update(Request $request, SlaRule $sla)
    {
        // Get categories from database
        $categories = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::orderBy('name')->pluck('name')->toArray()
            : [];
        
        $categoryRule = 'required|string';
        if (!empty($categories)) {
            $categoryRule .= '|in:' . implode(',', $categories);
        }
        
        $validator = Validator::make($request->all(), [
            'complaint_type' => $categoryRule,
            'priority' => 'required|in:low,medium,high,urgent',
            'max_response_time' => 'required|integer|min:1',
            'max_resolution_time' => 'required|integer|min:1',
            'notify_to' => 'required|exists:users,id',
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $sla->update($request->all());

        return redirect()->route('admin.sla.index')
            ->with('success', 'SLA rule updated successfully.');
    }

    /**
     * Remove the specified SLA rule (Soft Delete)
     */
    public function destroy(SlaRule $sla)
    {
        $sla->delete(); // This will now soft delete due to SoftDeletes trait

        return response()->json([
            'success' => true,
            'message' => 'SLA rule deleted successfully.'
        ]);
    }

    /**
     * Toggle SLA rule status
     */
    public function toggleStatus(SlaRule $sla)
    {
        $sla->update([
            'status' => $sla->status === 'active' ? 'inactive' : 'active'
        ]);

        $status = $sla->status === 'active' ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "SLA rule {$status} successfully.");
    }

    /**
     * Get SLA statistics
     */
    public function getStatistics(Request $request)
    {
        $period = $request->get('period', '30'); // days

        $stats = [
            'total_rules' => SlaRule::count(),
            'active_rules' => SlaRule::where('status', 'active')->count(),
            'inactive_rules' => SlaRule::where('status', 'inactive')->count(),
            'total_complaints' => Complaint::where('created_at', '>=', now()->subDays($period))->count(),
            'complaints_within_sla' => $this->getComplaintsWithinSla($period),
            'complaints_breached' => $this->getComplaintsBreached($period),
        ];

        return response()->json($stats);
    }

    /**
     * Get SLA performance chart data
     */
    public function getChartData(Request $request)
    {
        $period = $request->get('period', '30'); // days

        $data = SlaRule::withCount(['complaints' => function($query) use ($period) {
            $query->where('created_at', '>=', now()->subDays($period));
        }])
        ->orderBy('complaints_count', 'desc')
        ->get();

        return response()->json($data);
    }

    /**
     * Get SLA breach analysis
     */
    public function getBreachAnalysis(Request $request)
    {
        $period = $request->get('period', '30'); // days

        $breaches = Complaint::where('created_at', '>=', now()->subDays($period))
            ->whereIn('status', ['new', 'assigned', 'in_progress'])
            ->with(['client', 'assignedEmployee', 'slaRule'])
            ->get()
            ->filter(function($complaint) {
                return $complaint->isSlaBreached();
            })
            ->map(function($complaint) {
                return [
                    'id' => $complaint->id,
                    'client_name' => $complaint->client ? $complaint->client->client_name : 'Deleted Client',
                    'category' => $complaint->category,
                    'status' => $complaint->status,
                    'assigned_to' => $complaint->assignedEmployee ? $complaint->assignedEmployee->name : 'Unassigned',
                    'created_at' => $complaint->created_at,
                    'hours_overdue' => $complaint->getHoursOverdue(),
                    'sla_rule' => $complaint->slaRule ? $complaint->slaRule->max_resolution_time : null,
                ];
            })
            ->sortByDesc('hours_overdue')
            ->values();

        return response()->json($breaches);
    }

    /**
     * Get SLA performance by type
     */
    public function getPerformanceByType(Request $request)
    {
        $period = $request->get('period', '30'); // days

        $performance = [];
        // Fetch categories from database
        $complaintTypes = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::orderBy('name')->pluck('name', 'name')->toArray()
            : [];

        foreach ($complaintTypes as $type => $label) {
            $total = Complaint::where('category', $type)
                ->where('created_at', '>=', now()->subDays($period))
                ->count();

            $withinSla = Complaint::where('category', $type)
                ->where('created_at', '>=', now()->subDays($period))
                ->whereIn('status', ['resolved', 'closed'])
                ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, updated_at) <= COALESCE((SELECT MIN(max_resolution_time) FROM sla_rules WHERE complaint_type = ? AND status = "active"), 999999)', [$type])
                ->count();

            $performance[] = [
                'type' => $type,
                'label' => $label,
                'total' => $total,
                'within_sla' => $withinSla,
                'breached' => $total - $withinSla,
                'sla_percentage' => $total > 0 ? round(($withinSla / $total) * 100, 2) : 0,
            ];
        }

        return response()->json($performance);
    }

    /**
     * Get escalation alerts
     */
    public function getEscalationAlerts(Request $request)
    {
        $hours = $request->get('hours', 24);

        $alerts = Complaint::where('created_at', '>=', now()->subHours($hours))
            ->whereIn('status', ['new', 'assigned', 'in_progress'])
            ->with(['client', 'assignedEmployee', 'slaRule'])
            ->get()
            ->filter(function($complaint) {
                return $complaint->isSlaBreached();
            })
            ->map(function($complaint) {
                return [
                    'complaint_id' => $complaint->id,
                    'client_name' => $complaint->client ? $complaint->client->client_name : 'Deleted Client',
                    'category' => $complaint->category,
                    'assigned_to' => $complaint->assignedEmployee ? $complaint->assignedEmployee->name : 'Unassigned',
                    'hours_overdue' => $complaint->getHoursOverdue(),
                    'priority' => $complaint->slaRule ? $complaint->slaRule->priority : 'medium',
            'notify_to' => $complaint->slaRule ? $complaint->slaRule->notifyTo->name : null,
                ];
            })
            ->sortByDesc('hours_overdue')
            ->values();

        return response()->json($alerts);
    }

    /**
     * Test SLA rule
     */
    public function testSlaRule(Request $request, SlaRule $sla)
    {
        $validator = Validator::make($request->all(), [
            'test_complaint_id' => 'required|exists:complaints,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $complaint = Complaint::find($request->test_complaint_id);
        
        $result = [
            'complaint_id' => $complaint->id,
            'complaint_type' => $complaint->category,
            'created_at' => $complaint->created_at,
            'current_status' => $complaint->status,
            'hours_elapsed' => $complaint->getHoursElapsed(),
            'max_response_time' => $sla->max_response_time,
            'max_resolution_time' => $sla->max_resolution_time,
            'response_breached' => $complaint->isResponseTimeBreached($sla),
            'resolution_breached' => $complaint->isResolutionTimeBreached($sla),
            'priority' => $sla->priority,
            'notify_to' => $sla->notifyTo->name,
        ];

        return response()->json($result);
    }

    /**
     * Bulk actions on SLA rules
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,delete',
            'sla_rule_ids' => 'required|array|min:1',
            'sla_rule_ids.*' => 'exists:sla_rules,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $slaRuleIds = $request->sla_rule_ids;
        $action = $request->action;

        switch ($action) {
            case 'activate':
                SlaRule::whereIn('id', $slaRuleIds)->update(['status' => 'active']);
                $message = 'Selected SLA rules activated successfully.';
                break;

            case 'deactivate':
                SlaRule::whereIn('id', $slaRuleIds)->update(['status' => 'inactive']);
                $message = 'Selected SLA rules deactivated successfully.';
                break;

            case 'delete':
                SlaRule::whereIn('id', $slaRuleIds)->delete(); // Soft delete
                $message = 'Selected SLA rules deleted successfully.';
                break;
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Export SLA rules data
     */
    public function export(Request $request)
    {
        $query = SlaRule::with('notifyTo');

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('complaint_type', 'like', "%{$search}%");
            });
        }

        if ($request->has('complaint_type') && $request->complaint_type) {
            $query->where('complaint_type', $request->complaint_type);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $slaRules = $query->get();

        // Implementation for export
        return response()->json(['message' => 'Export functionality not implemented yet']);
    }

    /**
     * Get complaints within SLA
     */
    private function getComplaintsWithinSla($period)
    {
        return Complaint::where('created_at', '>=', now()->subDays($period))
            ->whereIn('status', ['resolved', 'closed'])
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, updated_at) <= COALESCE((SELECT MIN(max_resolution_time) FROM sla_rules WHERE complaint_type = complaints.category AND status = "active"), 999999)')
            ->count();
    }

    /**
     * Get complaints breached SLA
     */
    private function getComplaintsBreached($period)
    {
        return Complaint::where('created_at', '>=', now()->subDays($period))
            ->whereIn('status', ['resolved', 'closed'])
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, updated_at) > COALESCE((SELECT MIN(max_resolution_time) FROM sla_rules WHERE complaint_type = complaints.category AND status = "active"), 999999)')
            ->count();
    }
}
