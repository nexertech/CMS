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

        <div class="info-item mb-3">
          <div class="d-flex align-items-start">
            <i data-feather="grid" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
            <div class="flex-grow-1">
              <div class="text-muted small mb-1"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Category</div>
              <div>
                <span class="badge bg-info" style="font-size: 0.85rem; padding: 6px 12px;">
                  {{ $spare->category->name ?? 'N/A' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        @if($spare->brand_id)
          <div class="info-item mb-3">
            <div class="d-flex align-items-start">
              <i data-feather="tag" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
              <div class="flex-grow-1">
                <div class="text-muted small mb-1"
                  style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Brand Name</div>
                <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $spare->brand->name ?? 'N/A' }}</div>
              </div>
            </div>
          </div>
        @endif

        @if($spare->supplier)
          <div class="info-item mb-3">
            <div class="d-flex align-items-start">
              <i data-feather="truck" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
              <div class="flex-grow-1">
                <div class="text-muted small mb-1"
                  style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Vendor</div>
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


      window.addStock = function () {
        const modal = new bootstrap.Modal(document.getElementById('addStockModal'));
        modal.show();
      };

      // Initialize on page load
      document.addEventListener('DOMContentLoaded', function () {
        feather.replace();
      });
    </script>
  @endpush
@endsection