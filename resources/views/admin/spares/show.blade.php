@extends('layouts.sidebar')

@section('title', 'Spare Part Details — CMS Admin')

@section('content')
  <!-- PAGE HEADER -->
  <div class="mb-3">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h3 class="text-white mb-1" style="font-size: 1.4rem; font-weight: 700;">Product Details</h3>
        <p class="text-light mb-0 small" style="opacity: 0.8; font-size: 0.85rem;">View product information and stock records</p>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('admin.spares.index') }}" class="btn btn-outline-secondary btn-sm py-1 px-3">
          <i data-feather="arrow-left" class="me-1" style="width: 14px; height: 14px;"></i>Back
        </a>
      </div>
    </div>
  </div>

  <!-- PRODUCT DETAILS -->
  <div class="row g-3">
    <!-- Basic Information -->
    <div class="col-md-6">
      <div class="card-glass h-100">
        <div class="d-flex align-items-center mb-2 pb-2" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
          <i data-feather="package" class="me-2 text-primary" style="width: 17px; height: 17px;"></i>
          <h5 class="text-white mb-0" style="font-size: 0.95rem; font-weight: 600;">Product Information</h5>
        </div>

        <div class="info-item">
          <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
              <i data-feather="package" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
              <span class="info-label">Item Name</span>
            </div>
            <span class="info-value text-end fw-bold" data-item-name="{{ $spare->item_name }}">{{ $spare->item_name ?? 'N/A' }}</span>
          </div>
        </div>

        <div class="info-item">
          <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
              <i data-feather="hash" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
              <span class="info-label">Product Code</span>
            </div>
            <span class="info-value text-end">{{ $spare->product_code ?? 'N/A' }}</span>
          </div>
        </div>

        <div class="info-item">
          <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
              <i data-feather="grid" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
              <span class="info-label">Category</span>
            </div>
            <div>
              <span class="badge bg-info" style="font-size: 0.75rem; padding: 4px 10px;">
                {{ $spare->category->name ?? 'N/A' }}
              </span>
            </div>
          </div>
        </div>

        @if($spare->brand_id)
        <div class="info-item">
          <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
              <i data-feather="tag" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
              <span class="info-label">Brand Name</span>
            </div>
            <span class="info-value text-end">{{ $spare->brand->name ?? 'N/A' }}</span>
          </div>
        </div>
        @endif

        @if($spare->supplier)
        <div class="info-item">
          <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
              <i data-feather="truck" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
              <span class="info-label">Vendor</span>
            </div>
            <span class="info-value text-end">{{ $spare->supplier }}</span>
          </div>
        </div>
        @endif

        @if($spare->description)
        <div class="info-item pt-2 border-0">
          <div class="d-flex align-items-start flex-column">
            <div class="d-flex align-items-center mb-1">
              <i data-feather="file-text" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
              <span class="info-label">Description</span>
            </div>
            <div class="info-value w-100 p-2 rounded" style="background: rgba(255,255,255,0.04); font-size: 0.82rem; font-weight: 400; line-height: 1.4;">{{ $spare->description }}</div>
          </div>
        </div>
        @endif
      </div>
    </div>

    <!-- Stock Information -->
    <div class="col-md-6">
      <div class="card-glass h-100">
        <div class="d-flex align-items-center mb-2 pb-2" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2);">
          <i data-feather="database" class="me-2 text-primary" style="width: 17px; height: 17px;"></i>
          <h5 class="text-white mb-0" style="font-size: 0.95rem; font-weight: 600;">Stock Information</h5>
        </div>

        <div class="info-item">
          <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
              <i data-feather="box" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
              <span class="info-label">Stock Quantity</span>
            </div>
            <div>
              <span class="badge bg-{{ ($spare->stock_quantity ?? 0) <= 0 ? 'danger' : (($spare->stock_quantity ?? 0) <= ($spare->threshold_level ?? 0) ? 'warning' : 'success') }}" style="font-size: 0.75rem; padding: 4px 10px; color: #ffffff !important;">
                {{ number_format($spare->stock_quantity ?? 0, 0) }}
              </span>
            </div>
          </div>
        </div>

        <div class="info-item">
          <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
              <i data-feather="arrow-down-circle" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
              <span class="info-label">Total Received</span>
            </div>
            <span class="info-value text-end">{{ number_format($spare->total_received_quantity ?? 0, 0) }}</span>
          </div>
        </div>

        <div class="info-item">
          <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
              <i data-feather="arrow-up-circle" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
              <span class="info-label">Issued Quantity</span>
            </div>
            <span class="info-value text-end">{{ number_format($spare->issued_quantity ?? 0, 0) }}</span>
          </div>
        </div>

        <div class="info-item">
          <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
              <i data-feather="alert-circle" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
              <span class="info-label">Threshold Level</span>
            </div>
            <span class="info-value text-end">{{ number_format($spare->threshold_level ?? 0, 0) }}</span>
          </div>
        </div>

        <div class="info-item">
          <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
              <i data-feather="calendar" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
              <span class="info-label">Created</span>
            </div>
            <span class="info-value text-end">{{ $spare->created_at ? $spare->created_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') : 'N/A' }}</span>
          </div>
        </div>

        <div class="info-item">
          <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
              <i data-feather="clock" class="me-2 text-muted" style="width: 14px; height: 14px;"></i>
              <span class="info-label">Last Updated</span>
            </div>
            <span class="info-value text-end">{{ $spare->updated_at ? $spare->updated_at->timezone('Asia/Karachi')->format('M d, Y H:i:s') : 'N/A' }}</span>
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
            <button type="submit" class="btn btn-outline-secondary"><i data-feather="plus" class="me-2"></i>Add Stock</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  @push('styles')
  <style>
    body {
      position: relative;
    }
    
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      z-index: -1;
      pointer-events: none;
    }
    
    .card-glass {
      position: relative;
      z-index: 1;
      background: rgba(30, 41, 59, 0.85) !important;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
      transition: box-shadow 0.3s ease;
      padding: 12px 16px !important;
    }
    
    .card-glass:hover {
      box-shadow: 0 12px 40px rgba(15, 23, 42, 0.5);
    }
    
    .info-item {
      padding: 5px 0 !important;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
    }
    
    .info-item:last-child {
      border-bottom: none !important;
    }

    .info-label {
      font-size: 0.78rem !important;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #94a3b8;
      font-weight: 500;
    }

    .info-value {
      font-size: 0.88rem !important;
      font-weight: 500;
      color: #ffffff;
    }
  </style>
  @endpush

  @push('scripts')
    <script>
      window.addStock = function () {
        const modal = new bootstrap.Modal(document.getElementById('addStockModal'));
        modal.show();
      };

      document.addEventListener('DOMContentLoaded', function () {
        feather.replace();
      });
    </script>
  @endpush
@endsection