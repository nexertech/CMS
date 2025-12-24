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
        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
          <th>Username</th>
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
          <td>
            <div class="fw-bold">{{ $house->username }}</div>
          </td>
          <td>{{ $house->city ? $house->city->name : 'N/A' }}</td>
          <td>{{ $house->sector ? $house->sector->name : 'N/A' }}</td>
          <td>{{ Str::limit($house->address, 30) ?: 'N/A' }}</td>
          <td>
            <span class="badge {{ $house->status === 'active' ? 'bg-success' : 'bg-danger' }}" style="color: #ffffff !important;">
              {{ ucfirst($house->status ?? 'inactive') }}
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
              <button class="btn btn-outline-danger btn-sm" onclick="deleteHouse({{ $house->id }})" title="Delete" data-house-id="{{ $house->id }}" style="padding: 3px 8px;">
                <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
              </button>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" class="text-center text-muted py-4">
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
          <p class="mb-0"><strong>Username:</strong> <span id="houseUsernameModal"></span></p>
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
      document.getElementById('housesFiltersForm').submit();
    }, 500);
  }

  function submitHousesFilters() {
    document.getElementById('housesFiltersForm').submit();
  }

  function resetHousesFilters() {
    window.location.href = '{{ route('admin.houses.index') }}';
  }

  function viewHouse(houseId) {
    if (!houseId) return;
    
    const modalElement = document.getElementById('viewHouseModal');
    const modalBody = document.getElementById('viewHouseModalBody');
    
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
    
    // Load house details via AJAX
    fetch(`/admin/houses/${houseId}`, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'text/html',
      }
    })
    .then(response => {
      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
      return response.text();
    })
    .then(html => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      
      // Try to find the card-glass content
      let content = doc.querySelector('.card-glass');
      if (!content) {
        // Fallback to body content if card-glass not found
        content = doc.body;
      }
      
      modalBody.innerHTML = content.innerHTML;
      
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
      
      // Initialize icons and apply borders in the newly loaded content
      setTimeout(() => {
        feather.replace();
        applyTableBorders();
      }, 50);
    })
    .catch(error => {
      console.error('Error loading house details:', error);
      modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error loading house details. Please try again.</div>';
    });
    
    // Remove blur when modal is hidden
    modalElement.addEventListener('hidden.bs.modal', function() {
      document.body.classList.remove('modal-open-blur');
    }, { once: true });
  }

  let currentDeleteHouseId = null;
  
  function deleteHouse(houseId) {
    const row = document.querySelector(`button[data-house-id="${houseId}"]`)?.closest('tr');
    if (!row) return;
    
    const houseIdCell = row.cells[0].textContent.trim();
    const houseUsernameCell = row.cells[1].querySelector('.fw-bold')?.textContent || 'Unknown';
    
    document.getElementById('houseIdModal').textContent = houseIdCell;
    document.getElementById('houseUsernameModal').textContent = houseUsernameCell;
    
    currentDeleteHouseId = houseId;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteHouseModal'));
    modal.show();
    
    // Add blur effect to background
    document.body.classList.add('modal-open-blur');
    
    // Remove blur when modal is hidden
    document.getElementById('deleteHouseModal').addEventListener('hidden.bs.modal', function() {
      document.body.classList.remove('modal-open-blur');
    }, { once: true });
  }
  
  document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    if (!currentDeleteHouseId) return;
    
    const houseId = currentDeleteHouseId;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const btn = this;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i data-feather="loader" class="spinning"></i> Deleting...';
    
    fetch(`/admin/houses/${houseId}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteHouseModal'));
        modal.hide();
        location.reload();
      } else {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('Error: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(error => {
      btn.disabled = false;
      btn.innerHTML = originalText;
      alert('Error deleting house: ' + error.message);
    });
    
    currentDeleteHouseId = null;
  });
</script>
@endpush
