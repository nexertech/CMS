@php
    $stats = [
        'total' => \App\Models\SpareApprovalPerforma::count(),
        'pending' => \App\Models\SpareApprovalPerforma::where('status', 'pending')->count(),
        'approved' => \App\Models\SpareApprovalPerforma::where('status', 'approved')->count(),
        'rejected' => \App\Models\SpareApprovalPerforma::where('status', 'rejected')->count(),
        'overdue' => \App\Models\SpareApprovalPerforma::overdue()->count(),
    ];
    
    $recentApprovals = \App\Models\SpareApprovalPerforma::with(['complaint.client', 'requestedBy'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
@endphp

<div class="card-glass">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 text-white">Approval Statistics</h5>
        <a href="{{ route('admin.approvals.index') }}" class="btn btn-outline-primary btn-sm">
            <i data-feather="external-link" class="me-1"></i>View All
        </a>
    </div>
    
    <!-- Stats Grid -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="text-center">
                <div class="h4 mb-1 text-primary">{{ $stats['total'] }}</div>
                <div class="text-muted small">Total</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="text-center">
                <div class="h4 mb-1 text-warning">{{ $stats['pending'] }}</div>
                <div class="text-muted small">Pending</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="text-center">
                <div class="h4 mb-1 text-success">{{ $stats['approved'] }}</div>
                <div class="text-muted small">Approved</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="text-center">
                <div class="h4 mb-1 text-danger">{{ $stats['rejected'] }}</div>
                <div class="text-muted small">Rejected</div>
            </div>
        </div>
    </div>
    
    @if($stats['overdue'] > 0)
    <div class="alert alert-warning">
        <i data-feather="alert-triangle" class="me-2"></i>
        <strong>{{ $stats['overdue'] }}</strong> approval(s) are overdue
    </div>
    @endif
    
    <!-- Recent Approvals -->
    @if($recentApprovals->count() > 0)
    <div class="mt-4">
        <h6 class="text-white mb-3">Recent Approvals</h6>
        <div class="list-group list-group-flush">
            @foreach($recentApprovals as $approval)
            <div class="list-group-item bg-transparent border-0 px-0 py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-white">#{{ $approval->id }}</div>
                        <div class="text-muted small">{{ $approval->complaint->client->client_name ?? 'N/A' }}</div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-{{ $approval->status === 'pending' ? 'warning' : ($approval->status === 'approved' ? 'success' : 'danger') }}">
                            {{ ucfirst($approval->status) }}
                        </span>
                        <div class="text-muted small">{{ $approval->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
