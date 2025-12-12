@extends('layouts.sidebar')

@section('title', 'Frontend Users — CMS Admin')

@section('content')
  <!-- PAGE HEADER -->
  <div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2 class="text-white mb-2">Frontend Users</h2>
        <p class="text-light">Manage frontend users</p>
      </div>
      <a href="{{ route('admin.frontend-users.create') }}" class="btn btn-outline-secondary">
        <i data-feather="user-plus" class="me-2"></i>Add New User
      </a>
    </div>
  </div>

  <!-- FILTERS -->
  <div class="card-glass mb-4" style="display: inline-block; width: fit-content;">
    <form id="frontendUsersFiltersForm" method="GET" action="{{ route('admin.frontend-users.index') }}">
      <div class="row g-2 align-items-end">
        <div class="col-auto">
          <label class="form-label small mb-1"
            style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">Search</label>
          <input type="text" class="form-control" id="searchInput" name="search" placeholder="Search..."
            value="{{ request('search') }}" oninput="handleFrontendUsersSearchInput()"
            style="font-size: 0.9rem; width: 180px;">
        </div>
        <div class="col-auto">
          <label class="form-label small mb-1"
            style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">Status</label>
          <select class="form-select" name="status" onchange="submitFrontendUsersFilters()"
            style="font-size: 0.9rem; width: 120px;">
            <option value="" {{ request('status') ? '' : 'selected' }}>All</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
          </select>
        </div>
        <div class="col-auto">
          <label class="form-label small text-muted mb-1" style="font-size: 0.8rem;">&nbsp;</label>
          <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetFrontendUsersFilters()"
            style="font-size: 0.9rem; padding: 0.35rem 0.8rem;">
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
            <th>Email</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="frontendUsersTableBody">
          @forelse($users as $user)
            <tr>
              <td>{{ $user->id }}</td>
              <td>
                <div class="d-flex align-items-center">
                  <div>
                    <div class="fw-bold">{{ $user->username }}</div>
                  </div>
                </div>
              </td>
              <td>{{ $user->name ?? 'N/A' }}</td>
              <td>{{ $user->email ?? 'N/A' }}</td>
              <td>{{ $user->phone ?? 'N/A' }}</td>
              <td>
                <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-danger' }}"
                  style="color: #ffffff !important;">
                  {{ ucfirst($user->status) }}
                </span>
              </td>
              <td>
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-outline-success btn-sm" title="View Details"
                    onclick="viewFrontendUser({{ $user->id }})" style="padding: 3px 8px;">
                    <i data-feather="eye" style="width: 16px; height: 16px;"></i>
                  </button>
                  <a href="{{ route('admin.frontend-users.edit', $user) }}" class="btn btn-outline-primary btn-sm"
                    title="Edit" style="padding: 3px 8px;">
                    <i data-feather="edit" style="width: 16px; height: 16px;"></i>
                  </a>
                  <button type="button" class="btn btn-outline-info btn-sm" title="Assign Privileges"
                    onclick="openAssignLocationsModal({{ $user->id }})" style="padding: 3px 8px;">
                    <i data-feather="shield" style="width: 16px; height: 16px;"></i>
                  </button>
                  <button class="btn btn-outline-danger btn-sm" onclick="deleteFrontendUser({{ $user->id }})" title="Delete"
                    style="padding: 3px 8px;">
                    <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-4">
                <i data-feather="users" class="feather-lg mb-2"></i>
                <div>No frontend users found</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- TOTAL RECORDS -->
    <div id="frontendUsersTableFooter" class="text-center py-2 mt-2"
      style="background-color: rgba(59, 130, 246, 0.2); border-top: 2px solid #3b82f6; border-radius: 0 0 8px 8px;">
      <strong style="color: #ffffff; font-size: 14px;">
        Total Records: {{ $users->total() }}
      </strong>
    </div>

    <!-- PAGINATION -->
    <div class="d-flex justify-content-center mt-3" id="frontendUsersPagination">
      <div>
        {{ $users->links() }}
      </div>
    </div>
  </div>

  <!-- Assign Locations Modal -->
  <div class="modal fade" id="assignLocationsModal" tabindex="-1" aria-labelledby="assignLocationsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content card-glass"
        style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border: 1px solid rgba(59, 130, 246, 0.3);">
        <div class="modal-header" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
          <!-- <h5 class="modal-title text-white" id="assignLocationsModalLabel">
            <i data-feather="map-pin" class="me-2"></i>Assign CME ,GE'S & Nodes
          </h5> -->
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
            onclick="closeAssignLocationsModal()"
            style="background-color: rgba(255, 255, 255, 0.2); border-radius: 4px; padding: 0.5rem !important; opacity: 1 !important; filter: invert(1); background-size: 1.5em;"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-white">Assign Privileges (CMEs & Groups)</h6>
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="selectAllLocations" onchange="toggleSelectAll(this.checked)">
              <label class="form-check-label text-white" for="selectAllLocations">Grant All Privileges</label>
            </div>
          </div>
          <div id="locationsList" class="locations-container" style="max-height: 500px; overflow-y: auto;">
            <!-- Combined content will be loaded here -->
          </div>
        </div>
        <div class="modal-footer" style="border-top: 2px solid rgba(59, 130, 246, 0.2);">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
            onclick="closeAssignLocationsModal()">
            <i data-feather="x" class="me-2"></i>Cancel
          </button>
          <button type="button" class="btn btn-accent" onclick="saveAssignedLocations()">
            <i data-feather="save" class="me-2"></i>Save
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- User Modal -->
  <div class="modal fade" id="frontendUserModal" tabindex="-1" aria-labelledby="frontendUserModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content card-glass"
        style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border: 1px solid rgba(59, 130, 246, 0.3);">
        <div class="modal-header" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
          <h5 class="modal-title text-white" id="frontendUserModalLabel">
            <i data-feather="user" class="me-2"></i>Frontend User Details
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
            onclick="closeFrontendUserModal()"
            style="background-color: rgba(255, 255, 255, 0.2); border-radius: 4px; padding: 0.5rem !important; opacity: 1 !important; filter: invert(1); background-size: 1.5em;"></button>
        </div>
        <div class="modal-body" id="frontendUserModalBody">
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

    // Frontend User Functions
    let currentFrontendUserId = null;

    function viewFrontendUser(userId) {
      if (!userId) {
        alert('Invalid user ID');
        return;
      }

      currentFrontendUserId = userId;

      const modalElement = document.getElementById('frontendUserModal');
      const modalBody = document.getElementById('frontendUserModalBody');

      modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

      document.body.classList.add('modal-open-blur');

      const modal = new bootstrap.Modal(modalElement, {
        backdrop: false,
        keyboard: true,
        focus: true
      });
      modal.show();

      const removeBackdrop = () => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => {
          backdrop.remove();
        });
      };

      const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          mutation.addedNodes.forEach((node) => {
            if (node.nodeType === 1 && node.classList && node.classList.contains('modal-backdrop')) {
              node.remove();
            }
          });
        });
        removeBackdrop();
      });

      observer.observe(document.body, {
        childList: true,
        subtree: true
      });

      removeBackdrop();
      setTimeout(removeBackdrop, 10);
      setTimeout(removeBackdrop, 50);
      setTimeout(removeBackdrop, 100);

      modalElement.addEventListener('hidden.bs.modal', function () {
        observer.disconnect();
        removeBackdrop();
      }, { once: true });

      fetch(`/admin/frontend-users/${userId}?format=html`, {
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
          const contentType = response.headers.get('content-type');
          if (contentType && contentType.includes('application/json')) {
            return response.json().then(data => {
              throw new Error('Received JSON instead of HTML.');
            });
          }
          return response.text();
        })
        .then(html => {
          if (html.trim().startsWith('{')) {
            console.error('Received JSON instead of HTML');
            modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error: Server returned JSON instead of HTML.</div>';
            return;
          }

          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');

          let contentSection = doc.querySelector('section.content');
          if (!contentSection) {
            contentSection = doc.querySelector('.content');
          }
          if (!contentSection) {
            const mainContent = doc.querySelector('main') || doc.querySelector('[role="main"]');
            if (mainContent) {
              contentSection = mainContent;
            } else {
              contentSection = doc.body;
            }
          }

          let userContent = '';

          const allRows = contentSection.querySelectorAll('.row');
          const seenRows = new Set();

          allRows.forEach(row => {
            const isInHeader = row.closest('.mb-4') && row.closest('.mb-4').querySelector('h2');
            const hasCardGlass = row.querySelector('.card-glass');

            if (!isInHeader && hasCardGlass) {
              const rowHTML = row.outerHTML;
              const rowId = rowHTML.substring(0, 200);
              if (!seenRows.has(rowId)) {
                seenRows.add(rowId);
                userContent += rowHTML;
              }
            }
          });

          const allCards = contentSection.querySelectorAll('.card-glass');
          const seenCards = new Set();

          allCards.forEach(card => {
            const parentRow = card.closest('.row');
            const isInHeader = parentRow && parentRow.closest('.mb-4') && parentRow.closest('.mb-4').querySelector('h2');
            const cardHTML = card.outerHTML;
            const cardId = cardHTML.substring(0, 300);

            if (!isInHeader && !seenCards.has(cardId) && !userContent.includes(cardHTML.substring(0, 100))) {
              seenCards.add(cardId);
              const isInAddedRow = parentRow && userContent.includes(parentRow.outerHTML.substring(0, 100));
              if (!isInAddedRow) {
                userContent += '<div class="mb-3">' + cardHTML + '</div>';
              }
            }
          });

          if (userContent) {
            modalBody.innerHTML = userContent;
            setTimeout(() => {
              feather.replace();
            }, 50);
          } else {
            modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error: Could not load user details.</div>';
          }
        })
        .catch(error => {
          console.error('Error loading user:', error);
          modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error loading user details: ' + error.message + '</div>';
        });

      modalElement.addEventListener('shown.bs.modal', function () {
        feather.replace();
      });

      modalElement.addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-open-blur');
        feather.replace();
      }, { once: true });
    }

    function closeFrontendUserModal() {
      const modalElement = document.getElementById('frontendUserModal');
      if (modalElement) {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
          modal.hide();
        }
      }
      document.body.classList.remove('modal-open-blur');
    }

    let frontendUsersSearchTimeout = null;
    function handleFrontendUsersSearchInput() {
      if (frontendUsersSearchTimeout) clearTimeout(frontendUsersSearchTimeout);
      frontendUsersSearchTimeout = setTimeout(() => {
        loadFrontendUsers();
      }, 500);
    }

    function submitFrontendUsersFilters() {
      loadFrontendUsers();
    }

    function resetFrontendUsersFilters() {
      const form = document.getElementById('frontendUsersFiltersForm');
      if (!form) return;

      form.querySelectorAll('input[type="text"], input[type="date"], select').forEach(input => {
        if (input.type === 'select-one') {
          input.selectedIndex = 0;
        } else {
          input.value = '';
        }
      });

      window.location.href = '{{ route('admin.frontend-users.index') }}';
    }

    function loadFrontendUsers(url = null) {
      const form = document.getElementById('frontendUsersFiltersForm');
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

      const tbody = document.getElementById('frontendUsersTableBody');
      const paginationContainer = document.getElementById('frontendUsersPagination');

      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
      }

      fetch(`{{ route('admin.frontend-users.index') }}?${params.toString()}`, {
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

          const newTbody = doc.querySelector('#frontendUsersTableBody');
          const newPagination = doc.querySelector('#frontendUsersPagination');
          const newTfoot = doc.querySelector('#frontendUsersTableFooter');

          if (newTbody && tbody) {
            tbody.innerHTML = newTbody.innerHTML;
            feather.replace();
          }

          const tfoot = document.querySelector('#frontendUsersTableFooter');
          if (newTfoot && tfoot) {
            tfoot.innerHTML = newTfoot.innerHTML;
          }

          if (newPagination && paginationContainer) {
            paginationContainer.innerHTML = newPagination.innerHTML;
            feather.replace();
          }

          const newUrl = `{{ route('admin.frontend-users.index') }}?${params.toString()}`;
          window.history.pushState({ path: newUrl }, '', newUrl);
        })
        .catch(error => {
          console.error('Error loading users:', error);
          if (tbody) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger">Error loading data. Please refresh the page.</td></tr>';
          }
        });
    }

    document.addEventListener('click', function (e) {
      const paginationLink = e.target.closest('#frontendUsersPagination a');
      if (paginationLink && paginationLink.href && !paginationLink.href.includes('javascript:')) {
        e.preventDefault();
        loadFrontendUsers(paginationLink.href);
      }
    });

    window.addEventListener('popstate', function (e) {
      if (e.state && e.state.path) {
        loadFrontendUsers(e.state.path);
      } else {
        loadFrontendUsers();
      }
    });

    function deleteFrontendUser(userId) {
      if (confirm('Are you sure you want to delete this frontend user?')) {
        fetch(`/admin/frontend-users/${userId}`, {
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
              showNotification('Frontend user deleted successfully!', 'success');
              const row = document.querySelector(`button[onclick="deleteFrontendUser(${userId})"]`).closest('tr');
              if (row) {
                row.remove();
              }
              setTimeout(() => {
                location.reload();
              }, 1000);
            } else {
              showNotification('Error deleting frontend user: ' + (data.message || 'Unknown error'), 'error');
            }
          })
          .catch(error => {
            console.error('Error deleting user:', error);
            showNotification('Error deleting frontend user: ' + error.message, 'error');
          });
      }
    }

    function showNotification(message, type = 'info') {
      const notification = document.createElement('div');
      notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
      notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
      notification.innerHTML = `
              ${message}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
      document.body.appendChild(notification);

      setTimeout(() => {
        if (notification.parentNode) {
          notification.parentNode.removeChild(notification);
        }
      }, 3000);
    }

    // Assign Locations Functions
    let currentAssignUserId = null;
    let assignedCityIds = [];
    let assignedSectorIds = [];
    let privilegeCmeIds = [];
    let privilegeCityIds = [];

    function openAssignLocationsModal(userId) {
      currentAssignUserId = userId;
      const locationsList = document.getElementById('locationsList');

      // Reset Select All checkbox state
      const selectAllCb = document.getElementById('selectAllLocations');
      if (selectAllCb) {
        selectAllCb.checked = false;
        selectAllCb.indeterminate = false;
      }

      locationsList.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

      document.body.classList.add('modal-open-blur');

      const modalElement = document.getElementById('assignLocationsModal');
      const modal = new bootstrap.Modal(modalElement, {
        backdrop: false,
        keyboard: true,
        focus: true
      });
      modal.show();

      const removeBackdrop = () => {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
      };

      removeBackdrop();
      setTimeout(removeBackdrop, 10);
      setTimeout(removeBackdrop, 50);

      modalElement.addEventListener('hidden.bs.modal', function () {
        removeBackdrop();
        document.body.classList.remove('modal-open-blur');
      }, { once: true });

      // Load cities and sectors
      fetch(`/admin/frontend-users/${userId}/assign-locations`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
        credentials: 'same-origin'
      })
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          console.log('Received data:', data); // Debug log
          console.log('User CME IDs:', data.userCmeIds);
          console.log('User City IDs:', data.userCityIds);
          console.log('Assigned Sector IDs:', data.assignedSectorIds);

          // Reset all arrays before setting new values
          assignedCityIds = [];
          assignedSectorIds = [];
          privilegeCmeIds = [];
          privilegeCityIds = [];

          // Set fresh data from backend
          assignedCityIds = data.assignedCityIds || [];
          assignedSectorIds = data.assignedSectorIds || [];
          privilegeCmeIds = data.userCmeIds || [];
          privilegeCityIds = data.userCityIds || [];

          renderCombinedList(data.cities || [], data.allCmes || [], data.allCities || []);
          feather.replace();
        })
        .catch(error => {
          console.error('Error loading locations:', error);
          locationsList.innerHTML = '<div class="text-center py-5 text-danger">Error loading data: ' + error.message + '. Please check console for details.</div>';
        });
    }

    function renderCombinedList(cities, allCmes, allCities) {
      const locationsList = document.getElementById('locationsList');
      let html = '';

      if (!cities || cities.length === 0) {
        locationsList.innerHTML = '<div class="text-center py-5 text-muted">No data available.</div>';
        return;
      }

      // Group cities by CME
      const cmeMap = {};
      cities.forEach(city => {
        const cmeId = city.cme_id || 'no-cme';
        const cmeName = city.cme_name || 'N/A';
        if (!cmeMap[cmeId]) {
          cmeMap[cmeId] = {
            id: cmeId,
            name: cmeName,
            cities: []
          };
        }
        cmeMap[cmeId].cities.push(city);
      });

      html += '<div class="row">';

      // Render CMEs with their cities and sectors - privileges only
      Object.keys(cmeMap).forEach(cmeId => {
        const cmeData = cmeMap[cmeId];
        const cmeCities = cmeData.cities;

        // Check if CME is selected for privileges
        const cmePrivChecked = privilegeCmeIds.map(id => parseInt(id)).includes(parseInt(cmeId));

        html += `
          <div class="col-md-6 mb-4">
            <div class="h-100" style="border: 2px solid rgba(59, 130, 246, 0.3); border-radius: 10px; padding: 15px; background: rgba(59, 130, 246, 0.08);">
              <!-- CME Header with Privilege Checkbox -->
              <div class="d-flex align-items-center mb-3" style="border-bottom: 1px solid rgba(59, 130, 246, 0.2); padding-bottom: 10px;">
                <input type="checkbox"
                       class="form-check-input priv-cme-checkbox"
                       id="priv_cme_${cmeId}"
                       data-cme-id="${cmeId}"
                       ${cmePrivChecked ? 'checked' : ''}
                       onchange="handlePrivCmeChange(${cmeId}, this.checked)"
                       style="width: 20px; height: 20px; cursor: pointer; margin-right: 12px;">
                <label for="priv_cme_${cmeId}" class="text-white fw-bold mb-0" style="cursor: pointer; font-size: 1.1rem; flex-grow: 1;">
                  CME: ${cmeData.name}
                </label>
                <button type="button" class="btn btn-sm btn-link text-white p-0" onclick="toggleCmeDetails('${cmeId}')" style="text-decoration: none;">
                  <i data-feather="chevron-down" style="width: 18px; height: 18px;"></i>
                </button>
              </div>

              <!-- Cities & GE Nodes -->
              <div id="cme_details_${cmeId}" class="ms-3">
        `;

        cmeCities.forEach(city => {
          const cityId = parseInt(city.id);
          const citySectors = city.sectors || [];
          const cityPrivChecked = privilegeCityIds.map(id => parseInt(id)).includes(cityId);

          html += `
            <div class="mb-3" style="border: 1px solid rgba(100, 150, 200, 0.2); border-radius: 8px; padding: 10px; background: rgba(59, 130, 246, 0.03); margin-left: 10px;">
              <div class="d-flex align-items-center mb-2">
                <input type="checkbox"
                       class="form-check-input priv-city-checkbox"
                       id="priv_city_${city.id}"
                       data-city-id="${city.id}"
                       data-cme-id="${cmeId}"
                       ${cityPrivChecked ? 'checked' : ''}
                       onchange="handlePrivCityChange(${city.id}, this.checked)"
                       style="width: 18px; height: 18px; cursor: pointer; margin-right: 10px;">
                <label for="priv_city_${city.id}" class="text-light fw-bold mb-0" style="cursor: pointer; flex-grow: 1;">
                  ${city.name}
                </label>
                ${citySectors.length > 0 ? `
                  <button type="button" class="btn btn-sm btn-link text-white p-0" onclick="toggleCitySectors(${city.id})" style="text-decoration: none; margin-right: 5px;">
                    <i data-feather="chevron-down" style="width: 16px; height: 16px;"></i>
                  </button>
                ` : ''}
              </div>

              <!-- GE Nodes (with checkboxes) -->
              ${citySectors.length > 0 ? `
                <div id="city_sectors_${city.id}" class="ms-4" style="display: block;">
              ` : ''}
          `;

          citySectors.forEach(sector => {
            const sectorChecked = assignedSectorIds.map(id => parseInt(id)).includes(parseInt(sector.id));
            html += `
              <div class="mb-2 d-flex align-items-center">
                <input type="checkbox"
                       class="form-check-input sector-checkbox"
                       id="sector_${sector.id}"
                       data-sector-id="${sector.id}"
                       data-city-id="${city.id}"
                       ${sectorChecked ? 'checked' : ''}
                       onchange="handleSectorCheckboxChange(${city.id}, ${sector.id}, this.checked)"
                       style="width: 16px; height: 16px; cursor: pointer; margin-right: 8px;">
                <label for="sector_${sector.id}" class="text-white-50 mb-0" style="cursor: pointer; font-size: 0.9rem;">
                  ${sector.name}
                </label>
              </div>
            `;
          });

          if (citySectors.length > 0) {
            html += `</div>`;
          }

          html += `
            </div>
          `;
        });

        html += `
              </div>
            </div>
          </div>
        `;
      });

      html += '</div>';

      locationsList.innerHTML = html || '<div class="text-center py-5 text-muted">No data available</div>';
      updateSelectAllCheckbox();
      feather.replace();
    }

    function renderPrivilegesList(allCmes, allCities) {
      const privilegesList = document.getElementById('privilegesList');
      let html = '';

      if (!allCmes || allCmes.length === 0) {
        privilegesList.innerHTML = '<div class="text-center py-5 text-muted">No privileges available.</div>';
        return;
      }

      // Group cities by CME
      const cmeMap = {};
      allCmes.forEach(cme => {
        cmeMap[cme.id] = { ...cme, cities: [] };
      });
      allCities.forEach(city => {
        if (cmeMap[city.cme_id]) {
          cmeMap[city.cme_id].cities.push(city);
        }
      });

      Object.values(cmeMap).forEach(cme => {
        const cmeChecked = privilegeCmeIds.map(id => parseInt(id)).includes(parseInt(cme.id));

        html += `
          <div class="mb-4" style="border: 2px solid rgba(59, 130, 246, 0.3); border-radius: 10px; padding: 15px; background: rgba(59, 130, 246, 0.08);">
            <div class="d-flex align-items-center mb-3" style="border-bottom: 1px solid rgba(59, 130, 246, 0.2); padding-bottom: 10px;">
              <input type="checkbox"
                     class="form-check-input priv-cme-checkbox"
                     id="priv_cme_${cme.id}"
                     data-cme-id="${cme.id}"
                     ${cmeChecked ? 'checked' : ''}
                     onchange="handlePrivCmeChange(${cme.id}, this.checked)"
                     style="width: 20px; height: 20px; cursor: pointer; margin-right: 12px;">
              <label for="priv_cme_${cme.id}" class="text-white fw-bold mb-0" style="cursor: pointer; font-size: 1.1rem; flex-grow: 1;">
                ${cme.name}
              </label>
            </div>
            <div class="ms-3">
        `;

        cme.cities.forEach(city => {
          const cityChecked = privilegeCityIds.map(id => parseInt(id)).includes(parseInt(city.id));
          html += `
            <div class="form-check mb-2">
              <input type="checkbox"
                     class="form-check-input priv-city-checkbox"
                     id="priv_city_${city.id}"
                     data-city-id="${city.id}"
                     data-cme-id="${cme.id}"
                     ${cityChecked ? 'checked' : ''}
                     onchange="handlePrivCityChange(${city.id}, this.checked)"
                     style="cursor: pointer;">
              <label class="form-check-label text-light" for="priv_city_${city.id}" style="cursor: pointer;">
                ${city.name}
              </label>
            </div>
          `;
        });

        html += `</div></div>`;
      });

      privilegesList.innerHTML = html;
      updateSelectAllPrivilegesCheckbox();
    }

    function handlePrivCmeChange(cmeId, checked) {
      // Update state
      if (checked) {
        if (!privilegeCmeIds.includes(cmeId)) privilegeCmeIds.push(cmeId);
      } else {
        privilegeCmeIds = privilegeCmeIds.filter(id => parseInt(id) !== parseInt(cmeId));
      }

      // Check/Uncheck all child cities
      const cityCheckboxes = document.querySelectorAll(`.priv-city-checkbox[data-cme-id="${cmeId}"]`);
      cityCheckboxes.forEach(cb => {
        // We need to trigger the change event logic for each city
        // This ensures that handlePrivCityChange is called, which in turn handles sectors
        const cityId = parseInt(cb.dataset.cityId);

        // Only trigger if the state is actually changing
        if (cb.checked !== checked) {
          cb.checked = checked;
          handlePrivCityChange(cityId, checked);
        }
      });
      updateSelectAllPrivilegesCheckbox();
    }

    function handlePrivCityChange(cityId, checked) {
      if (checked) {
        if (!privilegeCityIds.includes(cityId)) privilegeCityIds.push(cityId);
      } else {
        privilegeCityIds = privilegeCityIds.filter(id => parseInt(id) !== parseInt(cityId));
      }

      // Check/Uncheck all sectors under this city
      const sectorCheckboxes = document.querySelectorAll(`.sector-checkbox[data-city-id="${cityId}"]`);
      sectorCheckboxes.forEach(cb => {
        cb.checked = checked;
        const sectorId = parseInt(cb.dataset.sectorId);
        if (checked) {
          if (!assignedSectorIds.includes(sectorId)) assignedSectorIds.push(sectorId);
        } else {
          assignedSectorIds = assignedSectorIds.filter(id => parseInt(id) !== sectorId);
        }
      });

      updateSelectAllCheckbox();
    }

    function toggleSelectAllPrivileges(checked) {
      const allCmeCbs = document.querySelectorAll('.priv-cme-checkbox');
      const allCityCbs = document.querySelectorAll('.priv-city-checkbox');

      privilegeCmeIds = [];
      privilegeCityIds = [];

      allCmeCbs.forEach(cb => {
        cb.checked = checked;
        const cmeId = parseInt(cb.dataset.cmeId);
        if (checked) {
          privilegeCmeIds.push(cmeId);
        }
      });

      allCityCbs.forEach(cb => {
        cb.checked = checked;
        const cityId = parseInt(cb.dataset.cityId);
        if (checked) {
          privilegeCityIds.push(cityId);
        }
      });
    }

    function updateSelectAllPrivilegesCheckbox() {
      const selectAllCb = document.getElementById('selectAllPrivileges');
      if (!selectAllCb) return;

      const allCmeCbs = document.querySelectorAll('.priv-cme-checkbox');
      const allCityCbs = document.querySelectorAll('.priv-city-checkbox');

      const totalCmes = allCmeCbs.length;
      const checkedCmes = document.querySelectorAll('.priv-cme-checkbox:checked').length;

      const totalCities = allCityCbs.length;
      const checkedCities = document.querySelectorAll('.priv-city-checkbox:checked').length;

      if (totalCmes === 0 && totalCities === 0) {
        selectAllCb.checked = false;
        selectAllCb.indeterminate = false;
        return;
      }

      const allChecked = (totalCmes === checkedCmes) && (totalCities === checkedCities);
      const anyChecked = (checkedCmes > 0 || checkedCities > 0);

      selectAllCb.checked = allChecked;
      selectAllCb.indeterminate = anyChecked && !allChecked;
    }

    function toggleCitySectors(cityId) {
      const sectorsDiv = document.getElementById(`city_sectors_${cityId}`);
      const toggleBtn = document.querySelector(`button[onclick="toggleCitySectors(${cityId})"]`);
      const icon = toggleBtn ? toggleBtn.querySelector('i[data-feather]') : null;

      if (!sectorsDiv) return;

      if (sectorsDiv.style.display === 'none' || !sectorsDiv.style.display) {
        sectorsDiv.style.display = 'block';
        if (icon) icon.setAttribute('data-feather', 'chevron-down');
      } else {
        sectorsDiv.style.display = 'none';
        if (icon) icon.setAttribute('data-feather', 'chevron-right');
      }
      feather.replace();
    }

    function handleCmeCheckboxChange(cmeId, checked) {
      // Get all city checkboxes for this CME
      const cityCheckboxes = document.querySelectorAll(`.city-checkbox[data-cme-id="${cmeId}"]`);

      cityCheckboxes.forEach(cityCheckbox => {
        const cityIdFromCheckbox = parseInt(cityCheckbox.dataset.cityId);

        if (checked) {
          // Check the city checkbox and trigger its change event
          if (!cityCheckbox.checked) {
            cityCheckbox.checked = true;
            handleCityCheckboxChange(cityIdFromCheckbox, true);
          }
        } else {
          // Uncheck the city checkbox and trigger its change event
          if (cityCheckbox.checked) {
            cityCheckbox.checked = false;
            handleCityCheckboxChange(cityIdFromCheckbox, false);
          }
        }
      });

      updateSelectAllCheckbox();
    }

    function toggleCmeDetails(cmeId) {
      const detailsDiv = document.getElementById(`cme_details_${cmeId}`);
      const toggleBtn = document.querySelector(`button[onclick="toggleCmeDetails('${cmeId}')"]`);
      const icon = toggleBtn ? toggleBtn.querySelector('i[data-feather]') : null;

      if (!detailsDiv) return;

      if (detailsDiv.style.display === 'none' || !detailsDiv.style.display) {
        detailsDiv.style.display = 'block';
        if (icon) icon.setAttribute('data-feather', 'chevron-down');
      } else {
        detailsDiv.style.display = 'none';
        if (icon) icon.setAttribute('data-feather', 'chevron-right');
      }
      feather.replace();
    }

    function handleCityCheckboxChange(cityId, checked) {
      const cityCheckbox = document.getElementById(`city_${cityId}`);
      const sectorsDiv = document.getElementById(`city_sectors_${cityId}`);
      const sectorCheckboxes = sectorsDiv ? sectorsDiv.querySelectorAll('.sector-checkbox') : [];
      const cityIdInt = parseInt(cityId);

      if (checked) {
        // Add city to assigned list (if not already there)
        if (!assignedCityIds.map(id => parseInt(id)).includes(cityIdInt)) {
          assignedCityIds.push(cityIdInt);
        }

        // Automatically select all sectors of this city
        sectorCheckboxes.forEach(cb => {
          const sectorId = parseInt(cb.dataset.sectorId);
          cb.checked = true;
          if (!assignedSectorIds.map(id => parseInt(id)).includes(sectorId)) {
            assignedSectorIds.push(sectorId);
          }
        });

        // Remove city from city-only list since sectors are now selected
        // assignedCityIds = assignedCityIds.filter(id => parseInt(id) !== cityIdInt);
      } else {
        // Remove city from city-only list
        assignedCityIds = assignedCityIds.filter(id => parseInt(id) !== cityIdInt);

        // Also uncheck all sectors of this city
        sectorCheckboxes.forEach(cb => {
          cb.checked = false;
          const sectorId = parseInt(cb.dataset.sectorId);
          assignedSectorIds = assignedSectorIds.filter(id => parseInt(id) !== sectorId);
        });
      }
      updateSelectAllCheckbox();
    }

    function handleSectorCheckboxChange(cityId, sectorId, checked) {
      const cityCheckbox = document.getElementById(`city_${cityId}`);
      const cityIdInt = parseInt(cityId);
      const sectorIdInt = parseInt(sectorId);

      if (checked) {
        // Add sector to assigned list
        if (!assignedSectorIds.map(id => parseInt(id)).includes(sectorIdInt)) {
          assignedSectorIds.push(sectorIdInt);
        }
        // Keep city checkbox checked even if sectors are selected
        // This way both city and sectors can be assigned
      } else {
        // Remove sector from assigned list
        assignedSectorIds = assignedSectorIds.filter(id => parseInt(id) !== sectorIdInt);

        // Check if any sector of this city is still selected
        const sectorsDiv = document.getElementById(`city_sectors_${cityId}`);
        const sectorCheckboxes = sectorsDiv ? sectorsDiv.querySelectorAll('.sector-checkbox') : [];
        const hasAnySectorChecked = Array.from(sectorCheckboxes).some(cb => cb.checked);

        // Don't uncheck city checkbox automatically - let user control it
        // If no sectors checked and city checkbox is checked, city-only assignment will be saved
      }
      updateSelectAllCheckbox();
    }

    function toggleSelectAll(checked) {
      const allCmeCbs = document.querySelectorAll('.priv-cme-checkbox');
      const allCityCbs = document.querySelectorAll('.priv-city-checkbox');
      const allSectorCbs = document.querySelectorAll('.sector-checkbox');

      privilegeCmeIds = [];
      privilegeCityIds = [];
      assignedSectorIds = [];

      if (checked) {
        allCmeCbs.forEach(cb => {
          cb.checked = true;
          const cmeId = parseInt(cb.dataset.cmeId);
          privilegeCmeIds.push(cmeId);
        });

        allCityCbs.forEach(cb => {
          cb.checked = true;
          const cityId = parseInt(cb.dataset.cityId);
          privilegeCityIds.push(cityId);
        });

        allSectorCbs.forEach(cb => {
          cb.checked = true;
          const sectorId = parseInt(cb.dataset.sectorId);
          assignedSectorIds.push(sectorId);
        });
      } else {
        allCmeCbs.forEach(cb => {
          cb.checked = false;
        });

        allCityCbs.forEach(cb => {
          cb.checked = false;
        });

        allSectorCbs.forEach(cb => {
          cb.checked = false;
        });
      }

      updateSelectAllCheckbox();
    }

    function updateSelectAllCheckbox() {
      const selectAllCheckbox = document.getElementById('selectAllLocations');
      if (!selectAllCheckbox) return;

      const allCmeCbs = document.querySelectorAll('.priv-cme-checkbox');
      const allCityCbs = document.querySelectorAll('.priv-city-checkbox');
      const allSectorCbs = document.querySelectorAll('.sector-checkbox');

      const totalCmes = allCmeCbs.length;
      const checkedCmes = document.querySelectorAll('.priv-cme-checkbox:checked').length;

      const totalCities = allCityCbs.length;
      const checkedCities = document.querySelectorAll('.priv-city-checkbox:checked').length;

      const totalSectors = allSectorCbs.length;
      const checkedSectors = document.querySelectorAll('.sector-checkbox:checked').length;

      if (totalCmes === 0 && totalCities === 0 && totalSectors === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        return;
      }

      const allChecked = (totalCmes === checkedCmes) && (totalCities === checkedCities) && (totalSectors === checkedSectors);
      const anyChecked = (checkedCmes > 0 || checkedCities > 0 || checkedSectors > 0);

      selectAllCheckbox.checked = allChecked;
      selectAllCheckbox.indeterminate = anyChecked && !allChecked;
    }

    function saveAssignedLocations() {
      if (!currentAssignUserId) {
        showNotification('Error: No user selected', 'error');
        return;
      }

      const saveBtn = event.target;
      const originalText = saveBtn.innerHTML;
      saveBtn.disabled = true;
      saveBtn.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Saving...';

      fetch(`/admin/frontend-users/${currentAssignUserId}/assign-locations`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
          body: JSON.stringify({
            privilege_cme_ids: privilegeCmeIds,
            privilege_city_ids: privilegeCityIds,
            sector_ids: assignedSectorIds
          }),
        credentials: 'same-origin'
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showNotification('Locations assigned successfully!', 'success');
            const modalElement = document.getElementById('assignLocationsModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
              modal.hide();
            }
          } else {
            showNotification('Error: ' + (data.message || 'Failed to assign locations'), 'error');
          }
        })
        .catch(error => {
          console.error('Error assigning locations:', error);
          showNotification('Error assigning locations: ' + error.message, 'error');
        })
        .finally(() => {
          saveBtn.disabled = false;
          saveBtn.innerHTML = originalText;
        });
    }

    function closeAssignLocationsModal() {
      const modalElement = document.getElementById('assignLocationsModal');
      if (modalElement) {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
          modal.hide();
        }
      }
      document.body.classList.remove('modal-open-blur');
      currentAssignUserId = null;
      assignedCityIds = [];
      assignedSectorIds = [];
    }
  </script>
@endpush
