@extends('layouts.sidebar')

@section('title', 'Spare Parts Management — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class=" mb-2">Product Management</h2>
      <p class="text-light">Manage inventory and Product</p>
    </div>
    <a href="{{ route('admin.spares.create') }}" class="btn btn-outline-secondary">
      <i class="fas fa-plus me-2"></i>Add Product
    </a>
  </div>
</div>

<!-- FILTERS -->
<div class="card-glass mb-4" style="display: inline-block; width: fit-content;">
  <form id="sparesFiltersForm" method="GET" action="{{ route('admin.spares.index') }}">
    <div class="row g-2 align-items-end">
      <div class="col-auto">
        <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">Search</label>
        <input type="text" class="form-control" id="searchInput" name="search" placeholder="Search..." 
               value="{{ request('search') }}" oninput="handleSparesSearchInput()" style="font-size: 0.9rem; width: 180px;">
      </div>
      <div class="col-auto">
        <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">Category</label>
        <select class="form-select" name="category" id="categoryFilter" onchange="submitSparesFilters()" style="font-size: 0.9rem; width: 160px;">
          <option value="">All Categories</option>
          @foreach($categories as $cat)
          <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-auto">
        <label class="form-label small mb-1" style="font-size: 0.8rem; color: #000000 !important; font-weight: 500;">Stock</label>
        <select class="form-select" name="stock_status" onchange="submitSparesFilters()" style="font-size: 0.9rem; width: 130px;">
          <option value="">All</option>
          <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
          <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
          <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
        </select>
      </div>
      <div class="col-auto">
        <label class="form-label small text-muted mb-1" style="font-size: 0.8rem;">&nbsp;</label>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetSparesFilters()" style="font-size: 0.9rem; padding: 0.35rem 0.8rem;">
          <i data-feather="refresh-cw" class="me-1" style="width: 14px; height: 14px;"></i>Reset
        </button>
      </div>
    </div>
  </form>
</div>

<!-- SPARES TABLE -->
<div class="card-glass">
  <div class="table-responsive" style="overflow-x: auto;">
    <table class="table table-dark table-sm" style="margin-bottom: 0;">
      <thead>
        <tr>
          <th style="padding: 0.4rem 0.5rem; font-size: 0.8rem; white-space: nowrap;">Sr.No</th>
                    <th style="padding: 0.4rem 0.5rem; font-size: 0.8rem; white-space: nowrap;">Product Name</th>

          <th style="padding: 0.4rem 0.5rem; font-size: 0.8rem; white-space: nowrap;">Brand Name</th>
                    <th style="padding: 0.4rem 0.5rem; font-size: 0.8rem; white-space: nowrap;">Product Code</th>

          <th style="padding: 0.4rem 0.5rem; font-size: 0.8rem; white-space: nowrap;">Category</th>
          <th style="padding: 0.4rem 0.5rem; font-size: 0.8rem; white-space: nowrap;">Total Received</th>
          <th style="padding: 0.4rem 0.5rem; font-size: 0.8rem; white-space: nowrap;">Issued Qty</th>
          <th style="padding: 0.4rem 0.5rem; font-size: 0.8rem; white-space: nowrap;">Balance Qty</th>
          <th style="padding: 0.4rem 0.5rem; font-size: 0.8rem; white-space: nowrap;">% Utilized</th>
          <th style="padding: 0.4rem 0.5rem; font-size: 0.8rem; white-space: nowrap;">Stock Status</th>
          <th style="padding: 0.4rem 0.5rem; font-size: 0.8rem; white-space: nowrap;">Last Stock Out</th>
          <th style="padding: 0.4rem 0.5rem; font-size: 0.8rem; white-space: nowrap;">Actions</th>
        </tr>
      </thead>
      <tbody id="sparesTableBody">
        @forelse($spares as $spare)
        <tr>
          <td class="text-muted" style="padding: 0.4rem 0.5rem;">{{ ($spares->currentPage() - 1) * $spares->perPage() + $loop->iteration }}</td>
                    <td style="padding: 0.4rem 0.5rem;">{{ $spare->item_name }}</td>

          <td style="padding: 0.4rem 0.5rem;">{{ $spare->brand_name ?? 'N/A' }}</td>
                    <td style="padding: 0.4rem 0.5rem;">{{ $spare->product_code ?? 'N/A' }}</td>

          <td style="padding: 0.4rem 0.5rem;">{{ ucfirst($spare->category ?? 'N/A') }}</td>
          <td style="padding: 0.4rem 0.5rem;"><span class="text-success">{{ number_format((float)($spare->total_received_quantity ?? 0), 0) }}</span></td>
          <td style="padding: 0.4rem 0.5rem;"><span class="text-danger">{{ number_format((float)($spare->issued_quantity ?? 0), 0) }}</span></td>
          <td style="padding: 0.4rem 0.5rem;">{{ number_format((float)($spare->stock_quantity ?? 0), 0) }}</td>
          <td style="padding: 0.4rem 0.5rem;">{{ number_format((float)($spare->utilization_percent ?? 0), 0) }}%</td>
          <td style="padding: 0.4rem 0.5rem;">
            @if(($spare->stock_quantity ?? 0) <= 0)
              <span class="badge bg-danger" style="font-size: 0.75rem; color: #ffffff !important;">Out</span>
            @elseif(($spare->stock_quantity ?? 0) <= ($spare->threshold_level ?? 0))
              <span class="badge bg-warning" style="font-size: 0.75rem; color: #ffffff !important;">Low</span>
            @else
              <span class="badge bg-success" style="font-size: 0.75rem; color: #ffffff !important;">In</span>
            @endif
          </td>
          <td style="padding: 0.4rem 0.5rem; font-size: 0.8rem;">
            @if(($spare->stock_quantity ?? 0) <= 0 && $spare->last_stock_out)
              <span class="text-danger">{{ $spare->last_stock_out->format('d M Y') }}</span>
            @elseif($spare->last_stock_out)
              {{ $spare->last_stock_out->format('d M Y') }}
            @else
              <span class="text-muted">Never</span>
            @endif
          </td>
          <td style="padding: 0.4rem 0.5rem;">
            <div class="btn-group" role="group">
              <button class="btn btn-outline-success btn-sm" style="padding: 3px 8px;" onclick="viewSpare('{{ $spare->id }}')" title="View">
                <i data-feather="eye" style="width: 16px; height: 16px;"></i>
              </button>
              <a href="{{ route('admin.spares.edit', $spare) }}" class="btn btn-outline-primary btn-sm" style="padding: 3px 8px;" title="Edit">
                <i data-feather="edit" style="width: 16px; height: 16px;"></i>
              </a>
              <button class="btn btn-outline-danger btn-sm" style="padding: 3px 8px;" onclick="deleteSpare('{{ $spare->id }}')" title="Delete">
                <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
              </button>
            </div>
          </td>
        </tr>
@empty
<tr>
  <td colspan="14" class="text-center py-4">
    <i data-feather="package" class="feather-lg mb-2"></i>
    <div>No Product found</div>
  </td>
</tr>
@endforelse
</tbody>
</table>
</div>

<!-- TOTAL RECORDS -->
<div id="sparesTableFooter" class="text-center py-2 mt-2" style="background-color: rgba(59, 130, 246, 0.2); border-top: 2px solid #3b82f6; border-radius: 0 0 8px 8px;">
  <strong style="color: #ffffff; font-size: 14px;">
    Total Records: {{ $spares->total() }}
  </strong>
</div>

<!-- PAGINATION -->
<div class="d-flex justify-content-center mt-3" id="sparesPagination">
  <div>
    {{ $spares->links() }}
  </div>
</div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="spareModal" tabindex="-1" aria-labelledby="spareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content card-glass" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border: 1px solid rgba(59, 130, 246, 0.3);">
            <div class="modal-header" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
                <h5 class="modal-title text-white" id="spareModalLabel">
                    <i data-feather="package" class="me-2"></i>Product Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="closeSpareModal()" style="background-color: rgba(255, 255, 255, 0.2); border-radius: 4px; padding: 0.5rem !important; opacity: 1 !important; filter: invert(1); background-size: 1.5em;"></button>
            </div>
            <div class="modal-body" id="spareModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 2px solid rgba(59, 130, 246, 0.2);">
                <a href="#" id="sparePrintBtn" class="btn btn-outline-primary" target="_blank" style="display: none;">
                    <i data-feather="printer" class="me-2" style="width: 16px; height: 16px;"></i>Print Slip
                </a>
            </div>
        </div>
    </div>
</div>


@endsection

@push('styles')
@endpush

@push('scripts')
<script>
  // Spare parts JavaScript loaded
  feather.replace();

  // Debounced search input handler
  let sparesSearchTimeout = null;
  function handleSparesSearchInput() {
    if (sparesSearchTimeout) clearTimeout(sparesSearchTimeout);
    sparesSearchTimeout = setTimeout(() => {
      loadSpares();
    }, 500);
  }

  // Auto-submit for select filters
  function submitSparesFilters() {
    loadSpares();
  }

  // Reset filters function
  function resetSparesFilters() {
    const form = document.getElementById('sparesFiltersForm');
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
    window.location.href = '{{ route('admin.spares.index') }}';
  }

  // Load Spares via AJAX
  function loadSpares(url = null) {
    const form = document.getElementById('sparesFiltersForm');
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

    const tbody = document.getElementById('sparesTableBody');
    const paginationContainer = document.getElementById('sparesPagination');
    const footerContainer = document.getElementById('sparesTableFooter');
    
    if (tbody) {
      tbody.innerHTML = '<tr><td colspan="13" class="text-center py-4"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
    }

    fetch(`{{ route('admin.spares.index') }}?${params.toString()}`, {
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
      
      const newTbody = doc.querySelector('#sparesTableBody');
      const newPagination = doc.querySelector('#sparesPagination');
      const newFooter = doc.querySelector('#sparesTableFooter');
      
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

      const newUrl = `{{ route('admin.spares.index') }}?${params.toString()}`;
      window.history.pushState({path: newUrl}, '', newUrl);
    })
    .catch(error => {
      console.error('Error loading spares:', error);
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="13" class="text-center py-4 text-danger">Error loading data. Please refresh the page.</td></tr>';
      }
    });
  }

  // Handle pagination clicks
  document.addEventListener('click', function(e) {
    const paginationLink = e.target.closest('#sparesPagination a');
    if (paginationLink && paginationLink.href && !paginationLink.href.includes('javascript:')) {
      e.preventDefault();
      loadSpares(paginationLink.href);
    }
  });

  // Handle browser back/forward buttons
  window.addEventListener('popstate', function(e) {
    if (e.state && e.state.path) {
      loadSpares(e.state.path);
    } else {
      loadSpares();
    }
  });

  // Spare Functions
  let currentSpareId = null;
  
  function viewSpare(spareId) {
    if (!spareId) {
      alert('Invalid product ID');
      return;
    }
    
    currentSpareId = spareId;
    
    const modalElement = document.getElementById('spareModal');
    const modalBody = document.getElementById('spareModalBody');
    const printBtn = document.getElementById('sparePrintBtn');
    
    // Hide print button initially
    if (printBtn) {
      printBtn.style.display = 'none';
    }
    
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
    
    // Load spare details via AJAX - force HTML response
    fetch(`/admin/spares/${spareId}?format=html`, {
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
      
      // Extract the spare details sections
      let spareContent = '';
      
      // Get all rows that contain spare information (skip page header)
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
            spareContent += rowHTML;
          }
        }
      });
      
      // If no rows found, fallback to extracting individual cards
      if (!spareContent) {
        const allCards = contentSection.querySelectorAll('.card-glass');
        const seenCards = new Set();
        
        allCards.forEach(card => {
          // Skip cards that are in page headers
          const parentRow = card.closest('.row');
          const isInHeader = parentRow && parentRow.closest('.mb-4') && parentRow.closest('.mb-4').querySelector('h2');
          
          if (!isInHeader) {
            const cardHTML = card.outerHTML;
            const cardId = cardHTML.substring(0, 300);
            if (!seenCards.has(cardId)) {
              seenCards.add(cardId);
              spareContent += '<div class="mb-3">' + cardHTML + '</div>';
            }
          }
        });
      }
      
      if (spareContent) {
        modalBody.innerHTML = spareContent;
        
        // Setup print button with spare ID
        const printBtn = document.getElementById('sparePrintBtn');
        if (printBtn && spareId) {
          printBtn.href = `/admin/spares/${spareId}/print-slip`;
          printBtn.style.display = 'inline-block';
        }
        
        // Initialize history loading after content is loaded
        setTimeout(() => {
          const stockHistoryContent = document.getElementById('stockHistoryContent');
          if (stockHistoryContent && typeof window.loadStockHistory === 'function') {
            window.loadStockHistory(spareId);
          }
          
          // Try to get item name from the loaded content
          const itemNameElement = modalBody.querySelector('[data-item-name]');
          if (itemNameElement && typeof window.loadRelatedBrands === 'function') {
            const itemName = itemNameElement.getAttribute('data-item-name');
            if (itemName) {
              window.loadRelatedBrands(itemName, spareId);
            }
          }
        }, 200);
        
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
          const modalElement = document.getElementById('spareModal');
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
        console.error('Could not find spare content in response');
        console.log('Content section:', contentSection);
        console.log('Found cards:', contentSection.querySelectorAll('.card-glass').length);
        modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error: Could not load product details. Please refresh and try again.</div>';
      }
    })
    .catch(error => {
      console.error('Error loading spare:', error);
      modalBody.innerHTML = '<div class="text-center py-5 text-danger">Error loading product details: ' + error.message + '. Please try again.</div>';
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
  
  function closeSpareModal() {
    const modalElement = document.getElementById('spareModal');
    if (modalElement) {
      const modal = bootstrap.Modal.getInstance(modalElement);
      if (modal) {
        modal.hide();
      }
    }
    document.body.classList.remove('modal-open-blur');
  }


  function deleteSpare(spareId) {
    if (confirm('Are you sure you want to delete this spare part?')) {
      // Use POST + _method=DELETE so Laravel receives form data and CSRF properly
      const fd = new FormData();
      fd.append('_method', 'DELETE');
      fd.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

      fetch(`/admin/spares/${spareId}`, {
          method: 'POST',
          credentials: 'same-origin',
          body: fd,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        .then(async response => {
          const text = await response.text();
          let data = null;
          try {
            data = JSON.parse(text);
          } catch (err) {
            console.error('Non-JSON response:', text);
            throw new Error('Unexpected response from server (not JSON). Status: ' + response.status);
          }
          return {
            response,
            data
          };
        })
        .then(({
          response,
          data
        }) => {
          if (data.success) {
            showNotification('Spare part deleted successfully!', 'success');
            // Remove the row from table
            const deleteButton = document.querySelector(`button[onclick="deleteSpare(${spareId})"]`);
            if (deleteButton) {
              const row = deleteButton.closest('tr');
              if (row) {
                row.remove();
              }
            }
            // Reload page after a short delay
            setTimeout(() => {
              location.reload();
            }, 1000);
          } else {
            showNotification('Error deleting spare part: ' + (data.message || 'Unknown error'), 'error');
          }
        })
        .catch(error => {
          console.error('Error deleting spare part:', error);
          showNotification('Error deleting spare part: ' + (error.message || 'Unknown error'), 'error');
        });
    }
  }

  // Create/Edit functionality moved to separate create.blade.php and edit.blade.php files

  // Global functions for stock history (available in modal)
  window.loadStockHistory = function(spareId) {
    const contentDiv = document.getElementById('stockHistoryContent');
    const refreshBtn = document.getElementById('refreshHistoryBtn');
    
    if (!contentDiv) return;
    
    // Show loading state
    contentDiv.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    if (refreshBtn) refreshBtn.disabled = true;
    
    fetch(`/admin/spares/${spareId}/history`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        if (typeof window.displayStockHistory === 'function') {
          window.displayStockHistory(data);
        }
      } else {
        contentDiv.innerHTML = `<div class="alert alert-danger">${data.message || 'Error loading history'}</div>`;
      }
      if (refreshBtn) refreshBtn.disabled = false;
      feather.replace();
    })
    .catch(error => {
      console.error('Error loading stock history:', error);
      contentDiv.innerHTML = '<div class="alert alert-danger">Error loading stock history. Please try again.</div>';
      if (refreshBtn) refreshBtn.disabled = false;
    });
  };

  // Global displayStockHistory function (same as in show.blade.php)
  window.displayStockHistory = function(data) {
    const contentDiv = document.getElementById('stockHistoryContent');
    
    if (!contentDiv) return;
    
    if (!data.history_by_brand || data.history_by_brand.length === 0) {
      contentDiv.innerHTML = '<div class="text-center py-4 text-muted">No stock history available.</div>';
      return;
    }
    
    let html = '';
    
    // Display history grouped by brand
    data.history_by_brand.forEach((brandData, brandIndex) => {
      const isFirstBrand = brandIndex === 0;
      const isLastBrand = brandIndex === data.history_by_brand.length - 1;
      
      html += `
        <div class="mb-4 ${!isLastBrand ? 'border-bottom pb-4' : ''}" style="border-color: rgba(59, 130, 246, 0.2) !important;">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
              <h6 class="text-white mb-1" style="font-size: 1rem; font-weight: 600;">
                <i data-feather="tag" class="me-2" style="width: 16px; height: 16px;"></i>
                ${brandData.brand_name || 'Unknown Brand'}
              </h6>
              <small class="text-muted">
                Total Quantity: <strong class="text-success">${brandData.total_quantity}</strong> | 
                First Entry: ${new Date(brandData.first_entry_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })} | 
                Last Entry: ${new Date(brandData.last_entry_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}
              </small>
            </div>
          </div>
          
          <div class="table-responsive">
            <table class="table table-dark table-sm" style="margin-bottom: 0;">
              <thead>
                <tr>
                  <th style="padding: 0.5rem; font-size: 0.85rem; white-space: nowrap; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">Date & Time</th>
                  <th style="padding: 0.5rem; font-size: 0.85rem; white-space: nowrap; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">Quantity</th>
                  <th style="padding: 0.5rem; font-size: 0.85rem; white-space: nowrap;">Remarks</th>
                </tr>
              </thead>
              <tbody>
      `;
      
      brandData.entries.forEach((entry) => {
        html += `
          <tr>
            <td style="padding: 0.5rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">
              <i data-feather="calendar" class="me-1" style="width: 14px; height: 14px;"></i>
              ${entry.formatted_date}
            </td>
            <td style="padding: 0.5rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">
              <span class="badge bg-success" style="font-size: 0.8rem; padding: 4px 8px;">
                +${entry.quantity}
              </span>
            </td>
            <td style="padding: 0.5rem;">
              ${entry.remarks || '<span class="text-muted">-</span>'}
            </td>
          </tr>
        `;
      });
      
      html += `
              </tbody>
            </table>
          </div>
          
          ${!isFirstBrand ? `
            <div class="mt-3 p-2" style="background-color: rgba(59, 130, 246, 0.1); border-radius: 4px; border-left: 3px solid #3b82f6;">
              <small class="text-info">
                <i data-feather="info" class="me-1" style="width: 14px; height: 14px;"></i>
                Previously, <strong>${data.history_by_brand[brandIndex - 1].brand_name}</strong> brand product was received with total quantity of <strong>${data.history_by_brand[brandIndex - 1].total_quantity}</strong> units.
                Now, <strong>${brandData.brand_name}</strong> brand product is being received.
                <a href="/admin/spares/old-brand-history/${encodeURIComponent(data.product.item_name)}/${encodeURIComponent(data.history_by_brand[brandIndex - 1].brand_name)}" class="ms-2 text-info" style="text-decoration: underline;">
                  View ${data.history_by_brand[brandIndex - 1].brand_name} History
                </a>
              </small>
            </div>
          ` : ''}
        </div>
      `;
    });
    
    // Add old brand summaries section if available
    if (data.old_brand_summaries && data.old_brand_summaries.length > 0) {
      html += `
        <div class="mt-4 pt-4 border-top" style="border-color: rgba(59, 130, 246, 0.2) !important;">
          <h6 class="text-white mb-3">
            <i data-feather="archive" class="me-2"></i>Old Brand History Summary
          </h6>
      `;
      
      data.old_brand_summaries.forEach(oldBrand => {
        html += `
          <div class="mb-3 p-3" style="background-color: rgba(245, 158, 11, 0.1); border-radius: 6px; border-left: 4px solid #f59e0b;">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <h6 class="text-warning mb-0" style="font-size: 0.95rem; font-weight: 600;">
                <i data-feather="tag" class="me-2" style="width: 14px; height: 14px;"></i>
                Previous Brand: <strong>${oldBrand.brand_name}</strong>
              </h6>
            </div>
            <div class="row mt-2">
              <div class="col-md-3 mb-2">
                <small class="text-muted d-block" style="font-size: 0.75rem;">Total Quantity Received</small>
                <strong class="text-white">${oldBrand.total_quantity_received}</strong>
              </div>
              <div class="col-md-3 mb-2">
                <small class="text-muted d-block" style="font-size: 0.75rem;">Quantity Used</small>
                <strong class="text-white">${oldBrand.total_quantity_used}</strong>
              </div>
              <div class="col-md-3 mb-2">
                <small class="text-muted d-block" style="font-size: 0.75rem;">Start Date</small>
                <strong class="text-white">${oldBrand.start_date_formatted}</strong>
              </div>
              <div class="col-md-3 mb-2">
                <small class="text-muted d-block" style="font-size: 0.75rem;">End Date</small>
                <strong class="text-white">${oldBrand.end_date_formatted}</strong>
              </div>
            </div>
            <div class="mt-2">
              <small class="text-muted d-block" style="font-size: 0.75rem;">Supplier</small>
              <strong class="text-white">${oldBrand.supplier || 'N/A'}</strong>
            </div>
          </div>
        `;
      });
      
      html += `</div>`;
    }
    
    contentDiv.innerHTML = html;
    feather.replace();
  };

  // Global loadRelatedBrands function
  window.loadRelatedBrands = function(itemName, currentSpareId) {
    const relatedSection = document.getElementById('relatedBrandsSection');
    const relatedContent = document.getElementById('relatedBrandsContent');
    
    if (!relatedSection || !relatedContent) return;
    
    fetch(`/admin/spares/get-product-brands?item_name=${encodeURIComponent(itemName)}&spare_id=${currentSpareId}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
      if (data.success && data.related_spares && data.related_spares.length > 0) {
        let html = '<div class="table-responsive"><table class="table table-dark table-sm"><thead><tr>';
        html += '<th style="padding: 0.5rem; font-size: 0.85rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">Brand Name</th>';
        html += '<th style="padding: 0.5rem; font-size: 0.85rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">Product Code</th>';
        html += '<th style="padding: 0.5rem; font-size: 0.85rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">Stock Qty</th>';
        html += '<th style="padding: 0.5rem; font-size: 0.85rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">Total Received</th>';
        html += '<th style="padding: 0.5rem; font-size: 0.85rem;">Actions</th>';
        html += '</tr></thead><tbody>';
        
        data.related_spares.forEach(spare => {
          html += '<tr>';
          html += `<td style="padding: 0.5rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;"><strong class="text-info">${spare.brand_name}</strong></td>`;
          html += `<td style="padding: 0.5rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">${spare.product_code || 'N/A'}</td>`;
          html += `<td style="padding: 0.5rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;"><span class="badge bg-${spare.stock_quantity <= 0 ? 'danger' : 'success'}">${spare.stock_quantity}</span></td>`;
          html += `<td style="padding: 0.5rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">${spare.total_received_quantity}</td>`;
          html += `<td style="padding: 0.5rem;"><a href="/admin/spares/old-brand-history/${encodeURIComponent(itemName)}/${encodeURIComponent(spare.brand_name)}" class="btn btn-outline-info btn-sm"><i data-feather="clock" style="width: 14px; height: 14px;"></i> View History</a></td>`;
          html += '</tr>';
        });
        
        html += '</tbody></table></div>';
        relatedContent.innerHTML = html;
        relatedSection.style.display = 'block';
        feather.replace();
      } else {
        relatedSection.style.display = 'none';
      }
    })
    .catch(error => {
      console.error('Error loading related brands:', error);
      relatedSection.style.display = 'none';
    });
  };


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


  // Print all spares list
  function printAllSpares() {
    console.log('Printing all spares list');

    // Get current table data
    const table = document.querySelector('.table-responsive table');
    if (!table) {
      showNotification('No data to print', 'error');
      return;
    }

    // Create print content
    const printContent = `
      <!DOCTYPE html>
      <html>
      <head>
        <title>Spare Parts List - ${new Date().toLocaleDateString()}</title>
        <style>
          body { font-family: Arial, sans-serif; margin: 20px; }
          .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
          table { width: 100%; border-collapse: collapse; margin: 20px 0; }
          th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
          th { background-color: #f2f2f2; font-weight: bold; }
          .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
          @media print {
            body { margin: 0; }
            .no-print { display: none; }
          }
        </style>
      </head>
      <body>
        <div class="header">
          <h1>Spare Parts Inventory Report</h1>
          <p>Generated on: ${new Date().toLocaleString()}</p>
        </div>
        
        ${table.outerHTML}
        
        <div class="footer">
          <p>This document was generated from CMS Portal</p>
        </div>
      </body>
      </html>
    `;

    // Open print window
    const printWindow = window.open('', '_blank');
    printWindow.document.write(printContent);
    printWindow.document.close();

    // Wait for content to load then print
    printWindow.onload = function() {
      printWindow.print();
      printWindow.close();
    };
  }

  // Add event listener for print button
  document.addEventListener('DOMContentLoaded', function() {
    const printBtn = document.getElementById('printSparesBtn');
    if (printBtn) {
      printBtn.addEventListener('click', function() {
        printAllSpares();
      });
    }
  });
</script>
@endpush
