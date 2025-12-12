@extends('layouts.sidebar')

@section('title', 'Create New Product — CMS Admin')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="text-white mb-2">Create New Product</h2>
                <p class="text-light">Add a new Product to inventory</p>
            </div>
            
        </div>
    </div>

    <!-- SPARE PART FORM -->
    <div class="card-glass">
        <div class="card-header">
            <h5 class="card-title mb-0 text-white">
                <i data-feather="package" class="me-2"></i>Product Information
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.spares.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="item_name" class="form-label text-white">Item Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('item_name') is-invalid @enderror"
                                id="item_name" name="item_name" value="{{ old('item_name') }}" required>
                            @error('item_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="product_code" class="form-label text-white">Product Code</label>
                            <input type="text" class="form-control @error('product_code') is-invalid @enderror"
                                id="product_code" name="product_code" value="{{ old('product_code') }}">
                            @error('product_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="brand_name" class="form-label text-white">Brand Name</label>
                            <input type="text" class="form-control @error('brand_name') is-invalid @enderror"
                                id="brand_name" name="brand_name" value="{{ old('brand_name') }}">
                            @error('brand_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="category" class="form-label text-white">Category <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                <option value="">Select Category</option>
                                @if(isset($categories) && $categories->count() > 0)
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="city_id" class="form-label text-white">GE Groups</label>
                            <select class="form-select @error('city_id') is-invalid @enderror" 
                                    id="city_id" name="city_id">
                                <option value="">Select GE Groups</option>
                                @if(isset($cities) && $cities->count() > 0)
                                    @foreach ($cities as $city)
                                        <option value="{{ $city->id }}" {{ old('city_id', $defaultCityId ?? null) == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}{{ $city->province ? ' (' . $city->province . ')' : '' }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('city_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="sector_id" class="form-label text-white">GE Nodes</label>
                            <select class="form-select @error('sector_id') is-invalid @enderror" 
                                    id="sector_id" name="sector_id" {{ (old('city_id', $defaultCityId ?? null)) ? '' : 'disabled' }}>
                                <option value="">{{ (old('city_id', $defaultCityId ?? null)) ? 'Loading GE Nodes...' : 'Select GE Groups First' }}</option>
                            </select>
                            @error('sector_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="stock_quantity" class="form-label text-white">Stock Quantity <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror"
                                id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" required>
                            @error('stock_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="threshold_level" class="form-label text-white">Threshold Level <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('threshold_level') is-invalid @enderror"
                                id="threshold_level" name="threshold_level" value="{{ old('threshold_level', 10) }}"
                                required>
                            @error('threshold_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="supplier" class="form-label text-white">Supplier</label>
                            <input type="text" class="form-control @error('supplier') is-invalid @enderror"
                                id="supplier" name="supplier" value="{{ old('supplier') }}">
                            @error('supplier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="total_received_quantity" class="form-label text-white">Total Received
                                Quantity</label>
                            <input type="number"
                                class="form-control @error('total_received_quantity') is-invalid @enderror"
                                id="total_received_quantity" name="total_received_quantity"
                                value="{{ old('total_received_quantity', 0) }}">
                            @error('total_received_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="description" class="form-label text-white">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.spares.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-accent">Create Product</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle city change to filter sectors
    const citySelect = document.getElementById('city_id');
    const sectorSelect = document.getElementById('sector_id');
    
    if (citySelect && sectorSelect) {
        citySelect.addEventListener('change', function() {
            const cityId = this.value;
            
            // Clear and disable sector dropdown
            sectorSelect.innerHTML = '<option value="">Loading...</option>';
            sectorSelect.disabled = true;
            
            if (cityId) {
                // Fetch sectors for this city
                const url = `{{ route('admin.sectors.by-city') }}?city_id=${cityId}`;
                
                fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    sectorSelect.innerHTML = '<option value="">Select GE Nodes</option>';
                    const sectors = Array.isArray(data) ? data : (data.sectors || []);
                    if (sectors && sectors.length > 0) {
                        sectors.forEach(function(sector) {
                            const option = document.createElement('option');
                            option.value = sector.id;
                            option.textContent = sector.name;
                            sectorSelect.appendChild(option);
                        });
                        sectorSelect.disabled = false;
                    } else {
                        sectorSelect.innerHTML = '<option value="">No GE Nodes Available</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching GE Nodes:', error);
                    sectorSelect.innerHTML = '<option value="">Error Loading GE Nodes</option>';
                });
            } else {
                sectorSelect.innerHTML = '<option value="">Select GE Groups First</option>';
            }
        });
        
        // Load sectors if city is already selected (for old values)
        // If city is pre-selected based on defaults (department staff) or old value, load sectors and preselect default
        const defaultCityId = '{{ old('city_id', $defaultCityId ?? '') }}';
        const defaultSectorId = '{{ old('sector_id', $defaultSectorId ?? '') }}';
        if (defaultCityId) {
            citySelect.value = defaultCityId;
            const url = `{{ route('admin.sectors.by-city') }}?city_id=${defaultCityId}`;
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(resp => resp.json())
            .then(data => {
                sectorSelect.innerHTML = '<option value="">Select GE Nodes</option>';
                const sectors = Array.isArray(data) ? data : (data.sectors || []);
                if (sectors && sectors.length > 0) {
                    sectors.forEach(sector => {
                        const option = document.createElement('option');
                        option.value = sector.id;
                        option.textContent = sector.name;
                        if (defaultSectorId && String(sector.id) === String(defaultSectorId)) {
                            option.selected = true;
                        }
                        sectorSelect.appendChild(option);
                    });
                    sectorSelect.disabled = false;
                } else {
                    sectorSelect.innerHTML = '<option value="">No GE Nodes Available</option>';
                }
            })
            .catch(() => {
                sectorSelect.innerHTML = '<option value="">Error Loading GE Nodes</option>';
            });
        }
    }
});
</script>
@endpush
