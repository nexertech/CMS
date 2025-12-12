@extends('layouts.sidebar')

@section('title', 'Documentation')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white mb-0">Documentation</h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.help.index') }}" class="text-decoration-none">Help & Support</a></li>
            <li class="breadcrumb-item active text-white">Documentation</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Table of Contents -->
    <div class="col-lg-3 mb-4">
      <div class="card-glass">
        <div class="d-flex align-items-center mb-3">
          <i data-feather="list" class="me-2 text-primary"></i>
          <h5 class="mb-0 text-white">Table of Contents</h5>
        </div>
        
        <nav class="nav flex-column">
          <a class="nav-link text-white" href="#getting-started">Getting Started</a>
          <a class="nav-link text-white" href="#user-management">User Management</a>
          <a class="nav-link text-white" href="#complaint-management">Complaint Management</a>
          <a class="nav-link text-white" href="#spare-parts">Spare Parts</a>
          <a class="nav-link text-white" href="#reports">Reports & Analytics</a>
          <a class="nav-link text-white" href="#sla-rules">SLA Rules</a>
          <a class="nav-link text-white" href="#approvals">Approvals</a>
          <a class="nav-link text-white" href="#troubleshooting">Troubleshooting</a>
        </nav>
      </div>
    </div>

    <!-- Documentation Content -->
    <div class="col-lg-9 mb-4">
      <div class="card-glass">
        <div class="card-body">
          
          <!-- Getting Started -->
          <section id="getting-started" class="mb-5">
            <h3 class="text-white mb-3">
              <i data-feather="play-circle" class="me-2 text-primary"></i>
              Getting Started
            </h3>
            
            <div class="mb-4">
              <h5 class="text-white">Welcome to CMS Portal</h5>
              <p class="text-muted">The CMS Portal is a comprehensive complaint management system designed to streamline customer service operations, track complaints, manage spare parts, and generate detailed reports.</p>
            </div>

            <div class="mb-4">
              <h5 class="text-white">Key Features</h5>
              <ul class="text-muted">
                <li>Complaint tracking and management</li>
                <li>User and employee management</li>
                <li>Spare parts inventory</li>
                <li>SLA rule configuration</li>
                <li>Approval workflows</li>
                <li>Comprehensive reporting</li>
                <li>Role-based access control</li>
              </ul>
            </div>

            <div class="mb-4">
              <h5 class="text-white">System Requirements</h5>
              <ul class="text-muted">
                <li>PHP 8.1 or higher</li>
                <li>Laravel 10.x</li>
                <li>MySQL 5.7 or higher</li>
                <li>Modern web browser (Chrome, Firefox, Safari, Edge)</li>
              </ul>
            </div>
          </section>

          <!-- User Management -->
          <section id="user-management" class="mb-5">
            <h3 class="text-white mb-3">
              <i data-feather="users" class="me-2 text-primary"></i>
              User Management
            </h3>
            
            <div class="mb-4">
              <h5 class="text-white">Creating Users</h5>
              <p class="text-muted">To create a new user:</p>
              <ol class="text-muted">
                <li>Navigate to Users section</li>
                <li>Click "Add New User" button</li>
                <li>Fill in required information (username, email, password)</li>
                <li>Select appropriate role</li>
                <li>Set user status (active/inactive)</li>
                <li>Save the user</li>
              </ol>
            </div>

            <div class="mb-4">
              <h5 class="text-white">User Roles</h5>
              <ul class="text-muted">
                <li><strong>Admin:</strong> Full system access</li>
                <li><strong>Employee:</strong> Limited access based on permissions</li>
                <li><strong>Manager:</strong> Management-level access</li>
              </ul>
            </div>

            <div class="mb-4">
              <h5 class="text-white">Managing Permissions</h5>
              <p class="text-muted">Permissions are managed through roles. Each role can have specific permissions assigned to control what users can access and modify.</p>
            </div>
          </section>

          <!-- Complaint Management -->
          <section id="complaint-management" class="mb-5">
            <h3 class="text-white mb-3">
              <i data-feather="alert-triangle" class="me-2 text-primary"></i>
              Complaint Management
            </h3>
            
            <div class="mb-4">
              <h5 class="text-white">Creating Complaints</h5>
              <p class="text-muted">To create a new complaint:</p>
              <ol class="text-muted">
                <li>Go to Complaints section</li>
                <li>Click "Add New Complaint"</li>
                <li>Select client</li>
                <li>Enter complaint details</li>
                <li>Assign to employee</li>
                <li>Set priority level</li>
                <li>Save complaint</li>
              </ol>
            </div>

            <div class="mb-4">
              <h5 class="text-white">Complaint Status</h5>
              <ul class="text-muted">
                <li><strong>Open:</strong> New complaint awaiting assignment</li>
                <li><strong>In Progress:</strong> Complaint is being worked on</li>
                <li><strong>Resolved:</strong> Complaint has been resolved</li>
                <li><strong>Closed:</strong> Complaint is closed</li>
              </ul>
            </div>

            <div class="mb-4">
              <h5 class="text-white">Tracking Progress</h5>
              <p class="text-muted">Each complaint has a detailed log that tracks all activities, status changes, and updates. This provides complete audit trail for compliance and reporting.</p>
            </div>
          </section>

          <!-- Spare Parts -->
          <section id="spare-parts" class="mb-5">
            <h3 class="text-white mb-3">
              <i data-feather="package" class="me-2 text-primary"></i>
              Spare Parts Management
            </h3>
            
            <div class="mb-4">
              <h5 class="text-white">Inventory Management</h5>
              <p class="text-muted">The system tracks spare parts inventory including:</p>
              <ul class="text-muted">
                <li>Part numbers and descriptions</li>
                <li>Current stock levels</li>
                <li>Minimum stock thresholds</li>
                <li>Supplier information</li>
                <li>Cost tracking</li>
              </ul>
            </div>

            <div class="mb-4">
              <h5 class="text-white">Stock Alerts</h5>
              <p class="text-muted">The system automatically generates alerts when stock levels fall below minimum thresholds, ensuring you never run out of critical parts.</p>
            </div>

            <div class="mb-4">
              <h5 class="text-white">Approval Process</h5>
              <p class="text-muted">Spare parts requests go through an approval process to ensure proper authorization before parts are allocated to complaints.</p>
            </div>
          </section>

          <!-- Reports -->
          <section id="reports" class="mb-5">
            <h3 class="text-white mb-3">
              <i data-feather="bar-chart-2" class="me-2 text-primary"></i>
              Reports & Analytics
            </h3>
            
            <div class="mb-4">
              <h5 class="text-white">Available Reports</h5>
              <ul class="text-muted">
                <li>Complaint summary reports</li>
                <li>Employee performance reports</li>
                <li>SLA compliance reports</li>
                <li>Spare parts usage reports</li>
                <li>Client satisfaction reports</li>
              </ul>
            </div>

            <div class="mb-4">
              <h5 class="text-white">Export Options</h5>
              <p class="text-muted">All reports can be exported in multiple formats including PDF, Excel, and CSV for further analysis and sharing.</p>
            </div>

            <div class="mb-4">
              <h5 class="text-white">Dashboard Analytics</h5>
              <p class="text-muted">The main dashboard provides real-time analytics including complaint trends, resolution times, and key performance indicators.</p>
            </div>
          </section>

          <!-- SLA Rules -->
          <section id="sla-rules" class="mb-5">
            <h3 class="text-white mb-3">
              <i data-feather="clock" class="me-2 text-primary"></i>
              SLA Rules
            </h3>
            
            <div class="mb-4">
              <h5 class="text-white">Service Level Agreements</h5>
              <p class="text-muted">SLA rules define the expected response and resolution times for different types of complaints. The system automatically tracks compliance and generates alerts for breaches.</p>
            </div>

            <div class="mb-4">
              <h5 class="text-white">Configuring SLA Rules</h5>
              <p class="text-muted">To configure SLA rules:</p>
              <ol class="text-muted">
                <li>Go to SLA Rules section</li>
                <li>Click "Add New Rule"</li>
                <li>Define rule conditions</li>
                <li>Set response and resolution times</li>
                <li>Configure notification settings</li>
                <li>Save the rule</li>
              </ol>
            </div>
          </section>

          <!-- Approvals -->
          <section id="approvals" class="mb-5">
            <h3 class="text-white mb-3">
              <i data-feather="check-circle" class="me-2 text-primary"></i>
              Approval Workflows
            </h3>
            
            <div class="mb-4">
              <h5 class="text-white">Approval Process</h5>
              <p class="text-muted">The system includes approval workflows for:</p>
              <ul class="text-muted">
                <li>Spare parts requests</li>
                <li>Employee leave requests</li>
                <li>High-value complaint resolutions</li>
                <li>System configuration changes</li>
              </ul>
            </div>

            <div class="mb-4">
              <h5 class="text-white">Managing Approvals</h5>
              <p class="text-muted">Approvers can view pending requests, review details, and approve or reject requests with comments. All approval actions are logged for audit purposes.</p>
            </div>
          </section>

          <!-- Troubleshooting -->
          <section id="troubleshooting" class="mb-5">
            <h3 class="text-white mb-3">
              <i data-feather="tool" class="me-2 text-primary"></i>
              Troubleshooting
            </h3>
            
            <div class="mb-4">
              <h5 class="text-white">Common Issues</h5>
              
              <div class="mb-3">
                <h6 class="text-white">Login Issues</h6>
                <p class="text-muted">If you cannot log in:</p>
                <ul class="text-muted">
                  <li>Check username and password</li>
                  <li>Ensure account is active</li>
                  <li>Contact administrator if locked out</li>
                </ul>
              </div>

              <div class="mb-3">
                <h6 class="text-white">Performance Issues</h6>
                <p class="text-muted">If the system is running slowly:</p>
                <ul class="text-muted">
                  <li>Clear browser cache</li>
                  <li>Check internet connection</li>
                  <li>Contact IT support for server issues</li>
                </ul>
              </div>

              <div class="mb-3">
                <h6 class="text-white">Data Not Loading</h6>
                <p class="text-muted">If data is not displaying correctly:</p>
                <ul class="text-muted">
                  <li>Refresh the page</li>
                  <li>Check your permissions</li>
                  <li>Verify data exists in the system</li>
                </ul>
              </div>
            </div>

            <div class="mb-4">
              <h5 class="text-white">Getting Help</h5>
              <p class="text-muted">If you need additional assistance:</p>
              <ul class="text-muted">
                <li>Check the FAQ section</li>
                <li>Contact support team</li>
                <li>Submit a support ticket</li>
                <li>Email: support@cmsadmin.com</li>
              </ul>
            </div>
          </section>

        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  feather.replace();
  
  // Smooth scrolling for table of contents links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });
</script>
@endpush
