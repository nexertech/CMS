@extends('layouts.sidebar')

@section('title', 'Houses Management — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Houses Management</h2>
      <p class="text-light">Manage house records and information</p>
    </div>
    <a href="{{ route('admin.houses.create') }}" class="btn btn-outline-secondary">
      <i data-feather="plus" class="me-2"></i>Add New House
    </a>
  </div>
</div>

<!-- FILTERS -->
<div class="card-glass mb-4" style="display: inline-block; width: fit-content;">
  <form id="housesFiltersForm" method="GET" action="{{ route('admin.houses.index') }}">
  <div class="row g-2 align-items-end">
    <div class="col-auto">
      <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">Search</label>
      <input type="text" class="form-control" id="searchInput" name="search" placeholder="Search..." 
             value="{{ request('search') }}" oninput="handleHousesSearchInput()" style="font-size: 0.9rem; width: 180px;">
    </div>
    <div class="col-auto">
      <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">GE Group</label>
      <select class="form-select" name="city_id" onchange="submitHousesFilters()" style="font-size: 0.9rem; width: 140px;">
        <option value="" {{ request('city_id') ? '' : 'selected' }}>All</option>
        @if(isset($cities) && $cities->count() > 0)
          @foreach($cities as $city)
            <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
          @endforeach
        @endif
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">GE Node</label>
      <select class="form-select" name="sector_id" onchange="submitHousesFilters()" style="font-size: 0.9rem; width: 140px;">
        <option value="" {{ request('sector_id') ? '' : 'selected' }}>All</option>
        @if(isset($sectors) && $sectors->count() > 0)
          @foreach($sectors as $sector)
            <option value="{{ $sector->id }}" {{ request('sector_id') == $sector->id ? 'selected' : '' }}>{{ $sector->name }}</option>
          @endforeach
        @endif
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">Status</label>
      <select class="form-select" name="status" onchange="submitHousesFilters()" style="font-size: 0.9rem; width: 120px;">
        <option value="" {{ request('status') ? '' : 'selected' }}>All</option>
        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label small text-muted mb-1" style="font-size: 0.8rem;">&nbsp;</label>
      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetHousesFilters()" style="font-size: 0.9rem; padding: 0.35rem 0.8rem;">
        <i data-feather="refresh-cw" class="me-1" style="width: 14px; height: 14px;"></i>Reset
      </button>
    </div>
  </div>
  </form>
</div>

<!-- HOUSES TABLE -->
<div class="card-glass">
  <div class="table-responsive">
    <table class="table table-dark table-sm" id="housesTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>House No</th>
          <th>Name</th>
          <th>Type</th>
          <th>GE Group</th>
          <th>GE Node</th>
          <th>Address</th>
          <th>Status</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="housesTableBody">
        @forelse($houses as $house)
        <tr>
          <td>{{ $house->id }}</td>
          <td class="px-4 py-2 whitespace-nowrap">{{ $house->house_no }}</td>
          <td>{{ $house->name ?: 'N/A' }}</td>
          <td>{{ $house->type ?: 'N/A' }}</td>
          <td>{{ $house->city ? $house->city->name : 'N/A' }}</td>
          <td>{{ $house->sector ? $house->sector->name : 'N/A' }}</td>
          <td>{{ Str::limit($house->address, 30) ?: 'N/A' }}</td>
          <td>
            <span class="badge {{ $house->status === 1 ? 'bg-success' : 'bg-danger' }}" style="color: #ffffff !important;">
              {{ ($house->status ? 'Active' : 'Inactive') }}
            </span>
          </td>
          <td>{{ $house->created_at ? $house->created_at->format('M d, Y') : 'N/A' }}</td>
          <td>
            <div class="btn-group" role="group">
              <button onclick="viewHouse({{ $house->id }})" class="btn btn-outline-success btn-sm" title="View Details" style="padding: 3px 8px;">
                <i data-feather="eye" style="width: 16px; height: 16px;"></i>
              </button>
              <a href="{{ route('admin.houses.edit', $house) }}" class="btn btn-outline-primary btn-sm" title="Edit" style="padding: 3px 8px;">
                <i data-feather="edit" style="width: 16px; height: 16px;"></i>
              </a>
              <button class="btn btn-outline-danger btn-sm" onclick="deleteHouse({{ $house->id }}, '{{ $house->house_no }}')" title="Delete" data-house-id="{{ $house->id }}" style="padding: 3px 8px;">
                <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
              </button>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="10" class="text-center text-muted py-4">
            <i data-feather="home" class="feather-lg mb-2"></i>
            <div>No houses found</div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  
  <!-- TOTAL RECORDS -->
  <div id="housesTableFooter" class="text-center py-2 mt-2" style="background-color: rgba(59, 130, 246, 0.2); border-top: 2px solid #3b82f6; border-radius: 0 0 8px 8px;">
    <strong style="color: #ffffff; font-size: 14px;">
      Total Records: {{ $houses->total() }}
    </strong>
  </div>
  
  <!-- PAGINATION -->
  <div class="d-flex justify-content-center mt-4" id="housesPagination">
    <div>
      {{ $houses->links() }}
    </div>
  </div>
</div>

<!-- View House Modal -->
<div class="modal fade" id="viewHouseModal" tabindex="-1" aria-labelledby="viewHouseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content card-glass" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border: 1px solid rgba(59, 130, 246, 0.3);">
      <div class="modal-header" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
        <h5 class="modal-title text-white" id="viewHouseModalLabel">
          <i data-feather="home" class="me-2"></i>House Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background-color: #888; opacity: 1 !important;"></button>
      </div>
      <div class="modal-body" id="viewHouseModalBody">
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Delete House Modal -->
<div class="modal fade" id="deleteHouseModal" tabindex="-1" aria-labelledby="deleteHouseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border: 1px solid rgba(239, 68, 68, 0.3);">
      <div class="modal-header" style="border-bottom: 1px solid rgba(239, 68, 68, 0.2);">
        <h5 class="modal-title text-white" id="deleteHouseModalLabel">
          <i data-feather="alert-triangle" class="me-2 text-danger"></i>Delete House
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background-color: #888; opacity: 1 !important;"></button>
      </div>
      <div class="modal-body">
        <p class="text-white mb-3">
          Are you sure you want to delete this house? This action cannot be undone.
        </p>
        <div class="alert alert-warning" role="alert">
          <i data-feather="info" class="me-2"></i>
          <strong>Note:</strong> This will soft delete the house record.
        </div>
        <div id="houseDetails" class="text-white">
          <p class="mb-1"><strong>House ID:</strong> <span id="houseIdModal"></span></p>
          <p class="mb-0"><strong>House No:</strong> <span id="houseUsernameModal"></span></p>
        </div>
      </div>
      <div class="modal-footer" style="border-top: 1px solid rgba(239, 68, 68, 0.2);">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i data-feather="x" class="me-1"></i>Cancel
        </button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
          <i data-feather="trash-2" class="me-1"></i>Delete House
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  feather.replace();
  
  let housesSearchTimeout = null;
  function handleHousesSearchInput() {
    if (housesSearchTimeout) clearTimeout(housesSearchTimeout);
    housesSearchTimeout = setTimeout(() => {
      loadHouses();
    }, 500);
  }

  function submitHousesFilters() {
    loadHouses();
  }

  function resetHousesFilters() {
    document.getElementById('housesFiltersForm').reset();
    window.location.href = '{{ route('admin.houses.index') }}';
  }

  function loadHouses(url = null) {
    const form = document.getElementById('housesFiltersForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();

    if (url) {
      const urlObj = new URL(url, window.location.origin);
      urlObj.searchParams.forEach((value, key) => {
        params.append(key, value);
      });
    } else {
      for (const [key, value] of formData.entries()) {
        if (value) params.append(key, value);
      }
    }

    const tbody = document.getElementById('housesTableBody');
    const paginationContainer = document.getElementById('housesPagination');
    const footer = document.getElementById('housesTableFooter');

    if (tbody) {
      tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
    }

    fetch(`{{ route('admin.houses.index') }}?${params.toString()}`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.html) {
        // Parse the full section HTML returned by the server
        const parser = new DOMParser();
        const doc = parser.parseFromString(data.html, 'text/html');
        const newTbody = doc.querySelector('#housesTableBody');
        
        if (tbody && newTbody) {
          tbody.innerHTML = newTbody.innerHTML;
          feather.replace();
        }
      }
      if (paginationContainer) {
        paginationContainer.innerHTML = data.pagination;
        feather.replace();
      }
      if (footer) {
        footer.innerHTML = `<strong style="color: #ffffff; font-size: 14px;">Total Records: ${data.total}</strong>`;
      }
      
      const newUrl = `{{ route('admin.houses.index') }}?${params.toString()}`;
      window.history.pushState({ path: newUrl }, '', newUrl);
    })
    .catch(error => {
      console.error('Error loading houses:', error);
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger">Error loading data.</td></tr>';
      }
    });
  }

  // Intercept pagination clicks
  document.addEventListener('click', function(e) {
    const paginationLink = e.target.closest('#housesPagination a');
    if (paginationLink && paginationLink.href && !paginationLink.href.includes('javascript:')) {
      e.preventDefault();
      loadHouses(paginationLink.href);
    }
  });

  // Handle browser back/forward
  window.addEventListener('popstate', function(e) {
    if (e.state && e.state.path) {
      loadHouses(e.state.path);
    } else {
      loadHouses();
    }
  });

  // House-specific functions
  window.viewHouse = function(houseId) {
    const modalElement = document.getElementById('viewHouseModal');
    const modalBody = document.getElementById('viewHouseModalBody');
    
    // Show loading spinner
    modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    // Add blur effect to background
    document.body.classList.add('modal-open-blur');
    
    const modal = new bootstrap.Modal(modalElement, {
      backdrop: false,
      keyboard: true,
      focus: true
    });
    modal.show();
    
    // Ensure any backdrop that might be created is removed
    const removeBackdrop = () => {
      const backdrops = document.querySelectorAll('.modal-backdrop');
      backdrops.forEach(backdrop => backdrop.remove());
    };
    
    // Use MutationObserver for safety
    const observer = new MutationObserver(removeBackdrop);
    observer.observe(document.body, { childList: true, subtree: true });
    
    removeBackdrop();
    
    modalElement.addEventListener('hidden.bs.modal', function() {
      document.body.classList.remove('modal-open-blur');
      observer.disconnect();
      removeBackdrop();
    }, { once: true });
    
    fetch(`/admin/houses/${houseId}`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'text/html'
      }
    })
    .then(response => response.text())
    .then(html => {
      modalBody.innerHTML = html;
      feather.replace();
    })
    .catch(error => {
      console.error('Error loading house details:', error);
      modalBody.innerHTML = '<div class="alert alert-danger">Error loading house details.</div>';
    });
  };

  window.deleteHouse = function(houseId, houseNo = '') {
    const modalElement = document.getElementById('deleteHouseModal');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const idSpan = document.getElementById('houseIdModal');
    const noSpan = document.getElementById('houseUsernameModal');
    
    idSpan.textContent = houseId;
    if (noSpan) noSpan.textContent = houseNo;
    
    // Add blur effect to background
    document.body.classList.add('modal-open-blur');
    
    const modal = new bootstrap.Modal(modalElement, {
      backdrop: false,
      keyboard: true,
      focus: true
    });
    modal.show();
    
    // Ensure any backdrop that might be created is removed
    const removeBackdrop = () => {
      const backdrops = document.querySelectorAll('.modal-backdrop');
      backdrops.forEach(backdrop => backdrop.remove());
    };
    
    // Use MutationObserver for safety
    const observer = new MutationObserver(removeBackdrop);
    observer.observe(document.body, { childList: true, subtree: true });
    
    removeBackdrop();
    
    modalElement.addEventListener('hidden.bs.modal', function() {
      document.body.classList.remove('modal-open-blur');
      observer.disconnect();
      removeBackdrop();
    }, { once: true });
    
    confirmBtn.onclick = function() {
      confirmBtn.disabled = true;
      confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Deleting...';
      
      fetch(`/admin/houses/${houseId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          modal.hide();
          loadHouses(); // Refresh table
        } else {
          alert('Error deleting house: ' + data.message);
          confirmBtn.disabled = false;
          confirmBtn.innerHTML = '<i data-feather="trash-2" class="me-1"></i>Delete House';
          feather.replace();
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the house.');
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i data-feather="trash-2" class="me-1"></i>Delete House';
        feather.replace();
      });
    };
  };
</script>
@endpush
