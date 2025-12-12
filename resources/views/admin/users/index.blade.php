@extends('layouts.sidebar')

@section('title', 'User Management — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">User Management</h2>
      <p class="text-light">Manage system users and their access</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-outline-secondary">
      <i data-feather="user-plus" class="me-2"></i>Add New User
    </a>
  </div>
</div>

<!-- FILTERS -->
<div class="card-glass mb-4" style="display: inline-block; width: fit-content;">
  <form id="usersFiltersForm" method="GET" action="{{ route('admin.users.index') }}">
  <div class="row g-2 align-items-end">
    <div class="col-auto">
      <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">Search</label>
      <input type="text" class="form-control" id="searchInput" name="search" placeholder="Search..."
             value="{{ request('search') }}" oninput="handleUsersSearchInput()" style="font-size: 0.9rem; width: 180px;">
    </div>
    <div class="col-auto">
      <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">Role</label>
      <select class="form-select" name="role_id" onchange="submitUsersFilters()" style="font-size: 0.9rem; width: 140px;">
        <option value="" {{ request('role_id') ? '' : 'selected' }}>All</option>
        @foreach($roles as $role)
        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->role_name }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">Status</label>
      <select class="form-select" name="status" onchange="submitUsersFilters()" style="font-size: 0.9rem; width: 120px;">
        <option value="" {{ request('status') ? '' : 'selected' }}>All</option>
        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label small text-muted mb-1" style="font-size: 0.8rem;">&nbsp;</label>
      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetUsersFilters()" style="font-size: 0.9rem; padding: 0.35rem 0.8rem;">
        <i data-feather="refresh-cw" class="me-1" style="width: 14px; height: 14px;"></i>Reset
      </button>
    </div>
  </div>
  </form>
</div>

<!-- USERS TABLE -->
<div class="card-glass">
  <div class="table-responsive">
    <table class="table table-dark table-sm">
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Name</th>
          <th>Role</th>
          <th>GE Groups</th>
          <th>GE Nodes</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="usersTableBody">
        @forelse($users as $user)
        <tr>
          <td>{{ $user->id }}</td>
          <td>
            <div class="d-flex align-items-center">
              {{-- <div class="avatar-sm me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold;">
                {{ substr($user->username, 0, 1) }}
              </div> --}}
              <div>
                <div class="fw-bold">{{ $user->username }}</div>
                {{-- <small class="text-muted">ID: {{ $user->id }}</small> --}}
              </div>
            </div>
          </td>
          <td>{{ $user->name ?? 'N/A' }}</td>
          <td>
            @php
              $rawRole = $user->role->role_name ?? 'No Role';
              $prettyRole = $rawRole === 'No Role' ? $rawRole : ucwords(str_replace('_', ' ', strtolower($rawRole)));
            @endphp
            <span class="badge bg-primary" style="font-size: 0.85rem; padding: 6px 10px;">{{ $prettyRole }}</span>
          </td>
          <td>
            @php
              $roleName = strtolower($user->role->role_name ?? '');
            @endphp
            @if(in_array($roleName, ['director', 'admin']))
              <span class="badge bg-info">All GE Groups</span>
            @else
              {{ $user->city->name ?? 'N/A' }}
            @endif
          </td>
          <td>
            @php
              $roleName = strtolower($user->role->role_name ?? '');
            @endphp
            @if(in_array($roleName, ['director', 'admin']))
              <span class="badge bg-info">All GE Nodes</span>
            @elseif($roleName === 'garrison_engineer')
              <span class="badge bg-info">All GE Nodes</span>
            @else
              {{ $user->sector->name ?? 'N/A' }}
            @endif
          </td>
          <td>
            <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-danger' }}" style="color: #ffffff !important;">
              {{ ucfirst($user->status) }}
            </span>
          </td>
          <td>
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-success btn-sm" title="View Details" onclick="viewUser({{ $user->id }})" style="padding: 3px 8px;">
                <i data-feather="eye" style="width: 16px; height: 16px;"></i>
              </button>
              <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary btn-sm" title="Edit" style="padding: 3px 8px;">
                <i data-feather="edit" style="width: 16px; height: 16px;"></i>
              </a>
              <button class="btn btn-outline-danger btn-sm" onclick="deleteUser({{ $user->id }})" title="Delete" style="padding: 3px 8px;">
                <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
              </button>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="9" class="text-center py-4">
            <i data-feather="users" class="feather-lg mb-2"></i>
            <div>No users found</div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- TOTAL RECORDS -->
  <div id="usersTableFooter" class="text-center py-2 mt-2" style="background-color: rgba(59, 130, 246, 0.2); border-top: 2px solid #3b82f6; border-radius: 0 0 8px 8px;">
    <strong style="color: #ffffff; font-size: 14px;">
      Total Records: {{ $users->total() }}
    </strong>
  </div>

  <!-- PAGINATION -->
  <div class="d-flex justify-content-center mt-3" id="usersPagination">
    <div>
      {{ $users->links() }}
    </div>
  </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content card-glass" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border: 1px solid rgba(59, 130, 246, 0.3);">
            <div class="modal-header" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
                <h5 class="modal-title text-white" id="userModalLabel">
                    <i data-feather="user" class="me-2"></i>User Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="closeUserModal()" style="background-color: rgba(255, 255, 255, 0.2); border-radius: 4px; padding: 0.5rem !important; opacity: 1 !important; filter: invert(1); background-size: 1.5em;"></button>
            </div>
            <div class="modal-body" id="userModalBody">
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

  // User Functions
  let currentUserId = null;

  function viewUser(userId) {
    if (!userId) {
      alert('Invalid user ID');
      return;
    }

    currentUserId = userId;

    const modalElement = document.getElementById('userModal');
    const modalBody = document.getElementById('userModalBody');

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

    // Load user details via AJAX - force HTML response
    fetch(`/admin/users/${userId}?format=html`, {
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

      // Extract the user details sections
      let userContent = '';

      // Get all rows that contain user information (skip page header)
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
            userContent += rowHTML;
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

        if (!isInHeader && !seenCards.has(cardId) && !userContent.includes(cardHTML.substring(0, 100))) {
          seenCards.add(cardId);
          // Check if it's already in a row that was added
          const isInAddedRow = parentRow && userContent.includes(parentRow.outerHTML.substring(0, 100));
          if (!isInAddedRow) {
            userContent += '<div class="mb-3">' + cardHTML + '</div>';
          }
        }
      });

      if (userContent) {
        modalBody.innerHTML = userContent;
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
          // Apply again after delays to catch any late-loading content
          setTimeout(applyTableBorders, 100);
          setTimeout(applyTableBorders, 200);
          setTimeout(applyTableBorders, 500);
          setTimeout(applyTableBorders, 1000);
        }, 50);

        // Also apply when modal is fully shown
        setTimeout(() => {
          const modalElement = document.getElementById('userModal');
          if (modalElement) {
            const applyOnShow = function() {
              setTimeout(applyTableBorders, 100);
              setTimeout(applyTableBorders, 300);
              setTimeout(applyTableBorders, 600);
            };
            modalElement.addEventListener('shown.bs.modal', applyOnShow, { once: true });
            // Also apply immediately if modal is already shown
            if (modalElement.classList.contains('show')) {
              applyOnShow();
            }
          }
        }, 100);
      } else {
        console.error('Could not find user content in response');
        console.log('Content section:', contentSection);
        console.log('Found cards:', contentSection.querySelectorAll('.card-glass').length);
        modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error: Could not load user details. Please refresh and try again.</div>';
      }
    })
    .catch(error => {
      console.error('Error loading user:', error);
      modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error loading user details: ' + error.message + '. Please try again.</div>';
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

  function closeUserModal() {
    const modalElement = document.getElementById('userModal');
    if (modalElement) {
      const modal = bootstrap.Modal.getInstance(modalElement);
      if (modal) {
        modal.hide();
      }
    }
    document.body.classList.remove('modal-open-blur');
  }

  // Debounced search input handler
  let usersSearchTimeout = null;
  function handleUsersSearchInput() {
    if (usersSearchTimeout) clearTimeout(usersSearchTimeout);
    usersSearchTimeout = setTimeout(() => {
      loadUsers();
    }, 500);
  }

  // Auto-submit for select filters
  function submitUsersFilters() {
    loadUsers();
  }

  // Reset filters function
  function resetUsersFilters() {
    const form = document.getElementById('usersFiltersForm');
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
    window.location.href = '{{ route('admin.users.index') }}';
  }

  // Load Users via AJAX
  function loadUsers(url = null) {
    const form = document.getElementById('usersFiltersForm');
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

    const tbody = document.getElementById('usersTableBody');
    const paginationContainer = document.getElementById('usersPagination');

    if (tbody) {
      tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
    }

    fetch(`{{ route('admin.users.index') }}?${params.toString()}`, {
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

      const newTbody = doc.querySelector('#usersTableBody');
      const newPagination = doc.querySelector('#usersPagination');
      const newTfoot = doc.querySelector('#usersTableFooter');

      if (newTbody && tbody) {
        tbody.innerHTML = newTbody.innerHTML;
        feather.replace();
      }

      // Update table footer (total records)
      const tfoot = document.querySelector('#usersTableFooter');
      if (newTfoot && tfoot) {
        tfoot.innerHTML = newTfoot.innerHTML;
      } else if (tfoot) {
        const extractedTfoot = doc.querySelector('#usersTableFooter') ||
          (html.includes('usersTableFooter') ? (() => {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            return tempDiv.querySelector('#usersTableFooter');
          })() : null);

        if (extractedTfoot) {
          tfoot.innerHTML = extractedTfoot.innerHTML;
        }
      }

      if (newPagination && paginationContainer) {
        paginationContainer.innerHTML = newPagination.innerHTML;
        // Re-initialize feather icons after pagination update
        feather.replace();
      }

      const newUrl = `{{ route('admin.users.index') }}?${params.toString()}`;
      window.history.pushState({path: newUrl}, '', newUrl);
    })
    .catch(error => {
      console.error('Error loading users:', error);
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger">Error loading data. Please refresh the page.</td></tr>';
      }
    });
  }

  // Handle pagination clicks
  document.addEventListener('click', function(e) {
    const paginationLink = e.target.closest('#usersPagination a');
    if (paginationLink && paginationLink.href && !paginationLink.href.includes('javascript:')) {
      e.preventDefault();
      loadUsers(paginationLink.href);
    }
  });

  // Handle browser back/forward buttons
  window.addEventListener('popstate', function(e) {
    if (e.state && e.state.path) {
      loadUsers(e.state.path);
    } else {
      loadUsers();
    }
  });

  // Export functionality


  // Delete user function
  function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
      fetch(`/admin/users/${userId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification('User deleted successfully!', 'success');
          // Remove the row from table
          const row = document.querySelector(`button[onclick="deleteUser(${userId})"]`).closest('tr');
          if (row) {
            row.remove();
          }
          // Reload page after a short delay
          setTimeout(() => {
            location.reload();
          }, 1000);
        } else {
          showNotification('Error deleting user: ' + (data.message || 'Unknown error'), 'error');
        }
      })
      .catch(error => {
        console.error('Error deleting user:', error);
        showNotification('Error deleting user: ' + error.message, 'error');
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
