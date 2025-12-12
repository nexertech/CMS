@extends('layouts.sidebar')

@section('title', 'Help & Support')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white mb-0">Help & Support</h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active text-white">Help & Support</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Quick Help Cards -->
    <div class="col-lg-4 mb-4">
      <div class="card-glass h-100">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="book-open" class="me-2 text-primary"></i>
          <h5 class="mb-0 text-white">Documentation</h5>
        </div>
        <p class="text-muted mb-3">Comprehensive guides and tutorials to help you get the most out of the system.</p>
        <a href="{{ route('admin.help.documentation') }}" class="btn btn-outline-primary">
          <i data-feather="arrow-right" class="me-1"></i>View Documentation
        </a>
      </div>
    </div>

    <div class="col-lg-4 mb-4">
      <div class="card-glass h-100">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="help-circle" class="me-2 text-primary"></i>
          <h5 class="mb-0 text-white">FAQ</h5>
        </div>
        <p class="text-muted mb-3">Find answers to frequently asked questions and common issues.</p>
        <a href="{{ route('admin.help.faq') }}" class="btn btn-outline-primary">
          <i data-feather="arrow-right" class="me-1"></i>View FAQ
        </a>
      </div>
    </div>

    <div class="col-lg-4 mb-4">
      <div class="card-glass h-100">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="mail" class="me-2 text-primary"></i>
          <h5 class="mb-0 text-white">Contact Support</h5>
        </div>
        <p class="text-muted mb-3">Get in touch with our support team for personalized assistance.</p>
        <a href="{{ route('admin.help.contact') }}" class="btn btn-outline-primary">
          <i data-feather="arrow-right" class="me-1"></i>Contact Us
        </a>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Quick Start Guide -->
    <div class="col-lg-8 mb-4">
      <div class="card-glass">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="play-circle" class="me-2 text-primary"></i>
          <h5 class="mb-0 text-white">Quick Start Guide</h5>
        </div>
        
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="d-flex align-items-start">
              <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px;">
                <span class="text-white fw-bold">1</span>
              </div>
              <div>
                <h6 class="text-white mb-1">Dashboard Overview</h6>
                <p class="text-muted small mb-0">Learn about the main dashboard and key metrics.</p>
              </div>
            </div>
          </div>
          
          <div class="col-md-6 mb-3">
            <div class="d-flex align-items-start">
              <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px;">
                <span class="text-white fw-bold">2</span>
              </div>
              <div>
                <h6 class="text-white mb-1">User Management</h6>
                <p class="text-muted small mb-0">Create and manage user accounts and permissions.</p>
              </div>
            </div>
          </div>
          
          <div class="col-md-6 mb-3">
            <div class="d-flex align-items-start">
              <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px;">
                <span class="text-white fw-bold">3</span>
              </div>
              <div>
                <h6 class="text-white mb-1">Complaint Management</h6>
                <p class="text-muted small mb-0">Handle customer complaints and track resolution.</p>
              </div>
            </div>
          </div>
          
          <div class="col-md-6 mb-3">
            <div class="d-flex align-items-start">
              <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px;">
                <span class="text-white fw-bold">4</span>
              </div>
              <div>
                <h6 class="text-white mb-1">Reports & Analytics</h6>
                <p class="text-muted small mb-0">Generate reports and analyze system performance.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- System Status -->
    <div class="col-lg-4 mb-4">
      <div class="card-glass">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="activity" class="me-2 text-primary"></i>
          <h5 class="mb-0 text-white">System Status</h5>
        </div>
        
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-white">API Status</span>
            <span class="badge bg-success">Operational</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-white">Database</span>
            <span class="badge bg-success">Connected</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-white">Email Service</span>
            <span class="badge bg-success">Active</span>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="text-white">Last Update</span>
            <span class="text-muted">2 minutes ago</span>
          </div>
        </div>
        
        <div class="mt-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-white small">System Load</span>
            <span class="text-muted small">45%</span>
          </div>
          <div class="progress" style="height: 6px;">
            <div class="progress-bar bg-success" style="width: 45%"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Contact Information -->
  <div class="row">
    <div class="col-12">
      <div class="card-glass">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="phone" class="me-2 text-primary"></i>
          <h5 class="mb-0 text-white">Need Immediate Help?</h5>
        </div>
        
        <div class="row">
          <div class="col-md-4 mb-3">
            <div class="text-center">
              <i data-feather="mail" class="text-primary mb-2" style="width: 24px; height: 24px;"></i>
              <h6 class="text-white mb-1">Email Support</h6>
              <p class="text-muted small mb-0">support@cmsadmin.com</p>
              <p class="text-muted small">Response within 24 hours</p>
            </div>
          </div>
          
          <div class="col-md-4 mb-3">
            <div class="text-center">
              <i data-feather="phone" class="text-primary mb-2" style="width: 24px; height: 24px;"></i>
              <h6 class="text-white mb-1">Phone Support</h6>
              <p class="text-muted small mb-0">+1 (555) 123-4567</p>
              <p class="text-muted small">Mon-Fri, 9AM-6PM</p>
            </div>
          </div>
          
          <div class="col-md-4 mb-3">
            <div class="text-center">
              <i data-feather="message-circle" class="text-primary mb-2" style="width: 24px; height: 24px;"></i>
              <h6 class="text-white mb-1">Live Chat</h6>
              <p class="text-muted small mb-0">Available 24/7</p>
              <p class="text-muted small">Instant response</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  feather.replace();
</script>
@endpush
