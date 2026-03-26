<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Employee;
use App\Models\Spare;
use App\Models\SpareApprovalPerforma;
use App\Models\SlaRule;
use App\Models\City;
use App\Models\Sector;
use App\Models\Cme;
use App\Models\ComplaintCategory;
use App\Traits\DatabaseTimeHelpers;
use App\Traits\LocationFilterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    use DatabaseTimeHelpers, LocationFilterTrait;
    public function __construct()
    {
        // Middleware is applied in routes
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $cmesId = $request->input('cmes_id');
        $cmesList = collect();
        if (Schema::hasTable('cmes')) {
            $cmesQuery = Cme::where('status', 1)->orderBy('name');
            $this->filterCmesByLocation($cmesQuery, $user);
            $cmesList = $cmesQuery->get();
        }

        // Get filter values from request
        $cityId = $request->input('city_id');
        $sectorId = $request->input('sector_id');
        $category = $request->input('category');
        $approvalStatus = $request->input('approval_status');
        $complaintStatus = $request->input('complaint_status');
        $dateRange = $request->input('date_range');

        // Get GE role for loading users - try multiple variations
        $geRole = \App\Models\Role::where(function ($q) {
            $q->whereRaw('LOWER(role_name) = ?', ['garrison_engineer'])
                ->orWhereRaw('LOWER(role_name) = ?', ['garrison engineer'])
                ->orWhereRaw('LOWER(role_name) LIKE ?', ['%garrison%engineer%'])
                ->orWhereRaw('LOWER(role_name) LIKE ?', ['%ge%']);
        })->first();

        // Determine if user has global location access (Admin/Director)
        $roleName = strtolower($user->role->role_name ?? '');
        $hasGlobalAccess = in_array($roleName, ['admin', 'director']);

        // Get cities for filter
        $cities = collect();
        if (Schema::hasTable('cities')) {
            if ($hasGlobalAccess || empty($user->city_ids)) {
                // User is admin/director OR has no city_ids assigned -> can see all active cities
                $citiesQuery = City::where('status', 1)->orderBy('name', 'asc');
                if ($cmesId) {
                    $citiesQuery->where('cme_id', $cmesId);
                }
                $cities = $citiesQuery->get();
                // OPTIMIZED: Batch load GE users for all cities in one query
                if ($geRole && $cities->isNotEmpty()) {
                    $cityIds = $cities->pluck('id')->toArray();
                    $allGeUsers = User::where('role_id', $geRole->id)
                        ->where('status', 1)
                        ->get(); // Fetch all active GE users once
                    
                    foreach ($cities as $city) {
                        $geUsersForCity = $allGeUsers->filter(function($u) use ($city) {
                            $uCityIds = is_array($u->city_ids) ? $u->city_ids : (json_decode($u->city_ids, true) ?: []);
                            return in_array($city->id, $uCityIds) || in_array((string)$city->id, $uCityIds);
                        });
                        $city->setRelation('users', $geUsersForCity);
                    }
                }
            } else {
                // User has city_ids assigned, sees only their cities
                $citiesQuery = City::whereIn('id', $user->city_ids)->where('status', 1)->orderBy('name', 'asc');
                if ($cmesId) {
                    $citiesQuery->where('cme_id', $cmesId);
                }
                $cities = $citiesQuery->get();
                // OPTIMIZED: Batch load GE users for these cities
                if ($geRole && $cities->isNotEmpty()) {
                    $cityIds = $cities->pluck('id')->toArray();
                    $allGeUsers = User::where('role_id', $geRole->id)
                        ->where('status', 1)
                        ->get();
                    
                    foreach ($cities as $city) {
                        $geUsersForCity = $allGeUsers->filter(function($u) use ($city) {
                            $uCityIds = is_array($u->city_ids) ? $u->city_ids : (json_decode($u->city_ids, true) ?: []);
                            return in_array($city->id, $uCityIds) || in_array((string)$city->id, $uCityIds);
                        });
                        $city->setRelation('users', $geUsersForCity);
                    }
                }
            }
        }

        // Get sectors for filter
        $sectors = collect();
        if (Schema::hasTable('sectors')) {
            $sectorsQuery = Sector::where('status', 1)->orderBy('name', 'asc');

            // Always apply selected City filter (most specific - always takes priority)
            if ($cityId) {
                $sectorsQuery->where('city_id', $cityId);
            } elseif ($cmesId) {
                // No city selected, but CMES selected -> Get cities belonging to this CMES
                $cmeCityIds = City::where('cme_id', $cmesId)->pluck('id')->toArray();
                if (!empty($cmeCityIds)) {
                    $sectorsQuery->whereIn('city_id', $cmeCityIds);
                }
                // If not global admin, restrict to their specific cities within this CMES
                if (!$hasGlobalAccess && !empty($user->city_ids)) {
                    $sectorsQuery->whereIn('city_id', $user->city_ids);
                }
            } else {
                // No CMES or city selected
                if (!$hasGlobalAccess) {
                    // Start with user's sector restriction if applicable
                    if (!empty($user->sector_ids)) {
                        $sectorsQuery->whereIn('id', $user->sector_ids);
                    }
                    // Or restrict by user's assigned cities
                    elseif (!empty($user->city_ids)) {
                        $sectorsQuery->whereIn('city_id', $user->city_ids);
                    }
                }
            }

            $sectors = $sectorsQuery->get();
        }

        // Get categories for filter
        $categories = collect();
        if (Schema::hasTable('complaint_categories')) {
            $categories = ComplaintCategory::where('status', 1)->orderBy('name')->pluck('name');
        } else {
            // Fallback: Get from complaints
            $categories = Complaint::select('category')
                ->distinct()
                ->whereNotNull('category')
                ->orderBy('category')
                ->pluck('category');
        }

        // Get approval statuses for filter (fetch from database)
        $approvalStatuses = collect();
        if (Schema::hasTable('spare_approval_performa')) {
            // Get all unique statuses from database
            $statusesFromDB = SpareApprovalPerforma::select('status')
                ->distinct()
                ->whereNotNull('status')
                ->orderBy('status')
                ->pluck('status');

            // Map to status => label format
            $statusLabels = SpareApprovalPerforma::getStatuses();
            $approvalStatuses = $statusesFromDB->mapWithKeys(function ($status) use ($statusLabels) {
                return [$status => $statusLabels[$status] ?? ucfirst($status)];
            });
        }

        // Get complaint statuses for filter (same as approvals page)
        $complaintStatuses = [
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

        // Get dashboard statistics with filters
        $stats = $this->getDashboardStats($user, $cityId, $sectorId, $category, $approvalStatus, $complaintStatus, $dateRange, $cmesId);

        // Get recent complaints with location filtering and filters
        $recentComplaintsQuery = Complaint::with(['assignedEmployee']);
        $this->filterComplaintsByLocation($recentComplaintsQuery, $user);
        $this->applyFilters($recentComplaintsQuery, $cityId, $sectorId, $category, $approvalStatus, $complaintStatus, $dateRange, $cmesId);
        $recentComplaints = $recentComplaintsQuery->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get approvals with location filtering and filters
        $pendingApprovalsQuery = SpareApprovalPerforma::with(['complaint.house', 'complaint.assignedEmployee', 'requestedBy', 'items.spare']);

        // Apply approval status filter directly on approvals (if specified, otherwise show only pending)
        if ($approvalStatus) {
            $pendingApprovalsQuery->where('status', $approvalStatus);
        } else {
            // If no approval status filter, show only pending approvals for "In Progress Complaints" section
            $pendingApprovalsQuery->where('status', 'pending');
        }

        // Apply location filter through complaint relationship
        if (!$this->canViewAllData($user)) {
            $pendingApprovalsQuery->whereHas('complaint', function ($q) use ($user, $cityId, $sectorId, $category, $complaintStatus, $dateRange, $cmesId) {
                $this->filterComplaintsByLocation($q, $user);
                $this->applyFilters($q, $cityId, $sectorId, $category, null, $complaintStatus, $dateRange, $cmesId);
            });
        } else {
            // Director: Apply filters through complaint relationship
            if ($cityId || $sectorId || $category || $complaintStatus || $dateRange || $cmesId) {
                $pendingApprovalsQuery->whereHas('complaint', function ($q) use ($cityId, $sectorId, $category, $complaintStatus, $dateRange, $cmesId) {
                    $this->applyFilters($q, $cityId, $sectorId, $category, null, $complaintStatus, $dateRange, $cmesId);
                });
            }
        }

        $pendingApprovals = $pendingApprovalsQuery->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get low stock items with location filtering
        $lowStockItemsQuery = Spare::lowStock();
        $this->filterSparesByLocation($lowStockItemsQuery, $user);
        $lowStockItems = $lowStockItemsQuery
            ->orderBy('stock_quantity', 'asc')
            ->limit(10)
            ->get();

        // Get overdue complaints with location filtering and filters
        $overdueComplaintsQuery = Complaint::overdue()
            ->with(['assignedEmployee']);
        $this->filterComplaintsByLocation($overdueComplaintsQuery, $user);
        $this->applyFilters($overdueComplaintsQuery, $cityId, $sectorId, $category, $approvalStatus, $complaintStatus, $dateRange, $cmesId);
        $overdueComplaints = $overdueComplaintsQuery->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        // Get complaints by status with location filtering and filters
        $complaintsByStatusQuery = Complaint::query();
        $this->filterComplaintsByLocation($complaintsByStatusQuery, $user);
        $this->applyFilters($complaintsByStatusQuery, $cityId, $sectorId, $category, $approvalStatus, $complaintStatus, $dateRange, $cmesId);

        // Clone query before selectRaw to use for performa type counts
        $performaCountQuery = clone $complaintsByStatusQuery;

        // Get status counts - map 'new' status to 'assigned' (same as approvals page)
        $statusCounts = $complaintsByStatusQuery->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Map 'new' status to 'unassigned' for display
        $complaintsByStatus = [];
        foreach ($statusCounts as $status => $count) {
            $displayStatus = ($status === 'new') ? 'unassigned' : $status;
            if (!isset($complaintsByStatus[$displayStatus])) {
                $complaintsByStatus[$displayStatus] = 0;
            }
            $complaintsByStatus[$displayStatus] += $count;
        }

        // Add performa type complaints that are stored as in_progress with performa_type in approvals
        // Work Performa - count complaints with work_performa status (waiting_for_authority removed)
        $workPerformaCount = (clone $performaCountQuery)
            ->where('status', 'work_performa')
            ->count();
        if ($workPerformaCount > 0) {
            $complaintsByStatus['work_performa'] = ($complaintsByStatus['work_performa'] ?? 0) + $workPerformaCount;
        }

        // Maintenance Performa - count complaints with maint_performa status (waiting_for_authority removed)
        $maintPerformaCount = (clone $performaCountQuery)
            ->where('status', 'maint_performa')
            ->count();
        if ($maintPerformaCount > 0) {
            $complaintsByStatus['maint_performa'] = ($complaintsByStatus['maint_performa'] ?? 0) + $maintPerformaCount;
        }

        // Work Performa Priced - count complaints with work_priced_performa status (waiting_for_authority removed)
        $workPricedPerformaCount = (clone $performaCountQuery)
            ->where('status', 'work_priced_performa')
            ->count();
        if ($workPricedPerformaCount > 0) {
            $complaintsByStatus['work_priced_performa'] = ($complaintsByStatus['work_priced_performa'] ?? 0) + $workPricedPerformaCount;
        }

        // Maintenance Performa Priced - count complaints with maint_priced_performa status (waiting_for_authority removed)
        $maintPricedPerformaCount = (clone $performaCountQuery)
            ->where('status', 'maint_priced_performa')
            ->count();
        if ($maintPricedPerformaCount > 0) {
            $complaintsByStatus['maint_priced_performa'] = ($complaintsByStatus['maint_priced_performa'] ?? 0) + $maintPricedPerformaCount;
        }

        // Product N/A - count product_na status OR in_progress complaints with product_na performa_type
        // First get direct product_na status count (already in $complaintsByStatus from line 199-202)
        $directProductNaCount = $complaintsByStatus['product_na'] ?? 0;

        // Then count in_progress complaints with product_na performa_type in approvals
        $productNaFromApprovals = (clone $performaCountQuery)
            ->where('status', 'in_progress')
            ->whereHas('spareApprovals', function ($q) {
                $q->where('performa_type', 'product_na')
                    ->whereNull('deleted_at'); // Exclude soft deleted approvals
            })
            ->count();

        // Set total product_na count
        $totalProductNa = $directProductNaCount + $productNaFromApprovals;
        if ($totalProductNa > 0) {
            $complaintsByStatus['product_na'] = $totalProductNa;
        } elseif (!isset($complaintsByStatus['product_na'])) {
            // Ensure product_na key exists even if count is 0
            $complaintsByStatus['product_na'] = 0;
        }

        // Get complaints by category - using ComplaintCategory model
        $allCategories = ComplaintCategory::where('status', 1)->orderBy('name', 'asc')->get();
        $complaintsByType = [];
        $complaintsByCategory = [];

        // OPTIMIZED: Get complaints count by category in a single bulk query
        $complaintsByType = [];
        $complaintsByCategory = [];
        
        if ($allCategories->isNotEmpty()) {
            $catCountQuery = Complaint::query();
            $this->filterComplaintsByLocation($catCountQuery, $user);
            $this->applyFilters($catCountQuery, $cityId, $sectorId, $category, $approvalStatus, $complaintStatus, $dateRange, $cmesId);
            
            $catCounts = $catCountQuery->selectRaw('category_id, COUNT(*) as aggregate')
                ->whereIn('category_id', $allCategories->pluck('id'))
                ->groupBy('category_id')
                ->pluck('aggregate', 'category_id');

            foreach ($allCategories as $cat) {
                $count = $catCounts[$cat->id] ?? 0;
                if ($count > 0) {
                    $complaintsByType[$cat->name] = $count;
                }
                $complaintsByCategory[$cat->id] = [
                    'name' => $cat->name,
                    'count' => $count,
                ];
            }
        }

        // Get employee performance with location filtering
        $employeePerformanceQuery = Employee::query();
        $this->filterEmployeesByLocation($employeePerformanceQuery, $user);
        $employeePerformance = $employeePerformanceQuery
            ->withCount([
                'assignedComplaints' => function ($query) {
                    $query->where('created_at', '>=', now()->subDays(30));
                }
            ])
            ->orderBy('assigned_complaints_count', 'desc')
            ->limit(5)
            ->get();

        // Get SLA performance
        $slaPerformance = $this->getSlaPerformance();

        // Get monthly trends with filters
        $monthlyTrends = $this->getMonthlyTrends($user, $cityId, $sectorId, $category, $approvalStatus, $complaintStatus, $dateRange);

        // Get GE progress based on location filter only
        // GE Groups are stored in cities table
        $geProgress = [];

        // Location filter logic:
        // 1. If user's city_id AND sector_id are both null - show all data
        // 2. If user's city_id is set but sector_id is null - show only their city's data
        // 3. If user has sector_ids - they shouldn't see GE Feedback Overview
        // Check if user has permission to see GE Feedback Overview
        // Allow: Director, Admin, Garrison Engineer (regardless of location assignments)
        $userRole = $user->role ? strtolower($user->role->role_name) : '';
        $isBigBoss = $userRole === 'director' || str_contains($userRole, 'admin'); // Director or Admin
        $isGE = str_contains($userRole, 'garrison') || str_contains($userRole, 'ge'); // GE
        
        // Also fallback to original location check (no sector_ids) for backward compatibility or other roles
        $shouldShowGeProgress = $isBigBoss || $isGE || (empty($user->sector_ids));

        // Always initialize geProgress array even if empty, so view can check permissions
        if ($shouldShowGeProgress) {
            if (Schema::hasTable('cities')) {
                // Base query for cities
                $geGroupsQuery = City::where('status', 1);
                
                $effectiveCityId = $cityId ?: null;
                $effectiveCityIds = empty($user->city_ids) ? [] : $user->city_ids;

                if ($effectiveCityId) {
                    $geGroupsQuery->where('id', $effectiveCityId);
                } elseif (!empty($effectiveCityIds)) {
                    $geGroupsQuery->whereIn('id', $effectiveCityIds);
                }
                
                $geGroups = $geGroupsQuery->orderBy('name')->get();
                $geGroupIds = $geGroups->pluck('id')->toArray();

                // OPTIMIZED: Fetch all stats for all relevant GE groups in one go
                // Get Total and Resolved Counts
                $allCityStats = Complaint::leftJoin('houses', 'houses.id', '=', 'complaints.house_id')
                    ->selectRaw('
                        COALESCE(houses.city_id, complaints.city_id) as city_id,
                        COUNT(*) as total,
                        SUM(CASE WHEN complaints.status IN ("resolved", "closed") THEN 1 ELSE 0 END) as resolved
                    ')
                    ->where(function($q) use ($geGroupIds) {
                        $q->whereIn('houses.city_id', $geGroupIds)
                          ->orWhere(function($cq) use ($geGroupIds) {
                              $cq->whereNull('complaints.house_id')->whereIn('complaints.city_id', $geGroupIds);
                          });
                    });

                // Apply Filters (sector, category, complaint status, date range, CMES)
                $this->applyFilters($allCityStats, null, $sectorId, $category, $approvalStatus, $complaintStatus, $dateRange, $cmesId);
                $cityCounts = $allCityStats->groupBy(\DB::raw('COALESCE(houses.city_id, complaints.city_id)'))->get()->keyBy('city_id');

                // Get Feedback Stats
                // Note: Feedback only on resolved/closed complaints
                $feedbackStatsQuery = Complaint::leftJoin('houses', 'houses.id', '=', 'complaints.house_id')
                    ->join('complaint_feedbacks', 'complaints.id', '=', 'complaint_feedbacks.complaint_id')
                    ->selectRaw('
                        COALESCE(houses.city_id, complaints.city_id) as city_id,
                        COUNT(*) as total_feedback,
                        SUM(complaint_feedbacks.rating_score) as rating_sum,
                        SUM(CASE WHEN complaint_feedbacks.overall_rating IN ("excellent", "good", "satisfied") THEN 1 ELSE 0 END) as good_feedback,
                        SUM(CASE WHEN complaint_feedbacks.overall_rating IN ("fair", "poor") THEN 1 ELSE 0 END) as bad_feedback
                    ')
                    ->whereIn('complaints.status', ['resolved', 'closed'])
                    ->where(function($q) use ($geGroupIds) {
                        $q->whereIn('houses.city_id', $geGroupIds)
                          ->orWhere(function($cq) use ($geGroupIds) {
                              $cq->whereNull('complaints.house_id')->whereIn('complaints.city_id', $geGroupIds);
                          });
                    })
                    ->whereNull('complaint_feedbacks.deleted_at');

                $this->applyFilters($feedbackStatsQuery, null, $sectorId, $category, $approvalStatus, null, $dateRange, $cmesId);
                $feedbackCounts = $feedbackStatsQuery->groupBy(\DB::raw('COALESCE(houses.city_id, complaints.city_id)'))->get()->keyBy('city_id');

                foreach ($geGroups as $geGroup) {
                    $counts = $cityCounts->get($geGroup->id);
                    $fback = $feedbackCounts->get($geGroup->id);

                    $total_complaints = $counts->total ?? 0;
                    $resolved_complaints = $counts->resolved ?? 0;
                    $totalFeedbacks = $fback->total_feedback ?? 0;
                    $ratingSum = $fback->rating_sum ?? 0;
                    $resolvedWithGoodFeedback = $fback->good_feedback ?? 0;
                    $resolvedWithBadFeedback = $fback->bad_feedback ?? 0;

                    $progressPercentage = $totalFeedbacks > 0
                        ? round(($ratingSum / ($totalFeedbacks * 5)) * 100, 2)
                        : 0;

                    $geProgress[] = [
                        'ge' => $geGroup,
                        'ge_name' => $geGroup->name,
                        'city' => $geGroup->name,
                        'total_complaints' => $total_complaints,
                        'resolved_complaints' => $resolved_complaints,
                        'resolved_with_good_feedback' => $resolvedWithGoodFeedback,
                        'resolved_with_bad_feedback' => $resolvedWithBadFeedback,
                        'total_feedbacks' => $totalFeedbacks,
                        'progress_percentage' => $progressPercentage,
                    ];
                }

                // Sort GE Progress: GEs with feedback first, then those without
                usort($geProgress, function ($a, $b) {
                    if ($a['total_feedbacks'] > 0 && $b['total_feedbacks'] == 0) return -1;
                    if ($a['total_feedbacks'] == 0 && $b['total_feedbacks'] > 0) return 1;
                    return 0;
                });
            }
        }

        return view('admin.dashboard', compact(
            'stats',
            'recentComplaints',
            'pendingApprovals',
            'lowStockItems',
            'overdueComplaints',
            'complaintsByStatus',
            'complaintsByType',
            'employeePerformance',
            'slaPerformance',
            'monthlyTrends',
            'cities',
            'sectors',
            'categories',
            'approvalStatuses',
            'complaintStatuses',
            'cityId',
            'sectorId',
            'category',
            'approvalStatus',
            'complaintStatus',
            'dateRange',
            'geRole',
            'geProgress'
            ,
            'cmesList',
            'cmesId'
        ));
    }

    /**
     * Apply filters to complaint query
     */
    private function applyFilters($query, $cityId = null, $sectorId = null, $category = null, $approvalStatus = null, $complaintStatus = null, $dateRange = null, $cmesId = null)
    {
        // Filter by city - inclusive: check house's city_id OR if its sector belongs to this city
        if ($cityId) {
            $sectorIdsForCity = Sector::where('city_id', $cityId)->pluck('id')->toArray();
            $query->where(function ($q) use ($cityId, $sectorIdsForCity) {
                // Match via house
                $q->whereHas('house', function ($hq) use ($cityId, $sectorIdsForCity) {
                    $hq->where(function ($sub) use ($cityId, $sectorIdsForCity) {
                        $sub->where('city_id', $cityId);
                        if (!empty($sectorIdsForCity)) {
                            $sub->orWhereIn('sector_id', $sectorIdsForCity);
                        }
                    });
                })
                // OR Match via direct complaint columns (for house-less complaints)
                ->orWhere(function ($cq) use ($cityId, $sectorIdsForCity) {
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

        // Filter by sector - use house's sector_id or direct sector_id
        if ($sectorId) {
            $query->where(function ($q) use ($sectorId) {
                $q->whereHas('house', function ($hq) use ($sectorId) {
                    $hq->where('sector_id', $sectorId);
                })->orWhere(function ($cq) use ($sectorId) {
                    $cq->whereNull('complaints.house_id')->where('complaints.sector_id', $sectorId);
                });
            });
        }

        // Filter by category
        if ($category) {
            if (is_numeric($category)) {
                $query->where('complaints.category_id', $category);
            } else {
                $query->whereHas('category', function($q) use ($category) {
                    $q->where('name', $category);
                });
            }
        }

        // Filter by approval status (through spareApprovals relationship)
        if ($approvalStatus) {
            $query->whereHas('spareApprovals', function ($q) use ($approvalStatus) {
                $q->where('status', $approvalStatus);
            });
        }

        // Filter by complaint status
        if ($complaintStatus) {
            // Handle special performa statuses - work_performa and maint_performa
            if ($complaintStatus === 'work_priced_performa') {
                $query->where('complaints.status', 'work_priced_performa');
            } elseif ($complaintStatus === 'maint_priced_performa') {
                $query->where('complaints.status', 'maint_priced_performa');
            } elseif ($complaintStatus === 'work_performa') {
                // Match complaints with work_performa status OR in_progress with work_performa performa_type
                $query->where(function ($q) {
                    $q->where('complaints.status', 'work_performa')
                        ->orWhere(function ($subQ) {
                            $subQ->where('complaints.status', 'in_progress')
                                ->whereHas('spareApprovals', function ($approvalQ) {
                                    $approvalQ->where('performa_type', 'work_performa');
                                });
                        });
                });
            } elseif ($complaintStatus === 'maint_performa') {
                // Match complaints with maint_performa status OR in_progress with maint_performa performa_type
                $query->where(function ($q) {
                    $q->where('complaints.status', 'maint_performa')
                        ->orWhere(function ($subQ) {
                            $subQ->where('complaints.status', 'in_progress')
                                ->whereHas('spareApprovals', function ($approvalQ) {
                                    $approvalQ->where('performa_type', 'maint_performa');
                                });
                        });
                });
            } elseif ($complaintStatus === 'product_na') {
                // Match product_na status OR in_progress with product_na performa_type
                $query->where(function ($q) {
                    $q->where('complaints.status', 'product_na')
                        ->orWhere(function ($subQ) {
                            $subQ->where('complaints.status', 'in_progress')
                                ->whereHas('spareApprovals', function ($approvalQ) {
                                    $approvalQ->where('performa_type', 'product_na');
                                });
                        });
                });
            } else {
                // For other statuses, filter by actual status
                $query->where('complaints.status', $complaintStatus);
            }
        }

        // Filter by date range
        if ($dateRange) {
            $now = now();
            switch ($dateRange) {
                case 'yesterday':
                    $query->whereDate('complaints.created_at', $now->copy()->subDay()->toDateString());
                    break;
                case 'today':
                    $query->whereDate('complaints.created_at', $now->toDateString());
                    break;
                case 'this_week':
                    $query->whereBetween('complaints.created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
                    break;
                case 'last_week':
                    $query->whereBetween('complaints.created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('complaints.created_at', $now->month)
                        ->whereYear('complaints.created_at', $now->year);
                    break;
                case 'last_month':
                    $query->whereMonth('complaints.created_at', $now->copy()->subMonth()->month)
                        ->whereYear('complaints.created_at', $now->copy()->subMonth()->year);
                    break;
                case 'last_6_months':
                    $query->where('complaints.created_at', '>=', $now->copy()->subMonths(6)->startOfDay());
                    break;
            }
        }
        // Filter by CMES (cmes_id) - restrict by house's city or sector belonging to selected CMES
        if ($cmesId) {
            try {
                $cmeCityIds = City::where('cme_id', $cmesId)->pluck('id')->toArray();
                $cmeSectorIds = Sector::where('cme_id', $cmesId)->pluck('id')->toArray();

                $query->where(function ($q) use ($cmeCityIds, $cmeSectorIds) {
                    // Match via house
                    $q->whereHas('house', function ($hq) use ($cmeCityIds, $cmeSectorIds) {
                        $hq->where(function ($sub) use ($cmeCityIds, $cmeSectorIds) {
                            if (!empty($cmeCityIds)) {
                                $sub->whereIn('city_id', $cmeCityIds);
                            }
                            if (!empty($cmeSectorIds)) {
                                $sub->orWhereIn('sector_id', $cmeSectorIds);
                            }
                        });
                    })
                    // OR Match via direct complaint columns (for house-less complaints)
                    ->orWhere(function ($cq) use ($cmeCityIds, $cmeSectorIds) {
                        $cq->whereNull('complaints.house_id')
                            ->where(function ($sub) use ($cmeCityIds, $cmeSectorIds) {
                                if (!empty($cmeCityIds)) {
                                    $sub->whereIn('complaints.city_id', $cmeCityIds);
                                }
                                if (!empty($cmeSectorIds)) {
                                    $sub->orWhereIn('complaints.sector_id', $cmeSectorIds);
                                }
                            });
                    });
                });
            } catch (\Exception $e) {
                // If tables/columns don't exist or query fails, ignore CMES scoping
            }
        }

        return $query;
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats($user = null, $cityId = null, $sectorId = null, $category = null, $approvalStatus = null, $complaintStatus = null, $dateRange = null, $cmesId = null)
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        // Apply location filtering to queries
        $complaintsQuery = Complaint::query();
        $this->filterComplaintsByLocation($complaintsQuery, $user);

        // Apply additional filters
        $this->applyFilters($complaintsQuery, $cityId, $sectorId, $category, $approvalStatus, $complaintStatus, $dateRange, $cmesId);

        $employeesQuery = Employee::query();
        $this->filterEmployeesByLocation($employeesQuery, $user);

        // Filter employees by selected filters based on their assigned complaints
        if ($category) {
            $employeesQuery->whereHas('assignedComplaints', function ($q) use ($category) {
                if (is_numeric($category)) {
                    $q->where('category_id', $category);
                } else {
                    $q->whereHas('category', function($subQ) use ($category) {
                        $subQ->where('name', $category);
                    });
                }
            });
        }
        if ($cityId) {
            $employeesQuery->where('city_id', $cityId);
        }
        if ($sectorId) {
            $employeesQuery->where('sector_id', $sectorId);
        }

        $sparesQuery = Spare::query();
        $this->filterSparesByLocation($sparesQuery, $user);

        // Consolidated stats query including complex performa counts
        $dashboardStats = (clone $complaintsQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN complaints.status IN ('new', 'assigned', 'in_progress') THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN complaints.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN complaints.status = 'resolved' THEN 1 ELSE 0 END) as addressed,
            SUM(CASE WHEN complaints.created_at >= ? THEN 1 ELSE 0 END) as today,
            SUM(CASE WHEN complaints.created_at >= ? THEN 1 ELSE 0 END) as this_month,
            SUM(CASE WHEN complaints.created_at >= ? AND complaints.created_at < ? THEN 1 ELSE 0 END) as last_month,
            
            SUM(CASE WHEN complaints.status = 'work_performa' THEN 1 ELSE 0 END) as direct_work_performa,
            SUM(CASE WHEN complaints.status = 'maint_performa' THEN 1 ELSE 0 END) as direct_maint_performa,
            SUM(CASE WHEN complaints.status = 'work_priced_performa' THEN 1 ELSE 0 END) as work_priced_performa,
            SUM(CASE WHEN complaints.status = 'maint_priced_performa' THEN 1 ELSE 0 END) as maint_priced_performa,
            SUM(CASE WHEN complaints.status = 'un_authorized' THEN 1 ELSE 0 END) as un_authorized,
            SUM(CASE WHEN complaints.status = 'product_na' THEN 1 ELSE 0 END) as direct_product_na,
            SUM(CASE WHEN complaints.status = 'pertains_to_ge_const_isld' THEN 1 ELSE 0 END) as pertains_to_ge_const_isld,
            SUM(CASE WHEN complaints.status = 'barak_damages' THEN 1 ELSE 0 END) as barak_damages
        ", [
            $today,
            $thisMonth,
            $lastMonth,
            $thisMonth
        ])->first();

        // For counts that require relationships (like performa_type in approvals), 
        // we'll do one joined query to get those specific overlays
        $performas = (clone $complaintsQuery)
            ->join('spare_approval_performa', 'complaints.id', '=', 'spare_approval_performa.complaint_id')
            ->where('complaints.status', 'in_progress')
            ->selectRaw('spare_approval_performa.performa_type, COUNT(*) as count')
            ->groupBy('spare_approval_performa.performa_type')
            ->pluck('count', 'performa_type');

        return [
            'total_complaints' => $dashboardStats->total ?? 0,
            'pending_complaints' => $dashboardStats->pending ?? 0,
            'in_progress_complaints' => $dashboardStats->in_progress ?? 0,
            'addressed_complaints' => $dashboardStats->addressed ?? 0,
            'overdue_complaints' => (clone $complaintsQuery)->overdue()->count(),
            'complaints_today' => $dashboardStats->today ?? 0,
            'complaints_this_month' => $dashboardStats->this_month ?? 0,
            'complaints_last_month' => $dashboardStats->last_month ?? 0,

            'work_performa' => ($dashboardStats->direct_work_performa ?? 0) + ($performas['work_performa'] ?? 0),
            'maint_performa' => ($dashboardStats->direct_maint_performa ?? 0) + ($performas['maint_performa'] ?? 0),
            'work_priced_performa' => $dashboardStats->work_priced_performa ?? 0,
            'maint_priced_performa' => $dashboardStats->maint_priced_performa ?? 0,
            'un_authorized' => $dashboardStats->un_authorized ?? 0,
            'product_na' => ($dashboardStats->direct_product_na ?? 0) + ($performas['product_na'] ?? 0),
            'pertains_to_ge_const_isld' => $dashboardStats->pertains_to_ge_const_isld ?? 0,
            'barak_damages' => $dashboardStats->barak_damages ?? 0,

            'total_users' => User::count(),
            'active_users' => User::where('status', 1)->count(),
            'total_employees' => (clone $employeesQuery)->count(),

            'total_spares' => (clone $sparesQuery)->count(),
            'low_stock_items' => (clone $sparesQuery)->lowStock()->count(),
            'out_of_stock_items' => (clone $sparesQuery)->outOfStock()->count(),
            'total_spare_value' => (clone $sparesQuery)->sum(DB::raw('stock_quantity * unit_price')),

            'active_sla_rules' => SlaRule::where('status', 1)->count(),
            'sla_breaches' => $this->getSlaBreaches(),
        ];
    }



    /**
     * Get SLA performance metrics
     */
    private function getSlaPerformance()
    {
        $totalComplaints = Complaint::query()
            ->where('created_at', '>=', now()->subDays(30))->count();
        $withinSla = 0;
        $breached = 0;

        if ($totalComplaints > 0) {
            $timeDiff = $this->getTimeDiffInHours('complaints.created_at', 'complaints.updated_at');

            // OPTIMIZED: Use a LEFT JOIN on sla_rules instead of a correlated subquery per row
            $withinSla = Complaint::query()
                ->leftJoin('sla_rules', function($join) {
                    $join->on('complaints.category_id', '=', 'sla_rules.category_id')
                         ->where('sla_rules.status', '=', 1)
                         ->whereNull('sla_rules.deleted_at');
                })
                ->where('complaints.created_at', '>=', now()->subDays(30))
                ->whereIn('complaints.status', ['resolved', 'closed'])
                ->whereRaw("{$timeDiff} <= COALESCE(sla_rules.max_resolution_time, 999999)")
                ->count();

            $breached = $totalComplaints - $withinSla;
        }

        return [
            'total' => $totalComplaints,
            'within_sla' => $withinSla,
            'breached' => $breached,
            'sla_percentage' => $totalComplaints > 0 ? round(($withinSla / $totalComplaints) * 100, 2) : 0,
        ];
    }

    /**
     * Get monthly trends
     */
    private function getMonthlyTrends($user = null, $cityId = null, $sectorId = null, $category = null, $approvalStatus = null, $complaintStatus = null, $dateRange = null, $cmesId = null)
    {
        // Initialize a base query with location and general filters
        $baseComplaintsQuery = Complaint::query();
        $this->filterComplaintsByLocation($baseComplaintsQuery, $user);
        $this->applyFilters($baseComplaintsQuery, $cityId, $sectorId, $category, $approvalStatus, $complaintStatus, $dateRange, $cmesId);

        $months = [];
        $complaints = [];
        $resolutions = [];

        // Optimized monthly trends fetch
        $trendsQuery = (clone $baseComplaintsQuery)
            ->where('complaints.created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw('
                YEAR(complaints.created_at) as year,
                MONTH(complaints.created_at) as month,
                COUNT(*) as total,
                SUM(CASE WHEN complaints.status IN ("resolved", "closed") THEN 1 ELSE 0 END) as resolved
            ')
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(function($row) { return $row->year . '_' . $row->month; });

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $key = $date->year . '_' . $date->month;
            $stat = $trendsQuery->get($key);
            
            $complaints[] = $stat->total ?? 0;
            $resolutions[] = $stat->resolved ?? 0;
        }

        return [
            'months' => $months,
            'complaints' => $complaints,
            'resolutions' => $resolutions,
        ];
    }

    /**
     * Get SLA breaches
     */
    private function getSlaBreaches()
    {
        // Optimized query: Use a JOIN instead of a correlated subquery for 132k+ records
        return Complaint::whereIn('complaints.status', ['assigned', 'in_progress'])
            ->join('sla_rules', 'complaints.category_id', '=', 'sla_rules.category_id')
            ->where('sla_rules.status', 1)
            ->whereNull('sla_rules.deleted_at')
            ->whereRaw("TIMESTAMPDIFF(HOUR, complaints.created_at, NOW()) > sla_rules.max_response_time")
            ->count();
    }

    /**
     * Get dashboard chart data
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'complaints');
        $period = $request->get('period', '30');

        switch ($type) {
            case 'complaints':
                return $this->getComplaintsChartData($period);
            case 'spares':
                return $this->getSparesChartData($period);
            case 'employees':
                return $this->getEmployeesChartData($period);
            case 'sla':
                return $this->getSlaChartData($period);
            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }

    /**
     * Get complaints chart data
     */
    private function getComplaintsChartData($period)
    {
        $data = Complaint::where('created_at', '>=', now()->subDays($period))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    /**
     * Get spares chart data
     */
    private function getSparesChartData($period)
    {
        $data = Spare::selectRaw('category, COUNT(*) as count, SUM(stock_quantity * unit_price) as total_value')
            ->groupBy('category')
            ->get();

        return response()->json($data);
    }

    /**
     * Get employees chart data
     */
    private function getEmployeesChartData($period)
    {
        $data = Employee::withCount([
            'assignedComplaints' => function ($query) use ($period) {
                $query->where('created_at', '>=', now()->subDays($period));
            }
        ])
            ->orderBy('assigned_complaints_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json($data);
    }

    /**
     * Get SLA chart data
     */
    private function getSlaChartData($period)
    {
        $timeDiff = $this->getTimeDiffInHours('complaints.created_at', 'complaints.updated_at');

        $data = Complaint::leftJoin('sla_rules', function($join) {
                $join->on('complaints.category_id', '=', 'sla_rules.category_id')
                     ->where('sla_rules.status', '=', 1);
            })
            ->where('complaints.created_at', '>=', now()->subDays($period))
            ->selectRaw("complaints.category, 
                COUNT(*) as total,
                SUM(CASE WHEN {$timeDiff} <= COALESCE(sla_rules.max_resolution_time, 999999) THEN 1 ELSE 0 END) as within_sla,
                SUM(CASE WHEN {$timeDiff} > COALESCE(sla_rules.max_resolution_time, 999999) THEN 1 ELSE 0 END) as breached")
            ->groupBy('complaints.category')
            ->get();

        return response()->json($data);
    }

    /**
     * Get real-time updates
     */
    public function getRealTimeUpdates()
    {
        $user = Auth::user();
        $lowStockQuery = Spare::lowStock();
        $this->filterSparesByLocation($lowStockQuery, $user);

        $updates = [
            'new_complaints' => Complaint::where('created_at', '>=', now()->subMinutes(5))->count(),
            'new_approvals' => SpareApprovalPerforma::where('created_at', '>=', now()->subMinutes(5))->count(),
            'low_stock_alerts' => $lowStockQuery->count(),
            'sla_breaches' => $this->getSlaBreaches(),
        ];

        return response()->json($updates);
    }
}
