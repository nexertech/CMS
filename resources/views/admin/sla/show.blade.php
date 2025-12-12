@extends('layouts.sidebar')

@section('title', 'SLA Rule Details — CMS Admin')

@section('content')
  <!-- PAGE HEADER -->
  <div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2 class="text-white mb-2">SLA Rule Details</h2>
        <p class="text-light">View SLA rule: {{ ucfirst($sla->complaint_type) }}</p>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('admin.sla.index') }}" class="btn btn-outline-secondary">
          <i data-feather="arrow-left" class="me-2"></i>Back to SLA Rules
        </a>
        <form action="{{ route('admin.sla.toggle-status', $sla) }}" method="POST" class="d-inline">
          @csrf
          <button type="submit" class="btn btn-{{ $sla->status === 'active' ? 'outline-warning' : 'outline-success' }}">
            <i data-feather="{{ $sla->status === 'active' ? 'pause' : 'play' }}" class="me-2"></i>
            {{ $sla->status === 'active' ? 'Deactivate' : 'Activate' }}
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- SLA DETAILS -->
  <div class="row">
    <!-- SLA Configuration -->
    <div class="col-md-6 mb-4">
      <div class="card-glass h-100">
        <div class="d-flex align-items-center mb-4"
          style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
          <i data-feather="settings" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
          <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">SLA Configuration</h5>
        </div>

        <div class="info-item mb-3">
          <div class="d-flex align-items-start">
            <i data-feather="type" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
            <div class="flex-grow-1">
              <div class="text-muted small mb-1"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Complaint Type</div>
              <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">
                <span class="badge bg-info" style="font-size: 0.85rem; padding: 6px 12px;">
                  {{ ucfirst($sla->complaint_type) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="info-item mb-3">
          <div class="d-flex align-items-start">
            <i data-feather="clock" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
            <div class="flex-grow-1">
              <div class="text-muted small mb-1"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Max Response Time</div>
              <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $sla->max_response_time }} hours
              </div>
            </div>
          </div>
        </div>

        <div class="info-item mb-3">
          <div class="d-flex align-items-start">
            <i data-feather="check-circle" class="me-3 text-muted"
              style="width: 18px; height: 18px; margin-top: 4px;"></i>
            <div class="flex-grow-1">
              <div class="text-muted small mb-1"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Max Resolution Time</div>
              <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">{{ $sla->max_resolution_time }} hours
              </div>
            </div>
          </div>
        </div>

        <div class="info-item mb-3">
          <div class="d-flex align-items-start">
            <i data-feather="flag" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
            <div class="flex-grow-1">
              <div class="text-muted small mb-1"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Priority</div>
              <div>
                @if($sla->priority == 'urgent')
                  <span class="badge"
                    style="background-color: #991b1b !important; color: #ffffff !important; border: 1px solid #7f1d1d !important; padding: 6px 12px !important; font-size: 0.85rem !important; border-radius: 6px !important;">
                    {{ ucfirst($sla->priority ?? 'medium') }}
                  </span>
                @else
                  <span
                    class="badge bg-{{ $sla->priority == 'low' ? 'success' : ($sla->priority == 'medium' ? 'warning' : ($sla->priority == 'high' ? 'info' : 'danger')) }}"
                    style="font-size: 0.85rem; padding: 6px 12px;">
                    {{ ucfirst($sla->priority ?? 'medium') }}
                  </span>
                @endif
              </div>
            </div>
          </div>
        </div>

        <div class="info-item mb-3">
          <div class="d-flex align-items-start">
            <i data-feather="activity" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
            <div class="flex-grow-1">
              <div class="text-muted small mb-1"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Status</div>
              <div>
                <span class="badge bg-{{ $sla->status === 'active' ? 'success' : 'danger' }}"
                  style="font-size: 0.85rem; padding: 6px 12px; color: #ffffff !important;">
                  {{ ucfirst($sla->status) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Notification & Timeline -->
    <div class="col-md-6 mb-4">
      <div class="card-glass h-100">
        <div class="d-flex align-items-center mb-4"
          style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
          <i data-feather="bell" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
          <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Notification & Timeline</h5>
        </div>

        <div class="info-item mb-3">
          <div class="d-flex align-items-start">
            <i data-feather="user" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
            <div class="flex-grow-1">
              <div class="text-muted small mb-1"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Notify To</div>
              <div class="text-white" style="font-size: 0.95rem; font-weight: 500;">
                {{ $sla->notifyTo->name ?? 'Not Set' }}
              </div>
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
                {{ $sla->created_at->format('M d, Y H:i') }}
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
                {{ $sla->updated_at->format('M d, Y H:i') }}
              </div>
            </div>
          </div>
        </div>

        <div class="info-item mb-3">
          <div class="d-flex align-items-start">
            <i data-feather="list" class="me-3 text-muted" style="width: 18px; height: 18px; margin-top: 4px;"></i>
            <div class="flex-grow-1">
              <div class="text-muted small mb-1"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Complaints</div>
              <div>
                <span class="badge bg-primary"
                  style="font-size: 0.85rem; padding: 6px 12px;">{{ $sla->complaints->count() }} complaints</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if($sla->description)
    <div class="row mb-4">
      <div class="col-12">
        <div class="card-glass">
          <div class="d-flex align-items-center mb-4"
            style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
            <i data-feather="file-text" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
            <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Description</h5>
          </div>
          <p class="text-white mb-0" style="font-size: 0.95rem; font-weight: 400; line-height: 1.6;">{{ $sla->description }}
          </p>
        </div>
      </div>
    </div>
  @endif

  <!-- Performance Metrics -->
  @if(isset($metrics))
    <div class="row mb-4">
      <div class="col-12">
        <div class="card-glass">
          <div class="d-flex align-items-center mb-4"
            style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
            <i data-feather="bar-chart-2" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
            <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Performance Metrics</h5>
          </div>
          <div class="row">
            <div class="col-md-3">
              <div class="card bg-primary text-white h-100 border-0"
                style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                <div class="card-body text-center">
                  <h3 class="card-title mb-1 fw-bold">{{ $metrics['total_complaints'] ?? 0 }}</h3>
                  <p class="card-text small opacity-75">Total Complaints</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-success text-white h-100 border-0"
                style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <div class="card-body text-center">
                  <h3 class="card-title mb-1 fw-bold">{{ $metrics['within_sla'] ?? 0 }}</h3>
                  <p class="card-text small opacity-75">Within SLA</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-danger text-white h-100 border-0"
                style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <div class="card-body text-center">
                  <h3 class="card-title mb-1 fw-bold">{{ $metrics['breached_sla'] ?? 0 }}</h3>
                  <p class="card-text small opacity-75">SLA Breached</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-info text-white h-100 border-0"
                style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                <div class="card-body text-center">
                  <h3 class="card-title mb-1 fw-bold">{{ $metrics['compliance_rate'] ?? 0 }}%</h3>
                  <p class="card-text small opacity-75">Compliance Rate</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

  <!-- Recent Complaints -->
  @if(isset($recentComplaints) && $recentComplaints->count() > 0)
    <div class="row mb-4">
      <div class="col-12">
        <div class="card-glass">
          <div class="d-flex align-items-center mb-4"
            style="border-bottom: 2px solid rgba(59, 130, 246, 0.2); padding-bottom: 12px;">
            <i data-feather="list" class="me-2 text-primary" style="width: 20px; height: 20px;"></i>
            <h5 class="text-white mb-0" style="font-size: 1.1rem; font-weight: 600;">Recent Complaints</h5>
          </div>
          <div class="table-responsive">
            <table class="table table-dark table-sm" style="margin-bottom: 0;">
              <thead>
                <tr>
                  <th
                    style="padding: 0.5rem; font-size: 0.85rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">
                    Ticket</th>
                  <th
                    style="padding: 0.5rem; font-size: 0.85rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">
                    Client</th>
                  <th
                    style="padding: 0.5rem; font-size: 0.85rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">
                    Status</th>
                  <th
                    style="padding: 0.5rem; font-size: 0.85rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">
                    Age</th>
                  <th
                    style="padding: 0.5rem; font-size: 0.85rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">
                    SLA Status</th>
                  <th style="padding: 0.5rem; font-size: 0.85rem;">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($recentComplaints as $complaint)
                  <tr>
                    <td style="padding: 0.5rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">
                      <span class="text-info">{{ $complaint->ticket_number }}</span>
                    </td>
                    <td style="padding: 0.5rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">
                      {{ $complaint->client->client_name ?? 'N/A' }}
                    </td>
                    <td style="padding: 0.5rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">
                      <span
                        class="badge bg-{{ $complaint->status === 'resolved' ? 'success' : ($complaint->status === 'closed' ? 'info' : 'warning') }}"
                        style="color: #ffffff !important;">
                        {{ ucfirst($complaint->status) }}
                      </span>
                    </td>
                    <td style="padding: 0.5rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">
                      <span class="badge bg-{{ $complaint->isOverdue() ? 'danger' : 'success' }}"
                        style="color: #ffffff !important;">
                        {{ $complaint->hours_elapsed }}h
                      </span>
                    </td>
                    <td style="padding: 0.5rem; border-right: 1px solid rgba(201, 160, 160, 0.3) !important;">
                      <span class="badge bg-{{ $complaint->isSlaBreached() ? 'danger' : 'success' }}"
                        style="color: #ffffff !important;">
                        {{ $complaint->isSlaBreached() ? 'Breached' : 'Within SLA' }}
                      </span>
                    </td>
                    <td style="padding: 0.5rem;">
                      <a href="{{ route('admin.complaints.show', $complaint) }}" class="btn btn-outline-info btn-sm"
                        style="padding: 2px 6px;">
                        <i data-feather="eye" style="width: 14px; height: 14px;"></i>
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

@endsection

@push('scripts')
  <script>
    feather.replace();
  </script>
@endpush