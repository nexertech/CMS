<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\Spare;
use App\Models\ComplaintCategory;
use App\Models\City;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\LocationFilterTrait;

class HomeController extends Controller
{
    use LocationFilterTrait;

    /**
     * Apply location-based filtering to complaints query for frontend users
     * using the GE Groups/Nodes assigned via frontend_user_locations records.
     */
    protected function filterComplaintsByLocationForFrontend($query, $user, ?array $locationScope = null)
    {
        $scope = $locationScope ?? $this->getFrontendUserLocationScope($user);

        return $this->applyFrontendLocationScope($query, $scope, 'complaints.city_id', 'complaints.sector_id');
    }

    /**
     * Build location scope (cities and sectors) assigned to frontend user
     */
    protected function getFrontendUserLocationScope($user): array
    {
        $scope = [
            'restricted' => false,
            'city_ids' => [],
            'sector_ids' => [],
            'city_sector_map' => [],
            'sector_city_map' => [],
        ];

        if (!$user) {
            return $scope;
        }

        // Get privileges from JSON columns
        $cityIds = $user->group_ids ?? [];
        $sectorIds = $user->node_ids ?? [];
        $cmeIds = $user->cme_ids ?? [];

        // If user has CMEs assigned, include all cities under those CMEs
        if (!empty($cmeIds)) {
            $cmeCityIds = \App\Models\City::whereIn('cme_id', $cmeIds)->pluck('id')->toArray();
            $cityIds = array_unique(array_merge($cityIds, $cmeCityIds));
        }

        if (empty($cityIds) && empty($sectorIds)) {
            // Check if user is Admin (role_id 1 or has admin role)
            // If Admin, return unrestricted scope (restricted = false)
            if ($user->role_id === 1 || (method_exists($user, 'isAdmin') && $user->isAdmin())) {
                return $scope;
            }

            // Regular user with no privileges -> Restricted access (sees nothing)
            $scope['restricted'] = true;
            return $scope;
        }

        $scope['restricted'] = true;
        $scope['city_ids'] = $cityIds;
        $scope['sector_ids'] = $sectorIds;

        // Build city_sector_map and sector_city_map for sector-based filtering
        if (!empty($sectorIds)) {
            $sectors = \App\Models\Sector::whereIn('id', $sectorIds)->get();
            foreach ($sectors as $sector) {
                $scope['sector_city_map'][$sector->id] = $sector->city_id;
                if (!isset($scope['city_sector_map'][$sector->city_id])) {
                    $scope['city_sector_map'][$sector->city_id] = [];
                }
                $scope['city_sector_map'][$sector->city_id][] = $sector->id;
            }
        }

        return $scope;
    }

    /**
     * Apply location scope (cities/sectors) to a query builder
     */
    protected function applyFrontendLocationScope($query, array $scope, string $cityColumn = 'city_id', string $sectorColumn = 'sector_id')
    {
        if (empty($scope['restricted'])) {
            return $query;
        }

        $cityIds = $scope['city_ids'] ?? [];
        $sectorIds = $scope['sector_ids'] ?? [];

        if (empty($cityIds) && empty($sectorIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($cityIds, $sectorIds, $cityColumn, $sectorColumn) {
            $applied = false;
            if (!empty($sectorIds)) {
                $q->whereIn($sectorColumn, $sectorIds);
                $applied = true;
            }
            if (!empty($cityIds)) {
                $method = $applied ? 'orWhereIn' : 'whereIn';
                $q->{$method}($cityColumn, $cityIds);
            }
        });
    }

    /**
     * Determine if selected city is accessible for frontend user
     */
    protected function canAccessCity(?int $cityId, array $scope): bool
    {
        if (!$cityId) {
            return true;
        }

        if (empty($scope['restricted'])) {
            return true;
        }

        if (!empty($scope['city_ids']) && in_array($cityId, $scope['city_ids'])) {
            return true;
        }

        // City accessible through sector-level assignments (used for dropdown visibility)
        if (!empty($scope['city_sector_map']) && array_key_exists($cityId, $scope['city_sector_map'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine if selected sector is accessible for frontend user
     */
    protected function canAccessSector(?int $sectorId, array $scope): bool
    {
        if (!$sectorId) {
            return true;
        }

        if (empty($scope['restricted'])) {
            return true;
        }

        if (!empty($scope['sector_ids']) && in_array($sectorId, $scope['sector_ids'])) {
            return true;
        }

        if (!empty($scope['city_ids'])) {
            $sectorCityId = $this->resolveSectorCity($sectorId, $scope);
            if ($sectorCityId && in_array($sectorCityId, $scope['city_ids'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get sectors permitted within a specific city for the current scope
     */
    protected function getPermittedSectorsForCity(int $cityId, array $scope): array
    {
        if (!empty($scope['city_ids']) && in_array($cityId, $scope['city_ids'])) {
            // Entire city is accessible
            return [];
        }

        return $scope['city_sector_map'][$cityId] ?? [];
    }

    /**
     * Resolve sector's city via cached data or database lookup
     */
    protected function resolveSectorCity(int $sectorId, array $scope): ?int
    {
        if (!empty($scope['sector_city_map']) && array_key_exists($sectorId, $scope['sector_city_map'])) {
            return $scope['sector_city_map'][$sectorId];
        }

        static $sectorCityCache = [];
        if (array_key_exists($sectorId, $sectorCityCache)) {
            return $sectorCityCache[$sectorId];
        }

        $sector = Sector::find($sectorId);
        $sectorCityCache[$sectorId] = $sector ? $sector->city_id : null;

        return $sectorCityCache[$sectorId];
    }

    /**
     * Return list of city IDs that should appear in GE Group dropdown
     */
    protected function getAccessibleCityIdsForDropdown(array $scope): ?array
    {
        if (empty($scope['restricted'])) {
            return null;
        }

        $cityIds = $scope['city_ids'] ?? [];
        $derivedCityIds = array_keys($scope['city_sector_map'] ?? []);

        $combined = array_unique(array_merge($cityIds, $derivedCityIds));

        return empty($combined) ? null : $combined;
    }

    public function index()
    {
        if (Auth::guard('frontend')->check()) {
            return redirect()->route('frontend.dashboard');
        }

        return response()->view('frontend.home')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function features()
    {
        return view('frontend.features');
    }

    public function dashboard(Request $request)
    {
        // Get logged-in user
        $user = Auth::user();
        $locationScope = $this->getFrontendUserLocationScope($user);

        // Get filter parameters (including CMES)
        $cmesId = $request->get('cmes_id');
        $cityId = $request->get('city_id');
        $sectorId = $request->get('sector_id');
        $category = $request->get('category');
        $status = $request->get('status');
        $dateRange = $request->get('date_range', 'all_time');

        // Build base query with filters
        $complaintsQuery = Complaint::query();

        // Apply location filtering based on GE Group (city_id) and GE Node (sector_id) selections
        $hasRestrictions = !empty($locationScope['restricted']);

        if ($cityId) {
            if ($this->canAccessCity((int) $cityId, $locationScope)) {
                $allowedSectors = $this->getPermittedSectorsForCity((int) $cityId, $locationScope);
                $sectorIdsForCity = empty($allowedSectors) 
                    ? \App\Models\Sector::where('city_id', $cityId)->pluck('id')->toArray()
                    : $allowedSectors;

                if ($sectorId) {
                    if ($this->canAccessSector((int) $sectorId, $locationScope)) {
                        $complaintsQuery->where('complaints.sector_id', $sectorId);
                    } else {
                        $complaintsQuery->whereRaw('1 = 0');
                    }
                } else {
                    // Inclusive GE Group filter: City ID OR Sector IDs belonging to this City (respecting scope)
                    $complaintsQuery->where(function ($q) use ($cityId, $sectorIdsForCity) {
                        $q->where('complaints.city_id', $cityId);
                        if (!empty($sectorIdsForCity)) {
                            $q->orWhereIn('complaints.sector_id', $sectorIdsForCity);
                        }
                    });
                }
            } else {
                $complaintsQuery->whereRaw('1 = 0');
            }
        }
 elseif ($sectorId) {
            if ($this->canAccessSector((int) $sectorId, $locationScope)) {
                $complaintsQuery->where('complaints.sector_id', $sectorId);
            } else {
                $complaintsQuery->whereRaw('1 = 0');
            }
        } elseif ($hasRestrictions) {
            $this->filterComplaintsByLocationForFrontend($complaintsQuery, $user, $locationScope);
        }

        if ($category && $category !== 'all') {
            if (is_numeric($category)) {
                $complaintsQuery->where('complaints.category_id', $category);
            } else {
                $complaintsQuery->whereHas('category', function($q) use ($category) {
                    $q->where('name', $category);
                });
            }
        }

        // Apply CMES filter (Inclusive: CME Cities OR CME Sectors) - Apply BEFORE cloning for graph base
        if ($cmesId) {
            $cityIdsForCmes = City::where('cme_id', $cmesId)->pluck('id')->toArray();
            $sectorIdsForCmes = Sector::where(function ($q) use ($cmesId, $cityIdsForCmes) {
                $q->where('cme_id', $cmesId);
                if (!empty($cityIdsForCmes)) {
                    $q->orWhereIn('city_id', $cityIdsForCmes);
                }
            })->pluck('id')->toArray();

            if (empty($cityIdsForCmes) && empty($sectorIdsForCmes)) {
                $complaintsQuery->whereRaw('1 = 0');
            } else {
                $complaintsQuery->where(function ($q) use ($cityIdsForCmes, $sectorIdsForCmes) {
                    if (!empty($cityIdsForCmes)) {
                        $q->whereIn('complaints.city_id', $cityIdsForCmes);
                    }
                    if (!empty($sectorIdsForCmes)) {
                        $method = !empty($cityIdsForCmes) ? 'orWhereIn' : 'whereIn';
                        $q->{$method}('complaints.sector_id', $sectorIdsForCmes);
                    }
                });
            }
        }

        // Capture base query for trend graphs (includes location and category but not status/date filters)
        $graphBaseQuery = clone $complaintsQuery;

        if ($status && $status !== 'all') {
            $complaintsQuery->where('complaints.status', $status);
        }

        // Filter by date range
        if ($dateRange) {
            $now = now();
            switch ($dateRange) {
                case 'yesterday':
                    $complaintsQuery->whereDate('complaints.created_at', $now->copy()->subDay()->toDateString());
                    break;
                case 'today':
                    $complaintsQuery->whereDate('complaints.created_at', $now->toDateString());
                    break;
                case 'this_week':
                    $complaintsQuery->whereBetween('complaints.created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
                    break;
                case 'last_week':
                    $complaintsQuery->whereBetween('complaints.created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()]);
                    break;
                case 'this_month':
                    $complaintsQuery->whereMonth('complaints.created_at', $now->month)
                        ->whereYear('complaints.created_at', $now->year);
                    break;
                case 'last_month':
                    $complaintsQuery->whereMonth('complaints.created_at', $now->copy()->subMonth()->month)
                        ->whereYear('complaints.created_at', $now->copy()->subMonth()->year);
                    break;
                case 'last_6_months':
                    $complaintsQuery->where('complaints.created_at', '>=', $now->copy()->subMonths(6)->startOfDay());
                    break;
                case 'custom':
                    if ($request->has('start_date') && $request->has('end_date')) {
                        $complaintsQuery->whereBetween('complaints.created_at', [
                            \Carbon\Carbon::parse($request->start_date)->startOfDay(),
                            \Carbon\Carbon::parse($request->end_date)->endOfDay()
                        ]);
                    }
                    break;
            }
        }

        // Get filter options - filter based on user's location access
        $geGroupsQuery = City::where(function ($q) {
            $q->where('name', 'LIKE', '%GE%')
                ->orWhere('name', 'LIKE', '%AGE%')
                ->orWhere('name', 'LIKE', '%ge%')
                ->orWhere('name', 'LIKE', '%age%');
        })
            ->where('status', 'active');

        // If CMES selected, restrict GE groups to that CMES
        if ($cmesId) {
            $geGroupsQuery->where('cme_id', $cmesId);
        }

        // Apply location restrictions (ALWAYS, even if CMES is selected)
        $accessibleCityIds = $this->getAccessibleCityIdsForDropdown($locationScope);
        if (!empty($accessibleCityIds)) {
            $geGroupsQuery->whereIn('id', $accessibleCityIds);
        } elseif (!empty($locationScope['restricted'])) {
            // Restricted user but no accessible cities -> show nothing
            $geGroupsQuery->whereRaw('1 = 0');
        }

        $geGroups = $geGroupsQuery->orderBy('name')->get();

        $geNodesQuery = Sector::where('status', 'active');

        // If CMES selected, show only nodes belonging to cities of that CMES
        if ($cmesId) {
            $cityIdsForCmes = City::where('cme_id', $cmesId)->pluck('id')->toArray();
            $geNodesQuery->where(function ($q) use ($cityIdsForCmes, $cmesId) {
                if (!empty($cityIdsForCmes)) {
                    $q->whereIn('city_id', $cityIdsForCmes);
                }
                // Also include sectors that have cme_id set directly
                $q->orWhere('cme_id', $cmesId);
            });
        }

        // Apply location filter to GE Nodes dropdown based on user's location (ALWAYS, even if CMES is selected)
        if (!empty($locationScope['restricted'])) {
            if (!empty($locationScope['sector_ids'])) {
                $geNodesQuery->whereIn('id', $locationScope['sector_ids']);
            } elseif (!empty($locationScope['city_ids'])) {
                $geNodesQuery->whereIn('city_id', $locationScope['city_ids']);
            } elseif (!empty($locationScope['city_sector_map'])) {
                $geNodesQuery->whereIn('city_id', array_keys($locationScope['city_sector_map']));
            } else {
                // Restricted user but no assigned sectors/cities -> show nothing
                $geNodesQuery->whereRaw('1 = 0');
            }
        }

        // Apply manual city filter if provided (for when user selects a GE Group)
        if ($cityId) {
            $geNodesQuery->where('city_id', $cityId);
        }

        $geNodes = $geNodesQuery->orderBy('name')->get();


        $categories = ComplaintCategory::all();

        // Get all statuses from database (same as admin side)
        $statuses = [
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
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

        // Calculate stats with filters in a single efficient query
        $now = now();
        $statsData = (clone $complaintsQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN complaints.status = 'new' THEN 1 ELSE 0 END) as new,
            SUM(CASE WHEN complaints.status IN ('new', 'assigned', 'in_progress') THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN complaints.status IN ('resolved', 'closed') THEN 1 ELSE 0 END) as addressed,
            SUM(CASE WHEN complaints.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN complaints.status = 'assigned' THEN 1 ELSE 0 END) as assigned,
            SUM(CASE WHEN complaints.status = 'closed' THEN 1 ELSE 0 END) as closed,
            SUM(CASE WHEN complaints.status = 'work_performa' THEN 1 ELSE 0 END) as work_performa,
            SUM(CASE WHEN complaints.status = 'maint_performa' THEN 1 ELSE 0 END) as maint_performa,
            SUM(CASE WHEN complaints.status = 'work_priced_performa' THEN 1 ELSE 0 END) as work_priced_performa,
            SUM(CASE WHEN complaints.status = 'maint_priced_performa' THEN 1 ELSE 0 END) as maint_priced_performa,
            SUM(CASE WHEN complaints.status = 'un_authorized' THEN 1 ELSE 0 END) as un_authorized,
            SUM(CASE WHEN complaints.status = 'product_na' THEN 1 ELSE 0 END) as product_na,
            SUM(CASE WHEN complaints.status = 'pertains_to_ge_const_isld' THEN 1 ELSE 0 END) as pertains_to_ge_const_isld,
            SUM(CASE WHEN complaints.status = 'barak_damages' THEN 1 ELSE 0 END) as barak_damages,
            SUM(CASE WHEN complaints.created_at >= ? THEN 1 ELSE 0 END) as today,
            SUM(CASE WHEN complaints.created_at >= ? THEN 1 ELSE 0 END) as this_month,
            SUM(CASE WHEN complaints.created_at >= ? AND complaints.created_at < ? THEN 1 ELSE 0 END) as last_month,
            complaints.status
        ", [
            $now->copy()->startOfDay(),
            $now->copy()->startOfMonth(),
            $now->copy()->subMonth()->startOfMonth(),
            $now->copy()->startOfMonth()
        ])->get();

        $statsDataAggregation = $statsData->first();

        $stats = [
            'total_complaints' => $statsDataAggregation->total ?? 0,
            'new_complaints' => $statsDataAggregation->new ?? 0,
            'pending_complaints' => $statsDataAggregation->pending ?? 0,
            'resolved_complaints' => $statsDataAggregation->addressed ?? 0,
            'overdue_complaints' => (clone $complaintsQuery)->overdue()->count(),
            'complaints_today' => $statsDataAggregation->today ?? 0,
            'complaints_this_month' => $statsDataAggregation->this_month ?? 0,
            'complaints_last_month' => $statsDataAggregation->last_month ?? 0,
            'in_progress' => $statsDataAggregation->in_progress ?? 0,
            'assigned' => $statsDataAggregation->assigned ?? 0,
            'closed' => $statsDataAggregation->closed ?? 0,
            'work_performa' => $statsDataAggregation->work_performa ?? 0,
            'maint_performa' => $statsDataAggregation->maint_performa ?? 0,
            'addressed' => $statsDataAggregation->addressed ?? 0,
            'un_authorized' => $statsDataAggregation->un_authorized ?? 0,
            'pertains_to_ge_const_isld' => $statsDataAggregation->pertains_to_ge_const_isld ?? 0,
            'barak_damages' => $statsDataAggregation->barak_damages ?? 0,
            'work_priced_performa' => $statsDataAggregation->work_priced_performa ?? 0,
            'maint_priced_performa' => $statsDataAggregation->maint_priced_performa ?? 0,
        ];

        // Status-wise counts for the pie chart - Group closed with resolved for consistency
        $statusCounts = (clone $complaintsQuery)
            ->selectRaw('complaints.status, COUNT(*) as count')
            ->groupBy('complaints.status')
            ->pluck('count', 'complaints.status')
            ->toArray();

        $complaintsByStatus = $statusCounts;
        if (isset($complaintsByStatus['closed'])) {
            $complaintsByStatus['resolved'] = ($complaintsByStatus['resolved'] ?? 0) + $complaintsByStatus['closed'];
            // Keep 'closed' as well for chart specificity, but 'resolved' is now the aggregate 'Addressed'
        }
        if (isset($complaintsByStatus['new'])) {
             $complaintsByStatus['assigned'] = ($complaintsByStatus['assigned'] ?? 0) + $complaintsByStatus['new'];
        }

        // Resolution rate & average time
        $totalComplaints = $stats['total_complaints'];
        $resolvedComplaints = $stats['resolved_complaints'];
        $stats['resolution_rate'] = $totalComplaints > 0 ? round(($resolvedComplaints / $totalComplaints) * 100) : 0;

        $resolvedWithTime = (clone $complaintsQuery)
            ->whereIn('complaints.status', ['resolved', 'closed'])
            ->whereNotNull('closed_at')
            ->selectRaw('SUM(DATEDIFF(closed_at, created_at)) as total_days, COUNT(*) as count')
            ->first();

        $stats['average_resolution_days'] = ($resolvedWithTime && $resolvedWithTime->count > 0)
            ? round($resolvedWithTime->total_days / $resolvedWithTime->count)
            : 0;

        $page = request()->get('page', 1);
        $perPage = 5;
        $recentComplaintsQuery = Complaint::with(['client', 'assignedEmployee']);
        $this->filterComplaintsByLocationForFrontend($recentComplaintsQuery, $user, $locationScope);
        $recentComplaints = $recentComplaintsQuery->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $self = $this; // Store reference for use in closure
        $pendingApprovals = class_exists(\App\Models\SpareApprovalPerforma::class)
            ? \App\Models\SpareApprovalPerforma::with(['complaint.client', 'requestedBy', 'items.spare'])
                ->whereHas('complaint', function ($q) use ($user, $self, $locationScope) {
                    $self->filterComplaintsByLocationForFrontend($q, $user, $locationScope);
                })
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
            : collect();

        $lowStockItems = collect();
        if (method_exists(Spare::class, 'lowStock')) {
            $lowStockItemsQuery = Spare::lowStock();
            $this->applyFrontendLocationScope($lowStockItemsQuery, $locationScope);
            $lowStockItems = $lowStockItemsQuery
                ->orderBy('stock_quantity', 'asc')
                ->limit(10)
                ->get();
        }

        // Dataset for dashboard complaint table
        $performaStatuses = [
            'work_performa',
            'maint_performa',
            'work_priced_performa',
            'maint_priced_performa',
            'product_na',
        ];

        $overdueComplaintsQuery = Complaint::overdue()
            ->with(['client', 'assignedEmployee']);
        $this->filterComplaintsByLocationForFrontend($overdueComplaintsQuery, $user, $locationScope);
        $overdueComplaints = $overdueComplaintsQuery->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($complaint) use ($performaStatuses) {
                // Reuse similar formatting logic
                $statusKey = $complaint->status === 'new' ? 'assigned' : $complaint->status;
                $statusKey = $statusKey === 'closed' ? 'resolved' : $statusKey;

                $statusLabel = $statusKey === 'resolved'
                    ? 'Addressed'
                    : ucfirst(str_replace('_', ' ', $statusKey));

                $performaType = in_array($complaint->status, $performaStatuses, true)
                    ? $complaint->status
                    : null;

                $client = $complaint->client;
 
                $createdAt = $complaint->created_at
                    ? $complaint->created_at->timezone('Asia/Karachi')->format('M d, Y H:i')
                    : null;
                $closedAt = $complaint->closed_at
                    ? $complaint->closed_at->timezone('Asia/Karachi')->format('M d, Y H:i')
                    : null;

                return [
                    'id' => $complaint->id,
                    'cmp' => $complaint->id,
                    'status' => $statusKey,
                    'status_label' => $statusLabel,
                    'performa_type' => $performaType,
                    'performa_label' => $performaType ? ucfirst(str_replace('_', ' ', $performaType)) : '-',
                    'category' => $complaint->category_display ?? 'N/A',
                    'designation' => $complaint->assignedEmployee->designation->name ?? 'N/A',
                    'client_name' => $client->client_name ?? 'N/A',
                    'house_no' => $complaint->house->house_no ?? 'N/A',
                    'address' => $complaint->house->address
                        ?? $client->address
                        ?? 'N/A',
                    'phone' => $client->phone ?? '-',
                    'created_at' => $createdAt,
                    'closed_at' => $closedAt,
                    'overdue' => true,
                    'view_url' => route('admin.complaints.show', $complaint->id),
                ];
            })
            ->values();

        $dashboardComplaints = (clone $complaintsQuery)
            ->select([
                'complaints.id',
                'complaints.status',
                'complaints.category_id',
                'complaints.created_at',
                'complaints.closed_at',
                'complaints.client_id',
                'complaints.house_id',
                'complaints.assigned_employee_id'
            ])
            ->selectRaw("
                (
                    complaints.status IN ('new', 'assigned', 'in_progress') AND 
                    EXISTS(
                        SELECT 1 FROM sla_rules 
                        WHERE sla_rules.category_id = complaints.category_id
                        AND sla_rules.status = 'active'
                        AND sla_rules.deleted_at IS NULL
                        AND complaints.created_at < DATE_SUB(NOW(), INTERVAL sla_rules.max_resolution_time HOUR)
                    )
                ) as is_overdue_sql
            ")
            ->with([
                'client:id,client_name,phone,address', 
                'assignedEmployee:id,name', 
                'house:id,house_no,address'
            ])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($complaint) use ($performaStatuses) {
                $statusKey = $complaint->status === 'new' ? 'assigned' : $complaint->status;
                $statusKey = $statusKey === 'closed' ? 'resolved' : $statusKey;

                $statusLabel = $statusKey === 'resolved'
                    ? 'Addressed'
                    : ucfirst(str_replace('_', ' ', $statusKey));

                $performaType = in_array($complaint->status, $performaStatuses, true)
                    ? $complaint->status
                    : null;

                $client = $complaint->client;
 
                $createdAt = $complaint->created_at
                    ? $complaint->created_at->timezone('Asia/Karachi')->format('M d, Y H:i')
                    : null;
                $closedAt = $complaint->closed_at
                    ? $complaint->closed_at->timezone('Asia/Karachi')->format('M d, Y H:i')
                    : null;

                return [
                    'id' => $complaint->id,
                    'cmp' => $complaint->id,
                    'status' => $statusKey,
                    'status_label' => $statusLabel,
                    'performa_type' => $performaType,
                    'performa_label' => $performaType ? ucfirst(str_replace('_', ' ', $performaType)) : '-',
                    'category' => $complaint->category_display ?? 'N/A',
                    'designation' => $complaint->assignedEmployee->designation->name ?? 'N/A',
                    'client_name' => $client->client_name ?? 'N/A',
                    'house_no' => $complaint->house->house_no ?? 'N/A',
                    'address' => $complaint->house->address
                        ?? $client->address
                        ?? 'N/A',
                    'phone' => $client->phone ?? '-',
                    'created_at' => $createdAt,
                    'closed_at' => $closedAt,
                    'overdue' => (bool) $complaint->is_overdue_sql,
                    'view_url' => route('admin.complaints.show', $complaint->id),
                ];
            })
            ->values();

        // Get rolling 12-month complaints data
        $monthlyComplaints = [];
        $monthLabels = [];

        $applyGlobalFilters = function ($q, $dateRangeOverride = null, $tablePrefix = 'complaints') use ($request, $category, $status, $dateRange, $cmesId, $cityId, $sectorId, $locationScope, $user, $self) {
            // Qualify columns with table prefix to avoid ambiguity in joined queries
            $cityCol = $tablePrefix ? $tablePrefix . '.city_id' : 'city_id';
            $sectorCol = $tablePrefix ? $tablePrefix . '.sector_id' : 'sector_id';
            $statusCol = $tablePrefix ? $tablePrefix . '.status' : 'status';
            $categoryCol = $tablePrefix ? $tablePrefix . '.category' : 'category';
            $createdAtCol = $tablePrefix ? $tablePrefix . '.created_at' : 'created_at';

            // Priority 1: Direct GE Group (city) / Node (sector) filters
            if ($cityId) {
                if ($self->canAccessCity((int) $cityId, $locationScope)) {
                    $allowedSectors = $self->getPermittedSectorsForCity((int) $cityId, $locationScope);
                    $sectorIdsForCity = empty($allowedSectors)
                        ? \App\Models\Sector::where('city_id', $cityId)->pluck('id')->toArray()
                        : $allowedSectors;

                    if ($sectorId) {
                        if ($self->canAccessSector((int) $sectorId, $locationScope)) {
                            $q->where($sectorCol, $sectorId);
                        } else {
                            $q->whereRaw('1 = 0');
                        }
                    } else {
                        // Inclusive GE Group filter (Graph)
                        $q->where(function ($query) use ($cityId, $sectorIdsForCity, $cityCol, $sectorCol) {
                            $query->where($cityCol, $cityId);
                            if (!empty($sectorIdsForCity)) {
                                $query->orWhereIn($sectorCol, $sectorIdsForCity);
                            }
                        });
                    }
                } else {
                    $q->whereRaw('1 = 0');
                }
            } elseif ($sectorId) {
                if ($self->canAccessSector((int) $sectorId, $locationScope)) {
                    $q->where($sectorCol, $sectorId);
                } else {
                    $q->whereRaw('1 = 0');
                }
            }

            // Priority 2: CMES Filter (Always apply if set, inclusive)
            if ($cmesId) {
                $cityIdsForCmes = City::where('cme_id', $cmesId)->pluck('id')->toArray();
                $sectorIdsForCmes = Sector::where(function ($sq) use ($cmesId, $cityIdsForCmes) {
                    $sq->where('cme_id', $cmesId);
                    if (!empty($cityIdsForCmes)) $sq->orWhereIn('city_id', $cityIdsForCmes);
                })->pluck('id')->toArray();

                $q->where(function ($sub) use ($cityIdsForCmes, $sectorIdsForCmes, $cityCol, $sectorCol) {
                    if (!empty($cityIdsForCmes)) $sub->whereIn($cityCol, $cityIdsForCmes);
                    if (!empty($sectorIdsForCmes)) {
                        $method = !empty($cityIdsForCmes) ? 'orWhereIn' : 'whereIn';
                        $sub->{$method}($sectorCol, $sectorIdsForCmes);
                    }
                });
            }

            // Priority 3: Default Location Scope (if not manual)
            if (!$cityId && !$sectorId && !$cmesId && !empty($locationScope['restricted'])) {
                $self->applyFrontendLocationScope($q, $locationScope, $cityCol, $sectorCol);
            }

            // Global Metadata Filters
            if ($category && $category !== 'all') {
                if (is_numeric($category)) {
                    $q->where($tablePrefix ? $tablePrefix . '.category_id' : 'category_id', $category);
                } else {
                    $q->whereHas('category', function($subQ) use ($category) {
                        $subQ->where('name', $category);
                    });
                }
            }
            if ($status && $status !== 'all') {
                $q->where($statusCol, $status);
            }

            $effectiveDateRange = $dateRangeOverride ?? $dateRange;
            if ($effectiveDateRange) {
                $now = now();
                switch ($effectiveDateRange) {
                    case 'yesterday': $q->whereDate($createdAtCol, $now->copy()->subDay()->toDateString()); break;
                    case 'today': $q->whereDate($createdAtCol, $now->toDateString()); break;
                    case 'this_week': $q->whereBetween($createdAtCol, [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]); break;
                    case 'last_week': $q->whereBetween($createdAtCol, [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()]); break;
                    case 'this_month': $q->whereMonth($createdAtCol, $now->month)->whereYear($createdAtCol, $now->year); break;
                    case 'last_month': $q->whereMonth($createdAtCol, $now->copy()->subMonth()->month)->whereYear($createdAtCol, $now->copy()->subMonth()->year); break;
                    case 'last_6_months': $q->where($createdAtCol, '>=', $now->copy()->subMonths(6)->startOfDay()); break;
                    case 'this_year': $q->whereYear($createdAtCol, $now->year); break;
                    case 'last_year': $q->whereYear($createdAtCol, $now->copy()->subYear()->year); break;
                    case 'custom':
                        if ($request->has('start_date') && $request->has('end_date')) {
                            $q->whereBetween($createdAtCol, [
                                \Carbon\Carbon::parse($request->start_date)->startOfDay(),
                                \Carbon\Carbon::parse($request->end_date)->endOfDay()
                            ]);
                        }
                        break;
                }
            }
        };

        // Efficiently fetch all rolling 12-month graph data in two queries
        $startDate = now()->startOfMonth()->subMonths(11);
        
        $monthlyData = (clone $graphBaseQuery)
            ->where('complaints.created_at', '>=', $startDate)
            ->selectRaw("
                YEAR(complaints.created_at) as year,
                MONTH(complaints.created_at) as month,
                COUNT(*) as total,
                SUM(CASE WHEN complaints.status IN ('resolved', 'closed') THEN 1 ELSE 0 END) as addressed,
                SUM(CASE WHEN complaints.status = 'barak_damages' THEN 1 ELSE 0 END) as barak,
                SUM(CASE WHEN complaints.status IN ('work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa') THEN 1 ELSE 0 END) as performa
            ")
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->keyBy(function ($row) {
                return $row->year . '-' . $row->month;
            });

        // Initialize arrays
        $monthlyComplaints = [];
        $monthLabels = [];
        $resolvedVsEdData = [];
        $recentEdData = [];
        $unauthorizedData = [];
        $performaData = [];
        $yearTdData = [];
        
        // Cumulative sum for YearTD
        $currentCumulative = (clone $graphBaseQuery)
            ->whereYear('complaints.created_at', now()->year)
            ->whereMonth('complaints.created_at', '<', $startDate->month)
            ->count();

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->startOfMonth()->subMonths($i);
            $key = $date->year . '-' . $date->month;
            $monthLabels[] = $date->format('M');
            
            $row = $monthlyData->get($key);
            $total = $row ? $row->total : 0;
            $addressed = $row ? $row->addressed : 0;
            $barak = $row ? $row->barak : 0;
            $performa = $row ? $row->performa : 0;

            $monthlyComplaints[] = $total;
            $recentEdData[] = $total;
            $resolvedVsEdData[] = $addressed;
            $unauthorizedData[] = $barak;
            $performaData[] = $performa;
            
            // YearTD Cumulative logic (reset at year start)
            if ($date->month == 1) {
                $currentCumulative = 0;
            }
            $currentCumulative += $total;
            $yearTdData[] = $currentCumulative;
        }

        $employeePerformanceQuery = Employee::query();
        $this->filterEmployeesByLocation($employeePerformanceQuery, $user);
        $this->applyFrontendLocationScope($employeePerformanceQuery, $locationScope);

        // Apply dynamic dashboard filters to Employee list
        if ($cityId) {
            $employeePerformanceQuery->where('city_id', $cityId);
        } elseif ($sectorId) {
            $employeePerformanceQuery->where('sector_id', $sectorId);
        } elseif ($cmesId) {
            $cityIdsForEmp = City::where('cme_id', $cmesId)->pluck('id')->toArray();
            $employeePerformanceQuery->whereIn('city_id', $cityIdsForEmp);
        }
        $employeePerformance = $employeePerformanceQuery
            ->withCount([
                'assignedComplaints' => function ($query) use ($applyGlobalFilters) {
                    $applyGlobalFilters($query);
                },
                'assignedComplaints as resolved_complaints_count' => function ($query) use ($applyGlobalFilters) {
                    $applyGlobalFilters($query);
                    $query->whereIn('status', ['resolved', 'closed']);
                }
            ])
            ->orderBy('assigned_complaints_count', 'desc')
            ->limit(10)
            ->get();

        // Prepare Employee Graph Data (Top 10 Most Assigned)
        $empGraphLabels = $employeePerformance->pluck('name')->toArray();
        $empGraphTotal = $employeePerformance->pluck('assigned_complaints_count')->toArray();
        $empGraphResolved = $employeePerformance->pluck('resolved_complaints_count')->toArray();

        // Get Employees with Least Assigned Complaints (Bottom 10)
        $employeeLeastAssignedQuery = Employee::query();
        $this->filterEmployeesByLocation($employeeLeastAssignedQuery, $user);
        $this->applyFrontendLocationScope($employeeLeastAssignedQuery, $locationScope);

        // Apply dynamic dashboard filters to Employee list
        if ($cityId) {
            $employeeLeastAssignedQuery->where('city_id', $cityId);
        } elseif ($sectorId) {
            $employeeLeastAssignedQuery->where('sector_id', $sectorId);
        } elseif ($cmesId) {
            $cityIdsForEmpLeast = City::where('cme_id', $cmesId)->pluck('id')->toArray();
            $employeeLeastAssignedQuery->whereIn('city_id', $cityIdsForEmpLeast);
        }
        $employeeLeastAssigned = $employeeLeastAssignedQuery
            ->withCount([
                'assignedComplaints' => function ($query) use ($applyGlobalFilters) {
                    $applyGlobalFilters($query);
                },
                'assignedComplaints as resolved_complaints_count' => function ($query) use ($applyGlobalFilters) {
                    $applyGlobalFilters($query);
                    $query->whereIn('status', ['resolved', 'closed']);
                }
            ])
            ->orderBy('assigned_complaints_count', 'asc') // Ascending to get least assigned
            ->limit(10)
            ->get();

        // Prepare Least Assigned Employee Graph Data
        $empLeastGraphLabels = $employeeLeastAssigned->pluck('name')->toArray();
        $empLeastGraphTotal = $employeeLeastAssigned->pluck('assigned_complaints_count')->toArray();
        $empLeastGraphResolved = $employeeLeastAssigned->pluck('resolved_complaints_count')->toArray();

        $slaPerformance = [
            'total' => 0,
            'within_sla' => 0,
            'breached' => 0,
            'sla_percentage' => 0,
        ];

        // Fetch CMES list for dropdown - filter based on user privileges
        $cmesListQuery = \App\Models\Cme::where('status', 'active');

        // Apply CMES filtering based on user privileges
        if ($user && !empty($user->cme_ids)) {
            // User has specific CMES assigned, show only those
            $cmesListQuery->whereIn('id', $user->cme_ids);
        } elseif (!empty($locationScope['restricted'])) {
            // User has restricted location scope (e.g. GE user), derive CMES from assigned cities/sectors
            $derivedCmeIds = [];

            // From assigned cities
            if (!empty($locationScope['city_ids'])) {
                $cityCmeIds = \App\Models\City::whereIn('id', $locationScope['city_ids'])
                    ->pluck('cme_id')
                    ->filter()
                    ->unique()
                    ->toArray();
                $derivedCmeIds = array_merge($derivedCmeIds, $cityCmeIds);
            }

            // From assigned sectors
            if (!empty($locationScope['sector_ids'])) {
                $sectorCmeIds = \App\Models\Sector::whereIn('id', $locationScope['sector_ids'])
                    ->pluck('cme_id')
                    ->filter()
                    ->unique()
                    ->toArray();
                $derivedCmeIds = array_merge($derivedCmeIds, $sectorCmeIds);
            }

            if (!empty($derivedCmeIds)) {
                $cmesListQuery->whereIn('id', array_unique($derivedCmeIds));
            } else {
                // Restricted user but no derived CMES -> show nothing
                $cmesListQuery->whereRaw('1 = 0');
            }
        }

        $cmesList = $cmesListQuery->orderBy('name')->get();

        // Get CME Complaint Stats for Graph
        $cmeGraphLabels = [];
        $cmeGraphData = [];
        $cmeResolvedData = []; // New array for addressed complaints

        $cmeDateRange = $request->get('cme_date_range', $dateRange); // Use specific filter or fallback to global

        // Check if user has unrestricted access (all privileges)
        // User is unrestricted if:
        // 1. They have no location restrictions (no group_ids and no node_ids), OR
        // 2. They have cme_ids that cover ALL available CMEs (meaning they can see everything)
        $hasUnrestrictedAccess = false;

        if ($user) {
            // Check if user has no location restrictions
            $hasNoLocationRestrictions = empty($user->group_ids) && empty($user->node_ids);

            // Check if user has all CMEs assigned
            $hasAllCmes = false;
            if (!empty($user->cme_ids)) {
                $totalCmes = \App\Models\Cme::where('status', 'active')->count();
                $userCmesCount = count($user->cme_ids);
                $hasAllCmes = ($userCmesCount >= $totalCmes);
            }

            $hasUnrestrictedAccess = $hasNoLocationRestrictions || $hasAllCmes;
        }

        // Efficiently fetch CMES Graph Data using grouped queries
        if (!$hasUnrestrictedAccess && $user && !empty($user->cme_ids)) {
            // CME User: Show all GE Groups (cities) under their assigned CMEs
            $geGroupsForCme = \App\Models\City::whereIn('cme_id', $user->cme_ids)
                ->where(function ($q) {
                    $q->where('name', 'LIKE', '%GE%')
                        ->orWhere('name', 'LIKE', '%AGE%');
                })
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
            
            $cityIds = $geGroupsForCme->pluck('id')->toArray();
            
            $cmeStats = \App\Models\Complaint::whereIn('city_id', $cityIds)
                ->selectRaw('city_id, COUNT(*) as total, SUM(CASE WHEN status IN ("resolved", "closed") THEN 1 ELSE 0 END) as resolved');
            
            $this->applyCmeDateFilter($cmeStats, $cmeDateRange);
            $cmeStats = $cmeStats->groupBy('city_id')->get()->keyBy('city_id');

            foreach ($geGroupsForCme as $city) {
                $cmeGraphLabels[] = $city->name;
                $stat = $cmeStats->get($city->id);
                $cmeGraphData[] = $stat->total ?? 0;
                $cmeResolvedData[] = $stat->resolved ?? 0;
            }
        } elseif (!$hasUnrestrictedAccess && $user && !empty($user->group_ids)) {
            // GE User: Show all GE Nodes (sectors) under their assigned GE Groups
            $geNodesForGroup = \App\Models\Sector::whereIn('city_id', $user->group_ids)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
            
            $sectorIds = $geNodesForGroup->pluck('id')->toArray();
            
            $cmeStats = \App\Models\Complaint::whereIn('sector_id', $sectorIds)
                ->selectRaw('sector_id, COUNT(*) as total, SUM(CASE WHEN status IN ("resolved", "closed") THEN 1 ELSE 0 END) as resolved');
                
            $this->applyCmeDateFilter($cmeStats, $cmeDateRange);
            $cmeStats = $cmeStats->groupBy('sector_id')->get()->keyBy('sector_id');

            foreach ($geNodesForGroup as $sector) {
                $cmeGraphLabels[] = $sector->name;
                $stat = $cmeStats->get($sector->id);
                $cmeGraphData[] = $stat->total ?? 0;
                $cmeResolvedData[] = $stat->resolved ?? 0;
            }
        } elseif (!$hasUnrestrictedAccess && $user && !empty($user->node_ids)) {
            // Node User: Show only their assigned GE Nodes (sectors)
            $userNodes = \App\Models\Sector::whereIn('id', $user->node_ids)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
            
            $sectorIds = $userNodes->pluck('id')->toArray();
            
            $cmeStats = \App\Models\Complaint::whereIn('sector_id', $sectorIds)
                ->selectRaw('sector_id, COUNT(*) as total, SUM(CASE WHEN status IN ("resolved", "closed") THEN 1 ELSE 0 END) as resolved');
                
            $this->applyCmeDateFilter($cmeStats, $cmeDateRange);
            $cmeStats = $cmeStats->groupBy('sector_id')->get()->keyBy('sector_id');

            foreach ($userNodes as $sector) {
                $cmeGraphLabels[] = $sector->name;
                $stat = $cmeStats->get($sector->id);
                $cmeGraphData[] = $stat->total ?? 0;
                $cmeResolvedData[] = $stat->resolved ?? 0;
            }
        } else {
            // Admin or Unrestricted
            foreach ($cmesList as $cme) {
                $cmeGraphLabels[] = $cme->name;
                $cityIdsForCme = \App\Models\City::where('cme_id', $cme->id)->pluck('id')->toArray();
                
                $cmeStatsQuery = \App\Models\Complaint::where(function ($q) use ($cityIdsForCme, $cme) {
                    if (!empty($cityIdsForCme)) $q->whereIn('city_id', $cityIdsForCme);
                    $q->orWhereIn('sector_id', function($sq) use ($cme) {
                        $sq->select('id')->from('sectors')->where('cme_id', $cme->id);
                    });
                })->selectRaw('COUNT(*) as total, SUM(CASE WHEN status IN ("resolved", "closed") THEN 1 ELSE 0 END) as resolved');

                $this->applyCmeDateFilter($cmeStatsQuery, $cmeDateRange);
                $stat = $cmeStatsQuery->first();
                
                $cmeGraphData[] = $stat->total ?? 0;
                $cmeResolvedData[] = $stat->resolved ?? 0;
            }
        }

        // Get Top 15 Products by Issued Quantity
        // REVERT: Use Spare model directly as ComplaintSpare might be empty or unpopulated
        $categoryUsageQuery = \App\Models\Spare::selectRaw('
                item_name,
                SUM(issued_quantity) as total_used,
                SUM(total_received_quantity) as total_received
            ')
            ->whereNotNull('item_name')
            ->groupBy('item_name')
            ->orderByDesc('total_used')
            ->limit(10);

        // Apply Global Location Scoping
        $this->applyFrontendLocationScope($categoryUsageQuery, $locationScope, 'city_id', 'sector_id');

        // Apply dynamic dashboard filters to Products list
        if ($cityId) {
            $categoryUsageQuery->where('city_id', $cityId);
        } elseif ($sectorId) {
            $categoryUsageQuery->where('sector_id', $sectorId);
        } elseif ($cmesId) {
            $cityIdsForProducts = City::where('cme_id', $cmesId)->pluck('id')->toArray();
            $categoryUsageQuery->whereIn('city_id', $cityIdsForProducts);
        }

        // If specific category date range is provided (Note: used_at/updated_at on Spare might be different)
        $categoryDateRange = $request->get('category_date_range');
        if ($categoryDateRange) {
            $now = now();
            switch ($categoryDateRange) {
                case 'this_month':
                    $categoryUsageQuery->whereMonth('updated_at', $now->month)
                        ->whereYear('updated_at', $now->year);
                    break;
                case 'last_6_months':
                    $categoryUsageQuery->where('updated_at', '>=', $now->copy()->subMonths(6)->startOfDay());
                    break;
                case 'this_year':
                    $categoryUsageQuery->whereYear('updated_at', $now->year);
                    break;
                case 'last_year':
                    $categoryUsageQuery->whereYear('updated_at', $now->copy()->subYear()->year);
                    break;
            }
        }

        $categoryUsageData = $categoryUsageQuery->get();

        $categoryLabels = $categoryUsageData->pluck('item_name')->toArray();

        $categoryUsageValues = $categoryUsageData->pluck('total_used')->toArray();
        $categoryTotalReceivedValues = $categoryUsageData->pluck('total_received')->toArray();

        // Data is now ready for return at the end of the method



        // Determine user type for graph heading
        // Priority: Unrestricted (Admin) > CME User > GE User > Node User
        // Users with unrestricted access should see CMEs, not GE Groups/Nodes
        $isCmeUser = !$hasUnrestrictedAccess && $user && !empty($user->cme_ids);
        $isGeUser = !$hasUnrestrictedAccess && !$isCmeUser && $user && !empty($user->group_ids);
        $isNodeUser = !$hasUnrestrictedAccess && !$isCmeUser && !$isGeUser && $user && !empty($user->node_ids);

        // Prepare Monthly Table Data
        $monthlyTableData = [];
        $tableEntities = collect([]);
        $entityType = '';

        if ($isCmeUser) {
            $tableEntities = $geGroupsForCme ?? collect([]);
            $entityType = 'city';
        } elseif ($isGeUser) {
            $tableEntities = $geNodesForGroup ?? collect([]);
            $entityType = 'sector';
        } elseif ($isNodeUser) {
            $tableEntities = $userNodes ?? collect([]);
            $entityType = 'sector';
        } else {
            $tableEntities = $cmesList;
            $entityType = 'cme';
        }

        if ($tableEntities->isNotEmpty()) {
            // Process months
            $months = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthName = date('F', mktime(0, 0, 0, $m, 1));
                $months[$m] = $monthName;
                $monthlyTableData[$monthName] = [];
            }

            // FETCHING ALL DATA IN ONE GO - OPTIMIZED
            $entityIds = $tableEntities->pluck('id')->toArray();
            
            $tableQuery = \App\Models\Complaint::query();
            if ($entityType === 'cme') {
                $allCityIds = \App\Models\City::whereIn('cme_id', $entityIds)->pluck('id')->toArray();
                $tableQuery->where(function($q) use ($allCityIds, $entityIds) {
                    if (!empty($allCityIds)) $q->whereIn('city_id', array_unique($allCityIds));
                    $q->orWhereIn('sector_id', function($sq) use ($entityIds) {
                        $sq->select('id')->from('sectors')->whereIn('cme_id', $entityIds);
                    });
                });
            } elseif ($entityType === 'city') {
                $tableQuery->where(function($q) use ($entityIds) {
                    $q->whereIn('city_id', $entityIds)
                      ->orWhereIn('sector_id', function($sq) use ($entityIds) {
                          $sq->select('id')->from('sectors')->whereIn('city_id', $entityIds);
                      });
                });
            } else {
                $tableQuery->whereIn('sector_id', $entityIds);
            }

            $this->applyCmeDateFilter($tableQuery, $cmeDateRange);

            $allStats = $tableQuery->selectRaw('
                city_id, sector_id, MONTH(created_at) as month,
                COUNT(*) as total,
                SUM(CASE WHEN status IN ("resolved", "closed") THEN 1 ELSE 0 END) as resolved
            ')
            ->groupBy('city_id', 'sector_id', 'month')
            ->get();

            // PRE-CALCULATE MAPPINGS FOR ACCURATE ATTRIBUTION
            $cityCmeMap = [];
            $sectorCmeMap = [];
            $sectorCityMap = [];

            if ($entityType === 'cme') {
                $cityCmeMap = \App\Models\City::whereIn('id', $allStats->pluck('city_id')->filter()->unique())->pluck('cme_id', 'id')->toArray();
                $sectorCmeMap = \App\Models\Sector::whereIn('id', $allStats->pluck('sector_id')->filter()->unique())->pluck('cme_id', 'id')->toArray();
                $sectorCityMap = \App\Models\Sector::whereIn('id', $allStats->pluck('sector_id')->filter()->unique())->pluck('city_id', 'id')->toArray();
            }

            // Mapping results in memory for performance
            foreach ($months as $mNum => $mName) {
                foreach ($tableEntities as $entity) {
                    $total = 0; $resolved = 0;
                    
                    foreach ($allStats as $stat) {
                        if ($stat->month != $mNum) continue;
                        
                        $match = false;
                        if ($entityType === 'cme') {
                            // Match Graph Logic: city_id in CME cities OR sector_id in CME sectors (direct)
                            if ($stat->city_id && isset($cityCmeMap[$stat->city_id]) && $cityCmeMap[$stat->city_id] == $entity->id) {
                                $match = true;
                            } elseif ($stat->sector_id && isset($sectorCmeMap[$stat->sector_id]) && $sectorCmeMap[$stat->sector_id] == $entity->id) {
                                $match = true;
                            }
                        } elseif ($entityType === 'city') {
                            // Match Graph Logic (isCmeUser): only check city_id
                            if ($stat->city_id == $entity->id) $match = true;
                        } else {
                            // Match Graph Logic (isGeUser/isNodeUser): only check sector_id
                            if ($stat->sector_id == $entity->id) $match = true;
                        }

                        if ($match) {
                            $total += $stat->total;
                            $resolved += $stat->resolved;
                        }
                    }
                    $monthlyTableData[$mName][$entity->name] = ['total' => $total, 'resolved' => $resolved];
                }
            }
        }

        // Prepare Stock Consumption Data - Monthly with Inventory Details
        $filters = [
            'cmes_id' => $cmesId,
            'city_id' => $cityId,
            'sector_id' => $sectorId,
            'category' => $category,
            'date_range' => $dateRange,
        ];
        $stockConsumptionData = $this->getStockConsumptionData($user, $locationScope, 'all_time', $filters);



        if ($request->ajax()) {
            return response()->json([
                'stats' => $stats,
                'complaintsByStatus' => $complaintsByStatus,
                'monthlyComplaints' => $monthlyComplaints,
                'monthLabels' => $monthLabels,
                'resolvedVsEdData' => $resolvedVsEdData,
                'recentEdData' => $recentEdData,
                'unauthorizedData' => $unauthorizedData,
                'performaData' => $performaData,
                'cmeGraphLabels' => $cmeGraphLabels,
                'cmeGraphData' => $cmeGraphData,
                'cmeResolvedData' => $cmeResolvedData,
                'dashboardComplaints' => $dashboardComplaints,
                'overdueComplaints' => $overdueComplaints,
                'categoryLabels' => $categoryLabels,
                'categoryUsageValues' => $categoryUsageValues,
                'categoryTotalReceivedValues' => $categoryTotalReceivedValues,
                'empGraphLabels' => $empGraphLabels,
                'empGraphTotal' => $empGraphTotal,
                'empGraphResolved' => $empGraphResolved,
                'empLeastGraphLabels' => $empLeastGraphLabels,
                'empLeastGraphTotal' => $empLeastGraphTotal,
                'empLeastGraphResolved' => $empLeastGraphResolved,
                'stockConsumptionData' => $stockConsumptionData,
                'serverStats' => $stats
            ]);
        }

        return view('frontend.dashboard', compact(
            'stats',
            'geGroups',
            'geNodes',
            'categories',
            'statuses',
            'monthlyComplaints',
            'monthLabels',
            'complaintsByStatus',
            'resolvedVsEdData',
            'recentEdData',
            'unauthorizedData',
            'performaData',
            'yearTdData',
            'cityId',
            'sectorId',
            'category',
            'status',
            'dateRange',
            'cmesList',
            'cmesId',
            'cmeGraphLabels',
            'cmeGraphData',
            'cmeResolvedData',
            'isCmeUser',
            'isGeUser',
            'isNodeUser',
            'dashboardComplaints',
            'empGraphLabels',
            'empGraphTotal',
            'empGraphResolved',
            'empLeastGraphLabels',
            'empLeastGraphTotal',
            'empLeastGraphResolved',
            'monthlyTableData',
            'tableEntities',
            'stockConsumptionData',
            'categoryLabels',
            'categoryUsageValues',
            'categoryTotalReceivedValues',
            'overdueComplaints'
        ));
    }

    /**
     * Show the user profile.
     *
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        $user = Auth::guard('frontend')->user();
        return view('frontend.profile', compact('user'));
    }

    /**
     * Update the user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::guard('frontend')->user();

        $request->validate([
            'username' => 'required|string|max:255|unique:frontend_users,username,' . $user->id,
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update([
            'username' => $request->username,
            'name' => $request->name,
            'status' => $request->status,
            'phone' => $request->phone,
        ]);

        return redirect()->route('frontend.profile')->with('success', 'Profile updated successfully.');
    }

    /**
     * Show the change password form.
     */
    public function changePassword()
    {
        return view('frontend.change-password');
    }

    /**
     * Update the user password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::guard('frontend')->user();

        if (!\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
        }

        $user->update([
            'password' => \Hash::make($request->password),
        ]);

        return redirect()->route('frontend.password')->with('success', 'Password updated successfully.');
    }

    /**
     * Show the public feedback form for a complaint.
     */
    public function feedback($id)
    {
        $complaint = Complaint::with(['category', 'assignedEmployee.designation', 'client'])->findOrFail($id);

        // If feedback already exists, show success message
        if (\App\Models\ComplaintFeedback::where('complaint_id', $id)->exists()) {
            return view('frontend.feedback', [
                'complaint' => $complaint,
                'already_submitted' => true
            ]);
        }

        return view('frontend.feedback', compact('complaint'));
    }

    /**
     * Submit public feedback for a complaint.
     */
    public function submitFeedback(Request $request, $id)
    {
        $complaint = Complaint::findOrFail($id);

        // Check if feedback already exists
        if (\App\Models\ComplaintFeedback::where('complaint_id', $id)->exists()) {
            return redirect()->route('frontend.feedback', $id)
                ->with('error', 'Feedback already submitted for this complaint.');
        }

        $request->validate([
            'submitted_by' => 'required|string|max:255',
            'overall_rating' => 'required|in:excellent,good,satisfied,fair,poor',
            'comments' => 'nullable|string|max:1000',
            'remarks' => 'nullable|string|max:1000',
        ]);

        \App\Models\ComplaintFeedback::create([
            'complaint_id' => $complaint->id,
            'client_id' => $complaint->client_id,
            'submitted_by' => $request->submitted_by,
            'overall_rating' => $request->overall_rating,
            'rating_score' => $this->getRatingScore($request->overall_rating),
            'comments' => $request->comments,
            'remarks' => $request->remarks,
            'feedback_date' => now(),
            'entered_at' => now(),
            // entered_by is null for public feedback
        ]);

        // Auto-resolve complaint if not already resolved/closed
        if (!in_array($complaint->status, ['resolved', 'closed'])) {
            $complaint->update([
                'status' => 'resolved',
                'closed_at' => now(),
                'resolved_at' => now(),
            ]);
            
            // Log the status change
            \App\Models\ComplaintLog::create([
                'complaint_id' => $complaint->id,
                'user_id' => null, // System action via public feedback
                'action' => 'status_changed',
                'remarks' => 'Status changed to Addressed (Resolved) automatically upon receiving client feedback.'
            ]);
        }

        return redirect()->route('frontend.feedback', $id)->with('success', 'Thank you for your feedback!');
    }

    /**
     * Show the complaint details.
     */
    public function show(Request $request, $id)
    {
        $complaint = Complaint::with([
            'client',
            'city',
            'sector',
            'category',
            'assignedEmployee.designation',
            'attachments',
            'spareApprovals',
            'feedback.enteredBy'
        ])->findOrFail($id);

        // Check Access
        $user = Auth::user();
        if ($user) {
            $scope = $this->getFrontendUserLocationScope($user);
            if (!empty($scope['restricted'])) {
                $cityIds = $scope['city_ids'] ?? [];
                $sectorIds = $scope['sector_ids'] ?? [];

                // Valid if complaint's city is in user's city_ids
                // OR complaint's sector is in user's sector_ids
                $hasCityAccess = !empty($cityIds) && in_array($complaint->city_id, $cityIds);
                $hasSectorAccess = !empty($sectorIds) && in_array($complaint->sector_id, $sectorIds);

                if (!$hasCityAccess && !$hasSectorAccess) {
                    if ($request->ajax()) {
                        return response('<div class="p-4 text-center text-danger font-weight-bold">You are not authorized to view this complaint.</div>', 403);
                    }
                    abort(403, 'Unauthorized access to this complaint.');
                }
            }
        }

        if ($request->ajax()) {
            return view('frontend.complaints.partials.detail_card', compact('complaint'))->render();
        }

        return view('frontend.complaints.show', compact('complaint'));
    }

    /**
     * Show the user profile.
     *
     * @return \Illuminate\View\View
     */
    public function stockAll(Request $request)
    {
        $user = Auth::user();
        $locationScope = $this->getFrontendUserLocationScope($user);
        
        $selectedYear = $request->get('year', 'all_time');
        
        // Dashboard filters
        $filters = [
            'city_id' => $request->get('city_id'),
            'sector_id' => $request->get('sector_id'),
            'cmes_id' => $request->get('cmes_id'),
            'date_range' => $request->get('date_range')
        ];
        
        $monthLabels = [];
        if ($selectedYear === 'all_time') {
            for ($i = 11; $i >= 0; $i--) {
                $monthLabels[] = now()->startOfMonth()->subMonths($i)->format('M');
            }
        } else {
            for ($m = 1; $m <= 12; $m++) {
                $monthLabels[] = date('M', mktime(0, 0, 0, $m, 1));
            }
        }

        $stockConsumptionData = $this->getStockConsumptionData($user, $locationScope, $selectedYear, $filters);

        // Get available years for filter
        $years = collect(range(date('Y'), 2023))->unique()->values()->all();

        return view('frontend.stock.all', compact('stockConsumptionData', 'monthLabels', 'selectedYear', 'years'));
    }

    protected function getStockConsumptionData($user, $locationScope, $year = null, $dashboardFilters = [])
    {
        $year = $year ?: 'all_time';
        $hasUnrestrictedAccess = empty($locationScope['restricted']);
        $stockConsumptionData = [];
        $sparesListQuery = \App\Models\Spare::selectRaw('
                item_name,
                SUM(total_received_quantity) as total_received_quantity,
                SUM(issued_quantity) as issued_quantity,
                SUM(stock_quantity) as stock_quantity
            ')
            ->whereNotNull('item_name')
            ->groupBy('item_name')
            ->orderByRaw('SUM(issued_quantity) desc');

        $this->applyFrontendLocationScope($sparesListQuery, $locationScope, 'city_id', 'sector_id');

        // Filter by category if provided
        if (!empty($dashboardFilters['category']) && $dashboardFilters['category'] !== 'all') {
            $cat = $dashboardFilters['category'];
            $sparesListQuery->whereHas('category', function($q) use ($cat) {
                if (is_numeric($cat)) {
                    $q->where('id', $cat);
                } else {
                    $q->where('name', $cat);
                }
            });
        }

        // Apply dashboard filters to spares list - Matching Graph Logic from dashboard() method
        if (!empty($dashboardFilters['city_id'])) {
            $sparesListQuery->where('city_id', $dashboardFilters['city_id']);
        } elseif (!empty($dashboardFilters['sector_id'])) {
            $sparesListQuery->where('sector_id', $dashboardFilters['sector_id']);
        } elseif (!empty($dashboardFilters['cmes_id'])) {
            $cmesId = $dashboardFilters['cmes_id'];
            $cityIdsStats = \App\Models\City::where('cme_id', $cmesId)->pluck('id')->toArray();
            $sparesListQuery->whereIn('city_id', $cityIdsStats);
        }

        $sparesList = $sparesListQuery->get();

        $stockQuery = \App\Models\SpareStockLog::selectRaw('
                spares.item_name,
                YEAR(spare_stock_logs.created_at) as year,
                MONTH(spare_stock_logs.created_at) as month,
                SUM(spare_stock_logs.quantity) as total_qty
            ')
            ->join('spares', 'spare_stock_logs.spare_id', '=', 'spares.id')
            ->where('spare_stock_logs.change_type', 'out')
            ->whereNull('spare_stock_logs.deleted_at')
            ->whereNull('spares.deleted_at');

        if ($year !== 'all_time') {
            $stockQuery->whereYear('spare_stock_logs.created_at', $year);
        }

        if ($hasUnrestrictedAccess) {
            // No filter
        } elseif ($user && !empty($user->cme_ids)) {
            $stockQuery->join('cities', 'spares.city_id', '=', 'cities.id')
                ->whereIn('cities.cme_id', $user->cme_ids);
        } elseif ($user && !empty($user->group_ids)) {
            $stockQuery->whereIn('spares.city_id', $user->group_ids);
        } elseif ($user && !empty($user->node_ids)) {
            $stockQuery->whereIn('spares.sector_id', $user->node_ids);
        }

        // Apply additional dashboard location filters to stock logs
        if (!empty($dashboardFilters['city_id'])) {
            $stockQuery->where('spares.city_id', $dashboardFilters['city_id']);
        } elseif (!empty($dashboardFilters['sector_id'])) {
            $stockQuery->where('spares.sector_id', $dashboardFilters['sector_id']);
        } elseif (!empty($dashboardFilters['cmes_id'])) {
            $cityIdsStats = \App\Models\City::where('cme_id', $dashboardFilters['cmes_id'])->pluck('id')->toArray();
            $stockQuery->whereIn('spares.city_id', $cityIdsStats);
        }

        // Apply Date Range filter if present (Dashboard filters override Year filter)
        if (!empty($dashboardFilters['date_range']) && $dashboardFilters['date_range'] !== 'all_time') {
            $now = now();
            switch ($dashboardFilters['date_range']) {
                case 'this_month':
                    $stockQuery->whereMonth('spare_stock_logs.created_at', $now->month)
                               ->whereYear('spare_stock_logs.created_at', $now->year);
                    break;
                case 'last_6_months':
                    $stockQuery->where('spare_stock_logs.created_at', '>=', $now->copy()->subMonths(6)->startOfDay());
                    break;
                case 'this_year':
                    $stockQuery->whereYear('spare_stock_logs.created_at', $now->year);
                    break;
                case 'last_year':
                    $stockQuery->whereYear('spare_stock_logs.created_at', $now->copy()->subYear()->year);
                    break;
            }
        }

        $stockResults = $stockQuery->groupBy('spares.item_name', 'year', 'month')->get();
        $stockResultsIndex = $stockResults->keyBy(function ($row) {
            return $row->item_name . '_' . $row->year . '_' . $row->month;
        });

        $stockReceivedQuery = \App\Models\SpareStockLog::selectRaw('
                spares.item_name,
                YEAR(spare_stock_logs.created_at) as year,
                MONTH(spare_stock_logs.created_at) as month,
                SUM(spare_stock_logs.quantity) as total_qty
            ')
            ->join('spares', 'spare_stock_logs.spare_id', '=', 'spares.id')
            ->where('spare_stock_logs.change_type', 'in')
            ->whereNull('spare_stock_logs.deleted_at')
            ->whereNull('spares.deleted_at');

        if ($year !== 'all_time') {
            $stockReceivedQuery->whereYear('spare_stock_logs.created_at', $year);
        }

        if ($hasUnrestrictedAccess) {
            // No filter
        } elseif ($user && !empty($user->cme_ids)) {
            $stockReceivedQuery->join('cities', 'spares.city_id', '=', 'cities.id')
                ->whereIn('cities.cme_id', $user->cme_ids);
        } elseif ($user && !empty($user->group_ids)) {
            $stockReceivedQuery->whereIn('spares.city_id', $user->group_ids);
        } elseif ($user && !empty($user->node_ids)) {
            $stockReceivedQuery->whereIn('spares.sector_id', $user->node_ids);
        }

        // Apply additional dashboard location filters to stock logs (Received)
        if (!empty($dashboardFilters['city_id'])) {
            $stockReceivedQuery->where('spares.city_id', $dashboardFilters['city_id']);
        } elseif (!empty($dashboardFilters['sector_id'])) {
            $stockReceivedQuery->where('spares.sector_id', $dashboardFilters['sector_id']);
        } elseif (!empty($dashboardFilters['cmes_id'])) {
            $cityIdsStatsReceived = \App\Models\City::where('cme_id', $dashboardFilters['cmes_id'])->pluck('id')->toArray();
            $stockReceivedQuery->whereIn('spares.city_id', $cityIdsStatsReceived);
        }

        // Apply Date Range filter to Received stock
        if (!empty($dashboardFilters['date_range']) && $dashboardFilters['date_range'] !== 'all_time') {
            $now = now();
            switch ($dashboardFilters['date_range']) {
                case 'this_month':
                    $stockReceivedQuery->whereMonth('spare_stock_logs.created_at', $now->month)
                                       ->whereYear('spare_stock_logs.created_at', $now->year);
                    break;
                case 'last_6_months':
                    $stockReceivedQuery->where('spare_stock_logs.created_at', '>=', $now->copy()->subMonths(6)->startOfDay());
                    break;
                case 'this_year':
                    $stockReceivedQuery->whereYear('spare_stock_logs.created_at', $now->year);
                    break;
                case 'last_year':
                    $stockReceivedQuery->whereYear('spare_stock_logs.created_at', $now->copy()->subYear()->year);
                    break;
            }
        }

        $stockReceivedQuery->groupBy('spares.item_name', 'year', 'month');
        $stockReceivedResults = $stockReceivedQuery->get();
        $stockReceivedIndex = $stockReceivedResults->keyBy(function ($row) {
            return $row->item_name . '_' . $row->year . '_' . $row->month;
        });

        // Define the time window for the report (rolling 12 months for 'all_time', Jan-Dec for specific year)
        $reportMonths = [];
        if ($year === 'all_time') {
            for ($i = 11; $i >= 0; $i--) {
                $d = now()->startOfMonth()->subMonths($i);
                $reportMonths[] = [
                    'label' => $d->format('M'),
                    'year' => (int)$d->year,
                    'month' => (int)$d->month
                ];
            }
        } else {
            for ($m = 1; $m <= 12; $m++) {
                $reportMonths[] = [
                    'label' => date('M', mktime(0, 0, 0, $m, 1)),
                    'year' => (int)$year,
                    'month' => $m
                ];
            }
        }

        foreach ($sparesList as $spare) {
            $monthlyData = [];
            $monthlyReceivedData = [];
            $windowTotalIssued = 0;
            $windowTotalReceived = 0;

            foreach ($reportMonths as $monthInfo) {
                $mName = $monthInfo['label'];
                $mNum = $monthInfo['month'];
                $yNum = $monthInfo['year'];
                $key = $spare->item_name . '_' . $yNum . '_' . $mNum;

                // Issued quantity
                $stat = $stockResultsIndex->get($key);
                $qtyIssued = $stat ? $stat->total_qty : 0;
                $monthlyData[$mName] = $qtyIssued;
                $windowTotalIssued += $qtyIssued;

                // Received quantity
                $statReceived = $stockReceivedIndex->get($key);
                $receivedQty = $statReceived ? $statReceived->total_qty : 0;
                $monthlyReceivedData[$mName] = $receivedQty;
                $windowTotalReceived += $receivedQty;
            }

            // If All Time is selected, use the master counters from the Spare model
            // This ensures matches with Dashboard and avoids issues with missing log history
            if ($year === 'all_time') {
                $stockConsumptionData[$spare->item_name] = [
                    'total_received' => (int)$spare->total_received_quantity,
                    'total_used' => (int)$spare->issued_quantity,
                    'current_stock' => (int)$spare->stock_quantity,
                    'monthly_data' => $monthlyData,
                    'monthly_received_data' => $monthlyReceivedData
                ];
            } else {
                // For specific year, use the calculated window values for Received/Used
                $stockConsumptionData[$spare->item_name] = [
                    'total_received' => $windowTotalReceived,
                    'total_used' => $windowTotalIssued,
                    'current_stock' => (int)$spare->stock_quantity, 
                    'monthly_data' => $monthlyData,
                    'monthly_received_data' => $monthlyReceivedData
                ];
            }
        }

        return $stockConsumptionData;
    }

    /**
     * Get rating score from rating text
     */
    public function getRatingScore($rating)
    {
        switch (strtolower($rating)) {
            case 'excellent': return 5;
            case 'good': return 4;
            case 'satisfied': return 3;
            case 'fair': return 2;
            case 'poor': return 1;
            default: return 0;
        }
    }

    /**
     * AJAX endpoint to get cities by CME, respecting user privileges
     */
    public function getCitiesByCmeAjax(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json([]);

        $locationScope = $this->getFrontendUserLocationScope($user);
        $cmeId = $request->query('cme_id');

        if (!$cmeId) {
            return response()->json([]);
        }

        $query = \App\Models\City::where('cme_id', $cmeId)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('name', 'LIKE', '%GE%')
                    ->orWhere('name', 'LIKE', '%AGE%')
                    ->orWhere('name', 'LIKE', '%ge%')
                    ->orWhere('name', 'LIKE', '%age%');
            });

        // Apply privileges
        $accessibleCityIds = $this->getAccessibleCityIdsForDropdown($locationScope);
        if ($accessibleCityIds !== null) {
            $query->whereIn('id', $accessibleCityIds);
        } elseif (!empty($locationScope['restricted'])) {
            return response()->json([]);
        }

        $cities = $query->orderBy('name')->get(['id', 'name']);
        return response()->json($cities);
    }

    /**
     * AJAX endpoint to get sectors by city, respecting user privileges
     */
    public function getSectorsByCityAjax(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json([]);

        $locationScope = $this->getFrontendUserLocationScope($user);
        $cityId = $request->query('city_id');

        if (!$cityId) {
            return response()->json([]);
        }

        $query = \App\Models\Sector::where('city_id', $cityId)
            ->where('status', 'active');

        // Apply privileges
        if (!empty($locationScope['restricted'])) {
            if (!empty($locationScope['sector_ids'])) {
                $query->whereIn('id', $locationScope['sector_ids']);
            } elseif (!empty($locationScope['city_ids'])) {
                $query->whereIn('city_id', $locationScope['city_ids']);
            } elseif (!empty($locationScope['city_sector_map'])) {
                $query->whereIn('city_id', array_keys($locationScope['city_sector_map']));
            } else {
                return response()->json([]);
            }
        }

        $sectors = $query->orderBy('name')->get(['id', 'name']);
        return response()->json($sectors);
    }

    protected function applyCmeDateFilter($query, $dateRange)
    {
        if ($dateRange) {
            $now = now();
            switch ($dateRange) {
                case 'this_month':
                    $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
                    break;
                case 'last_6_months':
                    $query->where('created_at', '>=', $now->copy()->subMonths(6)->startOfDay());
                    break;
                case 'this_year':
                    $query->whereYear('created_at', $now->year);
                    break;
                case 'last_year':
                    $query->whereYear('created_at', $now->copy()->subYear()->year);
                    break;
            }
        }
    }
}
