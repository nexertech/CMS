@extends('layouts.sidebar')

@section('title', 'Edit Role — CMS Admin')

@section('content')
<!-- PAGE HEADER -->
<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2 class="text-white mb-2">Edit Role: {{ $role->role_name }}</h2>
      <p class="text-light">Update role information and permissions</p>
    </div>
  </div>
</div>

<!-- ROLE FORM -->
<div class="card-glass">
          <form action="{{ route('admin.roles.update', $role) }}" method="POST">
            @csrf
            @method('PATCH')
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="role_name" class="form-label text-white">Role Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control @error('role_name') is-invalid @enderror" 
                         id="role_name" name="role_name" value="{{ old('role_name', $role->role_name) }}" required>
                  @error('role_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="description" class="form-label text-white">Description</label>
                  <input type="text" class="form-control @error('description') is-invalid @enderror" 
                         id="description" name="description" value="{{ old('description', $role->description) }}">
                  @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <!-- Permissions Section -->
            <div class="mb-4">
              <h6 class="text-white fw-bold mb-3">Select Modules</h6>
              <div class="row">
                @php
                $modules = [
                  'dashboard' => ['label' => 'Dashboard', 'icon' => 'home', 'sublinks' => []],
                  'users' => ['label' => 'Users', 'icon' => 'users', 'sublinks' => []],
                  'frontend-users' => ['label' => 'Frontend Users', 'icon' => 'user-check', 'sublinks' => []],
                  'city' => ['label' => 'GE Groups', 'icon' => 'map', 'sublinks' => []],
                  'sector' => ['label' => 'GE Nodes', 'icon' => 'map-pin', 'sublinks' => []],
                  'roles' => ['label' => 'Roles', 'icon' => 'shield', 'sublinks' => []],
                  'employees' => ['label' => 'Employees', 'icon' => 'user-check', 'sublinks' => ['designation' => 'Designations']],
                  'complaints' => ['label' => 'Complaints Mgmt', 'icon' => 'file-text', 'sublinks' => [
                    'category' => 'Complaint Cat',
                    'complaint-titles' => 'Complaint Types',
                    'complaints' => 'Complaints Regn',
                    'approvals' => 'Total Complaints'
                  ]],
                  'spares' => ['label' => 'Stock Products', 'icon' => 'package', 'sublinks' => []],
                  'reports' => ['label' => 'Reports', 'icon' => 'bar-chart-2', 'sublinks' => []],
                  'sla' => ['label' => 'SLA Rules', 'icon' => 'clock', 'sublinks' => []],
                ];
                
                $rolePermissions = $role->rolePermissions->pluck('module_name')->toArray();
                @endphp
                
                @foreach($modules as $moduleKey => $moduleData)
                @php
                $hasModulePermission = in_array($moduleKey, $rolePermissions);
                $hasAnySublink = false;
                if (!empty($moduleData['sublinks'])) {
                  foreach($moduleData['sublinks'] as $sublinkKey => $sublinkLabel) {
                    if (in_array($sublinkKey, $rolePermissions)) {
                      $hasAnySublink = true;
                      break;
                    }
                  }
                }
                $shouldShowSublinks = $hasModulePermission || $hasAnySublink;
                @endphp
                <div class="col-md-6 col-lg-4 mb-3">
                  <div class="card-glass p-3" style="background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.2);">
                    <div class="form-check mb-2 d-flex align-items-center justify-content-between">
                      <div class="d-flex align-items-center flex-grow-1">
                        <input class="form-check-input module-checkbox" type="checkbox" 
                               id="{{ $moduleKey }}" 
                               name="permissions[]"
                               value="{{ $moduleKey }}" 
                               data-module="{{ $moduleKey }}"
                               {{ $hasModulePermission ? 'checked' : '' }}>
                        <label class="form-check-label text-white fw-semibold d-flex align-items-center ms-2" for="{{ $moduleKey }}" style="cursor: pointer;">
                          <i data-feather="{{ $moduleData['icon'] }}" class="me-2" style="width: 16px; height: 16px;"></i>
                          {{ $moduleData['label'] }}
                        </label>
                      </div>
                      @if(!empty($moduleData['sublinks']))
                      <button type="button" class="btn btn-link text-white p-0 border-0 arrow-toggle-btn" 
                              data-bs-toggle="collapse" 
                              data-bs-target="#sublinks-{{ $moduleKey }}" 
                              aria-expanded="{{ $shouldShowSublinks ? 'true' : 'false' }}"
                              style="background: none !important; color: inherit; cursor: pointer; border: none !important; box-shadow: none !important; outline: none !important; padding: 0 !important; margin: 0 !important; min-width: 20px;">
                        <i data-feather="chevron-down" class="arrow-icon" style="font-size: 14px; transition: transform 0.3s; width: 16px; height: 16px; transform: {{ $shouldShowSublinks ? 'rotate(0deg)' : 'rotate(-90deg)' }};"></i>
                      </button>
                      @endif
                    </div>
                    
                    @if(!empty($moduleData['sublinks']))
                    <div class="collapse ms-4 mt-2 {{ $shouldShowSublinks ? 'show' : '' }}" id="sublinks-{{ $moduleKey }}">
                      @foreach($moduleData['sublinks'] as $sublinkKey => $sublinkLabel)
                      @php
                      $hasSublinkPermission = in_array($sublinkKey, $rolePermissions);
                      @endphp
                      <div class="form-check mb-2">
                        <input class="form-check-input sublink-checkbox" type="checkbox" 
                               id="{{ $moduleKey }}_{{ $sublinkKey }}" 
                           name="permissions[]"
                               value="{{ $sublinkKey }}"
                               data-parent="{{ $moduleKey }}"
                               {{ $hasSublinkPermission ? 'checked' : '' }}>
                        <label class="form-check-label text-white-50 small d-flex align-items-center" for="{{ $moduleKey }}_{{ $sublinkKey }}" style="cursor: pointer;">
                          <span style="margin-left: 8px;">└─</span>
                          {{ $sublinkLabel }}
                    </label>
                      </div>
                      @endforeach
                    </div>
                    @endif
                  </div>
                </div>
                @endforeach
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
              <a href="{{ route('admin.roles.index', $role) }}" class="btn btn-outline-secondary">
                <i data-feather="x" class="me-2"></i>Cancel
              </a>
              <button type="submit" class="btn btn-accent">
                <i data-feather="save" class="me-2"></i>Update Role
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  feather.replace();
  
  // Handle module checkbox change - show/hide sublinks and select/deselect all sublinks
  document.querySelectorAll('.module-checkbox').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
      const moduleKey = this.getAttribute('data-module');
      const sublinksDiv = document.getElementById('sublinks-' + moduleKey);
      const arrowBtn = document.querySelector(`[data-bs-target="#sublinks-${moduleKey}"]`);
      
      if (sublinksDiv) {
        if (this.checked) {
          // Show sublinks using Bootstrap collapse
          const bsCollapse = new bootstrap.Collapse(sublinksDiv, { toggle: false });
          bsCollapse.show();
          if (arrowBtn) {
            arrowBtn.setAttribute('aria-expanded', 'true');
            const arrowIcon = arrowBtn.querySelector('.arrow-icon');
            if (arrowIcon) arrowIcon.style.transform = 'rotate(0deg)';
          }
          // Select ALL sublinks when parent checkbox is checked
          sublinksDiv.querySelectorAll('.sublink-checkbox').forEach(function(sublink) {
            sublink.checked = true;
          });
        } else {
          // Hide sublinks using Bootstrap collapse
          const bsCollapse = new bootstrap.Collapse(sublinksDiv, { toggle: false });
          bsCollapse.hide();
          if (arrowBtn) {
            arrowBtn.setAttribute('aria-expanded', 'false');
            const arrowIcon = arrowBtn.querySelector('.arrow-icon');
            if (arrowIcon) arrowIcon.style.transform = 'rotate(-90deg)';
          }
          // Uncheck all sublinks when parent is unchecked
          sublinksDiv.querySelectorAll('.sublink-checkbox').forEach(function(sublink) {
            sublink.checked = false;
          });
        }
      }
    });
  });
  
  // Handle arrow button click for sublinks collapse
  document.querySelectorAll('.arrow-toggle-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const arrowIcon = this.querySelector('.arrow-icon');
      if (arrowIcon) {
        const isExpanded = this.getAttribute('aria-expanded') === 'true';
        arrowIcon.style.transform = isExpanded ? 'rotate(-90deg)' : 'rotate(0deg)';
      }
    });
  });
  
  // Update arrow rotation on collapse events
  document.querySelectorAll('[id^="sublinks-"]').forEach(function(collapseEl) {
    collapseEl.addEventListener('shown.bs.collapse', function() {
      const btn = document.querySelector(`[data-bs-target="#${this.id}"]`);
      if (btn) {
        btn.setAttribute('aria-expanded', 'true');
        const arrowIcon = btn.querySelector('.arrow-icon');
        if (arrowIcon) arrowIcon.style.transform = 'rotate(0deg)';
      }
    });
    
    collapseEl.addEventListener('hidden.bs.collapse', function() {
      const btn = document.querySelector(`[data-bs-target="#${this.id}"]`);
      if (btn) {
        btn.setAttribute('aria-expanded', 'false');
        const arrowIcon = btn.querySelector('.arrow-icon');
        if (arrowIcon) arrowIcon.style.transform = 'rotate(-90deg)';
      }
    });
  });
  
  // Handle sublink checkbox change - show sublinks but DON'T auto-check parent
  document.querySelectorAll('.sublink-checkbox').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
      const parentKey = this.getAttribute('data-parent');
      const sublinksDiv = document.getElementById('sublinks-' + parentKey);
      const arrowBtn = document.querySelector(`[data-bs-target="#sublinks-${parentKey}"]`);
      
      if (this.checked) {
        // Show sublinks div when any sublink is checked
        if (sublinksDiv) {
          const bsCollapse = new bootstrap.Collapse(sublinksDiv, { toggle: false });
          bsCollapse.show();
          if (arrowBtn) {
            arrowBtn.setAttribute('aria-expanded', 'true');
            const arrowIcon = arrowBtn.querySelector('.arrow-icon');
            if (arrowIcon) arrowIcon.style.transform = 'rotate(0deg)';
          }
        }
        // NOTE: Parent checkbox is NOT auto-checked when individual sublink is selected
      }
    });
  });
});
</script>
@endpush
@endsection
