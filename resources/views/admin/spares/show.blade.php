@extends('layouts.sidebar')

@section('title', 'Spare Part Details — CMS Admin')

@section('content')
  <!-- PAGE HEADER -->
  <div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2 class="text-white mb-2">Product Details</h2>
        <p class="text-light">View product information and stock records</p>
      </div>
    </div>
  </div>

  <!-- PRODUCT DETAILS -->
  <div class="row">
    <!-- Basic Information -->
    <div class="col-md-6 mb-4">
      <div class="card-glass h-100">
        <div class="d-flex align-items-center mb-4"
          style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
          <i data-feather="package" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
          <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Product Information</h5>
        </div>

        <div class="info-item mb-3">
          <div class="d-flex align-items-start">
            <i data-feather="package" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
            <div class="flex-grow-1">
              <div class="text-muted small mb-1"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Item Name</div>
              <div class="text-white" style="font-size: 0.95rem; font-weight: 500;"
                data-item-name="{{ $spare->item_name }}">{{ $spare->item_name ?? 'N/A' }}</div>
            </div>
          </div>
        </div>

        <div class="info-item mb-3">
          <div class="d-flex align-items-start">
            <i data-feather="hash" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
            <div class="flex-grow-1">
              <div class="text-muted small mb-1"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Product Code</div>
              <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $spare->product_code ?? 'N/A' }}
              </div>
            </div>
          </div>
        </div>

        @if($spare->brand_name)
          <div class="info-item mb-3">
            <div class="d-flex align-items-start">
              <i data-feather="tag" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
              <div class="flex-grow-1">
                <div class="text-muted small mb-1"
                  style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Brand Name</div>
                <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $spare->brand_name }}</div>
              </div>
            </div>
          </div>
        @endif

        <div class="info-item mb-3">
          <div class="d-flex align-items-start">
            <i data-feather="grid" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
            <div class="flex-grow-1">
              <div class="text-muted small mb-1"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Category</div>
              <div>
                <span class="badge bg-info" style="font-size: 0.85rem; padding: 6px 12px;">
                  {{ ucfirst($spare->category ?? 'N/A') }}
                </span>
              </div>
            </div>
          </div>
        </div>

        @if($spare->supplier)
          <div class="info-item mb-3">
            <div class="d-flex align-items-start">
              <i data-feather="truck" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
              <div class="flex-grow-1">
                <div class="text-muted small mb-1"
                  style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Supplier</div>
                <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $spare->supplier }}</div>
              </div>
            </div>
          </div>
        @endif

        @if($spare->description)
          <div class="info-item mb-3">
            <div class="d-flex align-items-start">
              <i data-feather="file-text" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
              <div class="flex-grow-1">
                <div class="text-muted small mb-1"
                  style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Description</div>
                <p class="text-white mb-0" style="font-size: 0.95rem; font-weight: 400; line-height: 1.6;">
                  {{ $spare->description }}</p>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>

    <!-- Stock Information -->
    <div class="col-md-6 mb-4">
      <div class="card-glass h-100">
        <div class="d-flex align-items-center mb-4"
          style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
          <i data-feather="database" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
          <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Stock Information</h5>
        </div>

        <div class="info-item mb-3">
          <div class="d-flex align-items-start">
            <i data-feather="box" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
            <div class="flex-grow-1">
              <div class="text-muted small mb-1"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Stock Quantity</div>
              <div>
                <span
                  class="badge bg-{{ ($spare->stock_quantity ?? 0) <= 0 ? 'danger' : (($spare->stock_quantity ?? 0) <= ($spare->threshold_level ?? 0) ? 'warning' : 'success') }}"
                  style="font-size: 0.85rem; padding: 6px 12px; color: #ffffff !important;">
                  {{ number_format($spare->stock_quantity ?? 0, 0) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="info-item mb-3">
          <div class="d-flex align-items-start">
            <i data-feather="arrow-down-circle" class="me-3 text-muted"
              style="width: 18px; height: 18px; margin-top: 4px;"></i>
            <div class="flex-grow-1">
              <div class="text-muted small mb-1"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Received</div>
              <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">
                {{ number_format($spare->total_received_quantity ?? 0, 0) }}</div>
            </div>
          </div>
        </div>

        <div class="info-item mb-3">
          <div class="d-flex align-items-start">
            <i data-feather="arrow-up-circle" class="me-3 text-muted"
              style="width: 18px; height: 18px; margin-top: 4px;"></i>
            <div class="flex-grow-1">
              <div class="text-muted small mb-1"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Issued Quantity</div>
              <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">
                {{ number_format($spare->issued_quantity ?? 0, 0) }}</div>
            </div>
          </div>
        </div>

        <div class="info-item mb-3">
          <div class="d-flex align-items-start">
            <i data-feather="alert-circle" class="me-3 text-muted"
              style="width: 18px; height: 18px; margin-top: 4px;"></i>
            <div class="flex-grow-1">
              <div class="text-muted small mb-1"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Threshold Level</div>
              <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">
                {{ number_format($spare->threshold_level ?? 0, 0) }}</div>
            </div>
          </div>
        </div>

        <div class="info-item mb-3">
          <div class="d-flex align-items-start">
            <i data-feather="calendar" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
            <div class="flex-grow-1">
              <div class="text-muted small mb-1"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Created</div>
              <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">
                {{ $spare->created_at ? $spare->created_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') : 'N/A' }}
              </div>
            </div>
          </div>
        </div>

        <div class="info-item mb-3">
          <div class="d-flex align-items-start">
            <i data-feather="clock" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
            <div class="flex-grow-1">
              <div class="text-muted small mb-1"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Last Updated</div>
              <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">
                {{ $spare->updated_at ? $spare->updated_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') : 'N/A' }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>



  <!-- Stock History Section -->
  <div class="row mb-4" id="stockHistorySection">
    <div class="col-12">
      <div class="card-glass">
        <div class="d-flex align-items-center justify-content-between mb-4"
          style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
          <div class="d-flex align-items-center">
            <i data-feather="clock" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
            <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Stock History</h5>
          </div>
          <button type="button" class="btn btn-outline-primary btn-sm" id="refreshHistoryBtn"
            onclick="window.loadStockHistory({{ $spare->id }})" title="Refresh History">
            <i data-feather="refresh-cw" style="width: 16px; height: 16px;"></i>
          </button>
        </div>
        <div id="stockHistoryContent">
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Stock Modal -->
  <div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Stock</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.spares.add-stock', $spare) }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label for="quantity" class="form-label">Quantity to Add</label>
              <input type="number" class="form-control" id="quantity" name="quantity" required>
            </div>
            <div class="mb-3">
              <label for="remarks" class="form-label">Remarks</label>
              <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-outline-secondary"><i data-feather="plus" class="me-2"></i>Add
              Stock</button>
          </div>
        </form>
      </div>
    </div>
  </div>


  @push('styles')
  @endpush

  @push('scripts')
    <script>
      // Make functions globally available
      window.loadStockHistory = function (spareId) {
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
              displayStockHistory(data);
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
      }

      window.displayStockHistory = function (data) {
        const contentDiv = document.getElementById('stockHistoryContent');

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

          brandData.entries.forEach((entry, entryIndex) => {
            const isLastEntry = entryIndex === brandData.entries.length - 1;
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
      }

      window.loadRelatedBrands = function (itemName, currentSpareId) {
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

      window.addStock = function () {
        const modal = new bootstrap.Modal(document.getElementById('addStockModal'));
        modal.show();
      };

      // Initialize on page load
      document.addEventListener('DOMContentLoaded', function () {
        feather.replace();
        // Load stock history on page load
        if (typeof window.loadStockHistory === 'function') {
          window.loadStockHistory({{ $spare->id }});
        }
      });
    </script>
  @endpush
@endsection