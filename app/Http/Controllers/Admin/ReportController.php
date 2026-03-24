<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\Spare;
use App\Models\SpareApprovalPerforma;
use App\Models\ReportsSummary;
use App\Traits\LocationFilterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    use LocationFilterTrait;
    public function __construct()
    {
        // Middleware is applied in routes
    }

    /**
     * Display the reports dashboard
     */
    public function index()
    {
        // Get real-time statistics
        $stats = $this->getRealTimeStats();
        $recentActivity = $this->getRecentActivity();

        // Get real data for JavaScript functions
        $realData = $this->getRealDataForJS();

        return view('admin.reports.index', compact('stats', 'recentActivity', 'realData'));
    }

    /**
     * Get real data for JavaScript functions
     */
    private function getRealDataForJS()
    {
        $user = Auth::user();
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();

        // Complaints with location filtering
        $complaintsQuery = \App\Models\Complaint::query();
        $this->filterComplaintsByLocation($complaintsQuery, $user);

        // Employees with location filtering
        $employeesQuery = \App\Models\Employee::query()->where('status', 1);
        $this->filterEmployeesByLocation($employeesQuery, $user);

        // Spares with location filtering
        $sparesQuery = \App\Models\Spare::query();
        $this->filterSparesByLocation($sparesQuery, $user);

        // Approvals with location filtering
        $approvalsQuery = \App\Models\SpareApprovalPerforma::query();
        $approvedApprovalsQuery = \App\Models\SpareApprovalPerforma::query()->where('status', 'approved');

        if ($user && !$this->canViewAllData($user)) {
            $filterApprovals = function ($query) use ($user) {
                $query->whereHas('complaint', function ($q) use ($user) {
                    $q->where(function ($sub) use ($user) {
                        // Match via house
                        $sub->whereHas('house', function ($hq) use ($user) {
                            if (!empty($user->city_ids)) {
                                $hq->whereIn('city_id', $user->city_ids);
                            }
                            if (!empty($user->sector_ids)) {
                                $hq->whereIn('sector_id', $user->sector_ids);
                            }
                        })
                            // OR Match via direct complaint columns (for house-less complaints)
                            ->orWhere(function ($cq) use ($user) {
                                $cq->whereNull('house_id');
                                if (!empty($user->city_ids)) {
                                    $cq->whereIn('city_id', $user->city_ids);
                                }
                                if (!empty($user->sector_ids)) {
                                    $cq->whereIn('sector_id', $user->sector_ids);
                                }
                            });
                    });
                });
            };
            $filterApprovals($approvalsQuery);
            $filterApprovals($approvedApprovalsQuery);
        }

        // Get aggregated complaint stats in one query
        $complaintStats = (clone $complaintsQuery)->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN complaints.status = "resolved" AND complaints.updated_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as resolved,
            SUM(CASE WHEN complaints.status != "resolved" THEN 1 ELSE 0 END) as pending
        ', [$startOfMonth, $now])->first();

        // Get aggregated spare stats in one query
        $spareStats = (clone $sparesQuery)->selectRaw('
            COUNT(*) as total_items,
            SUM(CASE WHEN stock_quantity <= threshold_level THEN 1 ELSE 0 END) as low_stock,
            SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
            SUM(stock_quantity * unit_price) as total_value
        ')->first();

        return [
            'complaints' => [
                'total' => $complaintStats->total ?? 0,
                'resolved' => $complaintStats->resolved ?? 0,
                'pending' => $complaintStats->pending ?? 0,
                'avg_resolution_time' => $this->getAverageResolutionTime($user)
            ],
            'employees' => [
                'total' => (clone $employeesQuery)->count(),
                'active' => (clone $employeesQuery)->count(),
                'avg_performance' => $this->getAverageEmployeePerformance($user)
            ],
            'spares' => [
                'total_items' => $spareStats->total_items ?? 0,
                'low_stock' => $spareStats->low_stock ?? 0,
                'out_of_stock' => $spareStats->out_of_stock ?? 0,
                'total_value' => $spareStats->total_value ?? 0
            ],
            'financial' => [
                'total_costs' => $this->getTotalSpareCosts($user),
                'approvals' => $approvalsQuery->count(),
                'approved' => $approvedApprovalsQuery->count(),
                'approval_rate' => $this->getApprovalRate()
            ]
        ];
    }

    /**
     * Get average resolution time in hours
     */
    private function getAverageResolutionTime($user = null)
    {
        $query = \App\Models\Complaint::query()->where('complaints.status', 'resolved')
            ->whereNotNull('complaints.updated_at')
            ->whereNotNull('complaints.created_at');

        $this->filterComplaintsByLocation($query, $user);

        $avgHours = $query->selectRaw('AVG(TIMESTAMPDIFF(HOUR, complaints.created_at, complaints.updated_at)) as avg_hours')->value('avg_hours');

        return round($avgHours ?? 0, 1);
    }

    /**
     * Get average employee performance
     */
    private function getAverageEmployeePerformance($user = null)
    {
        $employeesQuery = \App\Models\Employee::query()->where('status', 1);
        $this->filterEmployeesByLocation($employeesQuery, $user);

        $employees = $employeesQuery->withCount([
            'assignedComplaints as total_count' => function ($q) use ($user) {
                $this->filterComplaintsByLocation($q, $user);
            },
            'assignedComplaints as resolved_count' => function ($q) use ($user) {
                $q->where('status', 'resolved');
                $this->filterComplaintsByLocation($q, $user);
            }
        ])->get();

        if ($employees->isEmpty()) {
            return 0;
        }

        $totalPerformance = 0;
        $countWithComplaints = 0;

        foreach ($employees as $employee) {
            if ($employee->total_count > 0) {
                $totalPerformance += ($employee->resolved_count / $employee->total_count) * 100;
                $countWithComplaints++;
            }
        }

        if ($countWithComplaints === 0) {
            return 0;
        }

        return round($totalPerformance / $countWithComplaints, 1);
    }

    /**
     * Get total spare costs
     */
    private function getTotalSpareCosts($user = null)
    {
        // Prefer complaint_spares if exists; otherwise fallback to approved items
        if (Schema::hasTable('complaint_spares')) {
            $query = DB::table('complaint_spares')
                ->join('spares', 'complaint_spares.spare_id', '=', 'spares.id')
                ->join('complaints', 'complaint_spares.complaint_id', '=', 'complaints.id');

            // Apply location filtering
            if ($user && !$this->canViewAllData($user)) {
                $query->leftJoin('houses', 'complaints.house_id', '=', 'houses.id');
                $query->where(function ($q) use ($user) {
                    $q->where(function ($sub) use ($user) {
                        $sub->whereNotNull('complaints.house_id');
                        if (!empty($user->city_ids)) {
                            $sub->whereIn('houses.city_id', $user->city_ids);
                        }
                        if (!empty($user->sector_ids)) {
                            $sub->whereIn('houses.sector_id', $user->sector_ids);
                        }
                    })->orWhere(function ($sub) use ($user) {
                        $sub->whereNull('complaints.house_id');
                        if (!empty($user->city_ids)) {
                            $sub->whereIn('complaints.city_id', $user->city_ids);
                        }
                        if (!empty($user->sector_ids)) {
                            $sub->whereIn('complaints.sector_id', $user->sector_ids);
                        }
                    });
                });
            }

            return $query->sum(DB::raw('complaint_spares.quantity * spares.unit_price'));
        }

        // Fallback: use approved quantities from spare_approval_items joined to spares and approved performa
        if (Schema::hasTable('spare_approval_items') && Schema::hasTable('spare_approval_performa')) {
            $query = DB::table('spare_approval_items')
                ->join('spares', 'spare_approval_items.spare_id', '=', 'spares.id')
                ->join('spare_approval_performa', 'spare_approval_items.performa_id', '=', 'spare_approval_performa.id')
                ->where('spare_approval_performa.status', 'approved');

            // Apply location filtering if approval is linked to complaint
            if ($user && !$this->canViewAllData($user) && Schema::hasColumn('spare_approval_performa', 'complaint_id')) {
                $query->join('complaints', 'spare_approval_performa.complaint_id', '=', 'complaints.id')
                    ->leftJoin('houses', 'complaints.house_id', '=', 'houses.id');
                $query->where(function ($q) use ($user) {
                    $q->where(function ($sub) use ($user) {
                        $sub->whereNotNull('complaints.house_id');
                        if (!empty($user->city_ids)) {
                            $sub->whereIn('houses.city_id', $user->city_ids);
                        }
                        if (!empty($user->sector_ids)) {
                            $sub->whereIn('houses.sector_id', $user->sector_ids);
                        }
                    })->orWhere(function ($sub) use ($user) {
                        $sub->whereNull('complaints.house_id');
                        if (!empty($user->city_ids)) {
                            $sub->whereIn('complaints.city_id', $user->city_ids);
                        }
                        if (!empty($user->sector_ids)) {
                            $sub->whereIn('complaints.sector_id', $user->sector_ids);
                        }
                    });
                });
            }

            return $query->sum(DB::raw('COALESCE(spare_approval_items.quantity_approved, 0) * spares.unit_price'));
        }

        return 0;
    }

    /**
     * Get approval rate percentage
     */
    private function getApprovalRate()
    {
        $totalApprovals = \App\Models\SpareApprovalPerforma::count();
        if ($totalApprovals === 0)
            return 0;

        $approvedApprovals = \App\Models\SpareApprovalPerforma::query()->where('status', 'approved')->count();
        return round(($approvedApprovals / $totalApprovals) * 100, 1);
    }

    /**
     * Get real-time statistics for dashboard
     */
    private function getRealTimeStats()
    {
        $user = Auth::user();
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();

        // Apply location filtering
        $complaintsQuery = Complaint::query();
        $this->filterComplaintsByLocation($complaintsQuery, $user);

        $employeesQuery = Employee::query()->where('status', 1);
        $this->filterEmployeesByLocation($employeesQuery, $user);

        // Spares with location filtering
        $sparesQuery = Spare::query();
        $this->filterSparesByLocation($sparesQuery, $user);

        // Approvals with location filtering
        $approvalsQuery = SpareApprovalPerforma::query();
        $pendingApprovalsQuery = SpareApprovalPerforma::query()->where('status', 'pending');

        if ($user && !$this->canViewAllData($user)) {
            $filterApprovals = function ($query) use ($user) {
                $query->whereHas('complaint', function ($q) use ($user) {
                    $q->where(function ($sub) use ($user) {
                        // Match via house
                        $sub->whereHas('house', function ($hq) use ($user) {
                            if (!empty($user->city_ids)) {
                                $hq->whereIn('city_id', $user->city_ids);
                            }
                            if (!empty($user->sector_ids)) {
                                $hq->whereIn('sector_id', $user->sector_ids);
                            }
                        })
                            // OR Match via direct complaint columns (for house-less complaints)
                            ->orWhere(function ($cq) use ($user) {
                                $cq->whereNull('house_id');
                                if (!empty($user->city_ids)) {
                                    $cq->whereIn('city_id', $user->city_ids);
                                }
                                if (!empty($user->sector_ids)) {
                                    $cq->whereIn('sector_id', $user->sector_ids);
                                }
                            });
                    });
                });
            };
            $filterApprovals($approvalsQuery);
            $filterApprovals($pendingApprovalsQuery);
        }

        // Consolidated stats in fewer queries
        $complaintStats = (clone $complaintsQuery)->selectRaw('
            SUM(CASE WHEN complaints.created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as total_this_month,
            SUM(CASE WHEN complaints.status = "resolved" AND complaints.updated_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as resolved_this_month
        ', [$startOfMonth, $now, $startOfMonth, $now])->first();

        $spareStats = (clone $sparesQuery)->selectRaw('
            COUNT(*) as total_spares,
            SUM(CASE WHEN stock_quantity <= threshold_level THEN 1 ELSE 0 END) as low_stock,
            SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
            SUM(stock_quantity * unit_price) as total_value
        ')->first();

        return [
            'total_complaints_this_month' => $complaintStats->total_this_month ?? 0,
            'resolved_this_month' => $complaintStats->resolved_this_month ?? 0,
            'active_employees' => (clone $employeesQuery)->count(),
            'total_spares' => $spareStats->total_spares ?? 0,
            'low_stock_items' => $spareStats->low_stock ?? 0,
            'out_of_stock_items' => $spareStats->out_of_stock ?? 0,
            'total_approvals' => $approvalsQuery->count(),
            'pending_approvals' => $pendingApprovalsQuery->count(),
            'total_spare_value' => $spareStats->total_value ?? 0,
            'employee_performance' => $this->getAverageEmployeePerformance($user)
        ];
    }

    /**
     * Calculate SLA compliance percentage
     */
    private function calculateSlaCompliance($user = null)
    {
        $query = Complaint::query();
        $this->filterComplaintsByLocation($query, $user);

        $stats = (clone $query)->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN complaints.status = "resolved" THEN 1 ELSE 0 END) as resolved_count
        ')->first();

        if (!$stats || $stats->total == 0) {
            return 100;
        }

        // Get specifically compliant resolved complaints
        $compliantCount = (clone $query)->where('complaints.status', 'resolved')
            ->whereHas('category.slaRule', function ($q) {
                $q->whereRaw('TIMESTAMPDIFF(HOUR, complaints.created_at, complaints.updated_at) <= sla_rules.max_resolution_time')
                    ->where('sla_rules.status', 1);
            })->count();

        return round(($compliantCount / $stats->total) * 100, 1);
    }

    /**
     * Get recent activity for dashboard
     */
    private function getRecentActivity()
    {
        $user = Auth::user();
        $activities = collect();

        try {
            // Recent complaints with location filtering
            $recentComplaintsQuery = Complaint::with(['assignedEmployee']);
            $this->filterComplaintsByLocation($recentComplaintsQuery, $user);
            $recentComplaints = $recentComplaintsQuery->latest()
                ->limit(3)
                ->get();

            foreach ($recentComplaints as $complaint) {
                $time = $complaint->created_at ? $complaint->created_at->diffForHumans() : 'Unknown';
                $activities->push([
                    'type' => 'complaint',
                    'title' => 'New complaint submitted',
                    'description' => $complaint->title,
                    'time' => $time,
                    'badge' => ucfirst($complaint->status),
                    'badge_class' => $this->getStatusBadgeClass($complaint->status)
                ]);
            }

            // Recent approvals with location filtering
            $recentApprovalsQuery = SpareApprovalPerforma::with(['requestedBy']);

            // Apply location filtering to approvals
            if ($user && !$this->canViewAllData($user)) {
                $recentApprovalsQuery->whereHas('complaint', function ($q) use ($user) {
                    $q->where(function ($sub) use ($user) {
                        // Match via house
                        $sub->whereHas('house', function ($hq) use ($user) {
                            if (!empty($user->city_ids)) {
                                $hq->whereIn('city_id', $user->city_ids);
                            }
                            if (!empty($user->sector_ids)) {
                                $hq->whereIn('sector_id', $user->sector_ids);
                            }
                        })
                            // OR Match via direct complaint columns (for house-less complaints)
                            ->orWhere(function ($cq) use ($user) {
                                $cq->whereNull('house_id');
                                if (!empty($user->city_ids)) {
                                    $cq->whereIn('city_id', $user->city_ids);
                                }
                                if (!empty($user->sector_ids)) {
                                    $cq->whereIn('sector_id', $user->sector_ids);
                                }
                            });
                    });
                });
            }

            $recentApprovals = $recentApprovalsQuery->latest()
                ->limit(2)
                ->get();

            foreach ($recentApprovals as $approval) {
                $time = $approval->created_at ? $approval->created_at->diffForHumans() : 'Unknown';
                $activities->push([
                    'type' => 'approval',
                    'title' => 'Spare part approval',
                    'description' => 'Requested by ' . ($approval->requestedBy->name ?? 'Unknown'),
                    'time' => $time,
                    'badge' => ucfirst($approval->status),
                    'badge_class' => $this->getApprovalBadgeClass($approval->status)
                ]);
            }

            // Recent spare part activities
            $recentSpares = Spare::query()->where('stock_quantity', '<=', \DB::raw('threshold_level'))
                ->latest('updated_at')
                ->limit(1)
                ->get();

            foreach ($recentSpares as $spare) {
                $time = $spare->updated_at ? $spare->updated_at->diffForHumans() : 'Unknown';
                $activities->push([
                    'type' => 'spare',
                    'title' => 'Low stock alert',
                    'description' => $spare->item_name . ' is running low',
                    'time' => $time,
                    'badge' => 'Low Stock',
                    'badge_class' => 'warning'
                ]);
            }

        } catch (\Exception $e) {
            // If there's an error, return empty activities
            \Log::error('Error fetching recent activity: ' . $e->getMessage());
        }

        return $activities->sortByDesc('time')->take(5)->values();
    }

    /**
     * Get badge class for complaint status
     */
    private function getStatusBadgeClass($status)
    {
        switch (strtolower($status)) {
            case 'resolved':
                return 'success';
            case 'in_progress':
                return 'info';
            case 'pending':
                return 'warning';
            case 'closed':
                return 'secondary';
            default:
                return 'primary';
        }
    }

    /**
     * Get badge class for approval status
     */
    private function getApprovalBadgeClass($status)
    {
        switch (strtolower($status)) {
            case 'approved':
                return 'success';
            case 'pending':
                return 'warning';
            case 'rejected':
                return 'danger';
            default:
                return 'primary';
        }
    }

    /**
     * Generate SUB-DIVISION WISE PERFORMANCE report
     */
    public function complaints(Request $request)
    {
        // Set default values if not provided
        $dateFrom = $request->date_from ?? now()->subMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? now()->format('Y-m-d');
        $format = $request->input('format') ?? 'html';

        // Validate only if parameters are provided
        if ($request->has('date_from') || $request->has('date_to')) {
            $request->validate([
                'date_from' => 'date',
                'date_to' => 'date|after_or_equal:date_from',
                'format' => 'nullable|in:html,pdf,excel',
            ]);
        }

        // Ensure dates are properly formatted and include time for full day coverage
        $dateFromStart = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $dateToEnd = \Carbon\Carbon::parse($dateTo)->endOfDay();

        // Get user for location filtering
        $user = Auth::user();

        // Get actual categories from ComplaintCategory table - this is the source of truth
        $actualCategories = \App\Models\ComplaintCategory::query()->where('status', 1)->orderBy('name')
            ->pluck('name')
            ->toArray();

        // Use only categories from ComplaintCategory table (not from complaints table)
        // This ensures we only show categories that are defined in the categories table
        // and avoids showing old/renamed category names from existing complaints
        $allCategories = $actualCategories;
        sort($allCategories);

        // Map categories to report format
        $categories = [];
        foreach ($allCategories as $cat) {
            // Use the category name as key
            $key = strtolower(str_replace([' ', '&', '-', '(', ')'], ['_', '', '_', '', ''], $cat));
            $categories[$key] = $cat;
        }

        // If still no categories, use default structure for demonstration
        if (empty($categories)) {
            $categories = [
                'electric' => 'Electric',
                'plumbing' => 'Plumbing',
                'general' => 'General',
                'kitchen' => 'Kitchen',
                'barrak_damages' => 'Barrak Damages',
            ];
        }

        // Row categories/statuses - based on complaint statuses from Complaint model
        $rows = \App\Models\Complaint::getStatuses();

        // Remove closed status from report (but keep new for unassigned)
        unset($rows['closed']);

        // Add 'unassigned' manually to represent the 'new' status mapping, then remove 'new' from rows to avoid duplicate display
        if (isset($rows['new'])) {
            $rows['unassigned'] = 'Unassigned';
            unset($rows['new']);
        } else {
            // Fallback in case 'new' isn't explicitly returned by getStatuses()
            $rows['unassigned'] = 'Unassigned';
        }

        // Add performa-related statuses grouped by type (omitting work and maintenance so they go to work_performa and maint_performa)
        $rows['work_priced_performa'] = 'Work Performa Priced';
        $rows['maint_priced_performa'] = 'Maintenance Performa Priced';
        $rows['product_na'] = 'Product N/A';
        $rows['un_authorized'] = 'Un-Authorized';
        $rows['pertains_to_ge_const_isld'] = 'Pertains to GE(N) Const Isld';

        // Get user for location filtering and CMES filter
        $user = Auth::user();
        $cmesId = $request->cmes_id;

        // Base query for all complaints in date range
        $baseQuery = Complaint::query()->whereBetween('complaints.created_at', [$dateFromStart, $dateToEnd]);

        // Apply location-based filtering
        $this->filterComplaintsByLocation($baseQuery, $user);

        // Initialize report structure variables
        $reportData = [];
        $categoryTotals = [];
        $rowTotals = [];
        $grandTotal = 0;
        foreach ($rows as $rowKey => $rowName) {
            $rowTotals[$rowKey] = 0;
        }

        // Apply CMES filter if provided
        if ($cmesId) {
            $baseQuery->where(function ($q) use ($cmesId) {
                $q->whereHas('house', function ($hq) use ($cmesId) {
                    $hq->whereHas('city', function ($ccq) use ($cmesId) {
                        $ccq->where('cme_id', $cmesId); })
                        ->orWhereHas('sector', function ($ssq) use ($cmesId) {
                            $ssq->where('cme_id', $cmesId); });
                })->orWhere(function ($cq) use ($cmesId) {
                    $cq->whereNull('house_id')
                        ->where(function ($lq) use ($cmesId) {
                            $lq->whereHas('city', function ($ccq) use ($cmesId) {
                                $ccq->where('cme_id', $cmesId); })
                                ->orWhereHas('sector', function ($ssq) use ($cmesId) {
                                    $ssq->where('cme_id', $cmesId); });
                        });
                });
            });
        }

        // OPTIMIZED: Fetch all counts in ONE grouped query
        $allStats = (clone $baseQuery)
            ->selectRaw('category_id, status, COUNT(*) as count')
            ->groupBy('category_id', 'status')
            ->get();

        // Fetch performa-type statistics from approvals joined with complaints
        $performaStats = (clone $baseQuery)
            ->join('spare_approval_performa', 'complaints.id', '=', 'spare_approval_performa.complaint_id')
            ->where('complaints.status', 'in_progress')
            ->where('spare_approval_performa.status', '!=', 'rejected')
            ->selectRaw('complaints.category_id, spare_approval_performa.performa_type, COUNT(*) as count')
            ->groupBy('complaints.category_id', 'spare_approval_performa.performa_type')
            ->get();

        // Pre-index records for fast mapping
        $indexedStats = [];
        foreach ($allStats as $stat) {
            $indexedStats[$stat->category_id][$stat->status] = $stat->count;
        }

        $indexedPerformas = [];
        foreach ($performaStats as $pStat) {
            $indexedPerformas[$pStat->category_id][$pStat->performa_type] = $pStat->count;
        }

        // Map database categories to their IDs for lookups
        $categoryNameToId = \App\Models\ComplaintCategory::query()->where('status', 1)
            ->pluck('id', 'name')->toArray();

        // Process report data structure
        foreach ($categories as $catKey => $catName) {
            $catId = $categoryNameToId[$catName] ?? null;
            if (!$catId) {
                $categoryTotals[$catKey] = 0;
                foreach ($rows as $rowKey => $rowName) {
                    $reportData[$rowKey]['categories'][$catKey] = ['count' => 0, 'percentage' => 0];
                }
                continue;
            }

            // Category Total
            $catTotal = collect($indexedStats[$catId] ?? [])->sum();
            $categoryTotals[$catKey] = $catTotal;
            $grandTotal += $catTotal;

            foreach ($rows as $rowKey => $rowName) {
                if (!isset($reportData[$rowKey])) {
                    $reportData[$rowKey] = ['name' => $rowName, 'categories' => []];
                    $rowTotals[$rowKey] = 0;
                }

                $count = 0;
                if ($rowKey === 'unassigned') {
                    $count = $indexedStats[$catId]['new'] ?? 0;
                } elseif ($rowKey === 'assigned') {
                    $count = $indexedStats[$catId]['assigned'] ?? 0;
                } elseif ($rowKey === 'in_progress') {
                    $totalInProgress = $indexedStats[$catId]['in_progress'] ?? 0;
                    $hasPerformaCount = collect($indexedPerformas[$catId] ?? [])
                        ->only(['work_performa', 'maint_performa', 'product_na'])
                        ->sum();
                    $count = max(0, $totalInProgress - $hasPerformaCount);
                } elseif ($rowKey === 'resolved') {
                    $count = $indexedStats[$catId]['resolved'] ?? 0;
                } elseif ($rowKey === 'work_performa') {
                    $count = ($indexedStats[$catId]['work_performa'] ?? 0) + ($indexedPerformas[$catId]['work_performa'] ?? 0);
                } elseif ($rowKey === 'maint_performa') {
                    $count = ($indexedStats[$catId]['maint_performa'] ?? 0) + ($indexedPerformas[$catId]['maint_performa'] ?? 0);
                } elseif ($rowKey === 'work_priced_performa') {
                    $count = $indexedStats[$catId]['work_priced_performa'] ?? 0;
                } elseif ($rowKey === 'maint_priced_performa') {
                    $count = $indexedStats[$catId]['maint_priced_performa'] ?? 0;
                } elseif ($rowKey === 'product_na') {
                    $count = ($indexedStats[$catId]['product_na'] ?? 0) + ($indexedPerformas[$catId]['product_na'] ?? 0);
                } elseif ($rowKey === 'un_authorized') {
                    $count = $indexedStats[$catId]['un_authorized'] ?? 0;
                } elseif ($rowKey === 'pertains_to_ge_const_isld') {
                    $count = $indexedStats[$catId]['pertains_to_ge_const_isld'] ?? 0;
                } elseif ($rowKey === 'barak_damages') {
                    $count = $indexedStats[$catId]['barak_damages'] ?? 0;
                }

                $percentage = $catTotal > 0 ? round(($count / $catTotal) * 100, 1) : 0;
                $reportData[$rowKey]['categories'][$catKey] = ['count' => $count, 'percentage' => $percentage];
                $rowTotals[$rowKey] += $count;
            }
        }

        // Identify E&M NRC related categories - the 3 specific columns
        // E&M NRC (Electric), E&M NRC (Gas), E&M NRC (Water Supply)
        $emNrcCategoryKeys = [];
        $emNrcTotalKey = 'em_nrc_total';

        foreach ($categories as $catKey => $catName) {
            // Check if category contains "E&M NRC" but not "Total"
            if (stripos($catName, 'E&M NRC') !== false && stripos($catName, 'Total') === false) {
                $emNrcCategoryKeys[] = $catKey;
            }
        }

        // Calculate E&M NRC Total for each row
        $emNrcTotal = [];
        foreach ($rows as $rowKey => $rowName) {
            $emNrcTotal[$rowKey] = 0;
            foreach ($emNrcCategoryKeys as $emKey) {
                if (isset($reportData[$rowKey]['categories'][$emKey])) {
                    $emNrcTotal[$rowKey] += $reportData[$rowKey]['categories'][$emKey]['count'];
                }
            }
        }

        // Calculate E&M NRC Total for Total row
        $emNrcTotalForTotalRow = 0;
        foreach ($emNrcCategoryKeys as $emKey) {
            $emNrcTotalForTotalRow += $categoryTotals[$emKey] ?? 0;
        }

        // Reorganize categories: Place E&M NRC Total after individual E&M NRC columns
        $reorganizedCategories = [];
        $emNrcProcessed = [];

        foreach ($categories as $catKey => $catName) {
            if (in_array($catKey, $emNrcCategoryKeys)) {
                // Add E&M NRC category
                $reorganizedCategories[$catKey] = $catName;
                $emNrcProcessed[] = $catKey;

                // After adding all E&M NRC categories, add E&M NRC Total
                if (count($emNrcProcessed) === count($emNrcCategoryKeys) && !isset($reorganizedCategories[$emNrcTotalKey])) {
                    $reorganizedCategories[$emNrcTotalKey] = 'E&M NRC (Total)';
                }
            } else {
                // Add non-E&M NRC categories
                $reorganizedCategories[$catKey] = $catName;
            }
        }

        // If E&M NRC categories exist but total wasn't added (in case they were not consecutive)
        if (!empty($emNrcCategoryKeys) && !isset($reorganizedCategories[$emNrcTotalKey])) {
            // Find the position after last E&M NRC category
            $tempCategories = [];
            $foundLastEmNrc = false;
            foreach ($reorganizedCategories as $key => $name) {
                $tempCategories[$key] = $name;
                if (in_array($key, $emNrcCategoryKeys) && $key === $emNrcCategoryKeys[count($emNrcCategoryKeys) - 1]) {
                    $foundLastEmNrc = true;
                }
                if ($foundLastEmNrc && !isset($tempCategories[$emNrcTotalKey])) {
                    // Insert after this E&M NRC category
                    $tempCategories[$emNrcTotalKey] = 'E&M NRC (Total)';
                    $foundLastEmNrc = false; // Prevent duplicate insertion
                }
            }
            $reorganizedCategories = $tempCategories;
        }

        // Add E&M NRC Total data to reportData
        foreach ($reportData as $rowKey => &$row) {
            $row['categories'][$emNrcTotalKey] = [
                'count' => $emNrcTotal[$rowKey] ?? 0,
                'percentage' => $emNrcTotalForTotalRow > 0 ? round((($emNrcTotal[$rowKey] ?? 0) / $emNrcTotalForTotalRow) * 100, 1) : 0,
            ];
        }
        unset($row);

        // Add E&M NRC Total to categoryTotals
        $categoryTotals[$emNrcTotalKey] = $emNrcTotalForTotalRow;

        // Add Total row
        $reportData['total'] = ['name' => 'Total', 'categories' => []];
        foreach ($reorganizedCategories as $catKey => $catName) {
            if ($catKey === $emNrcTotalKey) {
                $reportData['total']['categories'][$catKey] = [
                    'count' => $emNrcTotalForTotalRow,
                    'percentage' => $grandTotal > 0 ? round(($emNrcTotalForTotalRow / $grandTotal) * 100, 1) : 0,
                ];
            } else {
                $reportData['total']['categories'][$catKey] = [
                    'count' => $categoryTotals[$catKey] ?? 0,
                    'percentage' => $grandTotal > 0 ? round((($categoryTotals[$catKey] ?? 0) / $grandTotal) * 100, 1) : 0,
                ];
            }
        }

        // Prepare data for view
        $data = [
            'reportData' => $reportData,
            'categories' => $reorganizedCategories,
            'rows' => $rows,
            'categoryTotals' => $categoryTotals,
            'grandTotal' => $grandTotal,
            'rowTotals' => $rowTotals,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'emNrcTotalKey' => $emNrcTotalKey,
        ];

        // Export based on format
        if ($format === 'excel') {
            return $this->exportToExcelReport($data);
        } elseif ($format === 'pdf') {
            return $this->exportToPDFReport($data);
        }

        return view('admin.reports.complaints', $data);
    }

    /**
     * Generate employee performance reports
     */
    public function employees(Request $request)
    {
        // Set default values if not provided
        $dateFrom = $request->date_from ?? now()->subMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? now()->format('Y-m-d');
        $format = $request->input('format') ?? 'html';

        // Validate only if parameters are provided
        if ($request->has('date_from') || $request->has('date_to')) {
            $request->validate([
                'date_from' => 'date',
                'date_to' => 'date|after_or_equal:date_from',
                'format' => 'nullable|in:html,pdf,excel',
            ]);
        }

        // Ensure dates are properly formatted and include time for full day coverage
        $dateFromStart = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $dateToEnd = \Carbon\Carbon::parse($dateTo)->endOfDay();
        $cmesId = $request->cmes_id;
        $category = $request->category;
        $user = Auth::user();
        $query = Employee::query();

        // Filter by category if provided
        if ($category) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }

        // Apply location-based filtering to employees
        $this->filterEmployeesByLocation($query, $user);

        // Fetch employee statistics efficiently
        $employeeIds = $query->pluck('id')->toArray();
        if (empty($employeeIds)) {
            $employees = collect();
        } else {
            $statsQuery = Complaint::query()->whereIn('assigned_employee_id', $employeeIds)
                ->whereBetween('created_at', [$dateFromStart, $dateToEnd]);

            // Apply location and CMES filters to the complaints counted
            $this->filterComplaintsByLocation($statsQuery, $user);
            if ($cmesId) {
                $statsQuery->where(function ($q) use ($cmesId) {
                    $q->whereHas('house', function ($hq) use ($cmesId) {
                        $hq->whereHas('city', function ($ccq) use ($cmesId) {
                            $ccq->where('cme_id', $cmesId); })
                            ->orWhereHas('sector', function ($ssq) use ($cmesId) {
                                $ssq->where('cme_id', $cmesId); });
                    })->orWhere(function ($cq) use ($cmesId) {
                        $cq->whereNull('house_id')
                            ->where(function ($lq) use ($cmesId) {
                                $lq->whereHas('city', function ($ccq) use ($cmesId) {
                                    $ccq->where('cme_id', $cmesId); })
                                    ->orWhereHas('sector', function ($ssq) use ($cmesId) {
                                        $ssq->where('cme_id', $cmesId); });
                            });
                    });
                });
            }

            $complaintStats = $statsQuery->selectRaw('
                assigned_employee_id,
                COUNT(*) as total,
                SUM(CASE WHEN status IN ("resolved", "closed") THEN 1 ELSE 0 END) as resolved,
                AVG(CASE WHEN status IN ("resolved", "closed") AND updated_at IS NOT NULL THEN TIMESTAMPDIFF(HOUR, created_at, updated_at) ELSE NULL END) as avg_time
            ')->groupBy('assigned_employee_id')->get()->keyBy('assigned_employee_id');

            $allEmployees = $query->get();
            $employees = $allEmployees->map(function ($employee) use ($complaintStats) {
                $stat = $complaintStats->get($employee->id);
                return [
                    'employee' => $employee,
                    'total_complaints' => $stat->total ?? 0,
                    'resolved_complaints' => $stat->resolved ?? 0,
                    'resolution_rate' => ($stat->total ?? 0) > 0 ? round(($stat->resolved / $stat->total) * 100, 2) : 0,
                    'avg_resolution_time' => round($stat->avg_time ?? 0, 1),
                ];
            });
        }

        $summary = [
            'total_employees' => $employees->count(),
            'total_complaints' => $employees->sum('total_complaints'),
            'total_resolved' => $employees->sum('resolved_complaints'),
            'avg_resolution_rate' => $employees->count() > 0 ? round($employees->avg('resolution_rate'), 1) : 0,
            'top_performer' => $employees->sortByDesc('resolution_rate')->first(),
            'average_resolution_time' => $employees->count() > 0 ? round($employees->avg('avg_resolution_time'), 1) : 0,
        ];

        if ($format === 'html') {
            return view('admin.reports.employees', compact('employees', 'summary', 'dateFrom', 'dateTo'));
        } else {
            return $this->exportReport('employees', $employees, $summary, $format);
        }
    }


    /** Printable versions - reuse data builders */
    public function printComplaints(Request $request)
    {
        // Redirect to the new complaints report with print format
        return $this->complaints($request);
    }

    public function printEmployees(Request $request)
    {
        return redirect()->route('admin.reports.employees', $request->query());
    }

    public function printSpares(Request $request)
    {
        return redirect()->route('admin.reports.spares', $request->query());
    }


    /**
     * Generate spare parts reports
     */
    public function spares(Request $request)
    {
        // Set default values if not provided
        $dateFrom = $request->date_from ?? now()->subMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? now()->format('Y-m-d');
        $category = $request->category;
        $format = $request->input('format') ?? 'html';

        // Validate only if parameters are provided
        if ($request->has('date_from') || $request->has('date_to')) {
            $request->validate([
                'date_from' => 'date',
                'date_to' => 'date|after_or_equal:date_from',
                'category' => 'nullable|string',
                'format' => 'nullable|in:html,pdf,excel',
            ]);
        }

        // Ensure dates are properly formatted and include time for full day coverage
        $dateFromStart = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $dateToEnd = \Carbon\Carbon::parse($dateTo)->endOfDay();

        $cmesId = $request->cmes_id;
        $user = Auth::user();
        $query = Spare::query();

        // Apply location-based filtering
        $this->filterSparesByLocation($query, $user);

        if ($category) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }

        // Fetch spares with pre-filtered usage stats
        $spareIds = $query->pluck('id')->toArray();
        if (empty($spareIds)) {
            $spares = collect();
        } else {
            // Stats from stock logs (total used)
            $logQuery = \App\Models\SpareStockLog::query()->whereIn('spare_id', $spareIds)
                ->where('change_type', 'out')
                ->whereBetween('spare_stock_logs.created_at', [$dateFromStart, $dateToEnd]);

            // Note: Stock logs use reference_id for complaint links
            $logQuery->whereHas('complaint', function ($q) use ($user, $cmesId) {
                $this->filterComplaintsByLocation($q, $user);

                if ($cmesId) {
                    $q->where(function ($sq) use ($cmesId) {
                        $sq->whereHas('house', function ($hq) use ($cmesId) {
                            $hq->whereHas('city', function ($ccq) use ($cmesId) {
                                $ccq->where('cme_id', $cmesId); })
                                ->orWhereHas('sector', function ($ssq) use ($cmesId) {
                                    $ssq->where('cme_id', $cmesId); });
                        })->orWhere(function ($cq) use ($cmesId) {
                            $cq->whereNull('house_id')->where(function ($lq) use ($cmesId) {
                                $lq->whereHas('city', function ($ccq) use ($cmesId) {
                                    $ccq->where('cme_id', $cmesId); })
                                    ->orWhereHas('sector', function ($ssq) use ($cmesId) {
                                        $ssq->where('cme_id', $cmesId); });
                            });
                        });
                    });
                }
            });

            $logStats = $logQuery->selectRaw('spare_id, SUM(quantity) as total_used, COUNT(*) as usage_count')
                ->groupBy('spare_id')->get()->keyBy('spare_id');

            $allSpares = $query->get();
            $spares = $allSpares->map(function ($spare) use ($logStats) {
                $stat = $logStats->get($spare->id);
                $periodUsed = $stat->total_used ?? 0;
                return [
                    'spare' => $spare,
                    'total_used' => $spare->issued_quantity ?? 0, // Use lifetime value for consistency with Index and Balance
                    'period_used' => $periodUsed,
                    'total_cost' => $periodUsed * ($spare->unit_price ?? 0),
                    'usage_count' => $stat->usage_count ?? 0,
                    'current_stock' => $spare->stock_quantity ?? 0,
                    'stock_status' => method_exists($spare, 'getStockStatusAttribute') ? $spare->getStockStatusAttribute() : 'in_stock',
                ];
            });
        }

        $summary = [
            'total_spares' => $spares->count(),
            'total_usage_count' => $spares->sum('usage_count'),
            'low_stock_items' => $spares->filter(function ($item) {
                return ($item['current_stock'] ?? 0) <= ($item['spare']->threshold_level ?? 0) && ($item['current_stock'] ?? 0) > 0;
            })->count(),
            'out_of_stock_items' => $spares->filter(function ($item) {
                return ($item['current_stock'] ?? 0) <= 0;
            })->count(),
        ];

        if ($format === 'html') {
            return view('admin.reports.spares', compact('spares', 'summary', 'dateFrom', 'dateTo', 'category'));
        } else {
            return $this->exportReport('spares', $spares, $summary, $format);
        }
    }

    /**
     * Generate financial reports
     */
    public function financial(Request $request)
    {
        // Set default values if not provided
        $dateFrom = $request->date_from ?? now()->subMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? now()->format('Y-m-d');
        $format = $request->input('format') ?? 'html';

        // Validate only if parameters are provided
        if ($request->has('date_from') || $request->has('date_to')) {
            $request->validate([
                'date_from' => 'date',
                'date_to' => 'date|after_or_equal:date_from',
                'format' => 'nullable|in:html,pdf,excel',
            ]);
        }

        // Ensure dates are properly formatted and include time for full day coverage
        $dateFromStart = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $dateToEnd = \Carbon\Carbon::parse($dateTo)->endOfDay();
        $user = Auth::user();
        $cmesId = $request->cmes_id;

        // Build spare costs query with location filtering
        $spareCostsQuery = DB::table('complaint_spares')
            ->join('spares', 'complaint_spares.spare_id', '=', 'spares.id')
            ->join('complaints', 'complaint_spares.complaint_id', '=', 'complaints.id')
            ->whereBetween('complaint_spares.used_at', [$dateFromStart, $dateToEnd]);

        // Apply location filtering
        if ($user && !$this->canViewAllData($user)) {
            $spareCostsQuery->leftJoin('houses', 'complaints.house_id', '=', 'houses.id');
            $spareCostsQuery->where(function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->whereNotNull('complaints.house_id');
                    if (!empty($user->city_ids)) {
                        $sub->whereIn('houses.city_id', $user->city_ids);
                    }
                    if (!empty($user->sector_ids)) {
                        $sub->whereIn('houses.sector_id', $user->sector_ids);
                    }
                })->orWhere(function ($sub) use ($user) {
                    $sub->whereNull('complaints.house_id');
                    if (!empty($user->city_ids)) {
                        $sub->whereIn('complaints.city_id', $user->city_ids);
                    }
                    if (!empty($user->sector_ids)) {
                        $sub->whereIn('complaints.sector_id', $user->sector_ids);
                    }
                });
            });
        }

        // Apply CMES filter if provided
        if ($cmesId) {
            $spareCostsQuery->whereExists(function ($query) use ($cmesId) {
                $query->select(DB::raw(1))
                    ->from('houses')
                    ->join('cities', 'houses.city_id', '=', 'cities.id')
                    ->whereRaw('houses.id = complaints.house_id')
                    ->where('cities.cme_id', $cmesId);
            })->orWhereExists(function ($query) use ($cmesId) {
                $query->select(DB::raw(1))
                    ->from('cities')
                    ->whereRaw('cities.id = complaints.city_id')
                    ->where('cities.cme_id', $cmesId);
            });
        }

        $spareCosts = $spareCostsQuery
            ->join('complaint_categories', 'spares.category_id', '=', 'complaint_categories.id')
            ->selectRaw('complaint_categories.name as category, SUM(complaint_spares.quantity * spares.unit_price) as total_cost')
            ->groupBy('complaint_categories.name')
            ->get();

        // Approval costs - simplified approach (with location filtering if needed)
        $approvalQuery = SpareApprovalPerforma::query()->whereBetween('created_at', [$dateFromStart, $dateToEnd])
            ->where('status', 'approved');

        // Note: Approvals might not have direct location, filter if they're related to complaints
        // For now, show all approvals if user can view all, otherwise filter by complaint location
        if ($user && !$this->canViewAllData($user)) {
            $approvalQuery->whereHas('complaint', function ($q) use ($user) {
                if (!empty($user->city_ids)) {
                    $q->whereIn('city_id', $user->city_ids);
                }
                if (!empty($user->sector_ids)) {
                    $q->whereIn('sector_id', $user->sector_ids);
                }
            });
        }

        $approvalCosts = $approvalQuery->get()
            ->groupBy(function ($approval) {
                return $approval->created_at->format('Y-m');
            })
            ->map(function ($approvals) {
                return $approvals->count(); // For now, just count approvals per month
            });

        // Calculate totals with location filtering
        $totalApprovalsQuery = SpareApprovalPerforma::query()->whereBetween('created_at', [$dateFromStart, $dateToEnd]);
        $approvedApprovalsQuery = SpareApprovalPerforma::query()->whereBetween('created_at', [$dateFromStart, $dateToEnd])
            ->where('status', 'approved');

        // Apply location filtering to approval counts if needed
        if ($user && !$this->canViewAllData($user)) {
            $filterApprovals = function ($query) use ($user) {
                $query->whereHas('complaint', function ($q) use ($user) {
                    $q->whereHas('house', function ($clientQ) use ($user) {
                        if (!empty($user->city_ids)) {
                            $clientQ->whereIn('city_id', $user->city_ids);
                        }
                        if (!empty($user->sector_ids)) {
                            $clientQ->whereIn('sector_id', $user->sector_ids);
                        }
                    });
                });
            };
            $filterApprovals($totalApprovalsQuery);
            $filterApprovals($approvedApprovalsQuery);
        }

        $summary = [
            'total_spare_costs' => $spareCosts->sum('total_cost'),
            'total_approvals' => $totalApprovalsQuery->count(),
            'approved_approvals' => $approvedApprovalsQuery->count(),
            'category_breakdown' => $spareCosts,
            'monthly_approvals' => $approvalCosts,
        ];

        if ($format === 'html') {
            return view('admin.reports.financial', compact('summary', 'dateFrom', 'dateTo'));
        } else {
            return $this->exportReport('financial', $summary, $summary, $format);
        }
    }



    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        $stats = [
            'total_complaints' => Complaint::count(),
            'resolved_complaints' => Complaint::query()->whereIn('status', ['resolved', 'closed'])->count(),
            'pending_complaints' => Complaint::pending()->count(),
            'overdue_complaints' => Complaint::overdue()->count(),
            'total_employees' => Employee::query()->where('status', 1)->count(),
            'total_clients' => \App\Models\House::count(),
            'total_spares' => Spare::count(),
            'low_stock_items' => Spare::lowStock()->count(),
            'pending_approvals' => SpareApprovalPerforma::pending()->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get chart data for dashboard
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'complaints');
        $period = $request->get('period', '30'); // days

        $user = Auth::user();

        switch ($type) {
            case 'complaints':
                $complaintsQuery = Complaint::query()->where('created_at', '>=', now()->subDays($period));
                $this->filterComplaintsByLocation($complaintsQuery, $user);
                $data = $complaintsQuery->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;

            case 'spares':
                $data = DB::table('complaint_spares')
                    ->join('spares', 'complaint_spares.spare_id', '=', 'spares.id')
                    ->where('complaint_spares.used_at', '>=', now()->subDays($period))
                    ->selectRaw('DATE(complaint_spares.used_at) as date, SUM(complaint_spares.quantity * spares.unit_price) as total_cost')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;

            case 'employees':
                $employeesQuery = Employee::query()->where('status', 1);
                $this->filterEmployeesByLocation($employeesQuery, $user);
                $data = $employeesQuery->withCount([
                    'assignedComplaints' => function ($q) use ($period, $user) {
                        $q->where('created_at', '>=', now()->subDays($period))
                            ->whereIn('status', ['resolved', 'closed']);
                        // Apply location filter to complaints - using whereHas on the relation
                        if ($user && !$this->canViewAllData($user)) {
                            if (!empty($user->city_ids)) {
                                $q->whereHas('house', function ($clientQ) use ($user) {
                                    $clientQ->whereIn('city_id', $user->city_ids);
                                });
                            }
                            if (!empty($user->sector_ids)) {
                                $q->whereHas('house', function ($clientQ) use ($user) {
                                    $clientQ->whereIn('sector_id', $user->sector_ids);
                                });
                            }
                        }
                    }
                ])
                    ->get()
                    ->map(function ($employee) {
                        return [
                            'name' => $employee->name,
                            'resolved' => $employee->assigned_complaints_count,
                        ];
                    });
                break;

            default:
                $data = [];
        }

        return response()->json($data);
    }

    /**
     * Export report
     */
    private function exportReport($type, $data, $summary, $format)
    {
        try {
            if ($format === 'pdf') {
                return $this->exportToPDF($type, $data, $summary);
            } elseif ($format === 'excel') {
                return $this->exportToExcel($type, $data, $summary);
            } else {
                return $this->exportToJSON($type, $data, $summary);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export SUB-DIVISION WISE PERFORMANCE report to PDF
     */
    private function exportToPDFReport($data)
    {
        // For now, redirect to print view or return HTML that can be printed
        // In production, you would use DomPDF or similar library
        return view('admin.reports.complaints-pdf', $data);
    }

    /**
     * Export SUB-DIVISION WISE PERFORMANCE report to Excel
     */
    private function exportToExcelReport($data)
    {
        // TODO: Implement Excel export using Laravel Excel
        // For now, return a simple CSV
        $filename = "sub_division_wise_performance_" . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Write header row - categories + Total
            $headerRow = ['Description'];
            foreach ($data['categories'] as $catName) {
                $headerRow[] = $catName . ' - Qty';
                $headerRow[] = $catName . ' - %age';
            }
            $headerRow[] = 'Total - Qty';
            $headerRow[] = 'Total - %age';
            fputcsv($file, $headerRow);

            // Write data rows
            foreach ($data['reportData'] as $rowKey => $row) {
                $rowData = [$row['name']];

                // Add category columns (count and percentage)
                foreach ($data['categories'] as $catKey => $catName) {
                    $count = $row['categories'][$catKey]['count'] ?? 0;
                    $percentage = $row['categories'][$catKey]['percentage'] ?? 0;
                    $rowData[] = $count;
                    $rowData[] = $percentage . '%';
                }

                // Add Grand Total columns: sum of all primary columns
                // Individual E&M NRC columns (Electric, Gas, Water Supply) should be EXCLUDED from Total
                // E&M NRC (Total) should be INCLUDED in Total
                $rowGrandTotal = 0;
                $emNrcTotalKey = $data['emNrcTotalKey'] ?? 'em_nrc_total';
                $hasEmNrcTotal = isset($row['categories'][$emNrcTotalKey]);

                foreach ($row['categories'] as $catKey => $catData) {
                    // Always include E&M NRC Total if it exists
                    if ($catKey === $emNrcTotalKey) {
                        $rowGrandTotal += $catData['count'] ?? 0;
                    }
                    // For other categories, check if it's an individual E&M NRC column
                    elseif (isset($data['categories'][$catKey])) {
                        $catName = $data['categories'][$catKey];

                        // Skip individual E&M NRC columns (Electric, Gas, Water Supply) if E&M NRC Total exists
                        $isIndividualEmNrc = false;
                        if ($hasEmNrcTotal) {
                            // Check if this is one of the 3 individual E&M NRC columns
                            if (stripos($catName, 'E&M NRC') !== false && stripos($catName, 'Total') === false) {
                                $isIndividualEmNrc = true;
                            }
                        }

                        // Include all other columns (non-individual E&M NRC columns)
                        if (!$isIndividualEmNrc) {
                            $rowGrandTotal += $catData['count'] ?? 0;
                        }
                    } else {
                        // Include if category key not found in categories array (fallback)
                        $rowGrandTotal += $catData['count'] ?? 0;
                    }
                }
                $rowGrandPercent = $data['grandTotal'] > 0 ? round(($rowGrandTotal / $data['grandTotal'] * 100), 1) : 0;
                $rowData[] = $rowGrandTotal;
                $rowData[] = $rowGrandPercent . '%';

                fputcsv($file, $rowData);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export report to PDF (legacy method for other reports)
     */
    private function exportToPDF($type, $data, $summary)
    {
        $filename = "{$type}_report_" . now()->format('Y-m-d_H-i-s') . '.pdf';

        // For now, return JSON with download link
        // In production, you would use a PDF library like DomPDF or TCPDF
        return response()->json([
            'message' => 'PDF export functionality will be implemented with a PDF library',
            'filename' => $filename,
            'data' => $data,
            'summary' => $summary,
            'download_url' => route('admin.reports.download', ['type' => $type, 'format' => 'pdf'])
        ]);
    }

    /**
     * Export report to Excel (legacy method for other reports)
     */
    private function exportToExcel($type, $data, $summary)
    {
        $filename = "{$type}_report_" . now()->format('Y-m-d_H-i-s') . '.xlsx';

        // For now, return JSON with download link
        // In production, you would use a library like Laravel Excel
        return response()->json([
            'message' => 'Excel export functionality will be implemented with Laravel Excel',
            'filename' => $filename,
            'data' => $data,
            'summary' => $summary,
            'download_url' => route('admin.reports.download', ['type' => $type, 'format' => 'excel'])
        ]);
    }

    /**
     * Export report to JSON
     */
    private function exportToJSON($type, $data, $summary)
    {
        $filename = "{$type}_report_" . now()->format('Y-m-d_H-i-s') . '.json';

        $exportData = [
            'report_type' => $type,
            'generated_at' => now()->toISOString(),
            'summary' => $summary,
            'data' => $data
        ];

        return response()->json($exportData)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }

    /**
     * Download report file
     */
    public function download($type, $format)
    {
        try {
            // Get report data based on type
            $request = new Request();
            $reportData = $this->getReportData($type, $request);

            if ($format === 'json') {
                return $this->exportToJSON($type, $reportData['data'], $reportData['summary']);
            } else {
                // For PDF and Excel, return a message with download instructions
                return response()->json([
                    'message' => "{$format} export functionality will be implemented with appropriate libraries",
                    'type' => $type,
                    'format' => $format,
                    'data' => $reportData
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Download failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get report data based on type
     */
    private function getReportData($type, $request)
    {
        switch ($type) {
            case 'complaints':
                return $this->getComplaintsData($request);
            case 'employees':
                return $this->getEmployeesData($request);
            case 'spares':
                return $this->getSparesData($request);
            case 'financial':
                return $this->getFinancialData($request);
            default:
                throw new \Exception("Unknown report type: {$type}");
        }
    }

    /** Builders for export/print reuse */
    private function getComplaintsData(Request $request): array
    {
        $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $groupBy = $request->get('group_by', 'status');

        $user = Auth::user();
        $dateFromStart = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $dateToEnd = \Carbon\Carbon::parse($dateTo)->endOfDay();

        $baseQuery = Complaint::query()->whereBetween('created_at', [$dateFromStart, $dateToEnd]);

        // Apply location-based filtering
        $this->filterComplaintsByLocation($baseQuery, $user);
        switch ($groupBy) {
            case 'status':
                $data = (clone $baseQuery)->selectRaw('status, COUNT(*) as count')->groupBy('status')->get();
                break;
            case 'type':
                $data = (clone $baseQuery)
                    ->join('complaint_categories', 'complaints.category_id', '=', 'complaint_categories.id')
                    ->selectRaw('complaint_categories.name as category, COUNT(*) as count')
                    ->groupBy('complaint_categories.name')
                    ->get();
                break;
            case 'priority':
                $data = (clone $baseQuery)->selectRaw('priority, COUNT(*) as count')->groupBy('priority')->get();
                break;
            case 'employee':
                $data = (clone $baseQuery)->whereNotNull('assigned_employee_id')
                    ->selectRaw('assigned_employee_id, COUNT(*) as count')->groupBy('assigned_employee_id')->get()
                    ->map(function ($item) {
                        $item->assignedEmployee = Employee::find($item->assigned_employee_id);
                        return $item;
                    });
                break;
            case 'house':
            case 'client':
                $data = (clone $baseQuery)->selectRaw('house_id, COUNT(*) as count')->groupBy('house_id')->get()
                    ->map(function ($item) {
                        $item->house = \App\Models\House::find($item->house_id);
                        return $item;
                    });
                break;
            default:
                $data = (clone $baseQuery)->with(['house', 'assignedEmployee'])->get();
        }

        $summary = [
            'total_complaints' => (clone $baseQuery)->count(),
            'resolved_complaints' => (clone $baseQuery)->whereIn('status', ['resolved', 'closed'])->count(),
            'pending_complaints' => (clone $baseQuery)->whereIn('status', ['new', 'assigned', 'in_progress'])->count(),
            'avg_resolution_time' => (clone $baseQuery)->whereIn('status', ['resolved', 'closed'])->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')->value('avg_hours') ?? 0,
        ];

        return ['data' => $data, 'summary' => $summary];
    }

    private function getEmployeesData(Request $request): array
    {
        $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $dateFromStart = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $dateToEnd = \Carbon\Carbon::parse($dateTo)->endOfDay();
        $category = $request->get('category');

        $user = Auth::user();
        $query = Employee::with([
            'user',
            'assignedComplaints' => function ($q) use ($dateFromStart, $dateToEnd, $user) {
                $q->whereBetween('created_at', [$dateFromStart, $dateToEnd]);
                // Apply location filter to complaints - using whereHas on the relation
                if ($user && !$this->canViewAllData($user)) {
                    if (!empty($user->city_ids)) {
                        $q->whereHas('house', function ($clientQ) use ($user) {
                            $clientQ->whereIn('city_id', $user->city_ids);
                        });
                    }
                    if (!empty($user->sector_ids)) {
                        $q->whereHas('house', function ($clientQ) use ($user) {
                            $clientQ->whereIn('sector_id', $user->sector_ids);
                        });
                    }
                }
            }
        ]);

        // Apply location-based filtering to employees
        $this->filterEmployeesByLocation($query, $user);

        // Filter by category if provided
        if ($category) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }

        $employees = $query->get()->map(function ($employee) {
            $complaints = $employee->assignedComplaints;
            $resolved = $complaints->whereIn('status', ['resolved', 'closed']);
            return [
                'employee' => $employee,
                'total_complaints' => $complaints->count(),
                'resolved_complaints' => $resolved->count(),
                'resolution_rate' => $complaints->count() > 0 ? round(($resolved->count() / $complaints->count()) * 100, 2) : 0,
                'avg_resolution_time' => $resolved->avg(function ($c) {
                    return $c->created_at->diffInHours($c->updated_at);
                }) ?? 0,
            ];
        });

        $summary = [
            'total_employees' => $employees->count(),
            'avg_resolution_rate' => $employees->avg('resolution_rate'),
            'top_performer' => $employees->sortByDesc('resolution_rate')->first(),
        ];

        return ['data' => $employees, 'summary' => $summary];
    }

    private function getSparesData(Request $request): array
    {
        $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $dateFromStart = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $dateToEnd = \Carbon\Carbon::parse($dateTo)->endOfDay();
        $category = $request->get('category');

        $user = Auth::user();
        $query = Spare::query();

        // Apply location-based filtering
        $this->filterSparesByLocation($query, $user);

        if ($category) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }

        $spares = $query->with([
            'complaintSpares' => function ($q) use ($dateFromStart, $dateToEnd) {
                $q->whereBetween('used_at', [$dateFromStart, $dateToEnd]);
            },
            'stockLogs' => function ($q) use ($dateFromStart, $dateToEnd) {
                $q->where('change_type', 'out')
                    ->whereBetween('created_at', [$dateFromStart, $dateToEnd]);
            }
        ])->get()->map(function (\App\Models\Spare $spare) {
            $usage = $spare->complaintSpares;

            $stockLogs = $spare->stockLogs;
            $stockOut = $stockLogs->sum('quantity');

            $totalUsed = $stockOut;
            $totalCost = $totalUsed * $spare->unit_price;
            $usageCount = $stockLogs->count();

            return [
                'spare' => $spare,
                'total_used' => $totalUsed,
                'total_cost' => $totalCost,
                'usage_count' => $usageCount,
                'current_stock' => $spare->stock_quantity,
                'stock_status' => $spare->getStockStatusAttribute(),
            ];
        });

        $summary = [
            'total_spares' => $spares->count(),
            'total_usage_count' => $spares->sum('usage_count'),
            'low_stock_items' => $spares->where('stock_status', 'low_stock')->count(),
            'out_of_stock_items' => $spares->where('stock_status', 'out_of_stock')->count(),
        ];

        return ['data' => $spares, 'summary' => $summary];
    }

    private function getFinancialData(Request $request): array
    {
        $user = Auth::user();
        $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $dateFromStart = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $dateToEnd = \Carbon\Carbon::parse($dateTo)->endOfDay();

        $spareCostsQuery = DB::table('complaint_spares')
            ->join('spares', 'complaint_spares.spare_id', '=', 'spares.id')
            ->join('complaints', 'complaint_spares.complaint_id', '=', 'complaints.id')
            ->whereBetween('complaint_spares.used_at', [$dateFromStart, $dateToEnd]);

        // Apply location filtering
        if ($user && !$this->canViewAllData($user)) {
            $spareCostsQuery->join('houses', 'complaints.house_id', '=', 'houses.id');
            if (!empty($user->city_ids)) {
                $spareCostsQuery->whereIn('houses.city_id', $user->city_ids);
            }
            if (!empty($user->sector_ids)) {
                $spareCostsQuery->whereIn('houses.sector_id', $user->sector_ids);
            }
        }

        $spareCosts = $spareCostsQuery
            ->join('complaint_categories', 'spares.category_id', '=', 'complaint_categories.id')
            ->selectRaw('complaint_categories.name as category, SUM(complaint_spares.quantity * spares.unit_price) as total_cost')
            ->groupBy('complaint_categories.name')
            ->get();

        $approvalCosts = SpareApprovalPerforma::query()->whereBetween('created_at', [$dateFromStart, $dateToEnd])
            ->where('status', 'approved')->get()->groupBy(function ($a) {
                return $a->created_at->format('Y-m');
            })
            ->map(function ($list) {
                return $list->count();
            });

        $summary = [
            'total_spare_costs' => $spareCosts->sum('total_cost'),
            'total_approvals' => SpareApprovalPerforma::query()->whereBetween('created_at', [$dateFromStart, $dateToEnd])->count(),
            'approved_approvals' => SpareApprovalPerforma::query()->whereBetween('created_at', [$dateFromStart, $dateToEnd])->where('status', 'approved')->count(),
            'category_breakdown' => $spareCosts,
            'monthly_approvals' => $approvalCosts,
        ];
        return ['data' => $summary, 'summary' => $summary];
    }

    /**
     * Save report to cache
     */
    public function saveReport(Request $request)
    {
        $validator = $request->validate([
            'report_type' => 'required|in:complaints,spares,employees',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'data' => 'required|array',
        ]);

        $report = ReportsSummary::getOrCreate(
            $request->report_type,
            $request->period_start,
            $request->period_end
        );

        $report->updateData($request->data);

        return response()->json(['success' => true, 'report_id' => $report->id]);
    }

    /**
     * Get cached report
     */
    public function getCachedReport(ReportsSummary $report)
    {
        return response()->json([
            'report' => $report,
            'data' => $report->data_json,
            'summary' => $report->getSummaryAttribute()
        ]);
    }

    /**
     * Generate SLA COMPLIANCE report
     */
    public function sla(Request $request)
    {
        // Set default values if not provided
        $dateFrom = $request->date_from ?? now()->subMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? now()->format('Y-m-d');

        // Ensure dates are properly formatted and include time for full day coverage
        $dateFromStart = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $dateToEnd = \Carbon\Carbon::parse($dateTo)->endOfDay();

        // Get user for location filtering
        $user = Auth::user();

        // Get actual categories from ComplaintCategory table
        $actualCategories = \App\Models\ComplaintCategory::query()->where('status', 1)->orderBy('name')
            ->pluck('name')
            ->toArray();

        $allCategories = $actualCategories;
        sort($allCategories);

        // Map categories for report
        $categories = [];
        foreach ($allCategories as $cat) {
            $key = strtolower(str_replace([' ', '&', '-', '(', ')'], ['_', '', '_', '', ''], $cat));
            $categories[$key] = $cat;
        }

        // Base query for resolved complaints in date range
        $baseQuery = Complaint::query()->whereBetween('created_at', [$dateFromStart, $dateToEnd]);

        // Apply location-based filtering
        $this->filterComplaintsByLocation($baseQuery, $user);

        // Initialize report data structure
        $slaData = [];
        $categoryTotals = [];
        $timeBuckets = ['lt_24h' => '< 24 Hours', '24_48h' => '24-48 Hours', 'gt_48h' => '> 48 Hours'];

        $grandTotal = 0;
        $totalResolved = 0;
        $totalCompliant = 0;
        $globalTotalHours = 0;
        $globalResolvedCount = 0;

        $cmesId = $request->cmes_id;
        $categoryNameToId = \App\Models\ComplaintCategory::query()->where('status', 1)
            ->pluck('id', 'name')->toArray();

        // Optimized SLA data fetch
        $slaQuery = (clone $baseQuery);
        // Apply CMES filter if provided
        if ($cmesId) {
            $slaQuery->where(function ($q) use ($cmesId) {
                $q->whereHas('house', function ($hq) use ($cmesId) {
                    $hq->whereHas('city', function ($ccq) use ($cmesId) {
                        $ccq->where('cme_id', $cmesId); })
                        ->orWhereHas('sector', function ($ssq) use ($cmesId) {
                            $ssq->where('cme_id', $cmesId); });
                })->orWhere(function ($cq) use ($cmesId) {
                    $cq->whereNull('house_id')->where(function ($lq) use ($cmesId) {
                        $lq->whereHas('city', function ($ccq) use ($cmesId) {
                            $ccq->where('cme_id', $cmesId); })
                            ->orWhereHas('sector', function ($ssq) use ($cmesId) {
                                $ssq->where('cme_id', $cmesId); });
                    });
                });
            });
        }

        $allSlaStats = $slaQuery->selectRaw('
                category_id,
                COUNT(*) as total,
                SUM(CASE WHEN status = "resolved" THEN 1 ELSE 0 END) as resolved_count,
                SUM(CASE WHEN status = "resolved" AND TIMESTAMPDIFF(HOUR, created_at, updated_at) < 24 THEN 1 ELSE 0 END) as lt_24,
                SUM(CASE WHEN status = "resolved" AND TIMESTAMPDIFF(HOUR, created_at, updated_at) BETWEEN 24 AND 48 THEN 1 ELSE 0 END) as bw_24_48,
                SUM(CASE WHEN status = "resolved" AND TIMESTAMPDIFF(HOUR, created_at, updated_at) > 48 THEN 1 ELSE 0 END) as gt_48,
                SUM(CASE WHEN status = "resolved" THEN TIMESTAMPDIFF(HOUR, created_at, updated_at) ELSE 0 END) as total_hours
            ')
            ->groupBy('category_id')
            ->get()->keyBy('category_id');

        foreach ($categories as $catKey => $catName) {
            $catId = $categoryNameToId[$catName] ?? null;
            $stat = $allSlaStats->get($catId);

            $total = $stat->total ?? 0;
            $resolvedCount = $stat->resolved_count ?? 0;
            $lt24 = $stat->lt_24 ?? 0;
            $bw2448 = $stat->bw_24_48 ?? 0;
            $gt48 = $stat->gt_48 ?? 0;
            $totalHours = $stat->total_hours ?? 0;

            $avgTime = $resolvedCount > 0 ? round($totalHours / $resolvedCount, 1) : 0;
            $slaMet = $lt24 + $bw2448;

            $slaData[$catKey] = [
                'name' => $catName,
                'total' => $total,
                'resolved' => $resolvedCount,
                'lt_24h' => $lt24,
                '24_48h' => $bw2448,
                'gt_48h' => $gt48,
                'avg_time' => $avgTime,
                'compliance_rate' => $resolvedCount > 0 ? round(($slaMet / $resolvedCount) * 100, 1) : 0
            ];

            $grandTotal += $total;
            $totalResolved += $resolvedCount;
            $totalCompliant += $slaMet;
            $globalTotalHours += $totalHours;
            $globalResolvedCount += $resolvedCount;
        }

        // Summary Statistics
        $summary = [
            'total_complaints' => $grandTotal,
            'avg_resolution_time' => $globalResolvedCount > 0 ? round($globalTotalHours / $globalResolvedCount, 1) : 0,
            'compliance_rate' => $globalResolvedCount > 0 ? round(($totalCompliant / $globalResolvedCount) * 100, 1) : 0,
            'breached_count' => $globalResolvedCount - $totalCompliant
        ];

        return view('admin.reports.sla', compact('slaData', 'summary', 'dateFrom', 'dateTo'));
    }
}
