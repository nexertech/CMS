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

        return $this->applyFrontendLocationScope($query, $scope);
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
        return view('frontend.home');
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
        $dateRange = $request->get('date_range');

        // Build base query with filters
        $complaintsQuery = Complaint::query();

        // Apply location filtering based on GE Group (city_id) and GE Node (sector_id) selections
        $hasRestrictions = !empty($locationScope['restricted']);

        if ($cityId) {
            if ($this->canAccessCity((int) $cityId, $locationScope)) {
                // If sector is also selected, prioritize sector filter
                if ($sectorId) {
                    if ($this->canAccessSector((int) $sectorId, $locationScope)) {
                        $complaintsQuery->where('sector_id', $sectorId);
                    } else {
                        $complaintsQuery->whereRaw('1 = 0');
                    }
                } else {
                    // Only city selected, no sector
                    if (!empty($locationScope['city_ids']) && in_array((int) $cityId, $locationScope['city_ids'])) {
                        $complaintsQuery->where('city_id', $cityId);
                    } else {
                        $allowedSectors = $this->getPermittedSectorsForCity((int) $cityId, $locationScope);
                        if (!empty($allowedSectors)) {
                            $complaintsQuery->whereIn('sector_id', $allowedSectors);
                        } else {
                            $complaintsQuery->whereRaw('1 = 0');
                        }
                    }
                }
            } else {
                $complaintsQuery->whereRaw('1 = 0');
            }
        } elseif ($sectorId) {
            if ($this->canAccessSector((int) $sectorId, $locationScope)) {
                $complaintsQuery->where('sector_id', $sectorId);
            } else {
                $complaintsQuery->whereRaw('1 = 0');
            }
        } elseif ($hasRestrictions) {
            $this->filterComplaintsByLocationForFrontend($complaintsQuery, $user, $locationScope);
        }

        if ($category && $category !== 'all') {
            $complaintsQuery->where('category', $category);
        }

        if ($status && $status !== 'all') {
            $complaintsQuery->where('status', $status);
        }

        // Filter by date range
        if ($dateRange) {
            $now = now();
            switch ($dateRange) {
                case 'yesterday':
                    $complaintsQuery->whereDate('created_at', $now->copy()->subDay()->toDateString());
                    break;
                case 'today':
                    $complaintsQuery->whereDate('created_at', $now->toDateString());
                    break;
                case 'this_week':
                    $complaintsQuery->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
                    break;
                case 'last_week':
                    $complaintsQuery->whereBetween('created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()]);
                    break;
                case 'this_month':
                    $complaintsQuery->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year);
                    break;
                case 'last_month':
                    $complaintsQuery->whereMonth('created_at', $now->copy()->subMonth()->month)
                        ->whereYear('created_at', $now->copy()->subMonth()->year);
                    break;
                case 'last_6_months':
                    $complaintsQuery->where('created_at', '>=', $now->copy()->subMonths(6)->startOfDay());
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

        // If CMES selected, also filter the main complaints query to CMES scope
        if ($cmesId) {
            $cityIdsForCmes = City::where('cme_id', $cmesId)->pluck('id')->toArray();

            $sectorIdsForCmes = Sector::where(function ($q) use ($cmesId, $cityIdsForCmes) {
                $q->where('cme_id', $cmesId);
                if (!empty($cityIdsForCmes)) {
                    $q->orWhereIn('city_id', $cityIdsForCmes);
                }
            })->pluck('id')->toArray();

            if (empty($cityIdsForCmes) && empty($sectorIdsForCmes)) {
                // No matching CMES scope — return no complaints
                $complaintsQuery->whereRaw('1 = 0');
            } else {
                $complaintsQuery->where(function ($q) use ($cityIdsForCmes, $sectorIdsForCmes) {
                    if (!empty($cityIdsForCmes)) {
                        $q->whereIn('city_id', $cityIdsForCmes);
                    }
                    if (!empty($sectorIdsForCmes)) {
                        // If city filter already applied above, this will OR with sector filter
                        $method = !empty($cityIdsForCmes) ? 'orWhereIn' : 'whereIn';
                        $q->{$method}('sector_id', $sectorIdsForCmes);
                    }
                });
            }
        }

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

        // Calculate stats with filters
        $stats = [
            'total_complaints' => (clone $complaintsQuery)->count(),
            'new_complaints' => (clone $complaintsQuery)->where('status', 'new')->count(),
            'pending_complaints' => (clone $complaintsQuery)->whereIn('status', ['new', 'assigned', 'in_progress'])->count(),
            'resolved_complaints' => (clone $complaintsQuery)->whereIn('status', ['resolved', 'closed'])->count(),
            'overdue_complaints' => (clone $complaintsQuery)->overdue()->count(),
            'complaints_today' => (clone $complaintsQuery)->whereDate('created_at', now()->startOfDay())->count(),
            'complaints_this_month' => (clone $complaintsQuery)->where('created_at', '>=', now()->startOfMonth())->count(),
            'complaints_last_month' => (clone $complaintsQuery)->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->startOfMonth()])->count(),
        ];

        // Calculate resolution rate
        $totalComplaints = $stats['total_complaints'];
        $resolvedComplaints = $stats['resolved_complaints'];
        $resolutionRate = $totalComplaints > 0 ? round(($resolvedComplaints / $totalComplaints) * 100) : 0;
        $stats['resolution_rate'] = $resolutionRate;

        // Calculate average resolution time
        $resolvedComplaintsWithTime = (clone $complaintsQuery)
            ->whereIn('status', ['resolved', 'closed'])
            ->whereNotNull('closed_at')
            ->get();

        $avgResolutionDays = 0;
        if ($resolvedComplaintsWithTime->count() > 0) {
            $totalDays = $resolvedComplaintsWithTime->sum(function ($complaint) {
                return $complaint->created_at->diffInDays($complaint->closed_at);
            });
            $avgResolutionDays = round($totalDays / $resolvedComplaintsWithTime->count());
        }
        $stats['average_resolution_days'] = $avgResolutionDays;

        // In Progress count
        $stats['in_progress'] = (clone $complaintsQuery)->where('status', 'in_progress')->count();

        // Assigned count
        $stats['assigned'] = (clone $complaintsQuery)->where('status', 'assigned')->count();

        // Closed count
        $stats['closed'] = (clone $complaintsQuery)->where('status', 'closed')->count();

        // Work Performa count
        $stats['work_performa'] = (clone $complaintsQuery)->where('status', 'work_performa')->count();

        // Maintenance Performa count
        $stats['maint_performa'] = (clone $complaintsQuery)->where('status', 'maint_performa')->count();

        // Addressed count (resolved status)
        $stats['addressed'] = (clone $complaintsQuery)->where('status', 'resolved')->count();

        // Un Authorized count
        $stats['un_authorized'] = (clone $complaintsQuery)->where('status', 'un_authorized')->count();

        // Product N/A count
        $stats['product'] = (clone $complaintsQuery)->where('status', 'product_na')->count();

        // Pertains to GE/Const/Isld count
        $stats['pertains_to_ge_const_isld'] = (clone $complaintsQuery)->where('status', 'pertains_to_ge_const_isld')->count();

        // Barak Damages count
        $stats['barak_damages'] = (clone $complaintsQuery)->where('status', 'barak_damages')->count();

        // Work Priced Performa count
        $stats['work_priced_performa'] = (clone $complaintsQuery)->where('status', 'work_priced_performa')->count();

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

        $overdueComplaintsQuery = Complaint::whereIn('status', ['new', 'assigned', 'in_progress'])
            ->with(['client', 'assignedEmployee']);
        $this->filterComplaintsByLocationForFrontend($overdueComplaintsQuery, $user, $locationScope);
        $overdueComplaints = $overdueComplaintsQuery->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        // Dataset for dashboard complaint table
        $performaStatuses = [
            'work_performa',
            'maint_performa',
            'work_priced_performa',
            'maint_priced_performa',
            'product_na',
        ];

        $dashboardComplaints = (clone $complaintsQuery)
            ->with(['client', 'assignedEmployee'])
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
                    'cmp' => (int) ($complaint->complaint_id ?? $complaint->id),
                    'status' => $statusKey,
                    'status_label' => $statusLabel,
                    'performa_type' => $performaType,
                    'performa_label' => $performaType ? ucfirst(str_replace('_', ' ', $performaType)) : '-',
                    'category' => $complaint->category_display ?? ucfirst($complaint->category ?? 'N/A'),
                    'designation' => $complaint->assignedEmployee->designation ?? 'N/A',
                    'client_name' => $client->client_name ?? 'N/A',
                    'address' => $client->address
                        ?? $client->home_address
                        ?? $client->house_no
                        ?? 'N/A',
                    'phone' => $client->phone ?? $client->mobile ?? '-',
                    'created_at' => $createdAt,
                    'closed_at' => $closedAt,
                    'overdue' => $complaint->isOverdue(),
                    'view_url' => route('admin.complaints.show', $complaint->id),
                ];
            })
            ->values();

        // Get monthly complaints data (current year)
        $monthlyComplaints = [];
        $monthLabels = [];
        for ($i = 0; $i < 12; $i++) { // Jan to Dec
            $date = now()->startOfYear()->addMonths($i);
            $monthLabels[] = $date->format('M'); // Short month names
            $monthQuery = (clone $complaintsQuery)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);
            $monthlyComplaints[] = $monthQuery->count();
        }

        // Get complaints by status with filters
        $complaintsByStatusQuery = (clone $complaintsQuery);
        $complaintsByStatus = $complaintsByStatusQuery
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Get resolved vs recent ed data (year to date)
        $resolvedVsEdData = [];
        $recentEdData = [];
        $yearTdData = [];
        $unauthorizedData = [];
        $performaData = [];
        for ($i = 0; $i < 12; $i++) { // Jan to Dec
            $date = now()->startOfYear()->addMonths($i);
            $monthQuery = (clone $complaintsQuery)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);

            $recentEdData[] = $monthQuery->count();
            $resolvedData = (clone $monthQuery)
                ->whereIn('status', ['resolved', 'closed'])
                ->count();
            $resolvedVsEdData[] = $resolvedData;

            // Barak Damages Data
            $unauthorizedData[] = (clone $monthQuery)
                ->where('status', 'barak_damages')
                ->count();

            // Performa Data (aggregating all performa types)
            $performaData[] = (clone $monthQuery)
                ->whereIn('status', ['work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa'])
                ->count();

            // Year TD (Year to Date) - cumulative from start of year
            $yearTdQuery = (clone $complaintsQuery)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', '<=', $date->month);
            $yearTdData[] = $yearTdQuery->count();
        }

        $employeePerformanceQuery = Employee::query();
        $this->filterEmployeesByLocation($employeePerformanceQuery, $user);
        $this->applyFrontendLocationScope($employeePerformanceQuery, $locationScope);
        $employeePerformance = $employeePerformanceQuery
            ->withCount([
                'assignedComplaints' => function ($query) use ($user, $self, $locationScope) {
                    $query->where('created_at', '>=', now()->subDays(30));
                    // Apply location filter to assigned complaints count
                    $self->filterComplaintsByLocationForFrontend($query, $user, $locationScope);
                },
                'assignedComplaints as pending_complaints_count' => function ($query) use ($user, $self, $locationScope) {
                    $query->where('created_at', '>=', now()->subDays(30))
                        ->whereIn('status', ['new', 'assigned', 'in_progress']);
                    $self->filterComplaintsByLocationForFrontend($query, $user, $locationScope);
                },
                'assignedComplaints as resolved_complaints_count' => function ($query) use ($user, $self, $locationScope) {
                    $query->where('created_at', '>=', now()->subDays(30))
                        ->whereIn('status', ['resolved', 'closed']);
                    $self->filterComplaintsByLocationForFrontend($query, $user, $locationScope);
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
        $employeeLeastAssigned = $employeeLeastAssignedQuery
            ->withCount([
                'assignedComplaints' => function ($query) use ($user, $self, $locationScope) {
                    $query->where('created_at', '>=', now()->subDays(30));
                    $self->filterComplaintsByLocationForFrontend($query, $user, $locationScope);
                },
                'assignedComplaints as pending_complaints_count' => function ($query) use ($user, $self, $locationScope) {
                    $query->where('created_at', '>=', now()->subDays(30))
                        ->whereIn('status', ['new', 'assigned', 'in_progress']);
                    $self->filterComplaintsByLocationForFrontend($query, $user, $locationScope);
                },
                'assignedComplaints as resolved_complaints_count' => function ($query) use ($user, $self, $locationScope) {
                    $query->where('created_at', '>=', now()->subDays(30))
                        ->whereIn('status', ['resolved', 'closed']);
                    $self->filterComplaintsByLocationForFrontend($query, $user, $locationScope);
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

        // Priority: Unrestricted (Admin) > CME User > GE User
        // If user has unrestricted access, show CMEs regardless of cme_ids/group_ids
        if (!$hasUnrestrictedAccess && $user && !empty($user->cme_ids)) {
            // CME User: Show all GE Groups (cities) under their assigned CMEs
            $geGroupsForCme = \App\Models\City::whereIn('cme_id', $user->cme_ids)
                ->where(function ($q) {
                    $q->where('name', 'LIKE', '%GE%')
                        ->orWhere('name', 'LIKE', '%AGE%')
                        ->orWhere('name', 'LIKE', '%ge%')
                        ->orWhere('name', 'LIKE', '%age%');
                })
                ->where('status', 'active')
                ->orderBy('name')
                ->get();

            foreach ($geGroupsForCme as $city) {
                $cmeGraphLabels[] = $city->name;

                // Get sectors (GE Nodes) for this city
                $sectorIdsForCity = \App\Models\Sector::where('city_id', $city->id)->pluck('id')->toArray();

                // Base query for this GE Group (city)
                $cityBaseQuery = \App\Models\Complaint::where(function ($q) use ($city, $sectorIdsForCity) {
                    $q->where('city_id', $city->id);
                    if (!empty($sectorIdsForCity)) {
                        $q->orWhereIn('sector_id', $sectorIdsForCity);
                    }
                });

                // Apply Global Filters (Category, Status)
                if ($category && $category !== 'all') {
                    $cityBaseQuery->where('category', $category);
                }
                if ($status && $status !== 'all') {
                    $cityBaseQuery->where('status', $status);
                }

                // Apply Date Filter
                if ($cmeDateRange) {
                    $now = now();
                    switch ($cmeDateRange) {
                        case 'yesterday':
                            $cityBaseQuery->whereDate('created_at', $now->copy()->subDay()->toDateString());
                            break;
                        case 'today':
                            $cityBaseQuery->whereDate('created_at', $now->toDateString());
                            break;
                        case 'this_week':
                            $cityBaseQuery->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
                            break;
                        case 'last_week':
                            $cityBaseQuery->whereBetween('created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()]);
                            break;
                        case 'this_month':
                            $cityBaseQuery->whereMonth('created_at', $now->month)
                                ->whereYear('created_at', $now->year);
                            break;
                        case 'last_month':
                            $cityBaseQuery->whereMonth('created_at', $now->copy()->subMonth()->month)
                                ->whereYear('created_at', $now->copy()->subMonth()->year);
                            break;
                        case 'last_6_months':
                            $cityBaseQuery->where('created_at', '>=', $now->copy()->subMonths(6)->startOfDay());
                            break;
                        case 'this_year':
                            $cityBaseQuery->whereYear('created_at', $now->year);
                            break;
                        case 'last_year':
                            $cityBaseQuery->whereYear('created_at', $now->copy()->subYear()->year);
                            break;
                    }
                }

                // Count total complaints for this GE Group
                $cmeGraphData[] = (clone $cityBaseQuery)->count();

                // Count addressed (resolved + closed) complaints
                $cmeResolvedData[] = (clone $cityBaseQuery)->whereIn('status', ['resolved', 'closed'])->count();
            }
        } elseif (!$hasUnrestrictedAccess && $user && !empty($user->group_ids)) {
            // GE User: Show all GE Nodes (sectors) under their assigned GE Groups
            $geNodesForGroup = \App\Models\Sector::whereIn('city_id', $user->group_ids)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();

            foreach ($geNodesForGroup as $sector) {
                $cmeGraphLabels[] = $sector->name;

                // Base query for this GE Node (sector)
                $sectorBaseQuery = \App\Models\Complaint::where('sector_id', $sector->id);

                // Apply Global Filters (Category, Status)
                if ($category && $category !== 'all') {
                    $sectorBaseQuery->where('category', $category);
                }
                if ($status && $status !== 'all') {
                    $sectorBaseQuery->where('status', $status);
                }

                // Apply Date Filter
                if ($cmeDateRange) {
                    $now = now();
                    switch ($cmeDateRange) {
                        case 'yesterday':
                            $sectorBaseQuery->whereDate('created_at', $now->copy()->subDay()->toDateString());
                            break;
                        case 'today':
                            $sectorBaseQuery->whereDate('created_at', $now->toDateString());
                            break;
                        case 'this_week':
                            $sectorBaseQuery->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
                            break;
                        case 'last_week':
                            $sectorBaseQuery->whereBetween('created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()]);
                            break;
                        case 'this_month':
                            $sectorBaseQuery->whereMonth('created_at', $now->month)
                                ->whereYear('created_at', $now->year);
                            break;
                        case 'last_month':
                            $sectorBaseQuery->whereMonth('created_at', $now->copy()->subMonth()->month)
                                ->whereYear('created_at', $now->copy()->subMonth()->year);
                            break;
                        case 'last_6_months':
                            $sectorBaseQuery->where('created_at', '>=', $now->copy()->subMonths(6)->startOfDay());
                            break;
                        case 'this_year':
                            $sectorBaseQuery->whereYear('created_at', $now->year);
                            break;
                        case 'last_year':
                            $sectorBaseQuery->whereYear('created_at', $now->copy()->subYear()->year);
                            break;
                    }
                }

                // Count total complaints for this GE Node
                $cmeGraphData[] = (clone $sectorBaseQuery)->count();

                // Count addressed (resolved + closed) complaints
                $cmeResolvedData[] = (clone $sectorBaseQuery)->whereIn('status', ['resolved', 'closed'])->count();
            }
        } else {
            // Non-CME/GE User or Admin: Show CMEs as before
            foreach ($cmesList as $cme) {
                $cmeGraphLabels[] = $cme->name;

                // Get cities (GE Groups) for this CME
                $cityIdsForCme = \App\Models\City::where('cme_id', $cme->id)->pluck('id')->toArray();

                // Get sectors (GE Nodes) for this CME (either directly or via city)
                $sectorIdsForCme = \App\Models\Sector::where(function ($q) use ($cme, $cityIdsForCme) {
                    $q->where('cme_id', $cme->id);
                    if (!empty($cityIdsForCme)) {
                        $q->orWhereIn('city_id', $cityIdsForCme);
                    }
                })->pluck('id')->toArray();

                // Base query for this CME
                $cmeBaseQuery = \App\Models\Complaint::where(function ($q) use ($cityIdsForCme, $sectorIdsForCme) {
                    if (!empty($cityIdsForCme)) {
                        $q->whereIn('city_id', $cityIdsForCme);
                    }
                    if (!empty($sectorIdsForCme)) {
                        $method = !empty($cityIdsForCme) ? 'orWhereIn' : 'whereIn';
                        $q->{$method}('sector_id', $sectorIdsForCme);
                    }
                });

                // Apply Global Filters (Category, Status) - consistent with other stats
                if ($category && $category !== 'all') {
                    $cmeBaseQuery->where('category', $category);
                }
                if ($status && $status !== 'all') {
                    $cmeBaseQuery->where('status', $status);
                }

                // Apply Date Filter (Specific or Global)
                if ($cmeDateRange) {
                    $now = now();
                    switch ($cmeDateRange) {
                        case 'yesterday':
                            $cmeBaseQuery->whereDate('created_at', $now->copy()->subDay()->toDateString());
                            break;
                        case 'today':
                            $cmeBaseQuery->whereDate('created_at', $now->toDateString());
                            break;
                        case 'this_week':
                            $cmeBaseQuery->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
                            break;
                        case 'last_week':
                            $cmeBaseQuery->whereBetween('created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()]);
                            break;
                        case 'this_month':
                            $cmeBaseQuery->whereMonth('created_at', $now->month)
                                ->whereYear('created_at', $now->year);
                            break;
                        case 'last_month':
                            $cmeBaseQuery->whereMonth('created_at', $now->copy()->subMonth()->month)
                                ->whereYear('created_at', $now->copy()->subMonth()->year);
                            break;
                        case 'last_6_months':
                            $cmeBaseQuery->where('created_at', '>=', $now->copy()->subMonths(6)->startOfDay());
                            break;
                        case 'this_year':
                            $cmeBaseQuery->whereYear('created_at', $now->year);
                            break;
                        case 'last_year':
                            $cmeBaseQuery->whereYear('created_at', $now->copy()->subYear()->year);
                            break;
                    }
                }

                // Count total complaints
                $cmeGraphData[] = (clone $cmeBaseQuery)->count();

                // Count addressed (resolved + closed) complaints
                $cmeResolvedData[] = (clone $cmeBaseQuery)->whereIn('status', ['resolved', 'closed'])->count();
            }
        }

        // Get Top 15 Products by Issued Quantity
        $categoryDateRange = $request->get('category_date_range');

        $categoryUsageQuery = \App\Models\Spare::selectRaw('
                item_name,
                SUM(issued_quantity) as total_used,
                SUM(total_received_quantity) as total_received
            ')
            ->whereNotNull('item_name')
            ->groupBy('item_name')
            ->orderByDesc('total_used')
            ->limit(15);

        // Apply date range filter if provided
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

        // Apply location filtering to product usage data based on user privileges
        $this->applyFrontendLocationScope($categoryUsageQuery, $locationScope, 'city_id', 'sector_id');

        $categoryUsageData = $categoryUsageQuery->get();

        $categoryLabels = $categoryUsageData->pluck('item_name')->toArray();

        $categoryUsageValues = $categoryUsageData->pluck('total_used')->toArray();
        $categoryTotalReceivedValues = $categoryUsageData->pluck('total_received')->toArray();

        // If AJAX request, return JSON data
        if ($request->ajax()) {
            return response()->json([
                'stats' => $stats,
                'monthlyComplaints' => $monthlyComplaints,
                'monthLabels' => $monthLabels,
                'complaintsByStatus' => $complaintsByStatus,
                'resolvedVsEdData' => $resolvedVsEdData,
                'recentEdData' => $recentEdData,
                'unauthorizedData' => $unauthorizedData,
                'performaData' => $performaData,
                'cmeGraphLabels' => $cmeGraphLabels,
                'cmeGraphData' => $cmeGraphData,
                'dashboardComplaints' => $dashboardComplaints,
                'categoryLabels' => $categoryLabels,
                'categoryUsageValues' => $categoryUsageValues,
                'categoryTotalReceivedValues' => $categoryTotalReceivedValues,
            ]);
        }



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
        } else {
            $tableEntities = $cmesList;
            $entityType = 'cme';
        }

        if ($tableEntities->isNotEmpty()) {
            // Fetch monthly data for the current year
            $monthlyQuery = \App\Models\Complaint::selectRaw('
                    MONTH(complaints.created_at) as month, 
                    YEAR(complaints.created_at) as year,
                    count(*) as total,
                    sum(case when complaints.status in ("resolved", "closed") then 1 else 0 end) as resolved
                ')
                ->whereYear('complaints.created_at', date('Y'))
                ->groupBy('year', 'month');

            if ($entityType === 'cme') {
                $monthlyQuery->join('cities', 'complaints.city_id', '=', 'cities.id')
                    ->selectRaw('cities.cme_id as entity_id')
                    ->groupBy('cities.cme_id');
            } elseif ($entityType === 'city') {
                $monthlyQuery->selectRaw('city_id as entity_id')
                    ->whereIn('city_id', $tableEntities->pluck('id'))
                    ->groupBy('city_id');
            } elseif ($entityType === 'sector') {
                $monthlyQuery->selectRaw('sector_id as entity_id')
                    ->whereIn('sector_id', $tableEntities->pluck('id'))
                    ->groupBy('sector_id');
            }

            $monthlyResults = $monthlyQuery->get();

            // Process results into table data
            $months = [];
            for ($m = 1; $m <= 12; $m++) {
                $months[$m] = date('F', mktime(0, 0, 0, $m, 1));
            }

            foreach ($months as $mNum => $mName) {
                $monthlyTableData[$mName] = [];
                foreach ($tableEntities as $entity) {
                    $stat = $monthlyResults->where('month', $mNum)->where('entity_id', $entity->id)->first();
                    $monthlyTableData[$mName][$entity->name] = [
                        'total' => $stat ? $stat->total : 0,
                        'resolved' => $stat ? $stat->resolved : 0
                    ];
                }
            }
        }

        // Prepare Stock Consumption Data - Monthly with Inventory Details
        $stockConsumptionData = [];
        $sparesListQuery = \App\Models\Spare::orderBy('issued_quantity', 'desc'); // No limit - fetch all for print/Excel

        // Apply location filtering to spares list based on user privileges
        $this->applyFrontendLocationScope($sparesListQuery, $locationScope, 'city_id', 'sector_id');

        $sparesList = $sparesListQuery->get();

        // Get monthly consumption data
        $stockQuery = \App\Models\ComplaintSpare::selectRaw('
                complaint_spares.spare_id,
                MONTH(complaint_spares.created_at) as month,
                SUM(complaint_spares.quantity) as total_qty
            ')
            ->join('complaints', 'complaint_spares.complaint_id', '=', 'complaints.id')
            ->whereYear('complaint_spares.created_at', date('Y'))
            ->whereNull('complaints.deleted_at');

        // Apply location filters based on privileges (filter by spare's location)
        if ($hasUnrestrictedAccess) {
            // No filter needed
        } elseif ($user && !empty($user->cme_ids)) {
            // Filter by CMEs
            $stockQuery->join('cities', 'complaints.city_id', '=', 'cities.id')
                ->whereIn('cities.cme_id', $user->cme_ids);
        } elseif ($user && !empty($user->group_ids)) {
            // Filter by Cities (Groups)
            $stockQuery->whereIn('complaints.city_id', $user->group_ids);
        } elseif ($user && !empty($user->node_ids)) {
            // Filter by Sectors (Nodes)
            $stockQuery->whereIn('complaints.sector_id', $user->node_ids);
        }

        $stockResults = $stockQuery->groupBy('complaint_spares.spare_id', 'month')->get();

        // Index stock results by spare_id and month for fast lookup
        $stockResultsIndex = $stockResults->keyBy(function ($row) {
            return $row->spare_id . '_' . $row->month;
        });

        // Get monthly stock received data from spare_stock_logs
        $stockReceivedQuery = \App\Models\SpareStockLog::selectRaw('
                spare_stock_logs.spare_id,
                MONTH(spare_stock_logs.created_at) as month,
                SUM(spare_stock_logs.quantity) as total_qty
            ')
            ->join('spares', 'spare_stock_logs.spare_id', '=', 'spares.id')
            ->where('spare_stock_logs.change_type', 'in')
            ->whereYear('spare_stock_logs.created_at', date('Y'))
            ->whereNull('spare_stock_logs.deleted_at')
            ->whereNull('spares.deleted_at');

        // Apply location filters to stock received query as well
        if ($hasUnrestrictedAccess) {
            // No filter needed
        } elseif ($user && !empty($user->cme_ids)) {
            $stockReceivedQuery->join('cities', 'spares.city_id', '=', 'cities.id')
                ->whereIn('cities.cme_id', $user->cme_ids);
        } elseif ($user && !empty($user->group_ids)) {
            $stockReceivedQuery->whereIn('spares.city_id', $user->group_ids);
        } elseif ($user && !empty($user->node_ids)) {
            $stockReceivedQuery->whereIn('spares.sector_id', $user->node_ids);
        }

        $stockReceivedQuery->groupBy('spare_stock_logs.spare_id', 'month');
        $stockReceivedResults = $stockReceivedQuery->get();

        // Process stock data
        foreach ($sparesList as $spare) {
            $totalReceived = $spare->total_received_quantity ?? 0;
            $currentStock = $spare->stock_quantity ?? 0;
            // Use issued_quantity from spares table as Total Used (Issue Qty column)
            $totalIssued = $spare->issued_quantity ?? 0;

            $monthlyData = [];
            $monthlyReceivedData = [];

            for ($m = 1; $m <= 12; $m++) {
                // Use short month names (Jan, Feb, ...) to match $monthLabels
                $mName = date('M', mktime(0, 0, 0, $m, 1));

                // Get consumption data
                // Key format: spareId_month
                $key = $spare->id . '_' . $m;
                $stat = $stockResultsIndex->get($key);

                $qty = $stat ? $stat->total_qty : 0;
                $monthlyData[$mName] = $qty;

                // Get received data
                $receivedStat = $stockReceivedResults->where('spare_id', $spare->id)->where('month', $m)->first();
                $receivedQty = $receivedStat ? $receivedStat->total_qty : 0;
                $monthlyReceivedData[$mName] = $receivedQty;
            }

            $stockConsumptionData[$spare->item_name] = [
                'total_received' => $totalReceived,
                'current_stock' => $currentStock,
                'monthly_data' => $monthlyData,
                'monthly_received_data' => $monthlyReceivedData,
                // Total Used should reflect the spare's issued_quantity (Issue Qty)
                'total_used' => $totalIssued
            ];
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
            'categoryTotalReceivedValues'
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
        $complaint = Complaint::findOrFail($id);

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
            'overall_rating' => 'required|in:excellent,good,average,poor',
            'comments' => 'nullable|string|max:1000',
        ]);

        \App\Models\ComplaintFeedback::create([
            'complaint_id' => $complaint->id,
            'client_id' => $complaint->client_id,
            'submitted_by' => $request->submitted_by,
            'overall_rating' => $request->overall_rating,
            'rating_score' => $this->getRatingScore($request->overall_rating),
            'comments' => $request->comments,
            'feedback_date' => now(),
            'entered_at' => now(),
            // entered_by is null for public feedback
        ]);

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
            'assignedEmployee',
            'attachments',
            'spareApprovals',
            'feedback.enteredBy'
        ])->findOrFail($id);

        if ($request->ajax()) {
            return view('frontend.complaints.partials.detail_card', compact('complaint'))->render();
        }

        return view('frontend.complaints.show', compact('complaint'));
    }

    /**
     * Get rating score from rating text
     */
    public function getRatingScore($rating)
    {
        switch ($rating) {
            case 'Excellent': return 5;
            case 'Good': return 4;
            case 'Average': return 3;
            case 'Below Average': return 2;
            case 'Poor': return 1;
            default: return 0;
        }
    }
}
