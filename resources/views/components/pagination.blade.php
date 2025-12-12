@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="pagination-nav">
        <div class="pagination-container">
            {{-- Pagination Info --}}
            <div class="pagination-info">
                Showing <strong>{{ $paginator->firstItem() ?? 0 }}</strong> to <strong>{{ $paginator->lastItem() ?? 0 }}</strong> of <strong>{{ $paginator->total() }}</strong> results
            </div>
            
            {{-- Pagination Controls --}}
            <ul class="pagination">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <i data-feather="chevron-left" class="feather-sm"></i>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a href="{{ $paginator->previousPageUrl() }}" class="page-link" rel="prev" aria-label="{{ __('pagination.previous') }}">
                            <i data-feather="chevron-left" class="feather-sm"></i>
                        </a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="page-item disabled">
                            <span class="page-link">{{ $element }}</span>
                        </li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a href="{{ $url }}" class="page-link" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a href="{{ $paginator->nextPageUrl() }}" class="page-link" rel="next" aria-label="{{ __('pagination.next') }}">
                            <i data-feather="chevron-right" class="feather-sm"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <i data-feather="chevron-right" class="feather-sm"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
@endif

<style>
/* Centralized Pagination Styles */
.pagination-nav {
    margin-top: 1rem;
}

.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.pagination-info {
    color: #6c757d;
    font-size: 14px;
    font-weight: 500;
}

.pagination-info strong {
    color: #fff;
    font-weight: 600;
}

.pagination {
    margin: 0;
    display: flex;
    list-style: none;
    padding: 0;
    align-items: center;
    gap: 4px;
}

.pagination .page-item {
    display: flex;
    align-items: center;
}

.pagination .page-link {
    color: #fff;
    background-color: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 8px 12px;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    min-width: 38px;
    font-weight: 500;
}

.pagination .page-link:hover {
    background-color: rgba(255, 255, 255, 0.2);
    color: #fff;
    transform: translateY(-1px);
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: #fff;
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    background-color: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.1);
    cursor: not-allowed;
}

.pagination .page-item.disabled .page-link:hover {
    transform: none;
    background-color: rgba(255, 255, 255, 0.05);
}

/* Responsive Design */
@media (max-width: 768px) {
    .pagination-container {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .pagination-info {
        margin-bottom: 0.5rem;
    }
    
    .pagination {
        justify-content: center;
    }
}

/* Dark Theme Compatibility */
@media (prefers-color-scheme: dark) {
    .pagination-info {
        color: #adb5bd;
    }
    
    .pagination .page-link {
        color: #fff;
        background-color: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.2);
    }
}
</style>

<script>
// Initialize Feather icons for pagination
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>
