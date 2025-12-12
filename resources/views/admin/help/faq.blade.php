@extends('layouts.sidebar')

@section('title', 'FAQ')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white mb-0">Frequently Asked Questions</h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.help.index') }}" class="text-decoration-none">Help & Support</a></li>
            <li class="breadcrumb-item active text-white">FAQ</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Search FAQ -->
    <div class="col-12 mb-4">
      <div class="card-glass">
        <div class="card-body">
          <div class="d-flex align-items-center mb-3">
            <i data-feather="search" class="me-2 text-primary"></i>
            <h5 class="mb-0 text-white">Search FAQ</h5>
          </div>
          <div class="input-group">
            <input type="text" class="form-control bg-dark text-white border-secondary" placeholder="Search for questions..." id="faqSearch">
            <button class="btn btn-primary" type="button">
              <i data-feather="search"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- FAQ Categories -->
    <div class="col-lg-3 mb-4">
      <div class="card-glass">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="list" class="me-2 text-primary"></i>
          <h5 class="mb-0 text-white">Categories</h5>
        </div>
        
        <nav class="nav flex-column">
          <a class="nav-link text-white active" href="#general" data-category="general">General</a>
          <a class="nav-link text-white" href="#user-management" data-category="user-management">User Management</a>
          <a class="nav-link text-white" href="#complaints" data-category="complaints">Complaints</a>
          <a class="nav-link text-white" href="#spare-parts" data-category="spare-parts">Spare Parts</a>
          <a class="nav-link text-white" href="#reports" data-category="reports">Reports</a>
          <a class="nav-link text-white" href="#technical" data-category="technical">Technical</a>
        </nav>
      </div>
    </div>

    <!-- FAQ Content -->
    <div class="col-lg-9 mb-4">
      
      <!-- General FAQ -->
      <div class="card-glass mb-4" data-category="general">
        <div class="card-body">
          <h4 class="text-white mb-4">
            <i data-feather="help-circle" class="me-2 text-primary"></i>
            General Questions
          </h4>
          
          <div class="accordion" id="generalAccordion">
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#general1">
                  What is the CMS Portal?
                </button>
              </h2>
              <div id="general1" class="accordion-collapse collapse show" data-bs-parent="#generalAccordion">
                <div class="accordion-body text-muted">
                  The CMS Portal is a comprehensive complaint management system designed to streamline customer service operations, track complaints, manage spare parts, and generate detailed reports for better business insights.
                </div>
              </div>
            </div>
            
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#general2">
                  How do I access the system?
                </button>
              </h2>
              <div id="general2" class="accordion-collapse collapse" data-bs-parent="#generalAccordion">
                <div class="accordion-body text-muted">
                  You can access the system through your web browser using the URL provided by your administrator. Use your username and password to log in. If you don't have credentials, contact your system administrator.
                </div>
              </div>
            </div>
            
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#general3">
                  What browsers are supported?
                </button>
              </h2>
              <div id="general3" class="accordion-collapse collapse" data-bs-parent="#generalAccordion">
                <div class="accordion-body text-muted">
                  The system supports all modern web browsers including Chrome, Firefox, Safari, and Microsoft Edge. For the best experience, we recommend using the latest version of your preferred browser.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- User Management FAQ -->
      <div class="card-glass mb-4" data-category="user-management" style="display: none;">
        <div class="card-body">
          <h4 class="text-white mb-4">
            <i data-feather="users" class="me-2 text-primary"></i>
            User Management Questions
          </h4>
          
          <div class="accordion" id="userAccordion">
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#user1">
                  How do I create a new user?
                </button>
              </h2>
              <div id="user1" class="accordion-collapse collapse show" data-bs-parent="#userAccordion">
                <div class="accordion-body text-muted">
                  To create a new user, go to the Users section, click "Add New User", fill in the required information (username, email, password), select the appropriate role, set the user status, and save.
                </div>
              </div>
            </div>
            
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#user2">
                  What are the different user roles?
                </button>
              </h2>
              <div id="user2" class="accordion-collapse collapse" data-bs-parent="#userAccordion">
                <div class="accordion-body text-muted">
                  The system has three main roles: Admin (full system access), Employee (limited access based on permissions), and Manager (management-level access). Each role has specific permissions that control what users can access and modify.
                </div>
              </div>
            </div>
            
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#user3">
                  How do I reset a user's password?
                </button>
              </h2>
              <div id="user3" class="accordion-collapse collapse" data-bs-parent="#userAccordion">
                <div class="accordion-body text-muted">
                  To reset a user's password, go to the Users section, find the user, click on their profile, and use the "Reset Password" option. The user will receive an email with instructions to set a new password.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Complaints FAQ -->
      <div class="card-glass mb-4" data-category="complaints" style="display: none;">
        <div class="card-body">
          <h4 class="text-white mb-4">
            <i data-feather="alert-triangle" class="me-2 text-primary"></i>
            Complaint Management Questions
          </h4>
          
          <div class="accordion" id="complaintAccordion">
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#complaint1">
                  How do I create a new complaint?
                </button>
              </h2>
              <div id="complaint1" class="accordion-collapse collapse show" data-bs-parent="#complaintAccordion">
                <div class="accordion-body text-muted">
                  To create a new complaint, go to the Complaints section, click "Add New Complaint", select the client, enter complaint details, assign to an employee, set the priority level, and save the complaint.
                </div>
              </div>
            </div>
            
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#complaint2">
                  What are the complaint statuses?
                </button>
              </h2>
              <div id="complaint2" class="accordion-collapse collapse" data-bs-parent="#complaintAccordion">
                <div class="accordion-body text-muted">
                  Complaints can have the following statuses: Open (new complaint awaiting assignment), In Progress (being worked on), Resolved (complaint has been resolved), and Closed (complaint is closed).
                </div>
              </div>
            </div>
            
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#complaint3">
                  How do I track complaint progress?
                </button>
              </h2>
              <div id="complaint3" class="accordion-collapse collapse" data-bs-parent="#complaintAccordion">
                <div class="accordion-body text-muted">
                  Each complaint has a detailed log that tracks all activities, status changes, and updates. You can view this log by opening the complaint details. This provides a complete audit trail for compliance and reporting.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Spare Parts FAQ -->
      <div class="card-glass mb-4" data-category="spare-parts" style="display: none;">
        <div class="card-body">
          <h4 class="text-white mb-4">
            <i data-feather="package" class="me-2 text-primary"></i>
            Spare Parts Questions
          </h4>
          
          <div class="accordion" id="spareAccordion">
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#spare1">
                  How do I add new spare parts?
                </button>
              </h2>
              <div id="spare1" class="accordion-collapse collapse show" data-bs-parent="#spareAccordion">
                <div class="accordion-body text-muted">
                  To add new spare parts, go to the Spare Parts section, click "Add New Spare Part", enter the part number, description, current stock level, minimum threshold, supplier information, and cost details, then save.
                </div>
              </div>
            </div>
            
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#spare2">
                  How do stock alerts work?
                </button>
              </h2>
              <div id="spare2" class="accordion-collapse collapse" data-bs-parent="#spareAccordion">
                <div class="accordion-body text-muted">
                  The system automatically generates alerts when stock levels fall below the minimum threshold you set for each spare part. These alerts help ensure you never run out of critical parts and can reorder in time.
                </div>
              </div>
            </div>
            
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#spare3">
                  How do I request spare parts for a complaint?
                </button>
              </h2>
              <div id="spare3" class="accordion-collapse collapse" data-bs-parent="#spareAccordion">
                <div class="accordion-body text-muted">
                  When working on a complaint, you can request spare parts by going to the complaint details, clicking "Request Spare Parts", selecting the required parts and quantities, and submitting the request for approval.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Reports FAQ -->
      <div class="card-glass mb-4" data-category="reports" style="display: none;">
        <div class="card-body">
          <h4 class="text-white mb-4">
            <i data-feather="bar-chart-2" class="me-2 text-primary"></i>
            Reports Questions
          </h4>
          
          <div class="accordion" id="reportAccordion">
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#report1">
                  What reports are available?
                </button>
              </h2>
              <div id="report1" class="accordion-collapse collapse show" data-bs-parent="#reportAccordion">
                <div class="accordion-body text-muted">
                  The system provides various reports including complaint summary reports, employee performance reports, SLA compliance reports, spare parts usage reports, and client satisfaction reports.
                </div>
              </div>
            </div>
            
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#report2">
                  How do I export reports?
                </button>
              </h2>
              <div id="report2" class="accordion-collapse collapse" data-bs-parent="#reportAccordion">
                <div class="accordion-body text-muted">
                  All reports can be exported in multiple formats including PDF, Excel, and CSV. Look for the export buttons at the top of each report page. Select your preferred format and the report will be downloaded to your device.
                </div>
              </div>
            </div>
            
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#report3">
                  Can I schedule automatic reports?
                </button>
              </h2>
              <div id="report3" class="accordion-collapse collapse" data-bs-parent="#reportAccordion">
                <div class="accordion-body text-muted">
                  Currently, reports need to be generated manually. However, you can save report configurations and quickly regenerate them with the same parameters. Automatic scheduling may be available in future updates.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Technical FAQ -->
      <div class="card-glass mb-4" data-category="technical" style="display: none;">
        <div class="card-body">
          <h4 class="text-white mb-4">
            <i data-feather="tool" class="me-2 text-primary"></i>
            Technical Questions
          </h4>
          
          <div class="accordion" id="techAccordion">
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#tech1">
                  What should I do if I can't log in?
                </button>
              </h2>
              <div id="tech1" class="accordion-collapse collapse show" data-bs-parent="#techAccordion">
                <div class="accordion-body text-muted">
                  If you can't log in, first check your username and password. Ensure your account is active and not locked. If the problem persists, contact your system administrator for assistance.
                </div>
              </div>
            </div>
            
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#tech2">
                  The system is running slowly. What should I do?
                </button>
              </h2>
              <div id="tech2" class="accordion-collapse collapse" data-bs-parent="#techAccordion">
                <div class="accordion-body text-muted">
                  If the system is running slowly, try clearing your browser cache, check your internet connection, and refresh the page. If the problem continues, contact IT support as it may be a server-side issue.
                </div>
              </div>
            </div>
            
            <div class="accordion-item bg-transparent border-secondary">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#tech3">
                  Data is not loading correctly. How do I fix this?
                </button>
              </h2>
              <div id="tech3" class="accordion-collapse collapse" data-bs-parent="#techAccordion">
                <div class="accordion-body text-muted">
                  If data is not displaying correctly, try refreshing the page, check your user permissions, and verify that the data exists in the system. If the issue persists, contact support for further assistance.
                </div>
              </div>
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
  
  // FAQ Search functionality
  document.getElementById('faqSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const accordionItems = document.querySelectorAll('.accordion-item');
    
    accordionItems.forEach(item => {
      const text = item.textContent.toLowerCase();
      if (text.includes(searchTerm)) {
        item.style.display = 'block';
      } else {
        item.style.display = 'none';
      }
    });
  });
  
  // Category filtering
  document.querySelectorAll('[data-category]').forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Update active category
      document.querySelectorAll('[data-category]').forEach(l => l.classList.remove('active'));
      this.classList.add('active');
      
      // Show/hide FAQ sections
      const category = this.getAttribute('data-category');
      document.querySelectorAll('[data-category]').forEach(section => {
        if (section.getAttribute('data-category') === category) {
          section.style.display = 'block';
        } else {
          section.style.display = 'none';
        }
      });
    });
  });
</script>
@endpush
