<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Client;
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
            $cmesList = Cme::where('status', 'active')->orderBy('name')->get();
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

        // Get cities for filter: Check user table - if city_id is null, user can see all cities
        $cities = collect();
        if (Schema::hasTable('cities')) {
            if (!$user->city_id) {
                // User has no city_id assigned, can see all cities
                $citiesQuery = City::where('status', 'active')->orderBy('id', 'asc');
                if ($cmesId) {
                    $citiesQuery->where('cme_id', $cmesId);
                }
                $cities = $citiesQuery->get();
                // Load GE users for each city
                if ($geRole) {
                    $cities->load([
                        'users' => function ($query) use ($geRole) {
                            $query->where('role_id', $geRole->id)
                                ->where('status', 'active')
                                ->with('role');
                        }
                    ]);
                }
            } elseif ($user->city_id && $user->city) {
                // User has city_id assigned, sees only their city
                $citiesQuery = City::where('id', $user->city_id)->where('status', 'active');
                if ($cmesId) {
                    $citiesQuery->where('cme_id', $cmesId);
                }
                $cities = $citiesQuery->get();
                // Load GE users for this city
                if ($geRole) {
                    $cities->load([
                        'users' => function ($query) use ($geRole) {
                            $query->where('role_id', $geRole->id)
                                ->where('status', 'active')
                                ->with('role');
                        }
                    ]);
                }
            }
        }

        // Get sectors for filter: Check user table - if sector_id is null, user can see sectors
        $sectors = collect();
        if (Schema::hasTable('sectors')) {
            if (!$user->sector_id) {
                // User has no sector_id assigned, can see sectors
                if (!$user->city_id) {
                    // If user has no city_id, show all sectors or sectors of selected city
                    if ($cityId) {
                        $sectorsQuery = Sector::where('city_id', $cityId)->where('status', 'active')->orderBy('id', 'asc');
                        if ($cmesId) {
                            $sectorsQuery->where('cme_id', $cmesId);
                        }
                        $sectors = $sectorsQuery->get();
                    } else {
                        // User has no city_id and no sector_id - show all sectors
                        $sectorsQuery = Sector::where('status', 'active')->orderBy('id', 'asc');
                        if ($cmesId) {
                            $sectorsQuery->where('cme_id', $cmesId);
                        }
                        $sectors = $sectorsQuery->get();
                    }
                } else {
                    // If user has city_id, show sectors in their city
                    $sectorsQuery = Sector::where('city_id', $user->city_id)->where('status', 'active')->orderBy('id', 'asc');
                    if ($cmesId) {
                        $sectorsQuery->where('cme_id', $cmesId);
                    }
                    $sectors = $sectorsQuery->get();
                }
            } elseif ($user->sector_id && $user->sector) {
                // User has sector_id assigned, sees only their sector
                $sectors = Sector::where('id', $user->sector_id)->where('status', 'active')->get();
            }
        }

        // Get categories for filter
        $categories = collect();
        if (Schema::hasTable('complaint_categories')) {
            $categories = ComplaintCategory::orderBy('name')->pluck('name');
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
        $recentComplaintsQuery = Complaint::with(['client', 'assignedEmployee']);
        $this->filterComplaintsByLocation($recentComplaintsQuery, $user);
        $this->applyFilters($recentComplaintsQuery, $cityId, $sectorId, $category, $approvalStatus, $complaintStatus, $dateRange, $cmesId);
        $recentComplaints = $recentComplaintsQuery->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get approvals with location filtering and filters
        $pendingApprovalsQuery = SpareApprovalPerforma::with(['complaint.client', 'complaint.assignedEmployee', 'requestedBy', 'items.spare']);

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
            ->with(['client', 'assignedEmployee']);
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

        // Map 'new' status to 'assigned' for display (same logic as approvals page)
        $complaintsByStatus = [];
        foreach ($statusCounts as $status => $count) {
            $displayStatus = ($status === 'new') ? 'assigned' : $status;
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
        $allCategories = ComplaintCategory::orderBy('name', 'asc')->get();
        $complaintsByType = [];
        $complaintsByCategory = [];

        foreach ($allCategories as $cat) {
            $complaintsByTypeQuery = Complaint::query();
            $this->filterComplaintsByLocation($complaintsByTypeQuery, $user);
            $this->applyFilters($complaintsByTypeQuery, $cityId, $sectorId, $category, $approvalStatus, $complaintStatus, $dateRange, $cmesId);
            $count = $complaintsByTypeQuery->where('category', $cat->name)->count();

            if ($count > 0) {
                $complaintsByType[$cat->name] = $count;
            }
            $complaintsByCategory[$cat->id] = [
                'name' => $cat->name,
                'count' => $count,
            ];
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
        // 3. If user has sector_id - they shouldn't see GE Feedback Overview
        $canSeeAllData = (!$user->city_id && !$user->sector_id);
        $canSeeCityData = ($user->city_id && !$user->sector_id);

        // Check if user has permission to see GE Feedback Overview based on location filter
        // Always initialize geProgress array even if empty, so view can check permissions
        if ($canSeeAllData || $canSeeCityData) {
            // Load GE Groups from cities table (cities with names containing 'GE' or 'AGE')
            // Check if cities table exists
            if (Schema::hasTable('cities')) {
                // Load GE Groups from cities table (cities with names containing 'GE' or 'AGE')
                $geGroupsQuery = City::where(function ($q) {
                    $q->where('name', 'LIKE', '%GE%')
                        ->orWhere('name', 'LIKE', '%AGE%')
                        ->orWhere('name', 'LIKE', '%ge%')
                        ->orWhere('name', 'LIKE', '%age%');
                })
                    ->where('status', 'active') // Only active cities
                    ->orderBy('name');

                // Apply location filtering based on logged-in user's city_id and sector_id
                // If user's city_id AND sector_id are both null, show all GE Groups (no filter)
                // If user's sector_id is null but city_id has value, show only that GE Group
                if (!$user->city_id && !$user->sector_id) {
                    // User has no city_id and no sector_id - show all GE Groups (no filter)
                } elseif ($user->city_id && !$user->sector_id) {
                    // User has city_id but no sector_id - show only that GE Group
                    $geGroupsQuery->where('id', $user->city_id);
                }

                // Apply GE filter if city_id is selected in dashboard filter (priority)
                // Dashboard filter uses city_id for GE Groups
                if ($cityId) {
                    $geGroupsQuery->where('id', $cityId);
                }

                $geGroups = $geGroupsQuery->get();

                foreach ($geGroups as $geGroup) {
                    // Get total complaints for this GE Group (city) with filters applied
                    // Filter by complaint's city_id
                    $totalComplaintsQuery = Complaint::query();
                    $totalComplaintsQuery->where(function ($q) use ($geGroup) {
                        // First try to match by complaint's city_id
                        $q->where('city_id', $geGroup->id)
                            // Or match by client's city name
                            ->orWhereHas('client', function ($clientQ) use ($geGroup) {
                                $clientQ->where('city', $geGroup->name);
                            });
                    });

                    // Apply location filtering based on logged-in user's city_id and sector_id
                    // If user's city_id AND sector_id are both null, show all data (no filter)
                    // If user's sector_id is null but city_id has value, show only that city's data
                    if (!$user->city_id && !$user->sector_id) {
                        // User has no city_id and no sector_id - show all data (no filter)
                    } elseif ($user->city_id && !$user->sector_id) {
                        // User has city_id but no sector_id - show only their city's data
                        $totalComplaintsQuery->where('city_id', $user->city_id);
                    }
                    // If user has sector_id, they shouldn't see GE Feedback Overview (handled by canSeeAllData check)

                    // Apply filters (sector, category, complaint status, date range)
                    // Note: Don't apply city_id filter again as we're already filtering by GE Group's city_id
                    $this->applyFilters($totalComplaintsQuery, null, $sectorId, $category, $approvalStatus, $complaintStatus, $dateRange, $cmesId);
                    $totalComplaints = $totalComplaintsQuery->count();

                    // Get resolved complaints for this GE Group (city) with feedback and filters
                    // Include both 'resolved' and 'closed' status complaints
                    // Feedback relationship automatically excludes soft deleted records
                    // When feedback is updated, the existing record is updated (not deleted)
                    $resolvedComplaintsQuery = Complaint::query();
                    $resolvedComplaintsQuery->where(function ($q) use ($geGroup) {
                        // First try to match by complaint's city_id
                        $q->where('city_id', $geGroup->id)
                            // Or match by client's city name
                            ->orWhereHas('client', function ($clientQ) use ($geGroup) {
                                $clientQ->where('city', $geGroup->name);
                            });
                    })
                        ->whereIn('status', ['resolved', 'closed'])
                        ->with('feedback'); // Load feedback relationship

                    // Apply location filtering based on logged-in user's city_id and sector_id
                    // If user's city_id AND sector_id are both null, show all data (no filter)
                    // If user's sector_id is null but city_id has value, show only that city's data
                    if (!$user->city_id && !$user->sector_id) {
                        // User has no city_id and no sector_id - show all data (no filter)
                    } elseif ($user->city_id && !$user->sector_id) {
                        // User has city_id but no sector_id - show only their city's data
                        $resolvedComplaintsQuery->where('city_id', $user->city_id);
                    }
                    // If user has sector_id, they shouldn't see GE Feedback Overview (handled by canSeeAllData check)

                    // Apply filters (sector, category, date range) - but NOT complaint status
                    // because we always need resolved status for feedback calculation
                    // Note: Don't apply city_id filter again as we're already filtering by GE Group's city_id
                    $this->applyFilters($resolvedComplaintsQuery, null, $sectorId, $category, $approvalStatus, null, $dateRange, $cmesId);

                    $resolvedComplaints = $resolvedComplaintsQuery->get();

                    // Calculate progress based on feedback
                    // Good feedback (excellent, good) = +1 point
                    // Bad feedback (average, poor) = -0.5 points (reduces percentage)
                    // No feedback = 0 points (doesn't affect)
                    $positivePoints = 0;
                    $negativePoints = 0;
                    $resolvedWithGoodFeedback = 0;
                    $resolvedWithBadFeedback = 0;
                    $totalFeedbacks = 0; // NEW: Track total feedbacks received

                    foreach ($resolvedComplaints as $complaint) {
                        if ($complaint->feedback) {
                            $totalFeedbacks++; // NEW: Increment total feedbacks
                            $rating = $complaint->feedback->overall_rating;
                            if (in_array($rating, ['excellent', 'good'])) {
                                $positivePoints += 1;
                                $resolvedWithGoodFeedback++;
                            } elseif (in_array($rating, ['average', 'poor'])) {
                                $negativePoints += 0.5; // Reduces percentage
                                $resolvedWithBadFeedback++;
                            }
                        }
                    }

                    // Calculate progress percentage
                    // CHANGED: Use total feedbacks as denominator instead of total complaints
                    // Formula: (positive points - negative points) / total feedbacks * 100
                    // This ensures percentages are based on feedbacks received, not total complaints
                    $netPoints = $positivePoints - $negativePoints;
                    $progressPercentage = $totalFeedbacks > 0
                        ? max(0, round(($netPoints / $totalFeedbacks) * 100, 2))
                        : 0;

                    $geProgress[] = [
                        'ge' => $geGroup, // Store city object (GE Group)
                        'ge_name' => $geGroup->name, // GE Group name
                        'city' => $geGroup->name, // GE Group name (same as city name)
                        'total_complaints' => $totalComplaints,
                        'resolved_complaints' => $resolvedComplaints->count(),
                        'resolved_with_good_feedback' => $resolvedWithGoodFeedback,
                        'resolved_with_bad_feedback' => $resolvedWithBadFeedback,
                        'total_feedbacks' => $totalFeedbacks, // NEW: Add total feedbacks to output
                        'progress_percentage' => $progressPercentage,
                    ];
                }

                // Sort GE Progress: GEs with feedback first, then those without
                usort($geProgress, function ($a, $b) {
                    // First, sort by whether they have feedback (descending: with feedback first)
                    if ($a['total_feedbacks'] > 0 && $b['total_feedbacks'] == 0) {
                        return -1; // $a has feedback, comes first
                    }
                    if ($a['total_feedbacks'] == 0 && $b['total_feedbacks'] > 0) {
                        return 1; // $b has feedback, comes first
                    }
                    // If both have feedback or both don't, maintain original order (stable sort)
                    return 0;
                });

            } else {
                // Cities table doesn't exist, geProgress will remain empty
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
        // Filter by city - use direct city_id field on complaints table
        if ($cityId) {
            $query->where('city_id', $cityId);
        }

        // Filter by sector - use direct sector_id field on complaints table
        if ($sectorId) {
            $query->where('sector_id', $sectorId);
        }

        // Filter by category
        if ($category) {
            $query->where('category', $category);
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
            if ($complaintStatus === 'work_performa') {
                // Match complaints with work_performa status OR in_progress with work_performa performa_type
                $query->where(function ($q) {
                    $q->where('status', 'work_performa')
                        ->orWhere(function ($subQ) {
                            $subQ->where('status', 'in_progress')
                                ->whereHas('spareApprovals', function ($approvalQ) {
                                    $approvalQ->where('performa_type', 'work_performa');
                                });
                        });
                });
            } elseif ($complaintStatus === 'maint_performa') {
                // Match complaints with maint_performa status OR in_progress with maint_performa performa_type
                $query->where(function ($q) {
                    $q->where('status', 'maint_performa')
                        ->orWhere(function ($subQ) {
                            $subQ->where('status', 'in_progress')
                                ->whereHas('spareApprovals', function ($approvalQ) {
                                    $approvalQ->where('performa_type', 'maint_performa');
                                });
                        });
                });
            } elseif ($complaintStatus === 'work_priced_performa') {
                // waiting_for_authority removed - only check direct status match
                $query->where('status', 'work_priced_performa');
            } elseif ($complaintStatus === 'maint_priced_performa') {
                // waiting_for_authority removed - only check direct status match
                $query->where('status', 'maint_priced_performa');
            } elseif ($complaintStatus === 'product_na') {
                // Match product_na status OR in_progress with product_na performa_type
                $query->where(function ($q) {
                    $q->where('status', 'product_na')
                        ->orWhere(function ($subQ) {
                            $subQ->where('status', 'in_progress')
                                ->whereHas('spareApprovals', function ($approvalQ) {
                                    $approvalQ->where('performa_type', 'product_na');
                                });
                        });
                });
            } else {
                // For other statuses, filter by actual status
                $query->where('status', $complaintStatus);
            }
        }

        // Filter by date range
        if ($dateRange) {
            $now = now();
            switch ($dateRange) {
                case 'yesterday':
                    $query->whereDate('created_at', $now->copy()->subDay()->toDateString());
                    break;
                case 'today':
                    $query->whereDate('created_at', $now->toDateString());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
                    break;
                case 'last_week':
                    $query->whereBetween('created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year);
                    break;
                case 'last_month':
                    $query->whereMonth('created_at', $now->copy()->subMonth()->month)
                        ->whereYear('created_at', $now->copy()->subMonth()->year);
                    break;
                case 'last_6_months':
                    $query->where('created_at', '>=', $now->copy()->subMonths(6)->startOfDay());
                    break;
            }
        }
        // Filter by CMES (cmes_id) - restrict by city or sector belonging to selected CMES
        if ($cmesId) {
            try {
                $cmeCityIds = City::where('cme_id', $cmesId)->pluck('id')->toArray();
                $cmeSectorIds = Sector::where('cme_id', $cmesId)->pluck('id')->toArray();

                $query->where(function ($q) use ($cmeCityIds, $cmeSectorIds) {
                    if (!empty($cmeCityIds)) {
                        $q->whereIn('city_id', $cmeCityIds);
                    }
                    if (!empty($cmeSectorIds)) {
                        $q->orWhereIn('sector_id', $cmeSectorIds);
                    }
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
                $q->where('category', $category);
            });
        }
        if ($cityId) {
            $employeesQuery->where('city_id', $cityId);
        }
        if ($sectorId) {
            $employeesQuery->where('sector_id', $sectorId);
        }

        $clientsQuery = Client::query();
        $this->filterClientsByLocation($clientsQuery, $user);

        $sparesQuery = Spare::query();
        $this->filterSparesByLocation($sparesQuery, $user);

        return [
            // Complaint statistics with location filtering
            'total_complaints' => (clone $complaintsQuery)->count(),
            'pending_complaints' => (clone $complaintsQuery)->whereIn('status', ['assigned', 'in_progress'])->count(),
            'in_progress_complaints' => (clone $complaintsQuery)->where('status', 'in_progress')->count(),
            'addressed_complaints' => (clone $complaintsQuery)->where('status', 'resolved')->count(),
            'overdue_complaints' => (clone $complaintsQuery)->overdue()->count(),
            'complaints_today' => (clone $complaintsQuery)->whereDate('created_at', $today)->count(),
            'complaints_this_month' => (clone $complaintsQuery)->where('created_at', '>=', $thisMonth)->count(),
            'complaints_last_month' => (clone $complaintsQuery)->whereBetween('created_at', [$lastMonth, $thisMonth])->count(),

            // Complaint status statistics - include both direct status and performa_type from approvals
            'work_performa' => (clone $complaintsQuery)->where(function ($q) {
                $q->where('status', 'work_performa')
                    ->orWhere(function ($subQ) {
                        $subQ->where('status', 'in_progress')
                            ->whereHas('spareApprovals', function ($approvalQ) {
                                $approvalQ->where('performa_type', 'work_performa');
                            });
                    });
            })->count(),
            'maint_performa' => (clone $complaintsQuery)->where(function ($q) {
                $q->where('status', 'maint_performa')
                    ->orWhere(function ($subQ) {
                        $subQ->where('status', 'in_progress')
                            ->whereHas('spareApprovals', function ($approvalQ) {
                                $approvalQ->where('performa_type', 'maint_performa');
                            });
                    });
            })->count(),
            'work_priced_performa' => (clone $complaintsQuery)->where('status', 'work_priced_performa')->count(),
            'maint_priced_performa' => (clone $complaintsQuery)->where('status', 'maint_priced_performa')->count(),
            'un_authorized' => (clone $complaintsQuery)->where('status', 'un_authorized')->count(),
            'product_na' => (clone $complaintsQuery)->where(function ($q) {
                $q->where('status', 'product_na')
                    ->orWhere(function ($subQ) {
                        $subQ->where('status', 'in_progress')
                            ->whereHas('spareApprovals', function ($approvalQ) {
                                $approvalQ->where('performa_type', 'product_na')
                                    ->whereNull('deleted_at'); // Exclude soft deleted approvals
                            });
                    });
            })->count(),
            'pertains_to_ge_const_isld' => (clone $complaintsQuery)->where('status', 'pertains_to_ge_const_isld')->count(),
            'barak_damages' => (clone $complaintsQuery)->where('status', 'barak_damages')->count(),

            // User statistics (users are not location-based)
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_employees' => (clone $employeesQuery)->count(),
            'total_clients' => (clone $clientsQuery)->count(),

            // Spare parts statistics with location filtering
            'total_spares' => (clone $sparesQuery)->count(),
            'low_stock_items' => (clone $sparesQuery)->lowStock()->count(),
            'out_of_stock_items' => (clone $sparesQuery)->outOfStock()->count(),
            'total_spare_value' => (clone $sparesQuery)->sum(DB::raw('stock_quantity * unit_price')),

            // SLA statistics
            'active_sla_rules' => SlaRule::where('status', 'active')->count(),
            'sla_breaches' => $this->getSlaBreaches(),
        ];
    }



    /**
     * Get SLA performance metrics
     */
    private function getSlaPerformance()
    {
        $totalComplaints = Complaint::where('created_at', '>=', now()->subDays(30))->count();
        $withinSla = 0;
        $breached = 0;

        if ($totalComplaints > 0) {
            $timeDiff = $this->getTimeDiffInHours('created_at', 'updated_at');

            $withinSla = Complaint::where('created_at', '>=', now()->subDays(30))
                ->whereIn('status', ['resolved', 'closed'])
                ->whereRaw("{$timeDiff} <= COALESCE((SELECT MIN(max_resolution_time) FROM sla_rules WHERE complaint_type = complaints.category AND status = 'active'), 999999)")
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
        $months = [];
        $complaints = [];
        $resolutions = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');

            // Get complaints for this month with location and filters
            $complaintsQuery = Complaint::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);
            $this->filterComplaintsByLocation($complaintsQuery, $user);
            $this->applyFilters($complaintsQuery, $cityId, $sectorId, $category, $approvalStatus, $complaintStatus, $dateRange, $cmesId);
            $complaints[] = $complaintsQuery->count();

            // Get resolutions for this month with location and filters
            $resolutionsQuery = Complaint::whereYear('updated_at', $date->year)
                ->whereMonth('updated_at', $date->month)
                ->where('status', 'resolved');
            $this->filterComplaintsByLocation($resolutionsQuery, $user);
            $this->applyFilters($resolutionsQuery, $cityId, $sectorId, $category, $approvalStatus, $complaintStatus, $dateRange, $cmesId);
            $resolutions[] = $resolutionsQuery->count();
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
        $timeDiff = $this->getTimeDiffFromNow('created_at');

        return Complaint::whereIn('status', ['assigned', 'in_progress'])
            ->whereRaw("{$timeDiff} > COALESCE((SELECT MIN(max_response_time) FROM sla_rules WHERE complaint_type = complaints.category AND status = 'active'), 999999)")
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
        $timeDiff = $this->getTimeDiffInHours('created_at', 'updated_at');

        $data = Complaint::where('created_at', '>=', now()->subDays($period))
            ->selectRaw("category, 
                COUNT(*) as total,
                SUM(CASE WHEN {$timeDiff} <= COALESCE((SELECT MIN(max_resolution_time) FROM sla_rules WHERE complaint_type = complaints.category AND status = 'active'), 999999) THEN 1 ELSE 0 END) as within_sla,
                SUM(CASE WHEN {$timeDiff} > COALESCE((SELECT MIN(max_resolution_time) FROM sla_rules WHERE complaint_type = complaints.category AND status = 'active'), 999999) THEN 1 ELSE 0 END) as breached")
            ->groupBy('category')
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
