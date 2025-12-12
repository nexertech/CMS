<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\Client;
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
        $employeesQuery = \App\Models\Employee::where('status', 'active');
        $this->filterEmployeesByLocation($employeesQuery, $user);

        // Spares with location filtering
        $sparesQuery = \App\Models\Spare::query();
        $this->filterSparesByLocation($sparesQuery, $user);

        // Approvals with location filtering
        $approvalsQuery = \App\Models\SpareApprovalPerforma::query();
        $approvedApprovalsQuery = \App\Models\SpareApprovalPerforma::where('status', 'approved');

        if ($user && !$this->canViewAllData($user)) {
            $filterApprovals = function ($query) use ($user) {
                $query->whereHas('complaint', function ($q) use ($user) {
                    $q->whereHas('client', function ($clientQ) use ($user) {
                        if ($user->city_id && $user->city) {
                            $clientQ->where('city', $user->city->name);
                        }
                        if ($user->sector_id && $user->sector) {
                            $clientQ->where('sector', $user->sector->name);
                        }
                    });
                });
            };
            $filterApprovals($approvalsQuery);
            $filterApprovals($approvedApprovalsQuery);
        }

        return [
            'complaints' => [
                'total' => (clone $complaintsQuery)->count(),
                'resolved' => (clone $complaintsQuery)->where('status', 'resolved')
                    ->whereBetween('updated_at', [$startOfMonth, $now])
                    ->count(),
                'pending' => (clone $complaintsQuery)->where('status', '!=', 'resolved')->count(),
                'avg_resolution_time' => $this->getAverageResolutionTime($user)
            ],
            'employees' => [
                'total' => (clone $employeesQuery)->count(),
                'active' => (clone $employeesQuery)->count(),
                'avg_performance' => $this->getAverageEmployeePerformance($user)
            ],
            'spares' => [
                'total_items' => (clone $sparesQuery)->count(),
                'low_stock' => (clone $sparesQuery)->where('stock_quantity', '<=', \DB::raw('threshold_level'))->count(),
                'out_of_stock' => (clone $sparesQuery)->where('stock_quantity', 0)->count(),
                'total_value' => (clone $sparesQuery)->sum(\DB::raw('stock_quantity * unit_price'))
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
        $query = \App\Models\Complaint::where('status', 'resolved')
            ->whereNotNull('updated_at');
        $this->filterComplaintsByLocation($query, $user);
        $resolvedComplaints = $query->get();

        if ($resolvedComplaints->isEmpty()) {
            return 0;
        }

        $totalHours = $resolvedComplaints->sum(function ($complaint) {
            return $complaint->created_at->diffInHours($complaint->updated_at);
        });

        return round($totalHours / $resolvedComplaints->count(), 1);
    }

    /**
     * Get average employee performance
     */
    private function getAverageEmployeePerformance($user = null)
    {
        $employeesQuery = \App\Models\Employee::query();
        $this->filterEmployeesByLocation($employeesQuery, $user);

        $employees = $employeesQuery->with([
            'assignedComplaints' => function ($complaintsQuery) use ($user) {
                $complaintsQuery->where('status', 'resolved');
                // Apply location filter to complaints - using whereHas on the relation
                if ($user && !$this->canViewAllData($user)) {
                    if ($user->city_id && $user->city) {
                        $complaintsQuery->whereHas('client', function ($clientQ) use ($user) {
                            $clientQ->where('city', $user->city->name);
                        });
                    }
                    if ($user->sector_id && $user->sector) {
                        $complaintsQuery->whereHas('client', function ($clientQ) use ($user) {
                            $clientQ->where('sector', $user->sector->name);
                        });
                    }
                }
            }
        ])->get();

        if ($employees->isEmpty()) {
            return 0;
        }

        $totalPerformance = $employees->sum(function ($employee) {
            $totalComplaints = $employee->assignedComplaints->count();
            if ($totalComplaints === 0)
                return 0;

            $resolvedComplaints = $employee->assignedComplaints->where('status', 'resolved')->count();
            return ($resolvedComplaints / $totalComplaints) * 100;
        });

        return round($totalPerformance / $employees->count(), 1);
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
                ->join('complaints', 'complaint_spares.complaint_id', '=', 'complaints.id')
                ->join('clients', 'complaints.client_id', '=', 'clients.id');

            // Apply location filtering
            if ($user && !$this->canViewAllData($user)) {
                if ($user->city_id && $user->city) {
                    $query->where('clients.city', $user->city->name);
                }
                if ($user->sector_id && $user->sector) {
                    $query->where('clients.sector', $user->sector->name);
                }
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
                    ->join('clients', 'complaints.client_id', '=', 'clients.id');
                if ($user->city_id && $user->city) {
                    $query->where('clients.city', $user->city->name);
                }
                if ($user->sector_id && $user->sector) {
                    $query->where('clients.sector', $user->sector->name);
                }
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

        $approvedApprovals = \App\Models\SpareApprovalPerforma::where('status', 'approved')->count();
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

        $employeesQuery = Employee::where('status', 'active');
        $this->filterEmployeesByLocation($employeesQuery, $user);

        $clientsQuery = Client::query();
        $this->filterClientsByLocation($clientsQuery, $user);

        // Spares with location filtering
        $sparesQuery = Spare::query();
        $this->filterSparesByLocation($sparesQuery, $user);

        // Approvals with location filtering
        $approvalsQuery = SpareApprovalPerforma::query();
        $pendingApprovalsQuery = SpareApprovalPerforma::where('status', 'pending');

        if ($user && !$this->canViewAllData($user)) {
            $filterApprovals = function ($query) use ($user) {
                $query->whereHas('complaint', function ($q) use ($user) {
                    $q->whereHas('client', function ($clientQ) use ($user) {
                        if ($user->city_id && $user->city) {
                            $clientQ->where('city', $user->city->name);
                        }
                        if ($user->sector_id && $user->sector) {
                            $clientQ->where('sector', $user->sector->name);
                        }
                    });
                });
            };
            $filterApprovals($approvalsQuery);
            $filterApprovals($pendingApprovalsQuery);
        }

        return [
            'total_complaints_this_month' => (clone $complaintsQuery)
                ->whereBetween('created_at', [$startOfMonth, $now])
                ->count(),
            'resolved_this_month' => (clone $complaintsQuery)->where('status', 'resolved')
                ->whereBetween('updated_at', [$startOfMonth, $now])
                ->count(),
            'active_employees' => (clone $employeesQuery)->count(),
            'total_spares' => (clone $sparesQuery)->count(),
            'low_stock_items' => (clone $sparesQuery)->where('stock_quantity', '<=', DB::raw('threshold_level'))->count(),
            'out_of_stock_items' => (clone $sparesQuery)->where('stock_quantity', 0)->count(),
            'total_approvals' => $approvalsQuery->count(),
            'pending_approvals' => $pendingApprovalsQuery->count(),
            'total_clients' => (clone $clientsQuery)->count(),
            'active_clients' => (clone $clientsQuery)->where('status', 'active')->count(),
            'total_spare_value' => (clone $sparesQuery)->sum(DB::raw('stock_quantity * unit_price')),
            'employee_performance' => $this->getAverageEmployeePerformance($user)
        ];
    }

    /**
     * Calculate SLA compliance percentage
     */
    private function calculateSlaCompliance($user = null)
    {
        $complaintsQuery = Complaint::query();
        $this->filterComplaintsByLocation($complaintsQuery, $user);

        $totalComplaints = (clone $complaintsQuery)->count();
        if ($totalComplaints === 0)
            return 100;

        // Get complaints that are resolved and within SLA time limits
        $compliantComplaintsQuery = Complaint::query();
        $this->filterComplaintsByLocation($compliantComplaintsQuery, $user);

        $compliantComplaints = (clone $compliantComplaintsQuery)->where('status', 'resolved')
            ->whereHas('slaRule', function ($query) {
                $query->whereRaw('TIMESTAMPDIFF(HOUR, complaints.created_at, complaints.updated_at) <= sla_rules.max_resolution_time');
            })->count();

        return round(($compliantComplaints / $totalComplaints) * 100, 1);
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
            $recentComplaintsQuery = Complaint::with(['client', 'assignedEmployee']);
            $this->filterComplaintsByLocation($recentComplaintsQuery, $user);
            $recentComplaints = $recentComplaintsQuery->latest()
                ->limit(3)
                ->get();

            foreach ($recentComplaints as $complaint) {
                $activities->push([
                    'type' => 'complaint',
                    'title' => 'New complaint submitted',
                    'description' => $complaint->title,
                    'time' => $complaint->created_at->diffForHumans(),
                    'badge' => ucfirst($complaint->status),
                    'badge_class' => $this->getStatusBadgeClass($complaint->status)
                ]);
            }

            // Recent approvals with location filtering
            $recentApprovalsQuery = SpareApprovalPerforma::with(['requestedBy']);

            // Apply location filtering to approvals
            if ($user && !$this->canViewAllData($user)) {
                $recentApprovalsQuery->whereHas('complaint', function ($q) use ($user) {
                    $q->whereHas('client', function ($clientQ) use ($user) {
                        if ($user->city_id && $user->city) {
                            $clientQ->where('city', $user->city->name);
                        }
                        if ($user->sector_id && $user->sector) {
                            $clientQ->where('sector', $user->sector->name);
                        }
                    });
                });
            }

            $recentApprovals = $recentApprovalsQuery->latest()
                ->limit(2)
                ->get();

            foreach ($recentApprovals as $approval) {
                $activities->push([
                    'type' => 'approval',
                    'title' => 'Spare part approval',
                    'description' => 'Requested by ' . ($approval->requestedBy->name ?? 'Unknown'),
                    'time' => $approval->created_at->diffForHumans(),
                    'badge' => ucfirst($approval->status),
                    'badge_class' => $this->getApprovalBadgeClass($approval->status)
                ]);
            }

            // Recent spare part activities
            $recentSpares = Spare::where('stock_quantity', '<=', DB::raw('threshold_level'))
                ->latest('updated_at')
                ->limit(1)
                ->get();

            foreach ($recentSpares as $spare) {
                $activities->push([
                    'type' => 'spare',
                    'title' => 'Low stock alert',
                    'description' => $spare->item_name . ' is running low',
                    'time' => $spare->updated_at->diffForHumans(),
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
        $format = $request->format ?? 'html';

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
        $actualCategories = \App\Models\ComplaintCategory::orderBy('name')
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

        // Remove new and closed statuses from report
        unset($rows['new']);
        unset($rows['closed']);

        // Add performa-related statuses grouped by type
        $rows['work'] = 'Work';
        $rows['maintenance'] = 'Maintenance';
        $rows['work_priced_performa'] = 'Work Performa Priced';
        $rows['maint_priced_performa'] = 'Maintenance Performa Priced';
        $rows['product_na'] = 'Product N/A';
        $rows['un_authorized'] = 'Un-Authorized';
        $rows['pertains_to_ge_const_isld'] = 'Pertains to GE(N) Const Isld';

        // Base query for all complaints in date range
        // Note: $user already defined above
        $baseQuery = Complaint::whereBetween('created_at', [$dateFromStart, $dateToEnd]);

        // Apply location-based filtering
        $this->filterComplaintsByLocation($baseQuery, $user);

        // Initialize report data structure
        $reportData = [];
        $categoryTotals = [];
        $rowTotals = [];
        $grandTotal = 0;

        // Process each category from actual database
        foreach ($categories as $catKey => $catName) {
            $categoryTotals[$catKey] = 0;

            // Query for this specific category
            $catQuery = (clone $baseQuery)->where('category', $catName);

            // Eager load relationships safely
            try {
                $catComplaints = $catQuery->with(['spareParts', 'spareApprovals', 'client'])->get();
            } catch (\Exception $e) {
                // If relationships fail, load without them
                \Log::warning('Failed to eager load relationships: ' . $e->getMessage());
                $catComplaints = $catQuery->with(['client'])->get();
            }
            $catTotal = $catComplaints->count();
            $categoryTotals[$catKey] = $catTotal;
            $grandTotal += $catTotal;

            // Process each row for this category
            foreach ($rows as $rowKey => $rowName) {
                if (!isset($reportData[$rowKey])) {
                    $reportData[$rowKey] = ['name' => $rowName, 'categories' => []];
                    $rowTotals[$rowKey] = 0;
                }

                $count = 0;

                // Row mapping logic - based on complaint status (new and closed removed)
                if ($rowKey === 'assigned') {
                    $count = $catComplaints->where('status', 'assigned')->count();
                } elseif ($rowKey === 'in_progress') {
                    // Count complaints with in_progress status that do NOT have any performa type
                    // Exclude complaints that have work_performa, maint_performa, or product_na performa_type
                    $count = $catComplaints->filter(function ($complaint) {
                        if ($complaint->status !== 'in_progress') {
                            return false;
                        }
                        // Exclude if status is product_na, work_performa, maint_performa, or priced performa (should be counted separately)
                        if (in_array($complaint->status, ['product_na', 'work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa'])) {
                            return false;
                        }
                        // Check if complaint has any performa type set (work_performa, maint_performa, or product_na) - exclude rejected
                        $hasWorkPerforma = $complaint->spareApprovals->contains(function ($approval) {
                            return $approval->performa_type === 'work_performa' &&
                                $approval->status !== 'rejected';
                        });
                        $hasMaintPerforma = $complaint->spareApprovals->contains(function ($approval) {
                            return $approval->performa_type === 'maint_performa' &&
                                $approval->status !== 'rejected';
                        });
                        $hasProductNa = $complaint->spareApprovals->contains(function ($approval) {
                            return $approval->performa_type === 'product_na' &&
                                $approval->status !== 'rejected';
                        });
                        // Exclude if has any performa type
                        return !$hasWorkPerforma && !$hasMaintPerforma && !$hasProductNa;
                    })->count();
                } elseif ($rowKey === 'resolved') {
                    $count = $catComplaints->where('status', 'resolved')->count();
                } elseif ($rowKey === 'work') {
                    // Count complaints that have work_performa performa_type in spareApprovals
                    // Logic: If performa_type is set, use it; otherwise use complaint status
                    $count = $catComplaints->filter(function ($complaint) {
                        // First check complaint status - if it's work_performa, include it (simple performa)
                        if ($complaint->status === 'work_performa') {
                            // Exclude if status is work_priced_performa (should be in work_priced_performa row)
                            if ($complaint->status === 'work_priced_performa') {
                                return false;
                            }
                            return true;
                        }

                        // Get approval with performa_type
                        $approval = $complaint->spareApprovals->first();

                        // If performa_type is set, use it
                        if ($approval && $approval->performa_type === 'work_performa') {
                            // Exclude if status is work_priced_performa (should be in work_priced_performa row)
                            if ($complaint->status === 'work_priced_performa') {
                                return false;
                            }
                            // waiting_for_authority removed - only check status
                            // Exclude if status is rejected
                            if ($approval->status === 'rejected') {
                                return false;
                            }
                            return true;
                        }

                        return false;
                    })->count();
                } elseif ($rowKey === 'maintenance') {
                    // Count complaints that have maint_performa performa_type in spareApprovals
                    // Logic: If performa_type is set, use it; otherwise use complaint status
                    $count = $catComplaints->filter(function ($complaint) {
                        // First check complaint status - if it's maint_performa, include it (simple performa)
                        if ($complaint->status === 'maint_performa') {
                            // Exclude if status is maint_priced_performa (should be in maint_priced_performa row)
                            if ($complaint->status === 'maint_priced_performa') {
                                return false;
                            }
                            return true;
                        }

                        // Get approval with performa_type
                        $approval = $complaint->spareApprovals->first();

                        // If performa_type is set, use it
                        if ($approval && $approval->performa_type === 'maint_performa') {
                            // Exclude if status is maint_priced_performa (should be in maint_priced_performa row)
                            if ($complaint->status === 'maint_priced_performa') {
                                return false;
                            }
                            // waiting_for_authority removed - only check status
                            // Exclude if status is rejected
                            if ($approval->status === 'rejected') {
                                return false;
                            }
                            return true;
                        }

                        return false;
                    })->count();
                } elseif ($rowKey === 'work_priced_performa') {
                    // waiting_for_authority removed - only count direct status match
                    $count = $catComplaints->filter(function ($complaint) {
                        return $complaint->status === 'work_priced_performa';
                    })->count();
                } elseif ($rowKey === 'maint_priced_performa') {
                    // waiting_for_authority removed - only count direct status match
                    $count = $catComplaints->filter(function ($complaint) {
                        return $complaint->status === 'maint_priced_performa';
                    })->count();
                } elseif ($rowKey === 'product_na') {
                    // Count complaints that have product_na status OR product_na performa_type in spareApprovals
                    // Logic: If performa_type is set, use it; otherwise use complaint status
                    $count = $catComplaints->filter(function ($complaint) {
                        // Get approval with performa_type
                        $approval = $complaint->spareApprovals->first();

                        // If performa_type is set, use it
                        if ($approval && $approval->performa_type === 'product_na') {
                            // Exclude if status is rejected
                            if ($approval->status === 'rejected') {
                                return false;
                            }
                            return true;
                        }

                        // If performa_type is null, check complaint status
                        if ($complaint->status === 'product_na') {
                            return true;
                        }

                        return false;
                    })->count();
                } elseif ($rowKey === 'un_authorized') {
                    // Count complaints with un_authorized status
                    $count = $catComplaints->filter(function ($complaint) {
                        return $complaint->status === 'un_authorized';
                    })->count();
                } elseif ($rowKey === 'pertains_to_ge_const_isld') {
                    // Count complaints with pertains_to_ge_const_isld status
                    $count = $catComplaints->filter(function ($complaint) {
                        return $complaint->status === 'pertains_to_ge_const_isld';
                    })->count();
                }

                $percentage = $catTotal > 0 ? round(($count / $catTotal) * 100, 1) : 0;

                $reportData[$rowKey]['categories'][$catKey] = [
                    'count' => $count,
                    'percentage' => $percentage,
                ];

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
        $format = $request->format ?? 'html';

        // Validate only if parameters are provided
        if ($request->has('date_from') || $request->has('date_to')) {
            $request->validate([
                'date_from' => 'date',
                'date_to' => 'date|after_or_equal:date_from',
                'format' => 'nullable|in:html,pdf,excel',
            ]);
        }

        $user = Auth::user();
        $query = Employee::with([
            'assignedComplaints' => function ($q) use ($dateFrom, $dateTo, $user) {
                $q->whereBetween('created_at', [$dateFrom, $dateTo]);
                // Apply location filter to complaints - get the underlying query builder
                if ($user && !$this->canViewAllData($user)) {
                    if ($user->city_id && $user->city) {
                        $q->whereHas('client', function ($clientQ) use ($user) {
                            $clientQ->where('city', $user->city->name);
                        });
                    }
                    if ($user->sector_id && $user->sector) {
                        $q->whereHas('client', function ($clientQ) use ($user) {
                            $clientQ->where('sector', $user->sector->name);
                        });
                    }
                }
            }
        ]);

        // Apply location-based filtering to employees
        $this->filterEmployeesByLocation($query, $user);

        $employees = $query->get()->map(function ($employee) {
            $complaints = $employee->assignedComplaints;
            $resolved = $complaints->whereIn('status', ['resolved', 'closed']);

            return [
                'employee' => $employee,
                'total_complaints' => $complaints->count(),
                'resolved_complaints' => $resolved->count(),
                'resolution_rate' => $complaints->count() > 0 ? round(($resolved->count() / $complaints->count()) * 100, 2) : 0,
                'avg_resolution_time' => $resolved->avg(function ($complaint) {
                    return $complaint->created_at->diffInHours($complaint->updated_at);
                }) ?? 0,
            ];
        });

        $summary = [
            'total_employees' => $employees->count(),
            'avg_resolution_rate' => $employees->avg('resolution_rate'),
            'top_performer' => $employees->sortByDesc('resolution_rate')->first(),
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
        $format = $request->format ?? 'html';

        // Validate only if parameters are provided
        if ($request->has('date_from') || $request->has('date_to')) {
            $request->validate([
                'date_from' => 'date',
                'date_to' => 'date|after_or_equal:date_from',
                'category' => 'nullable|string',
                'format' => 'nullable|in:html,pdf,excel',
            ]);
        }

        $user = Auth::user();
        $query = Spare::query();

        // Apply location-based filtering
        $this->filterSparesByLocation($query, $user);

        if ($category) {
            $query->where('category', $category);
        }

        // Eager load complaint spares and stock logs safely
        try {
            $spares = $query->with([
                'complaintSpares' => function ($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('used_at', [$dateFrom, $dateTo]);
                },
                'stockLogs' => function ($q) use ($dateFrom, $dateTo) {
                    $q->where('change_type', 'out')
                        ->whereBetween('created_at', [$dateFrom, $dateTo]);
                }
            ])->get()->map(function ($spare) use ($dateFrom, $dateTo) {
                // Calculate usage from complaint_spares (for cost/usage count if needed)
                $usage = $spare->complaintSpares ?? collect();
                $complaintUsed = $usage->sum('quantity') ?? 0;

                // Calculate usage from stock logs (primary source for issued quantity)
                $stockLogs = $spare->stockLogs ?? collect();
                $stockOut = $stockLogs->sum('quantity') ?? 0;

                // Use stock logs for issued quantity as it tracks all 'out' movements
                $totalUsed = $stockOut;

                // Calculate cost based on total used
                $totalCost = $totalUsed * ($spare->unit_price ?? 0);

                // Usage count can be from logs count
                $usageCount = $stockLogs->count();

                return [
                    'spare' => $spare,
                    'total_used' => $totalUsed,
                    'total_cost' => $totalCost,
                    'usage_count' => $usageCount ?? 0,
                    'current_stock' => $spare->stock_quantity ?? 0,
                    'stock_status' => method_exists($spare, 'getStockStatusAttribute') ? $spare->getStockStatusAttribute() : 'in_stock',
                ];
            });
        } catch (\Exception $e) {
            // If relationship fails, load without it
            \Log::warning('Failed to load complaint spares or approvals: ' . $e->getMessage());
            $spares = $query->get()->map(function ($spare) {
                return [
                    'spare' => $spare,
                    'total_used' => 0,
                    'total_cost' => 0,
                    'usage_count' => 0,
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
        $format = $request->format ?? 'html';

        // Validate only if parameters are provided
        if ($request->has('date_from') || $request->has('date_to')) {
            $request->validate([
                'date_from' => 'date',
                'date_to' => 'date|after_or_equal:date_from',
                'format' => 'nullable|in:html,pdf,excel',
            ]);
        }

        $user = Auth::user();

        // Build spare costs query with location filtering
        $spareCostsQuery = DB::table('complaint_spares')
            ->join('spares', 'complaint_spares.spare_id', '=', 'spares.id')
            ->join('complaints', 'complaint_spares.complaint_id', '=', 'complaints.id')
            ->join('clients', 'complaints.client_id', '=', 'clients.id')
            ->whereBetween('complaint_spares.used_at', [$dateFrom, $dateTo]);

        // Apply location filtering
        if ($user && !$this->canViewAllData($user)) {
            if ($user->city_id && $user->city) {
                $spareCostsQuery->where('clients.city', $user->city->name);
            }
            if ($user->sector_id && $user->sector) {
                $spareCostsQuery->where('clients.sector', $user->sector->name);
            }
        }

        $spareCosts = $spareCostsQuery->selectRaw('spares.category, SUM(complaint_spares.quantity * spares.unit_price) as total_cost')
            ->groupBy('spares.category')
            ->get();

        // Approval costs - simplified approach (with location filtering if needed)
        $approvalQuery = SpareApprovalPerforma::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'approved');

        // Note: Approvals might not have direct location, filter if they're related to complaints
        // For now, show all approvals if user can view all, otherwise filter by complaint location
        if ($user && !$this->canViewAllData($user)) {
            $approvalQuery->whereHas('complaint', function ($q) use ($user) {
                $q->whereHas('client', function ($clientQ) use ($user) {
                    if ($user->city_id && $user->city) {
                        $clientQ->where('city', $user->city->name);
                    }
                    if ($user->sector_id && $user->sector) {
                        $clientQ->where('sector', $user->sector->name);
                    }
                });
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
        $totalApprovalsQuery = SpareApprovalPerforma::whereBetween('created_at', [$dateFrom, $dateTo]);
        $approvedApprovalsQuery = SpareApprovalPerforma::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'approved');

        // Apply location filtering to approval counts if needed
        if ($user && !$this->canViewAllData($user)) {
            $filterApprovals = function ($query) use ($user) {
                $query->whereHas('complaint', function ($q) use ($user) {
                    $q->whereHas('client', function ($clientQ) use ($user) {
                        if ($user->city_id && $user->city) {
                            $clientQ->where('city', $user->city->name);
                        }
                        if ($user->sector_id && $user->sector) {
                            $clientQ->where('sector', $user->sector->name);
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
     * Generate SLA reports
     */
    public function sla(Request $request)
    {
        // Make date parameters optional with defaults
        $dateFrom = $request->date_from ?? now()->subMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? now()->format('Y-m-d');
        $format = $request->format ?? 'html';

        // Get SLA rules
        $slaRules = \App\Models\SlaRule::all()->keyBy('complaint_type');

        $user = Auth::user();

        // Get complaints with SLA analysis
        $complaintsQuery = Complaint::whereBetween('created_at', [$dateFrom, $dateTo]);

        // Apply location-based filtering
        $this->filterComplaintsByLocation($complaintsQuery, $user);

        $complaints = $complaintsQuery->with(['client', 'assignedEmployee'])
            ->get()
            ->map(function ($complaint) use ($slaRules) {
                $ageInHours = $complaint->created_at->diffInHours(now());

                // Get SLA rule for this complaint type
                $slaRule = $slaRules->get($complaint->category);
                $maxResponseTime = $slaRule ? $slaRule->max_response_time : 24; // Default 24 hours
    
                $isOverdue = $ageInHours > $maxResponseTime;
                $timeRemaining = max(0, $maxResponseTime - $ageInHours);

                // Calculate urgency level
                $urgencyLevel = 'low';
                if ($isOverdue) {
                    $urgencyLevel = 'critical';
                } elseif ($timeRemaining <= $maxResponseTime * 0.25) {
                    $urgencyLevel = 'high';
                } elseif ($timeRemaining <= $maxResponseTime * 0.5) {
                    $urgencyLevel = 'medium';
                }

                return [
                    'complaint' => $complaint,
                    'age_hours' => $ageInHours,
                    'max_response_time' => $maxResponseTime,
                    'time_remaining' => $timeRemaining,
                    'is_overdue' => $isOverdue,
                    'sla_status' => $isOverdue ? 'breached' : 'within_sla',
                    'urgency_level' => $urgencyLevel,
                    'sla_rule' => $slaRule,
                ];
            });

        // Calculate summary statistics
        $summary = [
            'total_complaints' => $complaints->count(),
            'within_sla' => $complaints->where('sla_status', 'within_sla')->count(),
            'breached_sla' => $complaints->where('sla_status', 'breached')->count(),
            'sla_compliance_rate' => $complaints->count() > 0 ?
                round(($complaints->where('sla_status', 'within_sla')->count() / $complaints->count()) * 100, 2) : 0,
            'critical_urgent' => $complaints->where('urgency_level', 'critical')->count(),
            'high_priority' => $complaints->where('urgency_level', 'high')->count(),
            'average_resolution_time' => $complaints->filter(function ($complaintData) {
                return $complaintData['complaint']->status === 'resolved';
            })->avg('age_hours') ?? 0,
        ];

        // Get SLA rules summary
        $slaRulesSummary = $slaRules->map(function ($rule) use ($complaints) {
            $ruleComplaints = $complaints->filter(function ($complaintData) use ($rule) {
                return $complaintData['complaint']->category === $rule->complaint_type;
            });
            return [
                'rule' => $rule,
                'total_complaints' => $ruleComplaints->count(),
                'within_sla' => $ruleComplaints->where('sla_status', 'within_sla')->count(),
                'breached_sla' => $ruleComplaints->where('sla_status', 'breached')->count(),
                'compliance_rate' => $ruleComplaints->count() > 0 ?
                    round(($ruleComplaints->where('sla_status', 'within_sla')->count() / $ruleComplaints->count()) * 100, 2) : 0,
            ];
        });

        if ($format === 'html') {
            return view('admin.reports.sla', compact('complaints', 'summary', 'slaRulesSummary', 'dateFrom', 'dateTo'));
        } else {
            return $this->exportReport('sla', $complaints, $summary, $format);
        }
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        $stats = [
            'total_complaints' => Complaint::count(),
            'resolved_complaints' => Complaint::whereIn('status', ['resolved', 'closed'])->count(),
            'pending_complaints' => Complaint::pending()->count(),
            'overdue_complaints' => Complaint::overdue()->count(),
            'total_employees' => Employee::where('status', 'active')->count(),
            'total_clients' => Client::count(),
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
                $complaintsQuery = Complaint::where('created_at', '>=', now()->subDays($period));
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
                $employeesQuery = Employee::where('status', 'active');
                $this->filterEmployeesByLocation($employeesQuery, $user);
                $data = $employeesQuery->withCount([
                    'assignedComplaints' => function ($q) use ($period, $user) {
                        $q->where('created_at', '>=', now()->subDays($period))
                            ->whereIn('status', ['resolved', 'closed']);
                        // Apply location filter to complaints - using whereHas on the relation
                        if ($user && !$this->canViewAllData($user)) {
                            if ($user->city_id && $user->city) {
                                $q->whereHas('client', function ($clientQ) use ($user) {
                                    $clientQ->where('city', $user->city->name);
                                });
                            }
                            if ($user->sector_id && $user->sector) {
                                $q->whereHas('client', function ($clientQ) use ($user) {
                                    $clientQ->where('sector', $user->sector->name);
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

        $baseQuery = Complaint::whereBetween('created_at', [$dateFromStart, $dateToEnd]);

        // Apply location-based filtering
        $this->filterComplaintsByLocation($baseQuery, $user);
        switch ($groupBy) {
            case 'status':
                $data = (clone $baseQuery)->selectRaw('status, COUNT(*) as count')->groupBy('status')->get();
                break;
            case 'type':
                $data = (clone $baseQuery)->selectRaw('category, COUNT(*) as count')->groupBy('category')->get();
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
            case 'client':
                $data = (clone $baseQuery)->selectRaw('client_id, COUNT(*) as count')->groupBy('client_id')->get()
                    ->map(function ($item) {
                        $item->client = Client::find($item->client_id);
                        return $item;
                    });
                break;
            default:
                $data = (clone $baseQuery)->with(['client', 'assignedEmployee'])->get();
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
        $user = Auth::user();
        $query = Employee::with([
            'user',
            'assignedComplaints' => function ($q) use ($dateFrom, $dateTo, $user) {
                $q->whereBetween('created_at', [$dateFrom, $dateTo]);
                // Apply location filter to complaints - using whereHas on the relation
                if ($user && !$this->canViewAllData($user)) {
                    if ($user->city_id && $user->city) {
                        $q->whereHas('client', function ($clientQ) use ($user) {
                            $clientQ->where('city', $user->city->name);
                        });
                    }
                    if ($user->sector_id && $user->sector) {
                        $q->whereHas('client', function ($clientQ) use ($user) {
                            $clientQ->where('sector', $user->sector->name);
                        });
                    }
                }
            }
        ]);

        // Apply location-based filtering to employees
        $this->filterEmployeesByLocation($query, $user);

        // Filter by category if provided
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
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
        $category = $request->get('category');

        $user = Auth::user();
        $query = Spare::query();

        // Apply location-based filtering
        $this->filterSparesByLocation($query, $user);

        if ($category) {
            $query->where('category', $category);
        }

        $spares = $query->with([
            'complaintSpares' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('used_at', [$dateFrom, $dateTo]);
            },
            'stockLogs' => function ($q) use ($dateFrom, $dateTo) {
                $q->where('change_type', 'out')
                    ->whereBetween('created_at', [$dateFrom, $dateTo]);
            }
        ])->get()->map(function ($spare) {
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

        $spareCostsQuery = DB::table('complaint_spares')
            ->join('spares', 'complaint_spares.spare_id', '=', 'spares.id')
            ->join('complaints', 'complaint_spares.complaint_id', '=', 'complaints.id')
            ->join('clients', 'complaints.client_id', '=', 'clients.id')
            ->whereBetween('complaint_spares.used_at', [$dateFrom, $dateTo]);

        // Apply location filtering
        if ($user && !$this->canViewAllData($user)) {
            if ($user->city_id && $user->city) {
                $spareCostsQuery->where('clients.city', $user->city->name);
            }
            if ($user->sector_id && $user->sector) {
                $spareCostsQuery->where('clients.sector', $user->sector->name);
            }
        }

        $spareCosts = $spareCostsQuery->selectRaw('spares.category, SUM(complaint_spares.quantity * spares.unit_price) as total_cost')
            ->groupBy('spares.category')->get();
        $approvalCosts = SpareApprovalPerforma::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'approved')->get()->groupBy(function ($a) {
                return $a->created_at->format('Y-m');
            })
            ->map(function ($list) {
                return $list->count();
            });
        $summary = [
            'total_spare_costs' => $spareCosts->sum('total_cost'),
            'total_approvals' => SpareApprovalPerforma::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'approved_approvals' => SpareApprovalPerforma::whereBetween('created_at', [$dateFrom, $dateTo])->where('status', 'approved')->count(),
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
}
