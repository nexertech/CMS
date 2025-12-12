<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AdminController as AdminController;
use App\Http\Controllers\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Admin\ComplaintCrudController as AdminComplaintCrudController;
use App\Http\Controllers\Admin\EmployeeController as AdminEmployeeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\FrontendUserController as AdminFrontendUserController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\SpareController as AdminSpareController;
use App\Http\Controllers\Admin\ApprovalController as AdminApprovalController;
use App\Http\Controllers\Admin\SlaController as AdminSlaController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ComplaintTitleController as AdminComplaintTitleController;
use App\Http\Controllers\Admin\SectorController as AdminSectorController;
use App\Http\Controllers\Admin\CityController as AdminCityController;
use App\Http\Controllers\Admin\CmeController as AdminCmeController;
use App\Http\Controllers\Admin\DesignationController as AdminDesignationController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\SearchController;
// Frontend routes are defined in routes/frontend.php and loaded here

/*
|--------------------------------------------------------------------------
| Default Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/frontend.php';

Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
});

// Public auth pages (distinct names to avoid clashing with Laravel auth)
// Auth pages for frontend are in routes/frontend.php

// Legacy redirects (removed to allow frontend to own public paths)
Route::redirect('/admin/rdashboard', '/admin/dashboard');

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Theme management
    Route::post('/theme', [App\Http\Controllers\ThemeController::class, 'update'])->name('theme.update');
    Route::get('/theme', [App\Http\Controllers\ThemeController::class, 'get'])->name('theme.get');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Admin Routes (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'admin.access'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    // ===============================
    // 🏠 Dashboard
    // ===============================
    Route::middleware(['permission:dashboard'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    });
    // Notifications
    Route::get('/notifications', [AdminController::class, 'notificationsIndex'])->name('notifications.index');
    Route::get('/notifications/api', [AdminController::class, 'getNotifications'])->name('notifications.api');
    Route::get('/dashboard/chart-data', [AdminDashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    Route::get('/dashboard/real-time-updates', [AdminDashboardController::class, 'getRealTimeUpdates'])->name('dashboard.real-time-updates');
    
    // ===============================
    // 👤 User Management
    // ===============================
    Route::middleware(['permission:users.view'])->group(function () {
        Route::resource('users', AdminUserController::class);
        Route::post('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('users/{user}/permissions', [AdminUserController::class, 'getPermissions'])->name('users.permissions');
        Route::post('users/{user}/permissions', [AdminUserController::class, 'updatePermissions'])->name('users.update-permissions');
        Route::get('users/{user}/activity', [AdminUserController::class, 'getActivityLog'])->name('users.activity');
        Route::post('users/bulk-action', [AdminUserController::class, 'bulkAction'])->name('users.bulk-action');
        Route::get('users/export', [AdminUserController::class, 'export'])->name('users.export');
    });
    
    // ===============================
    // 🌐 Frontend Users Management
    // ===============================
    Route::middleware(['permission:frontend-users'])->group(function () {
        Route::resource('frontend-users', AdminFrontendUserController::class)->parameters([
            'frontend-user' => 'frontend_user'
        ]);
        Route::post('frontend-users/{frontend_user}/toggle-status', [AdminFrontendUserController::class, 'toggleStatus'])->name('frontend-users.toggle-status');
        Route::get('frontend-users/{frontend_user}/assign-locations', [AdminFrontendUserController::class, 'getAssignForm'])->name('frontend-users.assign-locations');
        Route::post('frontend-users/{frontend_user}/assign-locations', [AdminFrontendUserController::class, 'assignLocations'])->name('frontend-users.assign-locations.save');
    });
    
    // ===============================
    // 🧩 Role Management
    // ===============================
    Route::middleware(['permission:roles.view'])->group(function () {
        Route::resource('roles', AdminRoleController::class)->except(['destroy']);
        Route::get('roles/statistics', [AdminRoleController::class, 'getStatistics'])->name('roles.statistics');
        Route::post('roles/bulk-action', [AdminRoleController::class, 'bulkAction'])->name('roles.bulk-action');
        Route::get('roles/export', [AdminRoleController::class, 'export'])->name('roles.export');
    });
    Route::middleware(['permission:roles.delete'])->delete('roles/{role}', [AdminRoleController::class, 'destroy'])->name('roles.destroy');

    // ===============================
    // 👨‍💼 Employee Management
    // ===============================
    Route::middleware(['permission:employees.view'])->group(function () {
        // Extra AJAX/helper routes (must come BEFORE resource routes to avoid conflicts)
        Route::get('employees/sectors', [AdminEmployeeController::class, 'getSectorsByCity'])->name('employees.sectors');
        Route::get('employees/designations', [AdminEmployeeController::class, 'getDesignationsByCategory'])->name('employees.designations');
        Route::get('employees/export', [AdminEmployeeController::class, 'export'])->name('employees.export');
        Route::post('employees/bulk-action', [AdminEmployeeController::class, 'bulkAction'])->name('employees.bulk-action');
        
        // Resource routes
        Route::resource('employees', AdminEmployeeController::class)->except(['destroy']);
        
        // Employee-specific routes (must come AFTER resource routes)
        Route::get('employees/{employee}/edit-data', [AdminEmployeeController::class, 'getEditData'])->name('employees.edit-data');
        Route::post('employees/{employee}/toggle-status', [AdminEmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');
        Route::get('employees/{employee}/performance', [AdminEmployeeController::class, 'getPerformance'])->name('employees.performance');
    });
    
    // Delete routes
    Route::middleware(['permission:employees.delete'])->group(function () {
        Route::delete('employees/{employee}', [AdminEmployeeController::class, 'destroy'])->name('employees.destroy');
    });

    // ===============================
    // 🛠 Complaints
    // ===============================
    Route::middleware(['permission:complaints.view'])->group(function () {
        Route::resource('complaints', AdminComplaintController::class);
        Route::post('complaints/{complaint}/assign', [AdminComplaintController::class, 'assign'])->name('complaints.assign');
        Route::post('complaints/{complaint}/update-status', [AdminComplaintController::class, 'updateStatus'])->name('complaints.update-status');
        Route::post('complaints/{complaint}/add-spare-parts', [AdminComplaintController::class, 'addSpareParts'])->name('complaints.add-spare-parts');
        Route::get('complaints/{complaint}/print-slip', [AdminComplaintController::class, 'printSlip'])->name('complaints.print-slip');
    });

    // ===============================
    // 💬 Feedback
    // ===============================
    Route::middleware(['permission:complaints.view'])->group(function () {
        // List route must come before parameterized routes
        Route::get('feedbacks', [AdminFeedbackController::class, 'index'])->name('feedbacks.index');
        // Create/Store routes
        Route::get('complaints/{complaint}/feedback/create', [AdminFeedbackController::class, 'create'])->name('feedback.create');
        Route::post('complaints/{complaint}/feedback', [AdminFeedbackController::class, 'store'])->name('feedback.store');
        // Edit/Update/Delete routes - must come after list route
        Route::get('feedbacks/{feedback}/edit', [AdminFeedbackController::class, 'edit'])->name('feedback.edit');
        Route::put('feedbacks/{feedback}', [AdminFeedbackController::class, 'update'])->name('feedback.update');
        Route::delete('feedbacks/{feedback}', [AdminFeedbackController::class, 'destroy'])->name('feedback.destroy');
    });

    // ===============================
    // 📂 Complaint Categories
    // ===============================
    Route::resource('category', AdminCategoryController::class)
        ->only(['index','store','update','destroy'])
        ->middleware(['permission:complaints.view']);

    // ===============================
    // 📝 Complaint Titles
    // ===============================
    Route::middleware(['permission:complaints.view'])->group(function () {
        Route::resource('complaint-titles', AdminComplaintTitleController::class);
        Route::get('complaint-titles-by-category', [AdminComplaintTitleController::class, 'getTitlesByCategory'])->name('complaint-titles.by-category');
    });

    // ===============================
    // 🏢 Sectors
    // ===============================
    Route::resource('sector', AdminSectorController::class)
        ->only(['index','store','update','destroy'])
        ->middleware(['permission:sector.view']);
    Route::get('sectors-by-city', [AdminSectorController::class, 'getSectorsByCity'])->name('sectors.by-city');

    // ===============================
    // 🏢 Cities
    // ===============================
    Route::resource('city', AdminCityController::class)
        ->only(['index','store','update','destroy'])
        ->middleware(['permission:city.view']);

    // ===============================
    // 🏢 CMES
    // ===============================
    Route::resource('cmes', AdminCmeController::class)
        ->only(['index','store','update','destroy'])
        ->middleware(['permission:city.view']);


    // ===============================
    // 🏢 Designations
    // ===============================
    Route::resource('designation', AdminDesignationController::class)
        ->only(['index','store','update','destroy'])
        ->middleware(['permission:designation.view']);

    // ===============================
    // ⚙️ Spares, Approvals, SLA, Reports, Settings, Help
    // ===============================
    // Routes without parameters must come BEFORE routes with parameters
    Route::get('spares/get-categories', [AdminSpareController::class, 'getCategories'])->middleware(['permission:spares.view'])->name('spares.get-categories');
    Route::get('spares/get-products-by-category', [AdminSpareController::class, 'getProductsByCategory'])->middleware(['permission:spares.view'])->name('spares.get-products-by-category');
    Route::get('spares/get-product-brands', [AdminSpareController::class, 'getProductBrands'])->middleware(['permission:spares.view'])->name('spares.get-product-brands');
    Route::get('spares/old-brand-history/{itemName}/{brandName}', [AdminSpareController::class, 'showOldBrandHistory'])->middleware(['permission:spares.view'])->name('spares.old-brand-history');
    
    // Resource routes and routes with parameters
    Route::resource('spares', AdminSpareController::class)->middleware(['permission:spares.view']);
    Route::get('spares/{spare}/edit-data', [AdminSpareController::class, 'editData'])->name('spares.edit-data');
    Route::get('spares/{spare}/print-slip', [AdminSpareController::class, 'printSlip'])->middleware(['permission:spares.view'])->name('spares.print-slip');
    Route::get('spares/{spare}/history', [AdminSpareController::class, 'getProductHistory'])->middleware(['permission:spares.view'])->name('spares.history');
    Route::post('spares/{spare}/add-stock', [AdminSpareController::class, 'addStock'])->middleware(['permission:spares.view'])->name('spares.add-stock');
    Route::post('spares/{spare}/issue-stock', [AdminSpareController::class, 'issueStock'])->middleware(['permission:spares.view'])->name('spares.issue-stock');
    Route::resource('approvals', AdminApprovalController::class)->except(['create', 'store'])->middleware(['permission:approvals.view']);
    Route::post('approvals/{approval}/approve', [AdminApprovalController::class, 'approve'])->middleware(['permission:approvals.view'])->name('approvals.approve');
    Route::post('approvals/{approval}/reject', [AdminApprovalController::class, 'reject'])->middleware(['permission:approvals.view'])->name('approvals.reject');
    Route::post('approvals/{approval}/update-reason', [AdminApprovalController::class, 'updateReason'])->middleware(['permission:approvals.view'])->name('approvals.update-reason');
    Route::post('approvals/{approval}/save-performa', [AdminApprovalController::class, 'saveWithPerforma'])->middleware(['permission:approvals.view'])->name('approvals.save-performa');
    Route::post('approvals/{approval}/update-performa-type', [AdminApprovalController::class, 'updatePerformaType'])->middleware(['permission:approvals.view'])->name('approvals.update-performa-type');
    Route::post('approvals/bulk-action', [AdminApprovalController::class, 'bulkAction'])->middleware(['permission:approvals.view'])->name('approvals.bulk-action');
    Route::post('approvals/complaints/{complaintId}/update-status', [AdminApprovalController::class, 'updateComplaintStatus'])->middleware(['permission:approvals.view'])->name('approvals.complaints.update-status');
    Route::resource('sla', AdminSlaController::class)->middleware(['permission:sla.view']);
    Route::post('sla/{sla}/toggle-status', [AdminSlaController::class, 'toggleStatus'])->name('sla.toggle-status');
    Route::get('reports', [AdminReportController::class, 'index'])->middleware(['permission:reports.view'])->name('reports.index');
    Route::get('reports/complaints', [AdminReportController::class, 'complaints'])->middleware(['permission:reports.view'])->name('reports.complaints');
    // Printable report routes
    Route::get('reports/complaints/print', [AdminReportController::class, 'printComplaints'])->middleware(['permission:reports.view'])->name('reports.complaints.print');
    Route::get('reports/employees/print', [AdminReportController::class, 'printEmployees'])->middleware(['permission:reports.view'])->name('reports.employees.print');
    Route::get('reports/spares/print', [AdminReportController::class, 'printSpares'])->middleware(['permission:reports.view'])->name('reports.spares.print');
    Route::get('reports/spares', [AdminReportController::class, 'spares'])->middleware(['permission:reports.view'])->name('reports.spares');
    Route::get('reports/employees', [AdminReportController::class, 'employees'])->middleware(['permission:reports.view'])->name('reports.employees');
    Route::get('reports/financial', [AdminReportController::class, 'financial'])->middleware(['permission:reports.view'])->name('reports.financial');
    Route::get('reports/sla', [AdminReportController::class, 'sla'])->middleware(['permission:reports.view'])->name('reports.sla');
    Route::get('reports/download/{type}/{format}', [AdminReportController::class, 'download'])->middleware(['permission:reports.view'])->name('reports.download');
    
    // Debug route for testing reports
    Route::get('reports/test', function() {
        return response()->json([
            'message' => 'Reports routes are working!',
            'timestamp' => now(),
            'routes' => [
                'complaints' => route('admin.reports.complaints'),
                'employees' => route('admin.reports.employees'),
                'spares' => route('admin.reports.spares'),
                'financial' => route('admin.reports.financial'),
            ]
        ]);
    })->name('reports.test');

    // ===============================
    // 🔍 Search
    // ===============================
    Route::get('search', [SearchController::class, 'index'])->name('search.index');
    Route::get('search/api', [SearchController::class, 'api'])->name('search.api');
    

    // Settings & Help
    Route::get('settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings/general', [App\Http\Controllers\Admin\SettingsController::class, 'updateGeneral'])->name('settings.general');
    Route::post('settings/notifications', [App\Http\Controllers\Admin\SettingsController::class, 'updateNotifications'])->name('settings.notifications');
    Route::post('settings/security', [App\Http\Controllers\Admin\SettingsController::class, 'updateSecurity'])->name('settings.security');
    Route::get('help', [App\Http\Controllers\Admin\HelpController::class, 'index'])->name('help.index');
    Route::get('help/documentation', [App\Http\Controllers\Admin\HelpController::class, 'documentation'])->name('help.documentation');
    Route::get('help/faq', [App\Http\Controllers\Admin\HelpController::class, 'faq'])->name('help.faq');
    Route::get('help/contact', [App\Http\Controllers\Admin\HelpController::class, 'contact'])->name('help.contact');
    Route::post('help/contact', [App\Http\Controllers\Admin\HelpController::class, 'submitTicket'])->name('help.submit-ticket');
});


