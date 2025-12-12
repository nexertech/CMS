@extends('layouts.sidebar')

@section('title', 'Approval Workflow — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Approval Workflow</h2>
      <p class="text-light">Track approval status and workflow progress</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary" onclick="refreshWorkflow()">
        <i data-feather="refresh-cw" class="me-2"></i>Refresh
      </button>
      <a href="{{ route('admin.approvals.index') }}" class="btn btn-outline-primary">
        <i data-feather="list" class="me-2"></i>View All Approvals
      </a>
    </div>
  </div>
</div>

<!-- WORKFLOW STATS -->
<div class="row mb-4">
  <div class="col-md-3">
    <div class="card-glass text-center">
      <div class="h3 mb-1 text-primary" id="total-approvals">0</div>
      <div class="text-muted">Total Approvals</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card-glass text-center">
      <div class="h3 mb-1 text-warning" id="pending-approvals">0</div>
      <div class="text-muted">Pending</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card-glass text-center">
      <div class="h3 mb-1 text-success" id="approved-approvals">0</div>
      <div class="text-muted">Approved</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card-glass text-center">
      <div class="h3 mb-1 text-danger" id="overdue-approvals">0</div>
      <div class="text-muted">Overdue</div>
    </div>
  </div>
</div>

<!-- WORKFLOW FILTERS -->
<div class="card-glass mb-4">
  <div class="row g-3">
    <div class="col-md-3">
      <select class="form-select" id="status-filter">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
      </select>
    </div>
    <div class="col-md-3">
      <select class="form-select" id="priority-filter">
        <option value="">All Priorities</option>
        <option value="critical">Critical</option>
        <option value="high">High</option>
        <option value="medium">Medium</option>
        <option value="low">Low</option>
      </select>
    </div>
    <div class="col-md-3">
      <input type="date" class="form-control" id="date-filter" placeholder="Filter by date">
    </div>
    <div class="col-md-3">
      <button class="btn btn-outline-secondary w-100" onclick="applyFilters()">
        <i data-feather="filter" class="me-2"></i>Apply Filters
      </button>
    </div>
  </div>
</div>

<!-- WORKFLOW TIMELINE -->
<div class="card-glass">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 text-white">Approval Workflow Timeline</h5>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-primary btn-sm" onclick="loadWorkflowData()">
        <i data-feather="refresh-cw" class="me-1"></i>Refresh
      </button>
      <button class="btn btn-outline-secondary btn-sm" onclick="toggleView()">
        <i data-feather="grid" class="me-1"></i>Toggle View
      </button>
    </div>
  </div>
  
  <div id="workflow-timeline">
    <div class="text-center py-4">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
  </div>
</div>

<!-- WORKFLOW DETAILS MODAL -->
<div class="modal fade" id="workflowDetailsModal" tabindex="-1" aria-labelledby="workflowDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="workflowDetailsModalLabel">Workflow Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="workflowDetailsModalBody">
        <div class="text-center">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
@endpush

@push('scripts')
<script>
  feather.replace();
  
  let currentView = 'timeline';
  let workflowData = [];

  // Load workflow data
  function loadWorkflowData() {
    fetch('/admin/approvals/workflow-data', {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        workflowData = data.workflow;
        updateStats(data.stats);
        renderWorkflow();
      } else {
        showError('Failed to load workflow data');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showError('Error loading workflow data');
    });
  }

  // Update statistics
  function updateStats(stats) {
    document.getElementById('total-approvals').textContent = stats.total || 0;
    document.getElementById('pending-approvals').textContent = stats.pending || 0;
    document.getElementById('approved-approvals').textContent = stats.approved || 0;
    document.getElementById('overdue-approvals').textContent = stats.overdue || 0;
  }

  // Render workflow
  function renderWorkflow() {
    const container = document.getElementById('workflow-timeline');
    
    if (currentView === 'timeline') {
      container.innerHTML = renderTimelineView();
    } else {
      container.innerHTML = renderGridView();
    }
    
    feather.replace();
  }

  // Render timeline view
  function renderTimelineView() {
    if (workflowData.length === 0) {
      return `
        <div class="text-center py-4">
          <i data-feather="inbox" class="feather-lg mb-2"></i>
          <div>No workflow data available</div>
        </div>
      `;
    }

    return `
      <div class="workflow-timeline">
        ${workflowData.map(item => `
          <div class="workflow-item ${item.status} ${item.priority}">
            <div class="workflow-dot"></div>
            <div class="d-flex justify-content-between align-items-start">
              <div class="flex-grow-1">
                <div class="d-flex align-items-center mb-2">
                  <h6 class="mb-0 text-white">#${item.id} - ${item.client_name}</h6>
                  <span class="badge bg-${item.status === 'pending' ? 'warning' : item.status === 'approved' ? 'success' : 'danger'} ms-2">
                    ${item.status.charAt(0).toUpperCase() + item.status.slice(1)}
                  </span>
                  <span class="badge priority-${item.priority} ms-2">${item.priority}</span>
                </div>
                <p class="text-muted mb-2">${item.description}</p>
                <div class="d-flex align-items-center text-muted small">
                  <i data-feather="user" class="me-1"></i>
                  <span class="me-3">${item.requested_by}</span>
                  <i data-feather="clock" class="me-1"></i>
                  <span>${item.time_ago}</span>
                </div>
              </div>
              <div class="text-end">
                <div class="text-white fw-bold">-</div>
                <div class="text-muted small">${item.items_count} items</div>
                <button class="btn btn-outline-info btn-sm mt-2" onclick="viewWorkflowDetails(${item.id})">
                  <i data-feather="eye"></i>
                </button>
              </div>
            </div>
          </div>
        `).join('')}
      </div>
    `;
  }

  // Render grid view
  function renderGridView() {
    if (workflowData.length === 0) {
      return `
        <div class="text-center py-4">
          <i data-feather="inbox" class="feather-lg mb-2"></i>
          <div>No workflow data available</div>
        </div>
      `;
    }

    return `
      <div class="row">
        ${workflowData.map(item => `
          <div class="col-md-6 col-lg-4 mb-3">
            <div class="card bg-dark h-100">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h6 class="text-white mb-0">#${item.id}</h6>
                  <span class="badge bg-${item.status === 'pending' ? 'warning' : item.status === 'approved' ? 'success' : 'danger'}">
                    ${item.status.charAt(0).toUpperCase() + item.status.slice(1)}
                  </span>
                </div>
                <p class="text-muted small mb-2">${item.client_name}</p>
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="text-white fw-bold">-</div>
                    <div class="text-muted small">${item.items_count} items</div>
                  </div>
                  <button class="btn btn-outline-info btn-sm" onclick="viewWorkflowDetails(${item.id})">
                    <i data-feather="eye"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        `).join('')}
      </div>
    `;
  }

  // Toggle view
  function toggleView() {
    currentView = currentView === 'timeline' ? 'grid' : 'timeline';
    renderWorkflow();
  }

  // View workflow details
  function viewWorkflowDetails(approvalId) {
    const modal = new bootstrap.Modal(document.getElementById('workflowDetailsModal'));
    modal.show();
    
    // Load details via AJAX
    fetch(`/admin/approvals/${approvalId}`, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        displayWorkflowDetails(data.approval);
      } else {
        showError('Failed to load workflow details');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showError('Error loading workflow details');
    });
  }

  // Display workflow details
  function displayWorkflowDetails(approval) {
    const modalBody = document.getElementById('workflowDetailsModalBody');
    modalBody.innerHTML = `
      <div class="row">
        <div class="col-md-6">
          <h6 class="text-primary mb-3">Approval Information</h6>
          <table class="table table-sm">
            <tr>
              <td><strong>ID:</strong></td>
              <td>#${approval.id}</td>
            </tr>
            <tr>
              <td><strong>Status:</strong></td>
              <td><span class="badge bg-${approval.status === 'pending' ? 'warning' : approval.status === 'approved' ? 'success' : 'danger'}">${approval.status.charAt(0).toUpperCase() + approval.status.slice(1)}</span></td>
            </tr>
            <tr>
              <td><strong>Client:</strong></td>
              <td>${approval.client_name}</td>
            </tr>
            <tr>
              <td><strong>Requested By:</strong></td>
              <td>${approval.requested_by_name}</td>
            </tr>
            <tr>
              <td><strong>Created:</strong></td>
              <td>${approval.created_at}</td>
            </tr>
          </table>
        </div>
        <div class="col-md-6">
          <h6 class="text-primary mb-3">Workflow Progress</h6>
          <div class="timeline">
            <div class="timeline-item">
              <div class="timeline-marker bg-primary"></div>
              <div class="timeline-content">
                <h6 class="text-white">Request Created</h6>
                <p class="text-muted small">${approval.created_at}</p>
              </div>
            </div>
            ${approval.approved_at ? `
            <div class="timeline-item">
              <div class="timeline-marker bg-${approval.status === 'approved' ? 'success' : 'danger'}"></div>
              <div class="timeline-content">
                <h6 class="text-white">${approval.status === 'approved' ? 'Approved' : 'Rejected'}</h6>
                <p class="text-muted small">${approval.approved_at}</p>
                <p class="text-muted small">By: ${approval.approved_by_name}</p>
              </div>
            </div>
            ` : ''}
          </div>
        </div>
      </div>
    `;
  }

  // Apply filters
  function applyFilters() {
    const status = document.getElementById('status-filter').value;
    const priority = document.getElementById('priority-filter').value;
    const date = document.getElementById('date-filter').value;
    
    // Filter workflow data
    let filteredData = workflowData;
    
    if (status) {
      filteredData = filteredData.filter(item => item.status === status);
    }
    
    if (priority) {
      filteredData = filteredData.filter(item => item.priority === priority);
    }
    
    if (date) {
      filteredData = filteredData.filter(item => item.created_at.startsWith(date));
    }
    
    // Update display
    const originalData = workflowData;
    workflowData = filteredData;
    renderWorkflow();
    workflowData = originalData;
  }

  // Refresh workflow
  function refreshWorkflow() {
    loadWorkflowData();
  }

  // Utility functions
  function showError(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
      <i data-feather="alert-circle" class="me-2"></i>
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    feather.replace();
    
    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.parentNode.removeChild(alertDiv);
      }
    }, 5000);
  }

  // Initialize
  document.addEventListener('DOMContentLoaded', function() {
    loadWorkflowData();
  });
</script>
@endpush
