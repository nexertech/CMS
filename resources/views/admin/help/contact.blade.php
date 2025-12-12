@extends('layouts.sidebar')

@section('title', 'Contact Support')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white mb-0">Contact Support</h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.help.index') }}" class="text-decoration-none">Help & Support</a></li>
            <li class="breadcrumb-item active text-white">Contact Support</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Contact Information -->
    <div class="col-lg-4 mb-4">
      <div class="card-glass h-100">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="phone" class="me-2 text-primary"></i>
          <h5 class="mb-0 text-white">Contact Information</h5>
        </div>
        
        <div class="mb-4">
          <div class="d-flex align-items-center mb-3">
            <i data-feather="mail" class="text-primary me-3"></i>
            <div>
              <h6 class="text-white mb-1">Email Support</h6>
              <p class="text-muted mb-0">support@cmsadmin.com</p>
              <small class="text-muted">Response within 24 hours</small>
            </div>
          </div>
          
          <div class="d-flex align-items-center mb-3">
            <i data-feather="phone" class="text-primary me-3"></i>
            <div>
              <h6 class="text-white mb-1">Phone Support</h6>
              <p class="text-muted mb-0">+1 (555) 123-4567</p>
              <small class="text-muted">Mon-Fri, 9AM-6PM</small>
            </div>
          </div>
          
          <div class="d-flex align-items-center mb-3">
            <i data-feather="message-circle" class="text-primary me-3"></i>
            <div>
              <h6 class="text-white mb-1">Live Chat</h6>
              <p class="text-muted mb-0">Available 24/7</p>
              <small class="text-muted">Instant response</small>
            </div>
          </div>
        </div>
        
        <div class="mt-4">
          <h6 class="text-white mb-3">Business Hours</h6>
          <div class="text-muted small">
            <div class="d-flex justify-content-between mb-1">
              <span>Monday - Friday</span>
              <span>9:00 AM - 6:00 PM</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span>Saturday</span>
              <span>10:00 AM - 4:00 PM</span>
            </div>
            <div class="d-flex justify-content-between">
              <span>Sunday</span>
              <span>Closed</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Support Ticket Form -->
    <div class="col-lg-8 mb-4">
      <div class="card-glass">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="send" class="me-2 text-primary"></i>
          <h5 class="mb-0 text-white">Submit Support Ticket</h5>
        </div>
        
        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i data-feather="check-circle" class="me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        @if($errors->any())
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i data-feather="alert-circle" class="me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        <form action="{{ route('admin.help.submit-ticket') }}" method="POST">
          @csrf
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="subject" class="form-label text-white">Subject <span class="text-danger">*</span></label>
              <input type="text" class="form-control bg-dark text-white border-secondary @error('subject') is-invalid @enderror" 
                     id="subject" name="subject" value="{{ old('subject') }}" required>
              @error('subject')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="category" class="form-label text-white">Category <span class="text-danger">*</span></label>
              <select class="form-select bg-dark text-white border-secondary @error('category') is-invalid @enderror" 
                      id="category" name="category" required>
                <option value="">Select Category</option>
                <option value="technical" {{ old('category') == 'technical' ? 'selected' : '' }}>Technical Issue</option>
                <option value="feature" {{ old('category') == 'feature' ? 'selected' : '' }}>Feature Request</option>
                <option value="bug" {{ old('category') == 'bug' ? 'selected' : '' }}>Bug Report</option>
                <option value="training" {{ old('category') == 'training' ? 'selected' : '' }}>Training Request</option>
                <option value="general" {{ old('category') == 'general' ? 'selected' : '' }}>General Inquiry</option>
              </select>
              @error('category')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="priority" class="form-label text-white">Priority <span class="text-danger">*</span></label>
              <select class="form-select bg-dark text-white border-secondary @error('priority') is-invalid @enderror" 
                      id="priority" name="priority" required>
                <option value="">Select Priority</option>
                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
              </select>
              @error('priority')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="contact_method" class="form-label text-white">Preferred Contact Method</label>
              <select class="form-select bg-dark text-white border-secondary" id="contact_method" name="contact_method">
                <option value="email" {{ old('contact_method') == 'email' ? 'selected' : '' }}>Email</option>
                <option value="phone" {{ old('contact_method') == 'phone' ? 'selected' : '' }}>Phone</option>
                <option value="chat" {{ old('contact_method') == 'chat' ? 'selected' : '' }}>Live Chat</option>
              </select>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="message" class="form-label text-white">Message <span class="text-danger">*</span></label>
            <textarea class="form-control bg-dark text-white border-secondary @error('message') is-invalid @enderror" 
                      id="message" name="message" rows="6" required 
                      placeholder="Please describe your issue or request in detail...">{{ old('message') }}</textarea>
            @error('message')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text text-muted">Minimum 10 characters required</div>
          </div>
          
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="urgent_checkbox" name="urgent_checkbox">
              <label class="form-check-label text-white" for="urgent_checkbox">
                This is an urgent issue that requires immediate attention
              </label>
            </div>
          </div>
          
          <div class="d-flex justify-content-between align-items-center">
            <button type="submit" class="btn btn-primary">
              <i data-feather="send" class="me-1"></i>
              Submit Ticket
            </button>
            
            <div class="text-muted small">
              <i data-feather="info" class="me-1"></i>
              We'll respond within 24 hours
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Additional Support Options -->
  <div class="row">
    <div class="col-12">
      <div class="card-glass">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="help-circle" class="me-2 text-primary"></i>
          <h5 class="mb-0 text-white">Additional Support Options</h5>
        </div>
        
        <div class="row">
          <div class="col-md-4 mb-3">
            <div class="text-center p-3">
              <i data-feather="book-open" class="text-primary mb-2" style="width: 32px; height: 32px;"></i>
              <h6 class="text-white mb-2">Documentation</h6>
              <p class="text-muted small mb-3">Comprehensive guides and tutorials</p>
              <a href="{{ route('admin.help.documentation') }}" class="btn btn-outline-primary btn-sm">
                View Documentation
              </a>
            </div>
          </div>
          
          <div class="col-md-4 mb-3">
            <div class="text-center p-3">
              <i data-feather="help-circle" class="text-primary mb-2" style="width: 32px; height: 32px;"></i>
              <h6 class="text-white mb-2">FAQ</h6>
              <p class="text-muted small mb-3">Find answers to common questions</p>
              <a href="{{ route('admin.help.faq') }}" class="btn btn-outline-primary btn-sm">
                View FAQ
              </a>
            </div>
          </div>
          
          <div class="col-md-4 mb-3">
            <div class="text-center p-3">
              <i data-feather="video" class="text-primary mb-2" style="width: 32px; height: 32px;"></i>
              <h6 class="text-white mb-2">Video Tutorials</h6>
              <p class="text-muted small mb-3">Step-by-step video guides</p>
              <button class="btn btn-outline-primary btn-sm" disabled>
                Coming Soon
              </button>
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
  
  // Auto-fill urgent priority when checkbox is checked
  document.getElementById('urgent_checkbox').addEventListener('change', function() {
    const prioritySelect = document.getElementById('priority');
    if (this.checked) {
      prioritySelect.value = 'urgent';
    }
  });
  
  // Form validation
  document.querySelector('form').addEventListener('submit', function(e) {
    const message = document.getElementById('message').value.trim();
    if (message.length < 10) {
      e.preventDefault();
      alert('Please provide a more detailed message (minimum 10 characters).');
      return false;
    }
  });
</script>
@endpush
