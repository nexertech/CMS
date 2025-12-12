@extends('layouts.sidebar')

@section('title', 'All Notifications — CMS Admin')

@section('content')
    <!-- PAGE HEADER -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="text-white mb-2">All Notifications</h2>
                <p class="text-light">View and manage all system notifications</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="refreshNotifications()" class="btn btn-outline-secondary">
                    <i data-feather="refresh-cw" class="me-2"></i>Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- NOTIFICATIONS STATS -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card-glass stat-card" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(59, 130, 246, 0.1) 100%); border: 1px solid rgba(59, 130, 246, 0.3);">
                <div class="card-body text-center">
                    <i data-feather="bell" class="feather-lg mb-2" style="color: #3b82f6;"></i>
                    <h3 class="text-white mb-0">{{ $totalCount }}</h3>
                    <small class="text-light">Total Notifications</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-glass stat-card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(16, 185, 129, 0.1) 100%); border: 1px solid rgba(16, 185, 129, 0.3);">
                <div class="card-body text-center">
                    <i data-feather="alert-circle" class="feather-lg mb-2" style="color: #10b981;"></i>
                    <h3 class="text-white mb-0">{{ $notifications->where('type', 'info')->count() }}</h3>
                    <small class="text-light">New Complaints</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-glass stat-card" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(245, 158, 11, 0.1) 100%); border: 1px solid rgba(245, 158, 11, 0.3);">
                <div class="card-body text-center">
                    <i data-feather="check-circle" class="feather-lg mb-2" style="color: #f59e0b;"></i>
                    <h3 class="text-white mb-0">{{ $notifications->where('type', 'warning')->count() }}</h3>
                    <small class="text-light">Pending Approvals</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-glass stat-card" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(239, 68, 68, 0.1) 100%); border: 1px solid rgba(239, 68, 68, 0.3);">
                <div class="card-body text-center">
                    <i data-feather="alert-triangle" class="feather-lg mb-2" style="color: #ef4444;"></i>
                    <h3 class="text-white mb-0">{{ $notifications->where('type', 'danger')->count() }}</h3>
                    <small class="text-light">Urgent Alerts</small>
                </div>
            </div>
        </div>
    </div>

    <!-- NOTIFICATIONS LIST -->
    <div class="card-glass">
        <div class="card-header">
            <h5 class="card-title mb-0 text-white">
                <i data-feather="list" class="me-2"></i>Notifications List
            </h5>
        </div>
        <div class="card-body">
            @if($notifications->count() > 0)
                <div class="list-group">
                    @foreach($notifications as $notification)
                        <a href="{{ $notification['url'] ?? '#' }}" class="list-group-item list-group-item-action notification-item mb-2" 
                           style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 8px; transition: all 0.3s ease;">
                            <div class="d-flex align-items-start">
                                <div class="notification-icon me-3" style="flex-shrink: 0;">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.2); border: 2px solid rgba(59, 130, 246, 0.4);">
                                        <i data-feather="{{ $notification['icon'] ?? 'bell' }}" 
                                           class="text-{{ $notification['type'] ?? 'primary' }}" 
                                           style="width: 24px; height: 24px;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="mb-0 text-white">{{ $notification['title'] ?? 'Notification' }}</h6>
                                        <span class="badge bg-{{ $notification['type'] ?? 'primary' }} ms-2">
                                            {{ ucfirst($notification['type'] ?? 'info') }}
                                        </span>
                                    </div>
                                    <p class="text-light mb-2" style="font-size: 0.9rem;">
                                        {{ $notification['message'] ?? 'No message' }}
                                    </p>
                                    <small class="text-muted">
                                        <i data-feather="clock" style="width: 14px; height: 14px;"></i>
                                        {{ $notification['time'] ?? 'Just now' }}
                                    </small>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i data-feather="bell-off" class="feather-lg mb-3" style="color: #64748b;"></i>
                    <h5 class="text-white mb-2">No Notifications</h5>
                    <p class="text-light">You're all caught up! No new notifications at the moment.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
<style>
    .notification-item:hover {
        background: rgba(59, 130, 246, 0.15) !important;
        border-color: rgba(59, 130, 246, 0.4) !important;
        transform: translateX(5px);
    }
    
    .stat-card {
        border-radius: 3px !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        opacity: 0.95;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
        filter: saturate(0.9) brightness(0.95);
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%);
        pointer-events: none;
        z-index: 1;
    }
    
    .stat-card::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        pointer-events: none;
        z-index: 1;
    }
    
    .stat-card .card-body {
        position: relative;
        z-index: 2;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();
    });
    
    function refreshNotifications() {
        window.location.reload();
    }
</script>
@endpush

