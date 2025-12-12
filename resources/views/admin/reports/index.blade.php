@extends('layouts.sidebar')

@section('title', 'Reports — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Reports & Analytics</h2>
      <p class="text-light mb-0">Generate comprehensive reports and analytics</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary" onclick="refreshReportData()">
        <i data-feather="refresh-cw" class="me-2"></i>Refresh Data
      </button>
    </div>
  </div>
</div>

<!-- REPORT CARDS -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="card-glass text-center h-100">
      <div class="mb-3">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
          <i data-feather="alert-circle" class="feather-lg text-white" style="width: 28px; height: 28px;"></i>
        </div>
      </div>
      <h4 class="text-white mb-2">Complaints Report</h4>
      <p class="text-muted mb-3" style="font-size: 0.9rem;">Performance analysis and statistics</p>
      <a href="{{ route('admin.reports.complaints') }}" class="btn btn-outline-primary btn-sm">
        <i data-feather="file-text" class="me-2" style="width: 14px; height: 14px;"></i>View Report
      </a>
    </div>
  </div>
  
  <div class="col-md-3">
    <div class="card-glass text-center h-100">
      <div class="mb-3">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
          <i data-feather="users" class="feather-lg text-white" style="width: 28px; height: 28px;"></i>
        </div>
      </div>
      <h4 class="text-white mb-2">Employee Report</h4>
      <p class="text-muted mb-3" style="font-size: 0.9rem;">Employee performance and attendance</p>
      <a href="{{ route('admin.reports.employees') }}" class="btn btn-outline-success btn-sm">
        <i data-feather="file-text" class="me-2" style="width: 14px; height: 14px;"></i>View Report
      </a>
    </div>
  </div>
  
  <div class="col-md-3">
    <div class="card-glass text-center h-100">
      <div class="mb-3">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
          <i data-feather="package" class="feather-lg text-white" style="width: 28px; height: 28px;"></i>
        </div>
      </div>
      <h4 class="text-white mb-2">Store Products Report</h4>
      <p class="text-muted mb-3" style="font-size: 0.9rem;">Inventory and usage statistics</p>
      <a href="{{ route('admin.reports.spares') }}" class="btn btn-outline-warning btn-sm">
        <i data-feather="file-text" class="me-2" style="width: 14px; height: 14px;"></i>View Report
      </a>
    </div>
  </div>
  
  <div class="col-md-3">
    <div class="card-glass text-center h-100">
      <div class="mb-3">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #ec4899, #db2777); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
          <i data-feather="clock" class="feather-lg text-white" style="width: 28px; height: 28px;"></i>
        </div>
      </div>
      <h4 class="text-white mb-2">SLA Report</h4>
      <p class="text-muted mb-3" style="font-size: 0.9rem;">Service level agreement compliance</p>
      <a href="{{ route('admin.reports.sla') }}" class="btn btn-outline-danger btn-sm">
        <i data-feather="file-text" class="me-2" style="width: 14px; height: 14px;"></i>View Report
      </a>
    </div>
  </div>
</div>

<!-- QUICK STATS -->
<div class="row g-4 mb-4">
  <div class="col-md-8">
    <div class="card-glass mb-4">
      <div class="card-header mb-3">
        <h5 class="text-white mb-0">
          <i data-feather="bar-chart-2" class="me-2"></i>Quick Statistics
        </h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <!-- Row 1: 3 items -->
          <div class="col-md-4 col-6">
            <div class="text-center p-3" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); border-radius: 8px; border: none;">
              <div class="h3 mb-1 fw-bold" style="color: #ffffff !important;">{{ $stats['total_complaints_this_month'] ?? 0 }}</div>
              <div class="small" style="color: #ffffff !important; opacity: 0.95;">Total Complaints This Month</div>
            </div>
          </div>
          <div class="col-md-4 col-6">
            <div class="text-center p-3" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); border-radius: 8px; border: none;">
              <div class="h3 mb-1 fw-bold" style="color: #ffffff !important;">{{ $stats['resolved_this_month'] ?? 0 }}</div>
              <div class="small" style="color: #ffffff !important; opacity: 0.95;">Resolved This Month</div>
            </div>
          </div>
          <div class="col-md-4 col-6">
            <div class="text-center p-3" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border-radius: 8px; border: none;">
              <div class="h3 mb-1 fw-bold" style="color: #ffffff !important;">{{ $stats['active_employees'] ?? 0 }}</div>
              <div class="small" style="color: #ffffff !important; opacity: 0.95;">Active Employees</div>
            </div>
          </div>
          
          <!-- Row 2: 3 items -->
          <div class="col-md-4 col-6">
            <div class="text-center p-3" style="background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%); border-radius: 8px; border: none;">
              <div class="h3 mb-1 fw-bold" style="color: #ffffff !important;">{{ $stats['total_spares'] ?? 0 }}</div>
              <div class="small" style="color: #ffffff !important; opacity: 0.95;">Total Products</div>
            </div>
          </div>
          <div class="col-md-4 col-6">
            <div class="text-center p-3" style="background: linear-gradient(135deg, #fb923c 0%, #f97316 100%); border-radius: 8px; border: none;">
              <div class="h3 mb-1 fw-bold" style="color: #ffffff !important;">{{ $stats['low_stock_items'] ?? 0 }}</div>
              <div class="small" style="color: #ffffff !important; opacity: 0.95;">Low Stock Items</div>
            </div>
          </div>
          <div class="col-md-4 col-6">
            <div class="text-center p-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 8px; border: none;">
              <div class="h3 mb-1 fw-bold" style="color: #ffffff !important;">{{ $stats['employee_performance'] ?? 0 }}%</div>
              <div class="small" style="color: #ffffff !important; opacity: 0.95;">Avg Performance</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- REPORT FILTERS -->
    <div class="card-glass" style="max-width: 1100px;">
      <div class="card-header mb-3">
        <h5 class="text-white mb-0">
          <i data-feather="filter" class="me-2"></i>Generate Custom Report
        </h5>
      </div>
      <div class="card-body">
        <form id="customReportForm">
          <div class="row g-2 align-items-end">
            <div class="col-auto">
              <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">Report Type</label>
              <select name="report_type" class="form-select" style="font-size: 0.9rem; width: 180px;" required>
                <option value="">Select Type</option>
                <option value="complaints">Complaints</option>
                <option value="employees">Employees</option>
                <option value="spares">Store Products</option>
                <option value="sla">SLA</option>
              </select>
            </div>
            <div class="col-auto">
              <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">From Date</label>
              <input type="date" name="date_from" class="form-control" style="font-size: 0.9rem; width: 150px;" required>
            </div>
            <div class="col-auto">
              <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">To Date</label>
              <input type="date" name="date_to" class="form-control" style="font-size: 0.9rem; width: 150px;" required>
            </div>
            <div class="col-auto">
              <label class="form-label small text-muted mb-1" style="font-size: 0.8rem;">&nbsp;</label>
              <button type="submit" class="btn btn-accent btn-sm" style="font-size: 0.9rem; padding: 0.35rem 0.8rem;">
                <i data-feather="file-text" class="me-1" style="width: 14px; height: 14px;"></i>Generate
              </button>
            </div>
            <div class="col-auto">
              <label class="form-label small text-muted mb-1" style="font-size: 0.8rem;">&nbsp;</label>
              <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetCustomReportForm()" style="font-size: 0.9rem; padding: 0.35rem 0.8rem;">
                <i data-feather="refresh-cw" class="me-1" style="width: 14px; height: 14px;"></i>Reset
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="card-glass h-100">
      <div class="card-header mb-3">
        <h5 class="text-white mb-0">
          <i data-feather="activity" class="me-2"></i>Recent Activity
        </h5>
      </div>
      <div class="card-body" style="max-height: 400px; overflow-y: auto; padding: 1rem !important;">
        <div class="list-group list-group-flush">
          @forelse($recentActivity as $activity)
          <div class="list-group-item py-2 mb-2" style="background: transparent !important; border: none !important; border-bottom: 1px solid rgba(0,0,0,0.1) !important; padding-left: 0 !important; padding-right: 0 !important;">
            <div class="d-flex justify-content-between align-items-start" style="gap: 10px;">
              <div class="flex-grow-1" style="min-width: 0;">
                <div class="fw-bold mb-1" style="font-size: 0.9rem; color: #000000 !important;">{{ $activity['title'] }}</div>
                <small class="d-block mb-1" style="font-size: 0.8rem; color: rgba(0,0,0,0.85) !important;">{{ $activity['description'] }}</small>
                <small style="font-size: 0.75rem; color: rgba(0,0,0,0.7) !important;">{{ $activity['time'] }}</small>
              </div>
              <span class="badge bg-{{ $activity['badge_class'] }} ms-2" style="flex-shrink: 0; white-space: nowrap;">{{ $activity['badge'] }}</span>
            </div>
          </div>
          @empty
          <div class="text-center py-4">
            <i data-feather="inbox" class="feather-lg mb-2" style="color: rgba(0,0,0,0.5) !important;"></i>
            <div style="color: rgba(0,0,0,0.7) !important;">No recent activity</div>
          </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  feather.replace();


  document.addEventListener('DOMContentLoaded', function() {
    // Custom Report Form
    const customReportForm = document.getElementById('customReportForm');
    if (customReportForm) {
      customReportForm.addEventListener('submit', function(e) {
        e.preventDefault();
        generateCustomReport();
      });
    }
    
    feather.replace();
  });

  // Reset custom report form
  function resetCustomReportForm() {
    const form = document.getElementById('customReportForm');
    if (!form) return;
    
    // Clear all form inputs
    form.querySelectorAll('input[type="date"], select').forEach(input => {
      if (input.type === 'select-one') {
        input.selectedIndex = 0;
      } else {
        input.value = '';
      }
    });
  }

  function refreshReportData() {
    // Show loading state
    const btn = document.querySelector('.btn-accent');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i data-feather="loader" class="me-2"></i>Refreshing...';
    btn.disabled = true;
    feather.replace();

    // Refresh the page data
    setTimeout(() => {
      location.reload();
    }, 1500);
  }

  function generateCustomReport() {
    const form = document.getElementById('customReportForm');
    const formData = new FormData(form);
    
    const reportType = formData.get('report_type');
    const dateFrom = formData.get('date_from');
    const dateTo = formData.get('date_to');

    if (!reportType || !dateFrom || !dateTo) {
      showNotification('Please fill in all fields', 'error');
      return;
    }

    // Show loading
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i data-feather="loader" class="me-2"></i>Generating...';
    submitBtn.disabled = true;
    feather.replace();

    // Generate report based on type
    setTimeout(() => {
      const reportUrl = getReportUrl(reportType, dateFrom, dateTo);
      window.open(reportUrl, '_blank');
      
      // Reset button
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
      feather.replace();
      
      showNotification('Custom report generated successfully!', 'success');
    }, 2000);
  }

  function getReportUrl(type, from, to) {
    const baseUrl = window.location.origin + '/admin/reports/';
    const params = new URLSearchParams({
      date_from: from,
      date_to: to
    });
    
    switch(type) {
      case 'complaints':
        return `${baseUrl}complaints?${params.toString()}`;
      case 'employees':
        return `${baseUrl}employees?${params.toString()}`;
      case 'spares':
        return `${baseUrl}spares?${params.toString()}`;
      case 'sla':
        return `${baseUrl}sla?${params.toString()}`;
      default:
        return baseUrl;
    }
  }

  

  function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 3000);
  }
</script>
@endpush
