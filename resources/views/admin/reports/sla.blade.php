@extends('layouts.sidebar')

@section('title', 'SLA Report — CMS Admin')

@section('content')
<div class="card-glass">
  <div class="card-body text-center py-5">
    <i data-feather="alert-circle" class="feather-xl text-warning mb-3"></i>
    <h4 class="text-white mb-3">Report Not Available</h4>
    <p class="text-muted">This report is currently unavailable. Please use the main reports dashboard.</p>
    <a href="{{ route('admin.reports.index') }}" class="btn btn-accent mt-3">
      <i data-feather="arrow-left" class="me-2"></i>Back to Reports
    </a>
  </div>
</div>
@endsection

