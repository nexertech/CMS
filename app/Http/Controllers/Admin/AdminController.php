<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\Client;
use App\Models\Spare;
use App\Models\SpareApprovalPerforma;
use App\Traits\LocationFilterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    use LocationFilterTrait;
    
    public function __construct()
    {
        // Remove role:admin middleware as it may conflict with other roles
        // Individual methods can check permissions as needed
    }

    /**
     * Display the admin dashboard
     */
    public function dashboard()
    {
        // Get dashboard statistics
        $stats = $this->getDashboardStats();
        
        // Get recent complaints
        $recentComplaints = Complaint::with(['client', 'assignedEmployee'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get pending approvals
        $pendingApprovals = SpareApprovalPerforma::with(['complaint', 'requestedBy', 'items.spare'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get low stock items
        $lowStockItems = Spare::lowStock()
            ->orderBy('stock_quantity', 'asc')
            ->limit(10)
            ->get();

        // Get overdue complaints
        $overdueComplaints = Complaint::overdue()
            ->with(['client', 'assignedEmployee'])
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        // Get complaints by status
        $complaintsByStatus = Complaint::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Get complaints by type
        $complaintsByType = Complaint::selectRaw('complaint_type, COUNT(*) as count')
            ->groupBy('complaint_type')
            ->pluck('count', 'complaint_type')
            ->toArray();

        // Get monthly complaint trends
        $monthlyTrends = Complaint::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentComplaints',
            'pendingApprovals',
            'lowStockItems',
            'overdueComplaints',
            'complaintsByStatus',
            'complaintsByType',
            'monthlyTrends'
        ));
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats()
    {
        return [
            'total_complaints' => Complaint::count(),
            'pending_complaints' => Complaint::pending()->count(),
            'resolved_complaints' => Complaint::completed()->count(),
            'total_clients' => Client::count(),
            'active_employees' => Employee::where('status', 'active')->count(),
            'total_spares' => Spare::count(),
            'low_stock_items' => Spare::lowStock()->count(),
            'out_of_stock_items' => Spare::outOfStock()->count(),
        ];
    }

    /**
     * Get complaints summary for charts
     */
    public function getComplaintsSummary(Request $request)
    {
        $period = $request->get('period', '30'); // days
        
        $complaints = Complaint::where('created_at', '>=', now()->subDays($period))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($complaints);
    }

    /**
     * Get employee performance data
     */
    public function getEmployeePerformance()
    {
        $employees = Employee::where('status', 'active')
            ->get()
            ->map(function($employee) {
                $metrics = $employee->getPerformanceMetrics();
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'total_complaints' => $metrics['total_complaints'],
                    'resolved_complaints' => $metrics['resolved_complaints'],
                    'resolution_rate' => $metrics['resolution_rate'],
                ];
            });

        return response()->json($employees);
    }

    /**
     * Get spare parts consumption data
     */
    public function getSpareConsumption(Request $request)
    {
        $period = $request->get('period', '30'); // days
        
        $consumption = DB::table('complaint_spares')
            ->join('spares', 'complaint_spares.spare_id', '=', 'spares.id')
            ->where('complaint_spares.used_at', '>=', now()->subDays($period))
            ->selectRaw('spares.item_name, spares.category, SUM(complaint_spares.quantity) as total_quantity, SUM(complaint_spares.quantity * spares.unit_price) as total_cost')
            ->groupBy('spares.id', 'spares.item_name', 'spares.category')
            ->orderBy('total_quantity', 'desc')
            ->get();

        return response()->json($consumption);
    }

    /**
     * Get system health status
     */
    public function getSystemHealth()
    {
        $health = [
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->checkStorageHealth(),
            'overdue_complaints' => Complaint::overdue()->count(),
            'low_stock_alerts' => Spare::lowStock()->count(),
            'pending_approvals' => SpareApprovalPerforma::pending()->count(),
        ];

        return response()->json($health);
    }

    /**
     * Check database health
     */
    private function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Database connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => 'Database connection failed'];
        }
    }

    /**
     * Check storage health
     */
    private function checkStorageHealth()
    {
        $totalSpace = disk_total_space(storage_path());
        $freeSpace = disk_free_space(storage_path());
        $usedPercentage = (($totalSpace - $freeSpace) / $totalSpace) * 100;

        if ($usedPercentage > 90) {
            return ['status' => 'critical', 'message' => 'Storage space critically low'];
        } elseif ($usedPercentage > 80) {
            return ['status' => 'warning', 'message' => 'Storage space running low'];
        } else {
            return ['status' => 'healthy', 'message' => 'Storage space adequate'];
        }
    }

    /**
     * Export dashboard data
     */
    public function exportDashboard(Request $request)
    {
        $format = $request->get('format', 'excel');
        $data = $this->getDashboardStats();
        
        // Add additional data for export
        $data['complaints_by_status'] = Complaint::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
        
        $data['complaints_by_type'] = Complaint::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->get();

        if ($format === 'pdf') {
            return $this->exportToPdf($data);
        } else {
            return $this->exportToExcel($data);
        }
    }

    /**
     * Export to PDF
     */
    private function exportToPdf($data)
    {
        // Implementation for PDF export
        // You can use libraries like DomPDF or TCPDF
        return response()->json(['message' => 'PDF export not implemented yet']);
    }

    /**
     * Export to Excel
     */
    private function exportToExcel($data)
    {
        // Implementation for Excel export
        // You can use libraries like Laravel Excel
        return response()->json(['message' => 'Excel export not implemented yet']);
    }

    /**
     * Get notification count for admin
     */
    public function getNotificationCount()
    {
        $count = [
            'overdue_complaints' => Complaint::overdue()->count(),
            'pending_approvals' => SpareApprovalPerforma::pending()->count(),
            'low_stock_items' => Spare::lowStock()->count(),
            'new_complaints_today' => Complaint::whereDate('created_at', today())->count(),
        ];

        return response()->json($count);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(Request $request)
    {
        $type = $request->get('type');
        
        // Implementation for marking notifications as read
        // This could involve updating a notifications table or session
        
        return response()->json(['success' => true]);
    }

    /**
     * Display all notifications page
     */
    public function notificationsIndex()
    {
        $user = Auth::user();
        
        // Get all notifications (not limited to 10)
        $notifications = collect();

        // 1) New complaints today
        try {
            $newComplaintsQuery = Complaint::with(['client'])
                ->orderBy('created_at', 'desc')
                ->whereDate('created_at', today());
            
            if ($user && !$this->canViewAllData($user)) {
                $this->filterComplaintsByLocation($newComplaintsQuery, $user);
            }
            
            $newComplaints = $newComplaintsQuery->get()
                ->filter(function($c) {
                    return $c->client !== null;
                })
                ->map(function($c) {
                    try {
                        return [
                            'id' => 'complaint-'.$c->id,
                            'title' => 'New Complaint',
                            'message' => ($c->client && $c->client->client_name ? $c->client->client_name : 'Client').': '.($c->title ?? 'N/A'),
                            'type' => 'info',
                            'icon' => 'alert-circle',
                            'time' => $c->created_at ? $c->created_at->diffForHumans() : 'Just now',
                            'timestamp' => $c->created_at ? $c->created_at->timestamp : time(),
                            'read' => false,
                            'url' => route('admin.complaints.show', $c->id),
                        ];
                    } catch (\Exception $e) {
                        return null;
                    }
                })
                ->filter();
        } catch (\Exception $e) {
            $newComplaints = collect();
        }

        // 2) Pending approvals
        try {
            $pendingApprovalsQuery = SpareApprovalPerforma::with(['complaint'])
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc');
            
            if ($user && !$this->canViewAllData($user)) {
                $pendingApprovalsQuery->whereHas('complaint', function($q) use ($user) {
                    $this->filterComplaintsByLocation($q, $user);
                });
            }
            
            $pendingApprovals = $pendingApprovalsQuery->get()
                ->filter(function($a) {
                    return $a->complaint !== null;
                })
                ->map(function($a) {
                    try {
                        return [
                            'id' => 'approval-'.$a->id,
                            'title' => 'Approval Pending',
                            'message' => 'Performa #'.$a->id.' awaiting action',
                            'type' => 'warning',
                            'icon' => 'check-circle',
                            'time' => $a->created_at ? $a->created_at->diffForHumans() : 'Just now',
                            'timestamp' => $a->created_at ? $a->created_at->timestamp : time(),
                            'read' => false,
                            'url' => route('admin.approvals.show', $a->id),
                        ];
                    } catch (\Exception $e) {
                        return null;
                    }
                })
                ->filter();
        } catch (\Exception $e) {
            $pendingApprovals = collect();
        }

        // 3) Low stock spares
        try {
            if (method_exists(Spare::class, 'lowStock')) {
                $lowStockQuery = Spare::lowStock()->orderBy('stock_quantity', 'asc');
            } else {
                $lowStockQuery = Spare::whereColumn('stock_quantity', '<=', 'threshold_level')
                    ->orderBy('stock_quantity', 'asc');
            }
            
            if ($user && !$this->canViewAllData($user)) {
                $this->filterSparesByLocation($lowStockQuery, $user);
            }
            
            $lowStock = $lowStockQuery->get()
                ->map(function($s) {
                    try {
                        return [
                            'id' => 'spare-'.$s->id,
                            'title' => 'Low Stock',
                            'message' => ($s->item_name ?? 'Item').' stock at '.($s->stock_quantity ?? 0),
                            'type' => 'danger',
                            'icon' => 'package',
                            'time' => now()->diffForHumans(),
                            'timestamp' => time(),
                            'read' => false,
                            'url' => route('admin.spares.show', $s->id),
                        ];
                    } catch (\Exception $e) {
                        return null;
                    }
                })
                ->filter();
        } catch (\Exception $e) {
            $lowStock = collect();
        }

        // 4) Overdue complaints
        try {
            if (method_exists(Complaint::class, 'overdue')) {
                $overdueQuery = Complaint::overdue()->with(['client'])->orderBy('created_at', 'asc');
            } else {
                $overdueQuery = Complaint::with(['client'])
                    ->where('status', '!=', 'resolved')
                    ->where('created_at', '<', now()->subDays(7))
                    ->orderBy('created_at', 'asc');
            }
            
            if ($user && !$this->canViewAllData($user)) {
                $this->filterComplaintsByLocation($overdueQuery, $user);
            }
            
            $overdue = $overdueQuery->get()
                ->filter(function($c) {
                    return $c->client !== null;
                })
                ->map(function($c) {
                    try {
                        return [
                            'id' => 'overdue-'.$c->id,
                            'title' => 'Overdue Complaint',
                            'message' => ($c->client && $c->client->client_name ? $c->client->client_name : 'Client').': '.($c->title ?? 'N/A'),
                            'type' => 'danger',
                            'icon' => 'clock',
                            'time' => $c->created_at ? $c->created_at->diffForHumans() : 'Just now',
                            'timestamp' => $c->created_at ? $c->created_at->timestamp : time(),
                            'read' => false,
                            'url' => route('admin.complaints.show', $c->id),
                        ];
                    } catch (\Exception $e) {
                        return null;
                    }
                })
                ->filter();
        } catch (\Exception $e) {
            $overdue = collect();
        }

        $notifications = $notifications
            ->merge($newComplaints)
            ->merge($pendingApprovals)
            ->merge($lowStock)
            ->merge($overdue)
            ->filter(function($n) {
                return isset($n['id']) && isset($n['time']);
            })
            ->sortByDesc(function($n) {
                return $n['timestamp'] ?? time();
            })
            ->values();

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'totalCount' => $notifications->count(),
        ]);
    }

    /**
     * Get latest notifications for the topbar dropdown
     */
    public function getNotifications(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Build a simple aggregated notification feed (max 10)
            $notifications = collect();

            // 1) New complaints today
            try {
                $newComplaintsQuery = Complaint::with(['client'])
                    ->orderBy('created_at', 'desc')
                    ->whereDate('created_at', today());
                
                // Apply location filtering if user is not admin/director
                if ($user && !$this->canViewAllData($user)) {
                    $this->filterComplaintsByLocation($newComplaintsQuery, $user);
                }
                
                $newComplaints = $newComplaintsQuery->limit(5)->get()
                    ->filter(function($c) {
                        return $c->client !== null; // Filter out complaints with deleted clients
                    })
                    ->map(function($c) {
                        try {
                            return [
                                'id' => 'complaint-'.$c->id,
                                'title' => 'New Complaint',
                                'message' => ($c->client && $c->client->client_name ? $c->client->client_name : 'Client').': '.($c->title ?? 'N/A'),
                                'type' => 'info',
                                'icon' => 'alert-circle',
                                'time' => $c->created_at ? $c->created_at->diffForHumans() : 'Just now',
                                'read' => false,
                                'url' => route('admin.complaints.show', $c->id),
                            ];
                        } catch (\Exception $e) {
                            \Log::warning('Error mapping complaint notification', ['complaint_id' => $c->id, 'error' => $e->getMessage()]);
                            return null;
                        }
                    })
                    ->filter(); // Remove null entries
            } catch (\Exception $e) {
                \Log::error('Error loading new complaints for notifications', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $newComplaints = collect();
            }

            // 2) Pending approvals
            try {
                $pendingApprovalsQuery = SpareApprovalPerforma::with(['complaint'])
                    ->where('status', 'pending')
                    ->orderBy('created_at', 'desc');
                
                // Apply location filtering if user is not admin/director
                if ($user && !$this->canViewAllData($user)) {
                    $pendingApprovalsQuery->whereHas('complaint', function($q) use ($user) {
                        $this->filterComplaintsByLocation($q, $user);
                    });
                }
                
                $pendingApprovals = $pendingApprovalsQuery->limit(5)->get()
                    ->filter(function($a) {
                        return $a->complaint !== null; // Filter out approvals with deleted complaints
                    })
                    ->map(function($a) {
                        try {
                            return [
                                'id' => 'approval-'.$a->id,
                                'title' => 'Approval Pending',
                                'message' => 'Performa #'.$a->id.' awaiting action',
                                'type' => 'warning',
                                'icon' => 'check-circle',
                                'time' => $a->created_at ? $a->created_at->diffForHumans() : 'Just now',
                                'read' => false,
                                'url' => route('admin.approvals.show', $a->id),
                            ];
                        } catch (\Exception $e) {
                            \Log::warning('Error mapping approval notification', ['approval_id' => $a->id, 'error' => $e->getMessage()]);
                            return null;
                        }
                    })
                    ->filter(); // Remove null entries
            } catch (\Exception $e) {
                \Log::error('Error loading pending approvals for notifications', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $pendingApprovals = collect();
            }

            // 3) Low stock spares
            try {
                // Check if lowStock() method exists, otherwise use manual query
                if (method_exists(Spare::class, 'lowStock')) {
                    $lowStockQuery = Spare::lowStock()->orderBy('stock_quantity', 'asc');
                } else {
                    // Fallback: manually query low stock items
                    $lowStockQuery = Spare::whereColumn('stock_quantity', '<=', 'threshold_level')
                        ->orderBy('stock_quantity', 'asc');
                }
                
                // Apply location filtering if user is not admin/director
                if ($user && !$this->canViewAllData($user)) {
                    $this->filterSparesByLocation($lowStockQuery, $user);
                }
                
                $lowStock = $lowStockQuery->limit(5)->get();
                
                $lowStock = $lowStock->map(function($s) {
                    try {
                        return [
                            'id' => 'spare-'.$s->id,
                            'title' => 'Low Stock',
                            'message' => ($s->item_name ?? 'Item').' stock at '.($s->stock_quantity ?? 0),
                            'type' => 'danger',
                            'icon' => 'package',
                            'time' => now()->diffForHumans(),
                            'read' => false,
                            'url' => route('admin.spares.show', $s->id),
                        ];
                    } catch (\Exception $e) {
                        \Log::warning('Error mapping low stock notification', ['spare_id' => $s->id, 'error' => $e->getMessage()]);
                        return null;
                    }
                })
                ->filter(); // Remove null entries
            } catch (\Exception $e) {
                \Log::error('Error loading low stock for notifications', ['error' => $e->getMessage()]);
                $lowStock = collect();
            }

            // 4) Overdue complaints
            try {
                // Check if overdue() method exists, otherwise use manual query
                if (method_exists(Complaint::class, 'overdue')) {
                    $overdueQuery = Complaint::overdue()->with(['client'])->orderBy('created_at', 'asc');
                } else {
                    // Fallback: manually query overdue complaints
                    $overdueQuery = Complaint::with(['client'])
                        ->where('status', '!=', 'resolved')
                        ->where('created_at', '<', now()->subDays(7))
                        ->orderBy('created_at', 'asc');
                }
                
                // Apply location filtering if user is not admin/director
                if ($user && !$this->canViewAllData($user)) {
                    $this->filterComplaintsByLocation($overdueQuery, $user);
                }
                
                $overdue = $overdueQuery->limit(5)->get();
                
                $overdue = $overdue->filter(function($c) {
                    return $c->client !== null; // Filter out complaints with deleted clients
                })
                ->map(function($c) {
                    try {
                        return [
                            'id' => 'overdue-'.$c->id,
                            'title' => 'Overdue Complaint',
                            'message' => ($c->client && $c->client->client_name ? $c->client->client_name : 'Client').': '.($c->title ?? 'N/A'),
                            'type' => 'danger',
                            'icon' => 'clock',
                            'time' => $c->created_at ? $c->created_at->diffForHumans() : 'Just now',
                            'read' => false,
                            'url' => route('admin.complaints.show', $c->id),
                        ];
                    } catch (\Exception $e) {
                        \Log::warning('Error mapping overdue complaint notification', ['complaint_id' => $c->id, 'error' => $e->getMessage()]);
                        return null;
                    }
                })
                ->filter(); // Remove null entries
            } catch (\Exception $e) {
                \Log::error('Error loading overdue complaints for notifications', ['error' => $e->getMessage()]);
                $overdue = collect();
            }

            $notifications = $notifications
                ->merge($newComplaints)
                ->merge($pendingApprovals)
                ->merge($lowStock)
                ->merge($overdue)
                ->filter(function($n) {
                    return isset($n['id']) && isset($n['time']); // Ensure required fields exist
                })
                ->sortByDesc(function($n) {
                    try {
                        // Try to parse time, fallback to timestamp
                        $time = $n['time'] ?? 'Just now';
                        if (is_string($time)) {
                            return strtotime($time) ?: time();
                        }
                        return time();
                    } catch (\Exception $e) {
                        return time();
                    }
                })
                ->values()
                ->take(10);

            return response()->json([
                'unread' => $notifications->count(),
                'notifications' => $notifications->values()->all(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getNotifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'unread' => 0,
                'notifications' => [],
                'error' => 'Failed to load notifications'
            ], 500);
        }
    }
}
