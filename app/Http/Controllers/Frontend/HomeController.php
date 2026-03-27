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

        return $this->applyFrontendHouseBasedScope($query, $scope);
    }

    /**
     * Apply location scope via the house relationship (house.city_id / house.sector_id)
     * This is the correct approach — complaints don't always have city_id/sector_id filled in.
     */
    protected function applyFrontendHouseBasedScope($query, array $scope, $tablePrefix = 'complaints')
    {
        if (empty($scope['restricted'])) {
            return $query;
        }

        $cityIds = $scope['city_ids'] ?? [];
        $sectorIds = $scope['sector_ids'] ?? [];

        if (empty($cityIds) && empty($sectorIds)) {
            return $query->whereRaw('1 = 0');
        }

        // Inclusive Filtering: Check House location OR direct Complaint location (for house-less complaints)
        return $query->where(function ($q) use ($cityIds, $sectorIds, $tablePrefix) {
            // 1. Match via House relationship
            $q->whereHas('house', function ($hq) use ($cityIds, $sectorIds) {
                $hq->where(function ($sub) use ($cityIds, $sectorIds) {
                    $applied = false;
                    if (!empty($sectorIds)) {
                        $sub->whereIn('sector_id', $sectorIds);
                        $applied = true;
                    }
                    if (!empty($cityIds)) {
                        $method = $applied ? 'orWhereIn' : 'whereIn';
                        $sub->{$method}('city_id', $cityIds);
                    }
                });
            })
                // 2. OR Match via direct complaint columns (for complaints without a house)
                ->orWhere(function ($cq) use ($cityIds, $sectorIds, $tablePrefix) {
                    $cq->whereNull($tablePrefix . '.house_id');
                    $applied = false;
                    if (!empty($sectorIds)) {
                        $cq->whereIn($tablePrefix . '.sector_id', $sectorIds);
                        $applied = true;
                    }
                    if (!empty($cityIds)) {
                        $method = $applied ? 'orWhereIn' : 'whereIn';
                        $cq->{$method}($tablePrefix . '.city_id', $cityIds);
                    }
                });
        });
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

        // Check for Unrestricted Access (Global Admin)
        // 1. Role-based check (if role_id exists)
        $isAdminByRole = (isset($user->role_id) && $user->role_id === 1) || 
                         (method_exists($user, 'isAdmin') && $user->isAdmin()) || 
                         (isset($user->role) && in_array(strtolower($user->role->role_name), ['admin', 'super admin', 'director']));

        // 2. CME-based check (if they have all CMEs assigned, they are global)
        $hasAllCmes = false;
        if (!empty($cmeIds)) {
            $totalCmes = \App\Models\Cme::where('status', 1)->count();
            if (count($cmeIds) >= $totalCmes) {
                $hasAllCmes = true;
            }
        }

        if ($isAdminByRole || $hasAllCmes) {
            return $scope; // Unrestricted
        }

        // If user has CMEs assigned, include all cities under those CMEs
        if (!empty($cmeIds)) {
            $cmeCityIds = \App\Models\City::whereIn('cme_id', $cmeIds)->pluck('id')->toArray();
            $cityIds = array_unique(array_merge($cityIds, $cmeCityIds));
        }

        if (empty($cityIds) && empty($sectorIds)) {
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

        // For complaints queries, always use house-based filtering
        if (str_contains($cityColumn, 'complaints.')) {
            return $this->applyFrontendHouseBasedScope($query, $scope);
        }

        // For non-complaint queries (employees, spares), use the direct columns
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
                        // Inclusive: house's sector_id OR direct complaint sector_id (for house-less)
                        $complaintsQuery->where(function ($q) use ($sectorId) {
                            $q->whereHas('house', function ($hq) use ($sectorId) {
                                $hq->where('sector_id', $sectorId);
                            })->orWhere(function ($cq) use ($sectorId) {
                                $cq->whereNull('complaints.house_id')
                                    ->where('complaints.sector_id', $sectorId);
                            });
                        });
                    } else {
                        $complaintsQuery->whereRaw('1 = 0');
                    }
                } else {
                    // Inclusive GE Group filter: houses in this city/sectors OR house-less with direct city_id
                    $complaintsQuery->where(function ($q) use ($cityId, $sectorIdsForCity) {
                        $q->whereHas('house', function ($hq) use ($cityId, $sectorIdsForCity) {
                            $hq->where(function ($sub) use ($cityId, $sectorIdsForCity) {
                                $sub->where('city_id', $cityId);
                                if (!empty($sectorIdsForCity)) {
                                    $sub->orWhereIn('sector_id', $sectorIdsForCity);
                                }
                            });
                        })->orWhere(function ($cq) use ($cityId, $sectorIdsForCity) {
                            $cq->whereNull('complaints.house_id')
                                ->where(function ($sub) use ($cityId, $sectorIdsForCity) {
                                    $sub->where('complaints.city_id', $cityId);
                                    if (!empty($sectorIdsForCity)) {
                                        $sub->orWhereIn('complaints.sector_id', $sectorIdsForCity);
                                    }
                                });
                        });
                    });
                }
            } else {
                $complaintsQuery->whereRaw('1 = 0');
            }
        } elseif ($sectorId) {
            if ($this->canAccessSector((int) $sectorId, $locationScope)) {
                // Inclusive: house's sector_id OR direct complaint sector_id (for house-less)
                $complaintsQuery->where(function ($q) use ($sectorId) {
                    $q->whereHas('house', function ($hq) use ($sectorId) {
                        $hq->where('sector_id', $sectorId);
                    })->orWhere(function ($cq) use ($sectorId) {
                        $cq->whereNull('complaints.house_id')
                            ->where('complaints.sector_id', $sectorId);
                    });
                });
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
                $complaintsQuery->whereHas('category', function ($q) use ($category) {
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
                // Filter via house's city/sector OR complaint's own columns (inclusive)
                $complaintsQuery->where(function ($eq) use ($cityIdsForCmes, $sectorIdsForCmes) {
                    $eq->whereHas('house', function ($hq) use ($cityIdsForCmes, $sectorIdsForCmes) {
                        $hq->where(function ($q) use ($cityIdsForCmes, $sectorIdsForCmes) {
                            if (!empty($cityIdsForCmes)) {
                                $q->whereIn('city_id', $cityIdsForCmes);
                            }
                            if (!empty($sectorIdsForCmes)) {
                                $method = !empty($cityIdsForCmes) ? 'orWhereIn' : 'whereIn';
                                $q->{$method}('sector_id', $sectorIdsForCmes);
                            }
                        });
                    })->orWhere(function ($cq) use ($cityIdsForCmes, $sectorIdsForCmes) {
                        $cq->whereNull('house_id');
                        if (!empty($cityIdsForCmes)) {
                            $cq->whereIn('city_id', $cityIdsForCmes);
                        }
                        if (!empty($sectorIdsForCmes)) {
                            $method = !empty($cityIdsForCmes) ? 'orWhereIn' : 'whereIn';
                            $cq->{$method}('sector_id', $sectorIdsForCmes);
                        }
                    });
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
        $geGroupsQuery = City::where('status', 1);

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

        $geNodesQuery = Sector::where('status', 1);

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


        $categories = ComplaintCategory::where('status', 1)->get();

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
        ];
        $performaStatuses = ['work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa'];

        // Calculate stats with filters in a single efficient query
        $now = now();
        $statsData = (clone $complaintsQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN complaints.status = 'new' THEN 1 ELSE 0 END) as new,
            SUM(CASE WHEN complaints.status IN ('new', 'assigned', 'in_progress') THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN complaints.status = 'resolved' THEN 1 ELSE 0 END) as addressed,
            SUM(CASE WHEN complaints.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN complaints.status = 'assigned' THEN 1 ELSE 0 END) as assigned,

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
            SUM(CASE WHEN complaints.created_at >= ? AND complaints.created_at < ? THEN 1 ELSE 0 END) as last_month
        ", [
            $now->copy()->startOfDay(),
            $now->copy()->startOfMonth(),
            $now->copy()->subMonth()->startOfMonth(),
            $now->copy()->startOfMonth()
        ])->get();

        $statsDataAggregation = $statsData->first();

        // Single query for overdue count to avoid another heavy operation
        $overdueCount = (clone $complaintsQuery)->overdue()->count();

        $stats = [
            'total_complaints' => $statsDataAggregation->total ?? 0,
            'new_complaints' => $statsDataAggregation->new ?? 0,
            'pending_complaints' => $statsDataAggregation->pending ?? 0,
            'resolved_complaints' => $statsDataAggregation->addressed ?? 0,
            'overdue_complaints' => $overdueCount,
            'complaints_today' => $statsDataAggregation->today ?? 0,
            'complaints_this_month' => $statsDataAggregation->this_month ?? 0,
            'complaints_last_month' => $statsDataAggregation->last_month ?? 0,
            'in_progress' => $statsDataAggregation->in_progress ?? 0,
            'assigned' => $statsDataAggregation->assigned ?? 0,

            'work_performa' => $statsDataAggregation->work_performa ?? 0,
            'maint_performa' => $statsDataAggregation->maint_performa ?? 0,
            'addressed' => $statsDataAggregation->addressed ?? 0,
            'un_authorized' => $statsDataAggregation->un_authorized ?? 0,
            'pertains_to_ge_const_isld' => $statsDataAggregation->pertains_to_ge_const_isld ?? 0,
            'barak_damages' => $statsDataAggregation->barak_damages ?? 0,
            'work_priced_performa' => $statsDataAggregation->work_priced_performa ?? 0,
            'maint_priced_performa' => $statsDataAggregation->maint_priced_performa ?? 0,
            'product' => $statsDataAggregation->product_na ?? 0,
        ];

        // Grouped query for status counts (Pie Chart)
        $statusCounts = (clone $complaintsQuery)
            ->selectRaw('complaints.status, COUNT(*) as count')
            ->groupBy('complaints.status')
            ->pluck('count', 'complaints.status')
            ->map(fn($val) => (int) $val)
            ->toArray();

        $complaintsByStatus = $statusCounts;
        if (isset($complaintsByStatus['new'])) {
            $complaintsByStatus['unassigned'] = ($complaintsByStatus['unassigned'] ?? 0) + $complaintsByStatus['new'];
            unset($complaintsByStatus['new']);
        }

        // Resolution rate & average time
        $totalComplaints = $stats['total_complaints'];
        $resolvedComplaints = $stats['resolved_complaints'];
        $stats['resolution_rate'] = $totalComplaints > 0 ? round(($resolvedComplaints / $totalComplaints) * 100) : 0;

        $resolvedWithTime = (clone $complaintsQuery)
            ->where('complaints.status', 'resolved')
            ->whereNotNull('closed_at')
            ->selectRaw('SUM(DATEDIFF(closed_at, created_at)) as total_days, COUNT(*) as count')
            ->first();

        $stats['average_resolution_days'] = ($resolvedWithTime && $resolvedWithTime->count > 0)
            ? round($resolvedWithTime->total_days / $resolvedWithTime->count)
            : 0;

        $page = request()->get('page', 1);
        $perPage = 5;
        $recentComplaintsQuery = Complaint::with(['house', 'assignedEmployee']);
        $this->filterComplaintsByLocationForFrontend($recentComplaintsQuery, $user, $locationScope);
        $recentComplaints = $recentComplaintsQuery->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $self = $this; // Store reference for use in closure
        $pendingApprovals = class_exists(\App\Models\SpareApprovalPerforma::class)
            ? \App\Models\SpareApprovalPerforma::with(['complaint.house', 'requestedBy', 'items.spare'])
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

        // dataset for dashboard complaint table
        // We only fetch a small subset for the initial load if NOT an AJAX request
        // AJAX requests for more data will be handled separately
        $perPage = 15;
        $page = $request->get('page', 1);

        $complaintsBase = (clone $complaintsQuery)
            ->select([
                'complaints.id',
                'complaints.status',
                'complaints.category_id',
                'complaints.created_at',
                'complaints.closed_at',
                'complaints.house_id',
                'complaints.assigned_employee_id'
            ])
            ->selectRaw("
                (
                    complaints.status IN ('new', 'assigned', 'in_progress') AND 
                    EXISTS(
                        SELECT 1 FROM sla_rules 
                        WHERE sla_rules.category_id = complaints.category_id
                        AND sla_rules.status = 1
                        AND sla_rules.deleted_at IS NULL
                        AND complaints.created_at < DATE_SUB(NOW(), INTERVAL sla_rules.max_resolution_time HOUR)
                    )
                ) as is_overdue_sql
            ")
            ->with([
                'assignedEmployee:id,name',
                'house:id,house_no,address'
            ])
            ->orderBy('id', 'desc');

        // Initialize pagination data for AJAX response
        $paginationData = null;

        if ($request->ajax()) {
            // Handle AJAX pagination/filtering for the complaints table if a status filter is provided or pagination
            $statusFilter = $request->get('status');
            $statusKey = ($statusFilter === 'all' || !$statusFilter) ? null : $statusFilter;

            if ($statusKey) {
                if ($statusKey === 'unassigned') {
                    $complaintsBase->where('complaints.status', 'new');
                } elseif ($statusKey === 'resolved') {
                    $complaintsBase->where('complaints.status', 'resolved');
                } elseif ($statusKey === 'overdue') {
                    $complaintsBase->overdue();
                } else {
                    $complaintsBase->where('complaints.status', $statusKey);
                }
            }

            $paginatedComplaints = $complaintsBase->paginate($perPage);
            $dashboardComplaintsRaw = $paginatedComplaints->getCollection();

            $paginationData = [
                'current_page' => $paginatedComplaints->currentPage(),
                'last_page' => $paginatedComplaints->lastPage(),
                'per_page' => $paginatedComplaints->perPage(),
                'total' => $paginatedComplaints->total(),
                'from' => $paginatedComplaints->firstItem(),
                'to' => $paginatedComplaints->lastItem(),
            ];
        } else {
            // Initial non-AJAX load: only get first 20
            $dashboardComplaintsRaw = $complaintsBase->limit($perPage)->get();
        }

        // Map complaints for unified display (both AJAX and Initial)
        $dashboardComplaints = $dashboardComplaintsRaw->map(function ($complaint) use ($performaStatuses) {
            $statusKey = $complaint->status === 'new' ? 'unassigned' : $complaint->status;


            $statusLabel = $statusKey === 'resolved'
                ? 'Addressed'
                : ucfirst(str_replace('_', ' ', $statusKey));

            $performaType = in_array($complaint->status, $performaStatuses, true)
                ? $complaint->status
                : null;

            $house = $complaint->house;

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
                'name' => $house ? ($house->name ?? 'N/A') : 'N/A',
                'house_no' => $house->house_no ?? 'N/A',
                'address' => $house->address ?? 'N/A',
                'phone' => $house->phone ?? '-',
                'created_at' => $createdAt,
                'closed_at' => $closedAt,
                'overdue' => (bool) $complaint->is_overdue_sql,
                'view_url' => route('admin.complaints.show', $complaint->id),
            ];
        });


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
                            // Inclusive sector filter
                            $q->where(function ($sub) use ($sectorId, $tablePrefix) {
                                $sub->whereHas('house', function ($hq) use ($sectorId) {
                                    $hq->where('houses.sector_id', $sectorId);
                                })->orWhere(function ($cq) use ($sectorId, $tablePrefix) {
                                    $cq->whereNull($tablePrefix . '.house_id')->where($tablePrefix . '.sector_id', $sectorId);
                                });
                            });
                        } else {
                            $q->whereRaw('1 = 0');
                        }
                    } else {
                        // Inclusive GE Group filter
                        $q->where(function ($sub) use ($cityId, $sectorIdsForCity, $tablePrefix) {
                            $sub->whereHas('house', function ($hq) use ($cityId, $sectorIdsForCity) {
                                $hq->where(function ($query) use ($cityId, $sectorIdsForCity) {
                                    $query->where('houses.city_id', $cityId);
                                    if (!empty($sectorIdsForCity)) {
                                        $query->orWhereIn('houses.sector_id', $sectorIdsForCity);
                                    }
                                });
                            })->orWhere(function ($cq) use ($cityId, $sectorIdsForCity, $tablePrefix) {
                                $cq->whereNull($tablePrefix . '.house_id');
                                $cq->where(function ($inner) use ($cityId, $sectorIdsForCity, $tablePrefix) {
                                    $inner->where($tablePrefix . '.city_id', $cityId);
                                    if (!empty($sectorIdsForCity)) {
                                        $inner->orWhereIn($tablePrefix . '.sector_id', $sectorIdsForCity);
                                    }
                                });
                            });
                        });
                    }
                } else {
                    $q->whereRaw('1 = 0');
                }
            } elseif ($sectorId) {
                if ($self->canAccessSector((int) $sectorId, $locationScope)) {
                    // Inclusive sector filter
                    $q->where(function ($sub) use ($sectorId, $tablePrefix) {
                        $sub->whereHas('house', function ($hq) use ($sectorId) {
                            $hq->where('houses.sector_id', $sectorId);
                        })->orWhere(function ($cq) use ($sectorId, $tablePrefix) {
                            $cq->whereNull($tablePrefix . '.house_id')->where($tablePrefix . '.sector_id', $sectorId);
                        });
                    });
                } else {
                    $q->whereRaw('1 = 0');
                }
            }

            // Priority 2: CMES Filter (Always apply if set, inclusive)
            if ($cmesId) {
                $cityIdsForCmes = City::where('cme_id', $cmesId)->pluck('id')->toArray();
                $sectorIdsForCmes = Sector::where(function ($sq) use ($cmesId, $cityIdsForCmes) {
                    $sq->where('cme_id', $cmesId);
                    if (!empty($cityIdsForCmes))
                        $sq->orWhereIn('city_id', $cityIdsForCmes);
                })->pluck('id')->toArray();

                // Inclusive Filter (House OR Direct Complaint columns)
                $q->where(function ($sub) use ($cityIdsForCmes, $sectorIdsForCmes, $tablePrefix) {
                    $sub->whereHas('house', function ($hq) use ($cityIdsForCmes, $sectorIdsForCmes) {
                        $hq->where(function ($subInner) use ($cityIdsForCmes, $sectorIdsForCmes) {
                            if (!empty($cityIdsForCmes))
                                $subInner->whereIn('city_id', $cityIdsForCmes);
                            if (!empty($sectorIdsForCmes)) {
                                $method = !empty($cityIdsForCmes) ? 'orWhereIn' : 'whereIn';
                                $subInner->{$method}('sector_id', $sectorIdsForCmes);
                            }
                        });
                    })->orWhere(function ($cq) use ($cityIdsForCmes, $sectorIdsForCmes, $tablePrefix) {
                        $cq->whereNull($tablePrefix . '.house_id');
                        if (!empty($cityIdsForCmes))
                            $cq->whereIn($tablePrefix . '.city_id', $cityIdsForCmes);
                        if (!empty($sectorIdsForCmes)) {
                            $method = !empty($cityIdsForCmes) ? 'orWhereIn' : 'whereIn';
                            $cq->{$method}($tablePrefix . '.sector_id', $sectorIdsForCmes);
                        }
                    });
                });
            }

            // Priority 3: Default Location Scope (if not manual)
            if (!$cityId && !$sectorId && !$cmesId && !empty($locationScope['restricted'])) {
                $self->applyFrontendHouseBasedScope($q, $locationScope, $tablePrefix);
            }

            // Global Metadata Filters
            if ($category && $category !== 'all') {
                if (is_numeric($category)) {
                    $q->where($tablePrefix ? $tablePrefix . '.category_id' : 'category_id', $category);
                } else {
                    $q->whereHas('category', function ($subQ) use ($category) {
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
                    case 'yesterday':
                        $q->whereDate($createdAtCol, $now->copy()->subDay()->toDateString());
                        break;
                    case 'today':
                        $q->whereDate($createdAtCol, $now->toDateString());
                        break;
                    case 'this_week':
                        $q->whereBetween($createdAtCol, [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
                        break;
                    case 'last_week':
                        $q->whereBetween($createdAtCol, [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()]);
                        break;
                    case 'this_month':
                        $q->whereMonth($createdAtCol, $now->month)->whereYear($createdAtCol, $now->year);
                        break;
                    case 'last_month':
                        $q->whereMonth($createdAtCol, $now->copy()->subMonth()->month)->whereYear($createdAtCol, $now->copy()->subMonth()->year);
                        break;
                    case 'last_6_months':
                        $q->where($createdAtCol, '>=', $now->copy()->subMonths(6)->startOfDay());
                        break;
                    case 'this_year':
                        $q->whereYear($createdAtCol, $now->year);
                        break;
                    case 'last_year':
                        $q->whereYear($createdAtCol, $now->copy()->subYear()->year);
                        break;
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
                SUM(CASE WHEN complaints.status = 'resolved' THEN 1 ELSE 0 END) as addressed,
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
        $this->applyFrontendLocationScope($employeePerformanceQuery, $locationScope);

        // Apply dynamic dashboard filters to Employee list
        if ($cityId) {
            $employeePerformanceQuery->where('city_id', $cityId);
        } elseif ($sectorId) {
            $employeePerformanceQuery->where('sector_id', $sectorId);
        } elseif ($cmesId) {
            $cityIdsForEmp = City::where('cme_id', $cmesId)->pluck('id')->toArray();
            $sectorIdsForEmp = Sector::where('cme_id', $cmesId)->orWhereIn('city_id', $cityIdsForEmp)->pluck('id')->toArray();
            $employeePerformanceQuery->where(function ($q) use ($cityIdsForEmp, $sectorIdsForEmp) {
                if (!empty($cityIdsForEmp)) $q->whereIn('city_id', $cityIdsForEmp);
                if (!empty($sectorIdsForEmp)) $q->orWhereIn('sector_id', $sectorIdsForEmp);
            });
        }
        $employeePerformance = $employeePerformanceQuery
            ->withCount([
                'assignedComplaints' => function ($query) use ($applyGlobalFilters) {
                    $applyGlobalFilters($query);
                },
                'assignedComplaints as resolved_complaints_count' => function ($query) use ($applyGlobalFilters) {
                    $applyGlobalFilters($query);
                    $query->where('status', 'resolved');
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
        $this->applyFrontendLocationScope($employeeLeastAssignedQuery, $locationScope);

        // Apply dynamic dashboard filters to Employee list
        if ($cityId) {
            $employeeLeastAssignedQuery->where('city_id', $cityId);
        } elseif ($sectorId) {
            $employeeLeastAssignedQuery->where('sector_id', $sectorId);
        } elseif ($cmesId) {
            $cityIdsForEmpLeast = City::where('cme_id', $cmesId)->pluck('id')->toArray();
            $sectorIdsForEmpLeast = Sector::where('cme_id', $cmesId)->orWhereIn('city_id', $cityIdsForEmpLeast)->pluck('id')->toArray();
            $employeeLeastAssignedQuery->where(function ($q) use ($cityIdsForEmpLeast, $sectorIdsForEmpLeast) {
                if (!empty($cityIdsForEmpLeast)) $q->whereIn('city_id', $cityIdsForEmpLeast);
                if (!empty($sectorIdsForEmpLeast)) $q->orWhereIn('sector_id', $sectorIdsForEmpLeast);
            });
        }
        $employeeLeastAssigned = $employeeLeastAssignedQuery
            ->withCount([
                'assignedComplaints' => function ($query) use ($applyGlobalFilters) {
                    $applyGlobalFilters($query);
                },
                'assignedComplaints as resolved_complaints_count' => function ($query) use ($applyGlobalFilters) {
                    $applyGlobalFilters($query);
                    $query->where('status', 'resolved');
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
        $cmesListQuery = \App\Models\Cme::where('status', 1);

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

        $cmesList = $cmesListQuery->orderByRaw("CASE WHEN name = 'CMES ISLD/LHR' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        // Get CME Complaint Stats for Graph
        $cmeGraphLabels = [];
        $cmeGraphData = [];
        $cmeResolvedData = []; // New array for addressed complaints

        $cmeDateRange = $request->get('cme_date_range', 'all_time'); // specific filter or default to all_time

        $years = collect(range(date('Y'), 2023))->unique()->values()->all();

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
                $totalCmes = \App\Models\Cme::where('status', 1)->count();
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
                ->where('status', 1)
                ->orderBy('name')
                ->get();

            $cmeGroupCityIds = $geGroupsForCme->pluck('id')->toArray();

            // Count complaints via house's city_id (Inclusive of house-less)
            $cmeStatsRaw = \App\Models\Complaint::leftJoin('houses', 'houses.id', '=', 'complaints.house_id')
                ->where(function ($q) use ($cmeGroupCityIds) {
                    $q->whereIn('houses.city_id', $cmeGroupCityIds)
                        ->orWhere(function ($cq) use ($cmeGroupCityIds) {
                            $cq->whereNull('complaints.house_id')->whereIn('complaints.city_id', $cmeGroupCityIds);
                        });
                })
                ->selectRaw('COALESCE(houses.city_id, complaints.city_id) as city_id, COUNT(*) as total, SUM(CASE WHEN complaints.status IN ("resolved", "closed") THEN 1 ELSE 0 END) as resolved');

            $this->applyCmeDateFilter($cmeStatsRaw, $cmeDateRange);
            $applyGlobalFilters($cmeStatsRaw); // Apply top-level filters (Category, etc.)
            $cmeStats = $cmeStatsRaw->groupBy(\DB::raw('COALESCE(houses.city_id, complaints.city_id)'))->get()->keyBy('city_id');

            foreach ($geGroupsForCme as $city) {
                $cmeGraphLabels[] = $city->name;
                $stat = $cmeStats->get($city->id);
                $cmeGraphData[] = $stat->total ?? 0;
                $cmeResolvedData[] = $stat->resolved ?? 0;
            }
        } elseif (!$hasUnrestrictedAccess && $user && !empty($user->group_ids)) {
            // GE User: Show all GE Nodes (sectors) under their assigned GE Groups
            $geNodesForGroup = \App\Models\Sector::whereIn('city_id', $user->group_ids)
                ->where('status', 1)
                ->orderBy('name')
                ->get();

            $cmeNodeSectorIds = $geNodesForGroup->pluck('id')->toArray();

            // Count complaints via house's sector_id (Inclusive of house-less)
            $cmeStatsRaw = \App\Models\Complaint::leftJoin('houses', 'houses.id', '=', 'complaints.house_id')
                ->where(function ($q) use ($cmeNodeSectorIds) {
                    $q->whereIn('houses.sector_id', $cmeNodeSectorIds)
                        ->orWhere(function ($cq) use ($cmeNodeSectorIds) {
                            $cq->whereNull('complaints.house_id')->whereIn('complaints.sector_id', $cmeNodeSectorIds);
                        });
                })
                ->selectRaw('COALESCE(houses.sector_id, complaints.sector_id) as sector_id, COUNT(*) as total, SUM(CASE WHEN complaints.status IN ("resolved", "closed") THEN 1 ELSE 0 END) as resolved');

            $this->applyCmeDateFilter($cmeStatsRaw, $cmeDateRange);
            $applyGlobalFilters($cmeStatsRaw); // Apply top-level filters (Category, etc.)
            $cmeStats = $cmeStatsRaw->groupBy(\DB::raw('COALESCE(houses.sector_id, complaints.sector_id)'))->get()->keyBy('sector_id');

            foreach ($geNodesForGroup as $sector) {
                $cmeGraphLabels[] = $sector->name;
                $stat = $cmeStats->get($sector->id);
                $cmeGraphData[] = $stat->total ?? 0;
                $cmeResolvedData[] = $stat->resolved ?? 0;
            }
        } elseif (!$hasUnrestrictedAccess && $user && !empty($user->node_ids)) {
            // Node User: Show only their assigned GE Nodes (sectors)
            $userNodes = \App\Models\Sector::whereIn('id', $user->node_ids)
                ->where('status', 1)
                ->orderBy('name')
                ->get();

            $cmeNodeOnlySectorIds = $userNodes->pluck('id')->toArray();

            // Count complaints via house's sector_id (Inclusive of house-less)
            $cmeStatsRaw = \App\Models\Complaint::leftJoin('houses', 'houses.id', '=', 'complaints.house_id')
                ->where(function ($q) use ($cmeNodeOnlySectorIds) {
                    $q->whereIn('houses.sector_id', $cmeNodeOnlySectorIds)
                        ->orWhere(function ($cq) use ($cmeNodeOnlySectorIds) {
                            $cq->whereNull('complaints.house_id')->whereIn('complaints.sector_id', $cmeNodeOnlySectorIds);
                        });
                })
                ->selectRaw('COALESCE(houses.sector_id, complaints.sector_id) as sector_id, COUNT(*) as total, SUM(CASE WHEN complaints.status IN ("resolved", "closed") THEN 1 ELSE 0 END) as resolved');

            $this->applyCmeDateFilter($cmeStatsRaw, $cmeDateRange);
            $applyGlobalFilters($cmeStatsRaw); // Apply top-level filters (Category, etc.)
            $cmeStats = $cmeStatsRaw->groupBy(\DB::raw('COALESCE(houses.sector_id, complaints.sector_id)'))->get()->keyBy('sector_id');

            foreach ($userNodes as $sector) {
                $cmeGraphLabels[] = $sector->name;
                $stat = $cmeStats->get($sector->id);
                $cmeGraphData[] = $stat->total ?? 0;
                $cmeResolvedData[] = $stat->resolved ?? 0;
            }
        } else {
            // Admin or Unrestricted: Show stats for all CMEs (Optimized grouped query)
            $cmeIds_list = $cmesList->pluck('id')->toArray();

            $cmeStatsQuery = \App\Models\Complaint::leftJoin('houses', 'houses.id', '=', 'complaints.house_id')
                ->leftJoin('cities', function ($join) {
                    $join->on('houses.city_id', '=', 'cities.id')
                        ->orOn('complaints.city_id', '=', 'cities.id');
                })
                ->leftJoin('sectors', function ($join) {
                    $join->on('houses.sector_id', '=', 'sectors.id')
                        ->orOn('complaints.sector_id', '=', 'sectors.id');
                })
                ->selectRaw('
                    COALESCE(cities.cme_id, sectors.cme_id) as cme_id, 
                    COUNT(*) as total, 
                    SUM(CASE WHEN complaints.status IN ("resolved", "closed") THEN 1 ELSE 0 END) as resolved
                ')
                ->where(function ($q) use ($cmeIds_list) {
                    $q->whereIn('cities.cme_id', $cmeIds_list)
                        ->orWhereIn('sectors.cme_id', $cmeIds_list);
                })
                ->groupBy(\DB::raw('COALESCE(cities.cme_id, sectors.cme_id)'));

            $this->applyCmeDateFilter($cmeStatsQuery, $cmeDateRange);
            $applyGlobalFilters($cmeStatsQuery); // Apply top-level filters (Category, etc.)
            $allCmeStats = $cmeStatsQuery->get()->keyBy('cme_id');

            foreach ($cmesList as $cme) {
                $cmeGraphLabels[] = $cme->name;
                $stat = $allCmeStats->get($cme->id);
                $cmeGraphData[] = $stat->total ?? 0;
                $cmeResolvedData[] = $stat->resolved ?? 0;
            }
        }

        // Get Top 10 Products by Issued Quantity from SpareStockLog for accurate time filtering
        $categoryUsageQuery = \App\Models\SpareStockLog::join('spares', 'spare_stock_logs.spare_id', '=', 'spares.id')
            ->selectRaw('
                spares.item_name,
                SUM(CASE WHEN spare_stock_logs.change_type = "out" THEN spare_stock_logs.quantity ELSE 0 END) as total_used,
                SUM(CASE WHEN spare_stock_logs.change_type = "in" THEN spare_stock_logs.quantity ELSE 0 END) as total_received
            ')
            ->whereNotNull('spares.item_name')
            ->groupBy('spares.item_name')
            ->orderByDesc('total_used')
            ->limit(10);

        // Apply Global Location Scoping (on joined spares table)
        $this->applyFrontendLocationScope($categoryUsageQuery, $locationScope, 'spares.city_id', 'spares.sector_id');

        // Apply dynamic dashboard filters to Products list
        if ($cityId) {
            $categoryUsageQuery->where('spares.city_id', $cityId);
        } elseif ($sectorId) {
            $categoryUsageQuery->where('spares.sector_id', $sectorId);
        } elseif ($cmesId) {
            $cityIdsForProducts = City::where('cme_id', $cmesId)->pluck('id')->toArray();
            $categoryUsageQuery->whereIn('spares.city_id', $cityIdsForProducts);
        }

        // Apply date filter to SpareStockLog created_at column (year-based)
        $categoryDateRange = $request->get('category_date_range', 'all_time');
        if ($categoryDateRange && $categoryDateRange !== 'all_time') {
            $categoryUsageQuery->whereYear('spare_stock_logs.created_at', $categoryDateRange);
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
            $isAllTime = ($cmeDateRange === 'all_time' || empty($cmeDateRange));

            // Build report periods
            $reportMonths = [];
            if ($isAllTime) {
                // Get oldest year from DB to build year rows
                $oldestYear = \App\Models\Complaint::min(\DB::raw('YEAR(created_at)'));
                $currentYear = now()->year;
                if (!$oldestYear)
                    $oldestYear = $currentYear;
                for ($yr = $oldestYear; $yr <= $currentYear; $yr++) {
                    $reportMonths[$yr] = ['label' => (string) $yr, 'year' => $yr, 'month' => null];
                    $monthlyTableData[(string) $yr] = [];
                }
            } else {
                $yearVal = (int) $cmeDateRange;
                for ($m = 1; $m <= 12; $m++) {
                    $label = date('F', mktime(0, 0, 0, $m, 1));
                    $reportMonths[] = ['label' => $label, 'year' => $yearVal, 'month' => $m];
                    $monthlyTableData[$label] = [];
                }
            }

            // FETCHING ALL DATA IN ONE GO - OPTIMIZED
            $entityIds = $tableEntities->pluck('id')->toArray();

            $tableQuery = \App\Models\Complaint::query();
            if ($entityType === 'cme') {
                $allCityIds = \App\Models\City::whereIn('cme_id', $entityIds)->pluck('id')->toArray();
                $tableQuery->where(function ($q) use ($allCityIds, $entityIds) {
                    $q->where(function ($sq) use ($allCityIds) {
                        if (!empty($allCityIds))
                            $sq->whereIn('houses.city_id', array_unique($allCityIds));
                    })->orWhereIn('houses.sector_id', function ($sq) use ($entityIds) {
                        $sq->select('id')->from('sectors')->whereIn('cme_id', $entityIds);
                    })->orWhere(function ($cq) use ($allCityIds, $entityIds) {
                        $cq->whereNull('complaints.house_id');
                        $cq->where(function ($inner) use ($allCityIds, $entityIds) {
                            if (!empty($allCityIds))
                                $inner->whereIn('complaints.city_id', array_unique($allCityIds));
                            $inner->orWhereIn('complaints.sector_id', function ($isq) use ($entityIds) {
                                $isq->select('id')->from('sectors')->whereIn('cme_id', $entityIds);
                            });
                        });
                    });
                });
            } elseif ($entityType === 'city') {
                $tableQuery->where(function ($q) use ($entityIds) {
                    $q->whereIn('houses.city_id', $entityIds)
                        ->orWhereIn('houses.sector_id', function ($sq) use ($entityIds) {
                            $sq->select('id')->from('sectors')->whereIn('city_id', $entityIds);
                        })
                        ->orWhere(function ($cq) use ($entityIds) {
                            $cq->whereNull('complaints.house_id');
                            $cq->where(function ($inner) use ($entityIds) {
                                $inner->whereIn('complaints.city_id', $entityIds)
                                    ->orWhereIn('complaints.sector_id', function ($isq) use ($entityIds) {
                                        $isq->select('id')->from('sectors')->whereIn('city_id', $entityIds);
                                    });
                            });
                        });
                });
            } else {
                $tableQuery->where(function ($q) use ($entityIds) {
                    $q->whereIn('houses.sector_id', $entityIds)
                        ->orWhere(function ($cq) use ($entityIds) {
                            $cq->whereNull('complaints.house_id')->whereIn('complaints.sector_id', $entityIds);
                        });
                });
            }

            // Apply date filter (no filter for all_time so totals match the graph)
            $this->applyCmeDateFilter($tableQuery, $cmeDateRange);

            if ($isAllTime) {
                // Group by year only (no month needed)
                $allStats = $tableQuery->leftJoin('houses', 'complaints.house_id', '=', 'houses.id')
                    ->selectRaw('
                    COALESCE(houses.city_id, complaints.city_id) as city_id, 
                    COALESCE(houses.sector_id, complaints.sector_id) as sector_id, 
                    YEAR(complaints.created_at) as year,
                    COUNT(*) as total,
                    SUM(CASE WHEN complaints.status IN ("resolved", "closed") THEN 1 ELSE 0 END) as resolved
                ')
                    ->groupBy(\DB::raw('COALESCE(houses.city_id, complaints.city_id)'), \DB::raw('COALESCE(houses.sector_id, complaints.sector_id)'), 'year')
                    ->get();
            } else {
                // Group by year+month for specific year
                $allStats = $tableQuery->leftJoin('houses', 'complaints.house_id', '=', 'houses.id')
                    ->selectRaw('
                    COALESCE(houses.city_id, complaints.city_id) as city_id, 
                    COALESCE(houses.sector_id, complaints.sector_id) as sector_id, 
                    YEAR(complaints.created_at) as year, 
                    MONTH(complaints.created_at) as month,
                    COUNT(*) as total,
                    SUM(CASE WHEN complaints.status IN ("resolved", "closed") THEN 1 ELSE 0 END) as resolved
                ')
                    ->groupBy(\DB::raw('COALESCE(houses.city_id, complaints.city_id)'), \DB::raw('COALESCE(houses.sector_id, complaints.sector_id)'), 'year', 'month')
                    ->get();
            }

            // PRE-CALCULATE MAPPINGS FOR ACCURATE ATTRIBUTION
            $cityCmeMap = [];
            $sectorCmeMap = [];

            if ($entityType === 'cme') {
                $cityCmeMap = \App\Models\City::whereIn('id', $allStats->pluck('city_id')->filter()->unique())->pluck('cme_id', 'id')->toArray();
                $sectorCmeMap = \App\Models\Sector::whereIn('id', $allStats->pluck('sector_id')->filter()->unique())->pluck('cme_id', 'id')->toArray();
            }

            // PRE-INDEX ALL STATS FOR FASTER LOOKUP
            $indexedStats = [];
            foreach ($allStats as $stat) {
                $timeKey = $isAllTime ? (string) $stat->year : $stat->year . '_' . $stat->month;
                $entityKey = '';
                if ($entityType === 'cme') {
                    if ($stat->city_id && isset($cityCmeMap[$stat->city_id])) {
                        $entityKey = 'cme_' . $cityCmeMap[$stat->city_id];
                    } elseif ($stat->sector_id && isset($sectorCmeMap[$stat->sector_id])) {
                        $entityKey = 'cme_' . $sectorCmeMap[$stat->sector_id];
                    }
                } elseif ($entityType === 'city') {
                    if ($stat->city_id)
                        $entityKey = 'city_' . $stat->city_id;
                } else {
                    if ($stat->sector_id)
                        $entityKey = 'sector_' . $stat->sector_id;
                }

                if ($entityKey) {
                    if (!isset($indexedStats[$timeKey][$entityKey])) {
                        $indexedStats[$timeKey][$entityKey] = ['total' => 0, 'resolved' => 0];
                    }
                    $indexedStats[$timeKey][$entityKey]['total'] += $stat->total;
                    $indexedStats[$timeKey][$entityKey]['resolved'] += $stat->resolved;
                }
            }

            // Map results using indexed data
            foreach ($reportMonths as $rm) {
                $timeKey = $isAllTime ? (string) $rm['year'] : $rm['year'] . '_' . $rm['month'];
                foreach ($tableEntities as $entity) {
                    $entityKey = $entityType . '_' . $entity->id;
                    $combinedStat = $indexedStats[$timeKey][$entityKey] ?? ['total' => 0, 'resolved' => 0];
                    $monthlyTableData[$rm['label']][$entity->name] = $combinedStat;
                }
            }
        }

        // Stock Consumption data - respects categoryDateRange as a year filter
        $stockFilters = [
            'cmes_id' => $cmesId,
            'city_id' => $cityId,
            'sector_id' => $sectorId,
            'category' => $category,
            'date_range' => $dateRange,
        ];
        $stockYear = ($categoryDateRange && $categoryDateRange !== 'all_time') ? $categoryDateRange : 'all_time';
        $stockConsumptionData = $this->getStockConsumptionData($user, $locationScope, $stockYear, $stockFilters);

        // Compute stock month labels for the selected year
        if ($stockYear === 'all_time') {
            $stockMonthLabels = [];
            for ($i = 11; $i >= 0; $i--) {
                $stockMonthLabels[] = now()->startOfMonth()->subMonths($i)->format('M');
            }
        } else {
            $stockMonthLabels = [];
            for ($m = 1; $m <= 12; $m++) {
                $stockMonthLabels[] = date('M', mktime(0, 0, 0, $m, 1));
            }
        }
        $overdueComplaints = $stats['overdue_complaints'] ?? 0;

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
                'pagination' => $paginationData,
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
                'stockMonthLabels' => $stockMonthLabels,
                'serverStats' => $stats,
                'cmeTableHtml' => view('frontend.dashboard.partials.cme_table', [
                    'monthlyTableData' => $monthlyTableData,
                    'tableEntities' => $tableEntities,
                ])->render(),
                'stockTableHtml' => view('frontend.dashboard.partials.stock_table', [
                    'stockConsumptionData' => $stockConsumptionData,
                    'monthLabels' => $stockMonthLabels,
                ])->render()
            ])->header('Cache-Control', 'no-cache, no-store, must-revalidate')
              ->header('Pragma', 'no-cache')
              ->header('Expires', '0');
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
            'stockMonthLabels',
            'categoryLabels',
            'categoryUsageValues',
            'categoryTotalReceivedValues',
            'overdueComplaints',
            'years',
            'cmeDateRange',
            'categoryDateRange'
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
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update([
            'username' => $request->username,
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        return redirect()->route('frontend.profile')->with('success', 'Profile updated successfully.');
    }


    /**
     * Update the user password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'password.different' => 'The new password must be different from the current password.',
        ]);

        $user = Auth::guard('frontend')->user();

        if (!\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
        }

        $user->update([
            'password' => \Hash::make($request->password),
            'password_updated_at' => now(),
        ]);

        return redirect()->route('frontend.profile')->with('success', 'Password updated successfully.');
    }

    /**
     * Show the public feedback form for a complaint.
     */
    public function feedback($id)
    {
        $complaint = Complaint::with(['category', 'assignedEmployee.designation', 'house'])->find($id);

        if (!$complaint) {
            abort(404, 'Complaint not found.');
        }

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
        $complaint = Complaint::find($id);

        if (!$complaint) {
            abort(404, 'Complaint not found.');
        }

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
            'house',
            'city',
            'sector',
            'category',
            'assignedEmployee.designation',
            'attachments',
            'spareApprovals',
            'feedback.enteredBy'
        ])->find($id);

        if (!$complaint) {
            if ($request->ajax()) {
                return response('<div class="p-8 text-center text-red-500 font-bold">Complaint not found. Please check the ID and try again.</div>', 404);
            }
            abort(404, 'Complaint not found.');
        }

        // Check Access
        $user = Auth::user();
        if ($user) {
            $scope = $this->getFrontendUserLocationScope($user);
            if (!empty($scope['restricted'])) {
                $cityIds = $scope['city_ids'] ?? [];
                $sectorIds = $scope['sector_ids'] ?? [];

                // Valid if complaint's city is in user's city_ids
                // OR complaint's sector is in user's sector_ids
                $hasCityAccess = !empty($cityIds) && in_array($complaint->house?->city_id ?? $complaint->city_id, $cityIds);
                $hasSectorAccess = !empty($sectorIds) && in_array($complaint->house?->sector_id ?? $complaint->sector_id, $sectorIds);

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
            $sparesListQuery->whereHas('category', function ($q) use ($cat) {
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
                    'year' => (int) $d->year,
                    'month' => (int) $d->month
                ];
            }
        } else {
            for ($m = 1; $m <= 12; $m++) {
                $reportMonths[] = [
                    'label' => date('M', mktime(0, 0, 0, $m, 1)),
                    'year' => (int) $year,
                    'month' => $m
                ];
            }
        }

        // Limit to top 30 spares for dashboard performance
        $sparesProcessed = 0;
        foreach ($sparesList as $spare) {
            if ($sparesProcessed >= 30)
                break;
            $sparesProcessed++;
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

            // If All Time is selected AND no specific dashboard time filter is applied, use the master counters from the Spare model
            // This ensures matches with Dashboard and avoids issues with missing log history
            $hasTimeFilter = !empty($dashboardFilters['date_range']) && $dashboardFilters['date_range'] !== 'all_time';

            if ($year === 'all_time' && !$hasTimeFilter) {
                $stockConsumptionData[$spare->item_name] = [
                    'total_received' => (int) $spare->total_received_quantity,
                    'total_used' => (int) $spare->issued_quantity,
                    'current_stock' => (int) $spare->stock_quantity,
                    'monthly_data' => $monthlyData,
                    'monthly_received_data' => $monthlyReceivedData
                ];
            } else {
                // For specific year OR when a dashboard time filter is applied,
                // use the calculated window values for Received/Used to ensure consistency with graphs
                $stockConsumptionData[$spare->item_name] = [
                    'total_received' => $windowTotalReceived,
                    'total_used' => $windowTotalIssued,
                    'current_stock' => (int) $spare->stock_quantity,
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
            case 'excellent':
                return 5;
            case 'good':
                return 4;
            case 'satisfied':
                return 3;
            case 'fair':
                return 2;
            case 'poor':
                return 1;
            default:
                return 0;
        }
    }

    /**
     * AJAX endpoint to get cities by CME, respecting user privileges
     */
    public function getCitiesByCmeAjax(Request $request)
    {
        $user = Auth::user();
        if (!$user)
            return response()->json([]);

        $locationScope = $this->getFrontendUserLocationScope($user);
        $cmeId = $request->query('cme_id');

        if (!$cmeId) {
            return response()->json([]);
        }

        $query = \App\Models\City::where('cme_id', $cmeId)
            ->where('status', 1);

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
        if (!$user)
            return response()->json([]);

        $locationScope = $this->getFrontendUserLocationScope($user);
        $cityId = $request->query('city_id');

        if (!$cityId) {
            return response()->json([]);
        }

        $query = \App\Models\Sector::where('city_id', $cityId)
            ->where('status', 1);

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
        if ($dateRange && $dateRange !== 'all_time') {
            $query->whereYear('complaints.created_at', $dateRange);
        }
    }
}
