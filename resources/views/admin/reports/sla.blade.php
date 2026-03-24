@extends('layouts.sidebar')

@section('title', 'SLA Report — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">CMS COMPLAINT MANAGEMENT SYSTEM</h2>
      <p class="text-light">SLA Compliance Report</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-primary" onclick="window.print()">
        <i data-feather="printer" class="me-2"></i>Print
      </button>
      <a href="{{ route('admin.reports.download', ['type' => 'sla', 'format' => 'excel'] + request()->query()) }}" class="btn btn-success">
        <i data-feather="download" class="me-2"></i>Excel
      </a>
      <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
        <i data-feather="arrow-left" class="me-2"></i>Back to Reports
      </a>
    </div>
  </div>
</div>

<!-- DATE FILTERS -->
<div class="card-glass mb-4" style="display: inline-block; width: fit-content;">
  <div class="card-body">
    <form id="slaReportFiltersForm" method="GET" class="row g-3">
      <div class="col-md-5">
        <label for="date_from" class="form-label text-white">From Date</label>
        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom }}" required onchange="submitSlaReportFilters()">
      </div>
      <div class="col-md-5">
        <label for="date_to" class="form-label text-white">To Date</label>
        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo }}" required onchange="submitSlaReportFilters()">
      </div>
      <!-- Placeholder for layout balance -->
      <div class="col-md-2"></div>
    </form>
  </div>
</div>

<!-- SUMMARY STATS -->
<div id="slaReportSummary">
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="card-glass text-center">
      <div class="card-body">
        <h4 class="text-primary mb-1">{{ $summary['total_complaints'] ?? 0 }}</h4>
        <p class="text-muted mb-0">Total Complaints</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card-glass text-center">
      <div class="card-body">
        <h4 class="text-info mb-1">{{ number_format($summary['avg_resolution_time'] ?? 0, 1) }}h</h4>
        <p class="text-muted mb-0">Avg Resolution Time</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card-glass text-center">
      <div class="card-body">
        <h4 class="text-success mb-1">{{ number_format($summary['compliance_rate'] ?? 0, 1) }}%</h4>
        <p class="text-muted mb-0">SLA Met (< 48h)</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card-glass text-center">
      <div class="card-body">
        <h4 class="text-danger mb-1">{{ $summary['breached_count'] ?? 0 }}</h4>
        <p class="text-muted mb-0">SLA Breached (> 48h)</p>
      </div>
    </div>
  </div>
</div>
</div>

<!-- REPORT TABLE -->
<div class="card-glass" id="slaReportContent" data-print-area="report-print-area">
  <div class="card-body">
    <div class="text-center mb-3">
      <h4 class="text-white mb-2">SLA Compliance Report</h4>
      <p class="text-muted small mb-0">Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</p>
    </div>
    
    <div class="table-responsive">
      <table class="table table-bordered table-dark" style="font-size: 0.75rem; width: 100%; table-layout: fixed; border: 1px solid #dee2e6 !important;">
        <thead>
          <tr>
            <th style="width: 20%; text-align: left; padding: 0.4rem 0.3rem; border: 1px solid #dee2e6 !important;">Category</th>
            <th style="width: 10%; text-align: center; padding: 0.4rem 0.3rem; border: 1px solid #dee2e6 !important;">Total</th>
            <th style="width: 10%; text-align: center; padding: 0.4rem 0.3rem; border: 1px solid #dee2e6 !important;">Resolved</th>
            <th style="width: 12%; text-align: center; padding: 0.4rem 0.3rem; border: 1px solid #dee2e6 !important;">< 24 Hours</th>
            <th style="width: 12%; text-align: center; padding: 0.4rem 0.3rem; border: 1px solid #dee2e6 !important;">24-48 Hours</th>
            <th style="width: 12%; text-align: center; padding: 0.4rem 0.3rem; border: 1px solid #dee2e6 !important;">> 48 Hours</th>
            <th style="width: 12%; text-align: center; padding: 0.4rem 0.3rem; border: 1px solid #dee2e6 !important;">Avg Time</th>
            <th style="width: 12%; text-align: center; padding: 0.4rem 0.3rem; border: 1px solid #dee2e6 !important;">% Compliance</th>
          </tr>
        </thead>
        <tbody>
          @forelse($slaData as $row)
          <tr>
            <td style="text-align: left; padding: 0.4rem 0.3rem; border: 1px solid #dee2e6 !important;">{{ $row['name'] }}</td>
            <td style="text-align: center; padding: 0.4rem 0.3rem; border: 1px solid #dee2e6 !important;">{{ number_format($row['total']) }}</td>
            <td style="text-align: center; padding: 0.4rem 0.3rem; border: 1px solid #dee2e6 !important;">{{ number_format($row['resolved']) }}</td>
            <td style="text-align: center; padding: 0.4rem 0.3rem; border: 1px solid #dee2e6 !important;" class="text-success">{{ number_format($row['lt_24h']) }}</td>
            <td style="text-align: center; padding: 0.4rem 0.3rem; border: 1px solid #dee2e6 !important;" class="text-warning">{{ number_format($row['24_48h']) }}</td>
            <td style="text-align: center; padding: 0.4rem 0.3rem; border: 1px solid #dee2e6 !important;" class="text-danger">{{ number_format($row['gt_48h']) }}</td>
            <td style="text-align: center; padding: 0.4rem 0.3rem; border: 1px solid #dee2e6 !important;">{{ number_format($row['avg_time'], 1) }}h</td>
            <td style="text-align: center; padding: 0.4rem 0.3rem; border: 1px solid #dee2e6 !important;">
              @if($row['compliance_rate'] >= 80)
                <span class="text-success">{{ $row['compliance_rate'] }}%</span>
              @elseif($row['compliance_rate'] >= 50)
                <span class="text-warning">{{ $row['compliance_rate'] }}%</span>
              @else
                <span class="text-danger">{{ $row['compliance_rate'] }}%</span>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="text-center py-4" style="border: 1px solid #dee2e6 !important;">
              <i data-feather="clock" class="feather-lg mb-2"></i>
              <div>No data found for selected period</div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@push('styles')
<style>
  #slaReportContent table {
    border-collapse: collapse !important;
  }
  #slaReportContent table th,
  #slaReportContent table td {
    border-left: 1px solid #dee2e6 !important;
    border-right: 1px solid #dee2e6 !important;
    border-top: 1px solid #dee2e6 !important;
    border-bottom: 1px solid #dee2e6 !important;
  }
  @media print {
    body * {
      visibility: hidden;
    }
    #slaReportContent, #slaReportContent * {
      visibility: visible;
    }
    #slaReportContent {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      background: #fff !important;
    }
    .btn, .card-glass .card-body form, .mb-4:first-child {
      display: none !important;
    }
    .table-dark {
      background-color: #fff !important;
      color: #000 !important;
    }
    .table-dark th {
      background-color: #f8f9fa !important;
      color: #000 !important;
      border: 1px solid #000 !important;
    }
    .table-dark td {
      border: 1px solid #000 !important;
      color: #000 !important;
    }
    .text-white {
      color: #000 !important;
    }
    .text-muted {
      color: #666 !important;
    }
    .text-success, .text-warning, .text-danger, .text-info {
        color: #000 !important; /* Force black for text in print usually, or keep colors if printer supports it */
    }
  }
</style>
@endpush

@push('scripts')
<script>
let slaReportDebounceTimer;

function submitSlaReportFilters() {
    clearTimeout(slaReportDebounceTimer);
    slaReportDebounceTimer = setTimeout(() => {
        loadSlaReport();
    }, 300);
}

function loadSlaReport() {
    const form = document.getElementById('slaReportFiltersForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    // Show loading
    const summaryDiv = document.getElementById('slaReportSummary');
    const content = document.getElementById('slaReportContent');
    if (summaryDiv) summaryDiv.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
    if (content) content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    // Update URL without reload
    const url = '{{ route("admin.reports.sla") }}?' + params.toString();
    window.history.pushState({}, '', url);
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html',
        }
    })
    .then(response => response.text())
    .then(html => {
        // Parse the response
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Extract summary and report content
        const newSummary = doc.getElementById('slaReportSummary');
        const newContent = doc.getElementById('slaReportContent');
        
        if (newSummary && summaryDiv) {
            summaryDiv.innerHTML = newSummary.innerHTML;
        }
        if (newContent && content) {
            content.innerHTML = newContent.innerHTML;
        }
        
        // Re-initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    })
    .catch(error => {
        console.error('Error loading report:', error);
        if (content) {
            content.innerHTML = '<div class="alert alert-danger">Error loading report. Please refresh the page.</div>';
        }
    });
}
</script>
@endpush

@endsection
