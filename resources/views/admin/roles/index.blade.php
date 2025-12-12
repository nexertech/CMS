@extends('layouts.sidebar')

@section('title', 'Role Management — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Role Management</h2>
      <p class="text-light">Manage user roles and permissions</p>
    </div>
    <a href="{{ route('admin.roles.create') }}" class="btn btn-outline-secondary">
      <i data-feather="plus-circle" class="me-2"></i>Add New Role
    </a>
  </div>
</div>

<!-- FILTERS -->
<div class="card-glass mb-4" style="display: inline-block; width: fit-content; min-width: 300px;">
  <form id="rolesFiltersForm" method="GET" action="{{ route('admin.roles.index') }}">
  <div class="row g-2 align-items-end">
    <div class="col-auto">
      <input type="text" class="form-control" id="searchInput" name="search" placeholder="Search roles..." 
             value="{{ request('search') }}" oninput="handleRolesSearchInput()" style="width: 280px;">
    </div>
  </div>
  </form>
</div>

<!-- ROLES TABLE -->
<div class="card-glass">
  <div class="table-responsive">
    <table class="table table-dark table-sm">
      <thead class="table-dark">
        <tr>
          <th class="text-white">ID</th>
          <th class="text-white">Role Name</th>
          <th class="text-white">Description</th>
          <th class="text-white">Users Count</th>
          <th class="text-white">Permissions</th>
          <th class="text-white">Created</th>
          <th class="text-white">Actions</th>
        </tr>
      </thead>
      <tbody id="rolesTableBody">
        @forelse($roles as $role)
        <tr>
          <td>{{ $role->id }}</td>
          <td>
            <div class="d-flex align-items-center">
              {{-- <div class="avatar-sm me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold;">
                {{ substr($role->role_name, 0, 1) }}
              </div> --}}
              <div>
                <div class="fw-bold">{{ $role->role_name }}</div>
                {{-- <div class="text-muted small">{{ $role->description ?? 'No description' }}</div> --}}
              </div>
            </div>
          </td>
          <td>{{ $role->description ?? 'N/A' }}</td>
          <td>
            <span class="badge bg-info">{{ $role->users_count ?? 0 }} users</span>
          </td>
          <td>
            <span class="badge bg-warning">{{ $role->role_permissions_count ?? 0 }} permissions</span>
          </td>
          <td>{{ $role->created_at->format('M d, Y') }}</td>
          <td>
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-success btn-sm" title="View Details" onclick="viewRole({{ $role->id }})" style="padding: 3px 8px;">
                <i data-feather="eye" style="width: 16px; height: 16px;"></i>
              </button>
              <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-outline-primary btn-sm" title="Edit" style="padding: 3px 8px;">
                <i data-feather="edit" style="width: 16px; height: 16px;"></i>
              </a>
              <button class="btn btn-outline-danger btn-sm" onclick="deleteRole({{ $role->id }})" title="Delete" style="padding: 3px 8px;">
                <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
              </button>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="text-center py-4">
            <i data-feather="shield" class="feather-lg mb-2"></i>
            <div class="text-muted">No roles found</div>
            <small class="text-muted">Create your first role to get started</small>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  
  <!-- TOTAL RECORDS -->
  <div id="rolesTableFooter" class="text-center py-2 mt-2" style="background-color: rgba(59, 130, 246, 0.2); border-top: 2px solid #3b82f6; border-radius: 0 0 8px 8px;">
    <strong style="color: #ffffff; font-size: 14px;">
      Total Records: {{ $roles->total() }}
    </strong>
  </div>
  
  <!-- PAGINATION -->
  <div class="d-flex justify-content-center mt-3" id="rolesPagination">
    <div>
      {{ $roles->links() }}
    </div>
  </div>
</div>

<!-- Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content card-glass" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border: 1px solid rgba(59, 130, 246, 0.3);">
            <div class="modal-header" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
                <h5 class="modal-title text-white" id="roleModalLabel">
                    <i data-feather="shield" class="me-2"></i>Role Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="closeRoleModal()" style="background-color: rgba(255, 255, 255, 0.2); border-radius: 4px; padding: 0.5rem !important; opacity: 1 !important; filter: invert(1); background-size: 1.5em;"></button>
            </div>
            <div class="modal-body" id="roleModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
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
  
  // Role Functions
  let currentRoleId = null;
  
  function viewRole(roleId) {
    if (!roleId) {
      alert('Invalid role ID');
      return;
    }
    
    currentRoleId = roleId;
    
    const modalElement = document.getElementById('roleModal');
    const modalBody = document.getElementById('roleModalBody');
    
    // Show loading state
    modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    // Add blur effect to background first
    document.body.classList.add('modal-open-blur');
    
    // Show modal WITHOUT backdrop so we can see the blurred background
    const modal = new bootstrap.Modal(modalElement, {
      backdrop: false, // Disable Bootstrap backdrop completely
      keyboard: true,
      focus: true
    });
    modal.show();
    
    // Ensure any backdrop that might be created is removed
    const removeBackdrop = () => {
      const backdrops = document.querySelectorAll('.modal-backdrop');
      backdrops.forEach(backdrop => {
        backdrop.remove(); // Remove from DOM
      });
    };
    
    // Use MutationObserver to catch and remove any backdrop creation
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
          if (node.nodeType === 1 && node.classList && node.classList.contains('modal-backdrop')) {
            node.remove(); // Remove immediately if created
          }
        });
      });
      removeBackdrop();
    });
    
    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
    
    // Remove any existing backdrops
    removeBackdrop();
    setTimeout(removeBackdrop, 10);
    setTimeout(removeBackdrop, 50);
    setTimeout(removeBackdrop, 100);
    
    // Clean up observer when modal is hidden
    modalElement.addEventListener('hidden.bs.modal', function() {
      observer.disconnect();
      removeBackdrop();
    }, { once: true });
    
    // Load role details via AJAX - force HTML response
    fetch(`/admin/roles/${roleId}?format=html`, {
      method: 'GET',
      headers: {
        'Accept': 'text/html',
      },
      credentials: 'same-origin'
    })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      // Check if response is JSON
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        return response.json().then(data => {
          throw new Error('Received JSON instead of HTML. Please check the route.');
        });
      }
      return response.text();
    })
    .then(html => {
      // Check if response is actually JSON (starts with {)
      if (html.trim().startsWith('{')) {
        console.error('Received JSON instead of HTML');
        modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error: Server returned JSON instead of HTML. Please check the route configuration.</div>';
        return;
      }
      
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
      
      // Extract the role details sections
      let roleContent = '';
      
      // Get all rows that contain role information (skip page header)
      const allRows = contentSection.querySelectorAll('.row');
      const seenRows = new Set();
      
      allRows.forEach(row => {
        // Skip rows that are in page headers
        const isInHeader = row.closest('.mb-4') && row.closest('.mb-4').querySelector('h2');
        
        // Check if this row contains card-glass elements
        const hasCardGlass = row.querySelector('.card-glass');
        
        if (!isInHeader && hasCardGlass) {
          const rowHTML = row.outerHTML;
          // Use a simple hash to avoid duplicates
          const rowId = rowHTML.substring(0, 200);
          if (!seenRows.has(rowId)) {
            seenRows.add(rowId);
            roleContent += rowHTML;
          }
        }
      });
      
      // Also extract standalone card-glass elements
      const allCards = contentSection.querySelectorAll('.card-glass');
      const seenCards = new Set();
      
      allCards.forEach(card => {
        // Skip cards that are in page headers
        const parentRow = card.closest('.row');
        const isInHeader = parentRow && parentRow.closest('.mb-4') && parentRow.closest('.mb-4').querySelector('h2');
        
        // Skip if already added from rows
        const cardHTML = card.outerHTML;
        const cardId = cardHTML.substring(0, 300);
        
        if (!isInHeader && !seenCards.has(cardId) && !roleContent.includes(cardHTML.substring(0, 100))) {
          seenCards.add(cardId);
          // Check if it's already in a row that was added
          const isInAddedRow = parentRow && roleContent.includes(parentRow.outerHTML.substring(0, 100));
          if (!isInAddedRow) {
            roleContent += '<div class="mb-3">' + cardHTML + '</div>';
          }
        }
      });
      
      if (roleContent) {
        modalBody.innerHTML = roleContent;
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
          const modalElement = document.getElementById('roleModal');
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
        console.error('Could not find role content in response');
        console.log('Content section:', contentSection);
        console.log('Found cards:', contentSection.querySelectorAll('.card-glass').length);
        modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error: Could not load role details. Please refresh and try again.</div>';
      }
    })
    .catch(error => {
      console.error('Error loading role:', error);
      modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error loading role details: ' + error.message + '. Please try again.</div>';
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
  
  function closeRoleModal() {
    const modalElement = document.getElementById('roleModal');
    if (modalElement) {
      const modal = bootstrap.Modal.getInstance(modalElement);
      if (modal) {
        modal.hide();
      }
    }
    document.body.classList.remove('modal-open-blur');
  }

  // Debounced search input handler
  let rolesSearchTimeout = null;
  function handleRolesSearchInput() {
    if (rolesSearchTimeout) clearTimeout(rolesSearchTimeout);
    rolesSearchTimeout = setTimeout(() => {
      loadRoles();
    }, 500);
  }

  // Auto-submit for select filters
  function submitRolesFilters() {
    loadRoles();
  }

  // Load Roles via AJAX
  function loadRoles(url = null) {
    const form = document.getElementById('rolesFiltersForm');
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

    const tbody = document.getElementById('rolesTableBody');
    const paginationContainer = document.getElementById('rolesPagination');
    
    if (tbody) {
      tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
    }

    fetch(`{{ route('admin.roles.index') }}?${params.toString()}`, {
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
      
      const newTbody = doc.querySelector('#rolesTableBody');
      const newPagination = doc.querySelector('#rolesPagination');
      
      if (newTbody && tbody) {
        tbody.innerHTML = newTbody.innerHTML;
        feather.replace();
      }
      
      if (newPagination && paginationContainer) {
        paginationContainer.innerHTML = newPagination.innerHTML;
        // Re-initialize feather icons after pagination update
        feather.replace();
      }

      const newUrl = `{{ route('admin.roles.index') }}?${params.toString()}`;
      window.history.pushState({path: newUrl}, '', newUrl);
    })
    .catch(error => {
      console.error('Error loading roles:', error);
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger">Error loading data. Please refresh the page.</td></tr>';
      }
    });
  }

  // Handle pagination clicks
  document.addEventListener('click', function(e) {
    const paginationLink = e.target.closest('#rolesPagination a');
    if (paginationLink && paginationLink.href && !paginationLink.href.includes('javascript:')) {
      e.preventDefault();
      loadRoles(paginationLink.href);
    }
  });

  // Handle browser back/forward buttons
  window.addEventListener('popstate', function(e) {
    if (e.state && e.state.path) {
      loadRoles(e.state.path);
    } else {
      loadRoles();
    }
  });

  // Export functionality
  

  // Delete role function
  function deleteRole(roleId) {
    if (confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
      // Show loading state
      const deleteBtn = event.target.closest('button');
      const originalContent = deleteBtn.innerHTML;
      deleteBtn.innerHTML = '<i data-feather="loader" class="spinner"></i>';
      deleteBtn.disabled = true;
      
      fetch(`/admin/roles/${roleId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      })
      .then(response => {
        if (!response.ok) {
          return response.json().then(data => {
            throw new Error(data.message || `HTTP error! status: ${response.status}`);
          });
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          // Show success message
          alert('Role deleted successfully!');
          location.reload();
        } else {
          alert('Error deleting role: ' + (data.message || 'Unknown error'));
          // Restore button
          deleteBtn.innerHTML = originalContent;
          deleteBtn.disabled = false;
          feather.replace();
        }
      })
      .catch(error => {
        console.error('Error deleting role:', error);
        alert('Error deleting role: ' + error.message);
        // Restore button
        deleteBtn.innerHTML = originalContent;
        deleteBtn.disabled = false;
        feather.replace();
      });
    }
  }
</script>
@endpush
