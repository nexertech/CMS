@extends('layouts.sidebar')

@section('title', 'SLA Rules Management — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
      <div>
      <h2 class="text-white mb-2" >SLA Rules Management</h2>
      <p class="text-light" >Manage Service Level Agreement rules and compliance</p>
      </div>
    <a href="{{ route('admin.sla.create') }}" class="btn btn-outline-secondary">
      <i data-feather="plus" class="me-2"></i>Add SLA Rule
        </a>
      </div>
    </div>

<!-- FILTERS -->
<div class="card-glass mb-4" style="display: inline-block; width: fit-content;">
  <form id="slaFiltersForm" method="GET" action="{{ route('admin.sla.index') }}">
  <div class="row g-2 align-items-end">
    <div class="col-12 col-md-4">
      <input type="text" class="form-control" id="searchInput" name="search" placeholder="Search SLA rules..." 
             value="{{ request('search') }}" oninput="handleSearchInput()">
    </div>
    <div class="col-6 col-md-3">
      <select class="form-select" name="priority" onchange="submitSlaFilters()">
            <option value="" {{ request('priority') ? '' : 'selected' }}>All Priorities</option>
            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
          </select>
    </div>
    <div class="col-6 col-md-3">
      <select class="form-select" name="status" onchange="submitSlaFilters()">
            <option value="" {{ request('status') ? '' : 'selected' }}>All Status</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
          </select>
    </div>
  </div>
  </form>
  </div>

<!-- SLA RULES TABLE -->
<div class="card-glass">
      <div class="table-responsive">
        <table class="table table-dark table-sm">
          <thead>
            <tr>
          <th >ID</th>
          <th >Rule Name</th>
          <th >Priority</th>
          <th >Response Time</th>
          <th >Resolution Time</th>
          <th >Notify To</th>
          <th >Status</th>
          <th >Created</th>
          <th >Actions</th>
            </tr>
          </thead>
          <tbody id="slaRulesTableBody">
            @forelse($slaRules as $rule)
            <tr>
          <td >{{ $rule->id }}</td>
          <td>
            <div class="fw-bold">{{ ucfirst($rule->complaint_type) }} Rule</div>
            <div class="text-muted small">{{ $rule->complaint_type_display ?? ucfirst($rule->complaint_type) }}</div>
              </td>
              <td>
            @if(($rule->priority ?? 'medium') === 'urgent')
              <span class="priority-badge priority-{{ $rule->priority ?? 'medium' }}" style="background-color: #991b1b !important; color: #ffffff !important; border: 1px solid #7f1d1d !important; padding: 3px 6px !important; font-size: 10px !important; border-radius: 6px !important; display: inline-block !important;">
                {{ ucfirst($rule->priority ?? 'medium') }}
              </span>
            @else
              <span class="priority-badge priority-{{ $rule->priority ?? 'medium' }}" style="color: #ffffff !important;">
                {{ ucfirst($rule->priority ?? 'medium') }}
              </span>
            @endif
              </td>
          <td >{{ $rule->max_response_time }} hours</td>
          <td >{{ $rule->max_resolution_time ?? 'N/A' }} hours</td>
          <td >{{ $rule->notifyTo->name ?? 'N/A' }}</td>
          <td>
            <span class="status-badge status-{{ $rule->status ?? 'active' }}" style="color: #ffffff !important;">
              {{ ucfirst($rule->status ?? 'active') }}
                </span>
              </td>
          <td >{{ $rule->created_at->format('M d, Y') }}</td>
              <td>
            <div class="btn-group" role="group">
              <button class="btn btn-outline-success btn-sm" onclick="viewRule({{ $rule->id }})" title="View Details" style="padding: 3px 8px;">
                    <i data-feather="eye" style="width: 16px; height: 16px;"></i>
              </button>
              <a href="{{ route('admin.sla.edit', $rule->id) }}" class="btn btn-outline-primary btn-sm" title="Edit" style="padding: 3px 8px;">
                    <i data-feather="edit" style="width: 16px; height: 16px;"></i>
              </a>
              <button class="btn btn-outline-danger btn-sm" onclick="deleteRule({{ $rule->id }})" title="Delete" style="padding: 3px 8px;">
                      <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
                    </button>
                </div>
              </td>
            </tr>
            @empty
            <tr>
          <td colspan="9" class="text-center py-4" >
            <i data-feather="clock" class="feather-lg mb-2"></i>
            <div>No SLA rules found</div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

  <!-- TOTAL RECORDS -->
      <div id="slaRulesTableFooter" class="text-center py-2 mt-2" style="background-color: rgba(59, 130, 246, 0.2); border-top: 2px solid #3b82f6; border-radius: 0 0 8px 8px;">
        <strong style="color: #ffffff; font-size: 14px;">
          Total Records: {{ $slaRules->total() }}
        </strong>
      </div>

  <!-- PAGINATION -->
      <div class="d-flex justify-content-center mt-3" id="slaRulesPagination">
        <div>
          {{ $slaRules->links() }}
        </div>
      </div>
    </div>
@endsection

@push('styles')
@endpush

@push('scripts')
  <script>
    feather.replace();

  // Debounced search input handler
  let searchTimeout = null;
  function handleSearchInput() {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      loadSlaRules();
    }, 500);
  }

  // Auto-submit for select filters
  function submitSlaFilters() {
    loadSlaRules();
  }

  // Reset filters function
  function resetSlaFilters() {
    const form = document.getElementById('slaFiltersForm');
    if (!form) return;
    
    // Clear all form inputs
    form.querySelectorAll('input[type="text"], input[type="date"], select').forEach(input => {
      if (input.type === 'select-one') {
        input.selectedIndex = 0;
      } else {
        input.value = '';
      }
    });
    
    // Reset URL to base route
    window.location.href = '{{ route('admin.sla.index') }}';
  }

  // Load SLA rules via AJAX
  function loadSlaRules(url = null) {
    const form = document.getElementById('slaFiltersForm');
    if (!form) return;
    
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    // If URL is provided (for pagination), use it; otherwise use form data
    if (url) {
      const urlObj = new URL(url, window.location.origin);
      urlObj.searchParams.forEach((value, key) => {
        params.append(key, value);
      });
    } else {
      // Add all form values to params
      for (const [key, value] of formData.entries()) {
        if (value) {
          params.append(key, value);
        }
      }
    }

    // Show loading state
    const tbody = document.getElementById('slaRulesTableBody');
    const paginationContainer = document.getElementById('slaRulesPagination');
    
    if (tbody) {
      tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
    }

    // Make AJAX request
    fetch(`{{ route('admin.sla.index') }}?${params.toString()}`, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'text/html',
      },
      credentials: 'same-origin'
    })
    .then(response => response.text())
    .then(html => {
      // Use DOMParser to extract table content
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      
      // Extract table body
      const newTbody = doc.querySelector('#slaRulesTableBody');
      const newPagination = doc.querySelector('#slaRulesPagination');
      const newTfoot = doc.querySelector('#slaRulesTableFooter');
      
      if (newTbody && tbody) {
        tbody.innerHTML = newTbody.innerHTML;
        feather.replace();
      }
      
      // Update table footer (total records)
      const tfoot = document.querySelector('#slaRulesTableFooter');
      if (newTfoot && tfoot) {
        tfoot.innerHTML = newTfoot.innerHTML;
      } else if (tfoot) {
        const extractedTfoot = doc.querySelector('#slaRulesTableFooter') || 
          (html.includes('slaRulesTableFooter') ? (() => {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            return tempDiv.querySelector('#slaRulesTableFooter');
          })() : null);
        
        if (extractedTfoot) {
          tfoot.innerHTML = extractedTfoot.innerHTML;
        }
      }
      
      // Update pagination
      if (newPagination && paginationContainer) {
        paginationContainer.innerHTML = newPagination.innerHTML;
        // Re-initialize feather icons after pagination update
        feather.replace();
      }

      // Update URL without reload
      const newUrl = `{{ route('admin.sla.index') }}?${params.toString()}`;
      window.history.pushState({path: newUrl}, '', newUrl);
    })
    .catch(error => {
      console.error('Error loading SLA rules:', error);
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger">Error loading data. Please refresh the page.</td></tr>';
      }
    });
  }

  // Handle pagination clicks
  document.addEventListener('click', function(e) {
    const paginationLink = e.target.closest('#slaRulesPagination a');
    if (paginationLink && paginationLink.href && !paginationLink.href.includes('javascript:')) {
      e.preventDefault();
      loadSlaRules(paginationLink.href);
    }
  });

  // Handle browser back/forward buttons
  window.addEventListener('popstate', function(e) {
    if (e.state && e.state.path) {
      loadSlaRules(e.state.path);
    } else {
      loadSlaRules();
    }
  });

  // SLA Rule Functions
  function viewRule(ruleId) {
    // Redirect to view page
    window.location.href = `/admin/sla/${ruleId}`;
  }

  function editRule(ruleId) {
    // Redirect to edit page
    window.location.href = `/admin/sla/${ruleId}/edit`;
  }

  function deleteRule(ruleId) {
    if (confirm('Are you sure you want to delete this SLA rule?')) {
      // Show loading state
      const deleteBtn = document.querySelector(`button[onclick="deleteRule(${ruleId})"]`);
      const originalText = deleteBtn.innerHTML;
      deleteBtn.innerHTML = '<i data-feather="loader" class="me-2"></i>Deleting...';
      deleteBtn.disabled = true;
      feather.replace();

      // Make delete request
      fetch(`/admin/sla/${ruleId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Content-Type': 'application/json',
        },
        credentials: 'same-origin'
      })
      .then(response => {
        if (response.ok) {
          return response.json();
        }
        throw new Error('Delete failed');
      })
      .then(data => {
        // Show success message
        showNotification('SLA rule deleted successfully!', 'success');
        
        // Remove the row from table
        const row = document.querySelector(`button[onclick="deleteRule(${ruleId})"]`).closest('tr');
        if (row) {
          row.remove();
        }
        
        // Reload page after a short delay
        setTimeout(() => {
          location.reload();
        }, 1000);
      })
      .catch(error => {
        console.error('Delete error:', error);
        showNotification('Failed to delete SLA rule: ' + error.message, 'error');
        
        // Reset button
        deleteBtn.innerHTML = originalText;
        deleteBtn.disabled = false;
        feather.replace();
      });
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
