@extends('layouts.sidebar')

@section('title', 'Old Brand History — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Brand History: {{ $itemName }}</h2>
      <p class="text-light">Complete history for brand: <strong>{{ $brandName }}</strong></p>
    </div>
    <a href="{{ route('admin.spares.index') }}" class="btn btn-outline-secondary">
      <i data-feather="arrow-left" class="me-2"></i>Back to Products
    </a>
  </div>
</div>

<!-- CURRENT BRAND INFORMATION -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card-glass">
      <div class="d-flex align-items-center mb-4" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
        <i data-feather="tag" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
        <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Brand: {{ $brandName }}</h5>
      </div>
      
      <div class="row">
        <div class="col-md-3 mb-3">
          <div class="info-item">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase;">Product Name</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $spare->item_name }}</div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="info-item">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase;">Product Code</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $spare->product_code ?? 'N/A' }}</div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="info-item">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase;">Current Stock</div>
            <div>
              <span class="badge bg-{{ ($spare->stock_quantity ?? 0) <= 0 ? 'danger' : (($spare->stock_quantity ?? 0) <= ($spare->threshold_level ?? 0) ? 'warning' : 'success') }}" style="font-size: 0.85rem; padding: 6px 12px;">
                {{ number_format($spare->stock_quantity ?? 0, 0) }}
              </span>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="info-item">
            <div class="text-muted small mb-1" style="font-size: 0.75rem; text-transform: uppercase;">Total Received</div>
            <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ number_format($spare->total_received_quantity ?? 0, 0) }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- RELATED BRANDS -->
@if($relatedSpares->count() > 0)
<div class="row mb-4">
  <div class="col-12">
    <div class="card-glass">
      <div class="d-flex align-items-center mb-4" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
        <i data-feather="package" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
        <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Other Brands for Same Product</h5>
      </div>
      
      <div class="table-responsive">
        <table class="table table-dark table-sm">
          <thead>
            <tr>
              <th style="padding: 0.5rem; font-size: 0.85rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">Brand Name</th>
              <th style="padding: 0.5rem; font-size: 0.85rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">Product Code</th>
              <th style="padding: 0.5rem; font-size: 0.85rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">Stock Qty</th>
              <th style="padding: 0.5rem; font-size: 0.85rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">Total Received</th>
              <th style="padding: 0.5rem; font-size: 0.85rem;">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($relatedSpares as $relatedSpare)
            <tr>
              <td style="padding: 0.5rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">
                <strong class="text-info">{{ $relatedSpare->brand_name }}</strong>
              </td>
              <td style="padding: 0.5rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">{{ $relatedSpare->product_code ?? 'N/A' }}</td>
              <td style="padding: 0.5rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">
                <span class="badge bg-{{ ($relatedSpare->stock_quantity ?? 0) <= 0 ? 'danger' : 'success' }}">
                  {{ number_format($relatedSpare->stock_quantity ?? 0, 0) }}
                </span>
              </td>
              <td style="padding: 0.5rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">{{ number_format($relatedSpare->total_received_quantity ?? 0, 0) }}</td>
              <td style="padding: 0.5rem;">
                <a href="{{ route('admin.spares.old-brand-history', ['itemName' => urlencode($itemName), 'brandName' => urlencode($relatedSpare->brand_name)]) }}" class="btn btn-outline-info btn-sm">
                  <i data-feather="clock" style="width: 14px; height: 14px;"></i> View History
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endif

<!-- STOCK HISTORY BY BRAND -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card-glass">
      <div class="d-flex align-items-center mb-4" style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
        <i data-feather="clock" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
        <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Complete Stock History</h5>
      </div>
      
      @if(count($historyByBrand) > 0)
        @foreach($historyByBrand as $brandData)
        <div class="mb-4 {{ !$loop->last ? 'border-bottom pb-4' : '' }}" style="border-color: rgba(59, 130, 246, 0.2) !important;">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
              <h6 class="text-white mb-1" style="font-size: 1rem; font-weight: 600;">
                <i data-feather="tag" class="me-2" style="width: 16px; height: 16px;"></i>
                {{ $brandData['brand_name'] }}
              </h6>
              <small class="text-muted">
                Total Quantity Received: <strong class="text-success">{{ $brandData['total_quantity'] }}</strong>
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
                @foreach($brandData['entries'] as $entry)
                <tr>
                  <td style="padding: 0.5rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">
                    <i data-feather="calendar" class="me-1" style="width: 14px; height: 14px;"></i>
                    {{ $entry['formatted_date'] }}
                  </td>
                  <td style="padding: 0.5rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">
                    <span class="badge bg-success" style="font-size: 0.8rem; padding: 4px 8px;">
                      +{{ $entry['quantity'] }}
                    </span>
                  </td>
                  <td style="padding: 0.5rem;">
                    {{ $entry['remarks'] ?: '-' }}
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        @endforeach
      @else
        <div class="text-center py-4 text-muted">No stock history available for this brand.</div>
      @endif
    </div>
  </div>
</div>

@push('styles')
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    feather.replace();
  });
</script>
@endpush
@endsection

