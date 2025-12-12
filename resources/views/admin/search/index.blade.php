@extends('layouts.sidebar')

@section('title', 'Search Results — CMS Admin')

@section('content')
<div class="mb-4">
  <h2 class="text-white mb-2">Search Results</h2>
  <p class="text-light">
    @if($query)
      Search results for "<strong>{{ $query }}</strong>" - {{ $totalResults }} results found
    @else
      Enter a search term to find what you're looking for
    @endif
  </p>
</div>

@if($query)
  @if($totalResults > 0)
    <div class="row">
      @foreach($results as $result)
        <div class="col-md-6 mb-4">
          <div class="card-glass">
            <div class="d-flex align-items-center mb-3">
              <div class="me-3">
                <div class="bg-{{ $result['color'] }} rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                  <i data-feather="{{ $result['icon'] }}" class="text-white"></i>
                </div>
              </div>
              <div class="flex-grow-1">
                <h5 class="mb-1 text-white">{{ $result['title'] }}</h5>
                <span class="badge bg-{{ $result['color'] }}">{{ $result['count'] }} {{ $result['count'] == 1 ? 'result' : 'results' }}</span>
              </div>
            </div>
            
            <div class="search-results-list">
              @foreach($result['items'] as $item)
                <a href="{{ $item['url'] }}" class="search-result-item d-block text-decoration-none mb-3 p-3 rounded" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s ease;">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                      <h6 class="text-white mb-1">{{ $item['title'] }}</h6>
                      <p class="text-muted mb-1">{{ $item['subtitle'] }}</p>
                      @if(isset($item['description']) && $item['description'])
                        <p class="text-light small mb-1">{{ Str::limit($item['description'], 100) }}</p>
                      @endif
                      <div class="d-flex align-items-center gap-3">
                        @if(isset($item['status']))
                          <span class="badge bg-{{ $result['color'] }}">{{ $item['status'] }}</span>
                        @endif
                        @if(isset($item['priority']))
                          <span class="badge bg-warning">{{ $item['priority'] }}</span>
                        @endif
                        <small class="text-muted">{{ $item['created_at'] }}</small>
                      </div>
                    </div>
                    <div class="ms-3">
                      <i data-feather="arrow-right" class="text-muted"></i>
                    </div>
                  </div>
                </a>
              @endforeach
            </div>
            
            @if($result['count'] > 10)
              <div class="text-center mt-3">
                <a href="#" class="btn btn-outline-{{ $result['color'] }} btn-sm">
                  View all {{ $result['count'] }} {{ $result['title'] }}
                </a>
              </div>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  @else
    <div class="row">
      <div class="col-12">
        <div class="card-glass text-center py-5">
          <div class="mb-4">
            <i data-feather="search" class="feather-xl text-muted"></i>
          </div>
          <h4 class="text-white mb-3">No results found</h4>
          <p class="text-light mb-4">
            We couldn't find anything matching "<strong>{{ $query }}</strong>". 
            Try different keywords or check your spelling.
          </p>
          <div class="d-flex justify-content-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-accent">
              <i data-feather="home" class="me-2"></i>Go to Dashboard
            </a>
            <button onclick="clearSearch()" class="btn btn-outline-light">
              <i data-feather="x" class="me-2"></i>Clear Search
            </button>
          </div>
        </div>
      </div>
    </div>
  @endif
@else
  <div class="row">
    <div class="col-12">
      <div class="card-glass text-center py-5">
        <div class="mb-4">
          <i data-feather="search" class="feather-xl text-primary"></i>
        </div>
        <h4 class="text-white mb-3">Search the System</h4>
        <p class="text-light mb-4">
          Use the search bar in the top navigation to find complaints, users, clients, employees, spare parts, and more.
        </p>
        <div class="row">
          <div class="col-md-3 mb-3">
            <div class="card-glass text-center p-3">
              <i data-feather="alert-circle" class="text-primary mb-2"></i>
              <h6 class="text-white">Complaints</h6>
              <p class="text-muted small">Search by ticket number, description, or client</p>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="card-glass text-center p-3">
              <i data-feather="users" class="text-success mb-2"></i>
              <h6 class="text-white">Users</h6>
              <p class="text-muted small">Search by name, email, or role</p>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="card-glass text-center p-3">
              <i data-feather="briefcase" class="text-info mb-2"></i>
              <h6 class="text-white">Clients</h6>
              <p class="text-muted small">Search by company name or contact person</p>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="card-glass text-center p-3">
              <i data-feather="package" class="text-warning mb-2"></i>
              <h6 class="text-white">Spare Parts</h6>
              <p class="text-muted small">Search by item name or part number</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endif

@endsection

@push('scripts')
<script>
  feather.replace();

  function clearSearch() {
    // Clear the search input in the topbar
    const searchInput = document.getElementById('globalSearch');
    if (searchInput) {
      searchInput.value = '';
    }
    
    // Redirect to dashboard
    window.location.href = '{{ route("admin.dashboard") }}';
  }

  // Auto-focus search input if it exists
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('globalSearch');
    if (searchInput && !searchInput.value) {
      searchInput.focus();
    }
  });
</script>
@endpush
