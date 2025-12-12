@extends('layouts.sidebar')

@section('title', 'Employees Management — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Employees Management</h2>
      <p class="text-light">Manage employee information and records</p>
    </div>
    <a href="{{ route('admin.employees.create') }}" class="btn btn-outline-secondary">
      <i data-feather="user-plus" class="me-2"></i>Add New Employee
    </a>
  </div>
</div>

<!-- FILTERS -->
<div class="card-glass mb-4" style="display: inline-block; width: fit-content;">
  <form id="employeesFiltersForm" method="GET" action="{{ route('admin.employees.index') }}">
  <div class="row g-2 align-items-end">
    <div class="col-auto">
      <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">Search</label>
      <input type="text" class="form-control" id="searchInput" name="search" placeholder="Search..." 
             value="{{ request('search') }}" oninput="handleEmployeesSearchInput()" style="font-size: 0.9rem; width: 180px;">
    </div>
    <div class="col-auto">
      <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">Category</label>
      <select class="form-select" name="category" onchange="submitEmployeesFilters()" style="font-size: 0.9rem; width: 140px;">
        <option value="" {{ request('category') ? '' : 'selected' }}>All</option>
        @if(isset($categories) && $categories->count() > 0)
          @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
          @endforeach
        @endif
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">Status</label>
      <select class="form-select" name="status" onchange="submitEmployeesFilters()" style="font-size: 0.9rem; width: 120px;">
        <option value="" {{ request('status') ? '' : 'selected' }}>All</option>
        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label small text-muted mb-1" style="font-size: 0.8rem;">&nbsp;</label>
      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetEmployeesFilters()" style="font-size: 0.9rem; padding: 0.35rem 0.8rem;">
        <i data-feather="refresh-cw" class="me-1" style="width: 14px; height: 14px;"></i>Reset
      </button>
    </div>
  </div>
  </form>
</div>

<!-- EMPLOYEES TABLE -->
<div class="card-glass">
  <div class="table-responsive">
    <table class="table table-dark table-sm" id="employeesTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Employee</th>
          <th>Category</th>
          <th>Designation</th>
          <th>GE Groups</th>
          <th>GE Nodes</th>
          <th>Phone</th>
          <th>Status</th>
          <th>Hire Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="employeesTableBody">
        @forelse($employees as $employee)
        <tr>
          <td>{{ $employee->id }}</td>
          <td>
            <div class="d-flex align-items-center">
              {{-- <div class="avatar-sm me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold;">
                {{ substr($employee->name ?? 'E', 0, 1) }}
              </div> --}}
              <div>
                <div class="fw-bold">{{ $employee->name ?? 'N/A' }}</div>
                {{-- <small class="text-muted">ID: {{ $employee->id }}</small> --}}
              </div>
            </div>
          </td>
          <td>{{ ucfirst($employee->category ?? 'N/A') }}</td>
          <td>{{ $employee->designation ?? '' }}</td>
          <td>{{ $employee->city ? $employee->city->name : 'N/A' }}</td>
          <td>{{ $employee->sector ? $employee->sector->name : 'N/A' }}</td>
          <td>{{ $employee->phone ?: 'N/A' }}</td>
          <td>
            <span class="badge {{ $employee->status === 'active' ? 'bg-success' : 'bg-danger' }}" style="color: #ffffff !important;">
              {{ ucfirst($employee->status ?? 'inactive') }}
            </span>
          </td>
          <td>{{ $employee->date_of_hire ? $employee->date_of_hire->format('M d, Y') : 'N/A' }}</td>
          <td>
            <div class="btn-group" role="group">
              <button onclick="viewEmployee({{ $employee->id }})" class="btn btn-outline-success btn-sm" title="View Details" style="padding: 3px 8px;">
                <i data-feather="eye" style="width: 16px; height: 16px;"></i>
              </button>
              <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-outline-primary btn-sm" title="Edit" style="padding: 3px 8px;">
                <i data-feather="edit" style="width: 16px; height: 16px;"></i>
              </a>
              <button class="btn btn-outline-danger btn-sm" onclick="deleteEmployee({{ $employee->id }})" title="Delete" data-employee-id="{{ $employee->id }}" style="padding: 3px 8px;">
                <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
              </button>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="11" class="text-center text-muted py-4">
            <i data-feather="users" class="feather-lg mb-2"></i>
            <div>No employees found</div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  
  <!-- TOTAL RECORDS -->
  <div id="employeesTableFooter" class="text-center py-2 mt-2" style="background-color: rgba(59, 130, 246, 0.2); border-top: 2px solid #3b82f6; border-radius: 0 0 8px 8px;">
    <strong style="color: #ffffff; font-size: 14px;">
      Total Records: {{ $employees->total() }}
    </strong>
  </div>
  
  <!-- PAGINATION -->
  <div class="d-flex justify-content-center mt-4" id="employeesPagination">
    <div>
      {{ $employees->links() }}
    </div>
  </div>
</div>

<!-- View Employee Modal -->
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-labelledby="viewEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content card-glass" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border: 1px solid rgba(59, 130, 246, 0.3);">
      <div class="modal-header" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); position: relative;">
        <h5 class="modal-title text-white" id="viewEmployeeModalLabel">
          <i data-feather="user" class="me-2"></i>Employee Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1) grayscale(100%) brightness(200%); opacity: 1 !important; background-size: 1.5em; padding: 0.5em; background-color: rgba(255, 255, 255, 0.2); border-radius: 4px; width: 2em; height: 2em; display: block !important; visibility: visible !important;"></button>
      </div>
      <div class="modal-body" id="viewEmployeeModalBody">
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Delete Employee Modal -->
<div class="modal fade" id="deleteEmployeeModal" tabindex="-1" aria-labelledby="deleteEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border: 1px solid rgba(239, 68, 68, 0.3);">
      <div class="modal-header" style="border-bottom: 1px solid rgba(239, 68, 68, 0.2);">
        <h5 class="modal-title text-white" id="deleteEmployeeModalLabel">
          <i data-feather="alert-triangle" class="me-2 text-danger"></i>Delete Employee
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1);"></button>
      </div>
      <div class="modal-body">
        <p class="text-white mb-3">
          Are you sure you want to delete this employee? This action cannot be undone.
        </p>
        <div class="alert alert-warning" role="alert">
          <i data-feather="info" class="me-2"></i>
          <strong>Note:</strong> This will soft delete the employee record. The data will be preserved in the database.
        </div>
        <div id="employeeDetails" class="text-white">
          <p class="mb-1"><strong>Employee ID:</strong> <span id="employeeIdModal"></span></p>
          <p class="mb-0"><strong>Name:</strong> <span id="employeeNameModal"></span></p>
        </div>
      </div>
      <div class="modal-footer" style="border-top: 1px solid rgba(239, 68, 68, 0.2);">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i data-feather="x" class="me-1"></i>Cancel
        </button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
          <i data-feather="trash-2" class="me-1"></i>Delete Employee
        </button>
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
  
  // Debounced search input handler
  let employeesSearchTimeout = null;
  function handleEmployeesSearchInput() {
    if (employeesSearchTimeout) clearTimeout(employeesSearchTimeout);
    employeesSearchTimeout = setTimeout(() => {
      loadEmployees();
    }, 500);
  }

  // Auto-submit for select filters
  function submitEmployeesFilters() {
    loadEmployees();
  }

  // Reset filters function
  function resetEmployeesFilters() {
    const form = document.getElementById('employeesFiltersForm');
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
    window.location.href = '{{ route('admin.employees.index') }}';
  }

  // Load Employees via AJAX
  function loadEmployees(url = null) {
    const form = document.getElementById('employeesFiltersForm');
    if (!form) return;
    
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    if (url) {
      const urlObj = new URL(url, window.location.origin);
      urlObj.searchParams.forEach((value, key) => {
        params.append(key, value);
      });
    } else {
      for (const [key, value] of formData.entries()) {
        if (value) {
          params.append(key, value);
        }
      }
    }

    const tbody = document.getElementById('employeesTableBody');
    const paginationContainer = document.getElementById('employeesPagination');
    const footerContainer = document.getElementById('employeesTableFooter');
    
    if (tbody) {
      tbody.innerHTML = '<tr><td colspan="11" class="text-center py-4"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
    }

    fetch(`{{ route('admin.employees.index') }}?${params.toString()}`, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'text/html',
      },
      credentials: 'same-origin'
    })
    .then(response => response.text())
    .then(html => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      
      const newTbody = doc.querySelector('#employeesTableBody');
      const newPagination = doc.querySelector('#employeesPagination');
      const newFooter = doc.querySelector('#employeesTableFooter');
      
      if (newTbody && tbody) {
        tbody.innerHTML = newTbody.innerHTML;
        feather.replace();
      }
      
      if (newPagination && paginationContainer) {
        paginationContainer.innerHTML = newPagination.innerHTML;
        // Re-initialize feather icons after pagination update
        feather.replace();
      }
      
      // Update total records footer with filtered count
      if (newFooter && footerContainer) {
        footerContainer.innerHTML = newFooter.innerHTML;
      }

      const newUrl = `{{ route('admin.employees.index') }}?${params.toString()}`;
      window.history.pushState({path: newUrl}, '', newUrl);
    })
    .catch(error => {
      console.error('Error loading employees:', error);
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="11" class="text-center py-4 text-danger">Error loading data. Please refresh the page.</td></tr>';
      }
    });
  }

  // Handle pagination clicks
  document.addEventListener('click', function(e) {
    const paginationLink = e.target.closest('#employeesPagination a');
    if (paginationLink && paginationLink.href && !paginationLink.href.includes('javascript:')) {
      e.preventDefault();
      loadEmployees(paginationLink.href);
    }
  });

  // Handle browser back/forward buttons
  window.addEventListener('popstate', function(e) {
    if (e.state && e.state.path) {
      loadEmployees(e.state.path);
    } else {
      loadEmployees();
    }
  });
  
  
  
  // View employee function
  function viewEmployee(employeeId) {
    if (!employeeId) {
      alert('Invalid employee ID');
      return;
    }
    
    const modalElement = document.getElementById('viewEmployeeModal');
    const modalBody = document.getElementById('viewEmployeeModalBody');
    
    // Show loading state
    modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    // Show modal
    const modal = new bootstrap.Modal(modalElement, {
      backdrop: true,
      keyboard: true,
      focus: true
    });
    modal.show();
    
    // Add blur effect to background
    document.body.classList.add('modal-open-blur');
    
    // Load employee details via AJAX
    fetch(`/admin/employees/${employeeId}`, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'text/html',
      },
      credentials: 'same-origin'
    })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.text();
    })
    .then(html => {
      // Extract the content from the show page
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      
      // Get the content section - try multiple selectors
      let contentSection = doc.querySelector('section.content');
      if (!contentSection) {
        contentSection = doc.querySelector('.content');
      }
      if (!contentSection) {
        // Try to find the main content area
        const mainContent = doc.querySelector('main') || doc.querySelector('[role="main"]');
        if (mainContent) {
          contentSection = mainContent;
        } else {
          contentSection = doc.body;
        }
      }
      
      // Extract the employee details sections
      let employeeContent = '';
      let profileCardAdded = false;
      
      // Get all rows that contain employee information
      const allRows = contentSection.querySelectorAll('.row');
      
      allRows.forEach(row => {
        // Check if this row contains employee profile card (with avatar)
        const hasProfileCard = row.querySelector('.employee-avatar');
        
        // Check if this row contains details section (with col-md-6 and card-glass, but no avatar)
        const hasDetails = row.querySelector('.col-md-6 .card-glass') && !row.querySelector('.employee-avatar');
        
        // Only add profile card once
        if (hasProfileCard && !profileCardAdded) {
          employeeContent += row.outerHTML;
          profileCardAdded = true;
        }
        // Add details section
        else if (hasDetails) {
          employeeContent += row.outerHTML;
        }
      });
      
      // If we found content, use it
      if (employeeContent) {
        modalBody.innerHTML = employeeContent;
        // Function to apply table column borders
        const applyTableBorders = () => {
          const modalTables = modalBody.querySelectorAll('table');
          modalTables.forEach((table) => {
            const ths = table.querySelectorAll('th');
            const tds = table.querySelectorAll('td');
            
            ths.forEach((th) => {
              const row = th.parentElement;
              const cellsInRow = Array.from(row.querySelectorAll('th'));
              const cellIndex = cellsInRow.indexOf(th);
              const isLast = cellIndex === cellsInRow.length - 1;
              
              if (!isLast) {
                th.setAttribute('style', (th.getAttribute('style') || '') + ' border-right: 1px solid rgba(201, 160, 160, 0.3) !important;');
                th.style.borderRight = '1px solid rgba(201, 160, 160, 0.3)';
                th.style.setProperty('border-right', '1px solid rgba(201, 160, 160, 0.3)', 'important');
              } else {
                th.setAttribute('style', (th.getAttribute('style') || '') + ' border-right: none !important;');
                th.style.borderRight = 'none';
                th.style.setProperty('border-right', 'none', 'important');
              }
            });
            
            tds.forEach((td) => {
              const row = td.parentElement;
              const cellsInRow = Array.from(row.querySelectorAll('td'));
              const cellIndex = cellsInRow.indexOf(td);
              const isLast = cellIndex === cellsInRow.length - 1;
              
              if (!isLast) {
                td.setAttribute('style', (td.getAttribute('style') || '') + ' border-right: 1px solid rgba(201, 160, 160, 0.3) !important;');
                td.style.borderRight = '1px solid rgba(201, 160, 160, 0.3)';
                td.style.setProperty('border-right', '1px solid rgba(201, 160, 160, 0.3)', 'important');
              } else {
                td.setAttribute('style', (td.getAttribute('style') || '') + ' border-right: none !important;');
                td.style.borderRight = 'none';
                td.style.setProperty('border-right', 'none', 'important');
              }
            });
          });
        };
        
        // Replace feather icons after content is loaded
        setTimeout(() => {
          feather.replace();
          applyTableBorders();
          setTimeout(applyTableBorders, 100);
          setTimeout(applyTableBorders, 200);
          setTimeout(applyTableBorders, 500);
          setTimeout(applyTableBorders, 1000);
        }, 50);
        
        // Also apply when modal is fully shown
        setTimeout(() => {
          const modalElement = document.getElementById('viewEmployeeModal');
          if (modalElement) {
            const applyOnShow = function() {
              setTimeout(applyTableBorders, 100);
              setTimeout(applyTableBorders, 300);
              setTimeout(applyTableBorders, 600);
            };
            modalElement.addEventListener('shown.bs.modal', applyOnShow, { once: true });
            if (modalElement.classList.contains('show')) {
              applyOnShow();
            }
          }
        }, 100);
      } else {
        // Fallback: try to get all card-glass elements, but avoid duplicates
        const cards = contentSection.querySelectorAll('.card-glass');
        const seenCards = new Set();
        if (cards.length > 0) {
          cards.forEach(card => {
            const cardHTML = card.outerHTML;
            // Use a simple hash to avoid duplicates
            const cardId = cardHTML.substring(0, 100);
            if (!seenCards.has(cardId)) {
              seenCards.add(cardId);
              employeeContent += '<div class="mb-3">' + cardHTML + '</div>';
            }
          });
          if (employeeContent) {
            modalBody.innerHTML = employeeContent;
            feather.replace();
          } else {
            console.error('Could not find employee content in response');
            modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error: Could not load employee details. Please refresh and try again.</div>';
          }
        } else {
          console.error('Could not find employee content in response');
          modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error: Could not load employee details. Please refresh and try again.</div>';
        }
      }
    })
    .catch(error => {
      console.error('Error loading employee:', error);
      modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error loading employee details: ' + error.message + '. Please try again.</div>';
    });
    
    // Replace feather icons when modal is shown
    modalElement.addEventListener('shown.bs.modal', function() {
      feather.replace();
    });
    
    // Remove blur when modal is hidden
    modalElement.addEventListener('hidden.bs.modal', function() {
      document.body.classList.remove('modal-open-blur');
      feather.replace();
    }, { once: true });
  }
  
  // Delete employee function
  let currentDeleteEmployeeId = null;
  
  function deleteEmployee(employeeId) {
    if (!employeeId) {
      alert('Invalid employee ID');
      return;
    }
    
    // Find the employee details from the table
    const row = document.querySelector(`button[data-employee-id="${employeeId}"]`)?.closest('tr');
    if (!row) {
      alert('Employee not found');
      return;
    }
    
    // Get employee details
    const employeeIdCell = row.cells[0].textContent.trim();
    const employeeNameCell = row.cells[1].querySelector('.fw-bold')?.textContent || 'Unknown';
    
    // Set modal details
    document.getElementById('employeeIdModal').textContent = employeeIdCell;
    document.getElementById('employeeNameModal').textContent = employeeNameCell;
    
    // Store the employee ID for deletion
    currentDeleteEmployeeId = employeeId;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('deleteEmployeeModal'));
    modal.show();
  }
  
  // Handle confirm delete button
  document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    if (!currentDeleteEmployeeId) {
      alert('No employee selected for deletion');
      return;
    }
    
    const employeeId = currentDeleteEmployeeId;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Show loading state
    const btn = document.getElementById('confirmDeleteBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i data-feather="loader" class="spinning"></i> Deleting...';
    
    fetch(`/admin/employees/${employeeId}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteEmployeeModal'));
        modal.hide();
        
        // Show success notification
        showNotification('Employee deleted successfully!', 'success');
        
        // Reload page after a short delay
        setTimeout(() => {
          location.reload();
        }, 1000);
      } else {
        btn.disabled = false;
        btn.innerHTML = originalText;
        showNotification('Error deleting employee: ' + (data.message || 'Unknown error'), 'error');
      }
    })
    .catch(error => {
      console.error('Error deleting employee:', error);
      btn.disabled = false;
      btn.innerHTML = originalText;
      showNotification('Error deleting employee: ' + error.message, 'error');
    });
    
    // Reset
    currentDeleteEmployeeId = null;
  });

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
