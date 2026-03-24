<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Employee;
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
        $recentComplaints = Complaint::with(['assignedEmployee'])
            ->orderBy('complaints.created_at', 'desc')
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
            ->with(['assignedEmployee'])
            ->orderBy('complaints.created_at', 'asc')
            ->limit(10)
            ->get();

        // Get complaints by status
        $complaintsByStatus = Complaint::selectRaw('status, COUNT(*) as count')
            ->groupBy('complaints.status')
            ->pluck('count', 'status')
            ->toArray();

        // Get complaints by type
        $complaintsByType = Complaint::selectRaw('complaint_type, COUNT(*) as count')
            ->groupBy('complaints.complaint_type')
            ->pluck('count', 'complaint_type')
            ->toArray();

        // Get monthly complaint trends
        $monthlyTrends = Complaint::selectRaw('DATE_FORMAT(complaints.created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('complaints.created_at', '>=', now()->subMonths(12))
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
            'active_employees' => Employee::where('status', 1)->count(),
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
        $employees = Employee::where('status', 1)
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


}
