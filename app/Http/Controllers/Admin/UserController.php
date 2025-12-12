<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\City;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        // Middleware is applied in routes
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with(['role', 'city', 'sector']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role_id') && $request->role_id) {
            $query->where('role_id', $request->role_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('id', 'asc')->paginate(15);
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();
        $cities = City::where('status', 'active')->orderBy('id', 'asc')->get();
        $sectors = collect(); // Will be populated dynamically based on selected city
        return view('admin.users.create', compact('roles', 'cities', 'sectors'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:100|unique:users',
            'name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150|unique:users,email',
            'phone' => 'nullable|string|min:11|max:20',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'city_id' => 'nullable|exists:cities,id',
            'sector_id' => 'nullable|exists:sectors,id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get role to check if city/sector is required
        $role = Role::find($request->role_id);
        $roleName = strtolower($role->role_name ?? '');

        // Validate city/sector based on role
        if ($roleName === 'garrison_engineer' && !$request->city_id) {
            return redirect()->back()
                ->withErrors(['city_id' => 'City is required for Garrison Engineer role'])
                ->withInput();
        }

        if (in_array($roleName, ['complaint_center', 'department_staff']) && (!$request->city_id || !$request->sector_id)) {
            return redirect()->back()
                ->withErrors(['city_id' => 'City and Sector are required for this role', 'sector_id' => 'City and Sector are required for this role'])
                ->withInput();
        }

        $user = User::create([
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'city_id' => $request->city_id,
            'sector_id' => $request->sector_id,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        if (request()->get('format') === 'html') {
            $user->load(['role.rolePermissions', 'city', 'sector', 'slaRules']);
            return view('admin.users.show', compact('user'));
        }

        $user->load(['role.rolePermissions', 'city', 'sector', 'slaRules']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the user
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $cities = City::where('status', 'active')->orderBy('id', 'asc')->get();
        $sectors = $user->city_id 
            ? Sector::where('city_id', $user->city_id)->where('status', 'active')->orderBy('id', 'asc')->get()
            : collect();
        return view('admin.users.edit', compact('user', 'roles', 'cities', 'sectors'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        // Convert empty strings to null for city_id and sector_id before validation
        $request->merge([
            'city_id' => $request->city_id ?: null,
            'sector_id' => $request->sector_id ?: null,
        ]);

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:100|unique:users,username,' . $user->id,
            'name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|min:11|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'city_id' => 'nullable|exists:cities,id',
            'sector_id' => 'nullable|exists:sectors,id',
            'status' => 'required|in:active,inactive',
            'theme' => 'nullable|in:auto,light,dark',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get role to check if city/sector is required
        $role = Role::find($request->role_id);
        if (!$role) {
            return redirect()->back()
                ->withErrors(['role_id' => 'Selected role does not exist'])
                ->withInput();
        }
        
        $roleName = strtolower($role->role_name ?? '');

        // Validate city/sector based on role
        if ($roleName === 'garrison_engineer' && !$request->city_id) {
            return redirect()->back()
                ->withErrors(['city_id' => 'City is required for Garrison Engineer role'])
                ->withInput();
        }

        if (in_array($roleName, ['complaint_center', 'department_staff']) && (!$request->city_id || !$request->sector_id)) {
            return redirect()->back()
                ->withErrors(['city_id' => 'City and Sector are required for this role', 'sector_id' => 'City and Sector are required for this role'])
                ->withInput();
        }

        try {
            $updateData = [
                'username' => $request->username,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role_id' => $request->role_id,
                'status' => $request->status,
            ];

            // If role doesn't require location, clear city_id and sector_id
            if ($roleName === 'director' || $roleName === 'admin') {
                $updateData['city_id'] = null;
                $updateData['sector_id'] = null;
            } else {
                // Convert empty strings to null for city_id and sector_id
                $updateData['city_id'] = $request->city_id ?: null;
                $updateData['sector_id'] = $request->sector_id ?: null;
            }

            if ($request->filled('theme')) {
                $updateData['theme'] = $request->theme;
            }

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return redirect()->route('admin.users.index')
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Soft delete - no need to check for related records as soft delete preserves them
        $user->delete(); // This will now soft delete due to SoftDeletes trait

        // Debug: Log request details
        \Log::info('User delete request details:', [
            'expectsJson' => request()->expectsJson(),
            'ajax' => request()->ajax(),
            'accept_header' => request()->header('Accept'),
            'content_type' => request()->header('Content-Type'),
            'method' => request()->method(),
        ]);

        // Check if request expects JSON response
        if (request()->expectsJson() || request()->ajax() || request()->header('Accept') === 'application/json') {
            \Log::info('Returning JSON response for user delete');
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.'
            ]);
        }

        \Log::info('Returning redirect response for user delete');
        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(User $user)
    {
        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active'
        ]);

        $status = $user->status === 'active' ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "User {$status} successfully.");
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->back()
            ->with('success', 'Password reset successfully.');
    }

    /**
     * Get user permissions
     */
    public function getPermissions(User $user)
    {
        $permissions = $user->role ? $user->role->getPermissions() : [];
        $availableModules = RolePermission::getAvailableModules();
        $availableActions = RolePermission::getAvailableActions();

        return view('admin.users.permissions', compact('user', 'permissions', 'availableModules', 'availableActions'));
    }

    /**
     * Update user permissions
     */
    public function updatePermissions(Request $request, User $user)
    {
        if (!$user->role) {
            return redirect()->back()
                ->with('error', 'User does not have a role assigned.');
        }

        // Clear existing permissions
        $user->role->rolePermissions()->delete();

        // Add new permissions
        if ($request->has('permissions')) {
            foreach ($request->permissions as $module => $actions) {
                $user->role->rolePermissions()->create([
                    'module_name' => $module,
                    'can_view' => in_array('view', $actions),
                    'can_add' => in_array('add', $actions),
                    'can_edit' => in_array('edit', $actions),
                    'can_delete' => in_array('delete', $actions),
                ]);
            }
        }

        return redirect()->back()
            ->with('success', 'Permissions updated successfully.');
    }

    /**
     * Get user activity log
     */
    public function getActivityLog(User $user)
    {
        $activities = $user->complaintLogs()
            ->with('complaint')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.activity', compact('user', 'activities'));
    }

    /**
     * Bulk actions on users
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,delete,change_role',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $userIds = $request->user_ids;
        $action = $request->action;

        switch ($action) {
            case 'activate':
                User::whereIn('id', $userIds)->update(['status' => 'active']);
                $message = 'Selected users activated successfully.';
                break;

            case 'deactivate':
                User::whereIn('id', $userIds)->update(['status' => 'inactive']);
                $message = 'Selected users deactivated successfully.';
                break;

            case 'change_role':
                $validator = Validator::make($request->all(), [
                    'role_id' => 'required|exists:roles,id',
                ]);
                
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator);
                }
                
                User::whereIn('id', $userIds)->update(['role_id' => $request->role_id]);
                $message = 'Selected users role changed successfully.';
                break;

            case 'delete':
                // Check for related records before deletion
                // Note: complaintLogs relationship is not functional since employees table doesn't have user_id
                $usersWithRecords = User::whereIn('id', $userIds)
                    ->where(function($q) {
                        $q->whereHas('slaRules');
                    })
                    ->count();

                if ($usersWithRecords > 0) {
                    return redirect()->back()
                        ->with('error', 'Some users cannot be deleted due to existing records.');
                }

                User::whereIn('id', $userIds)->delete();
                $message = 'Selected users deleted successfully.';
                break;
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Export users data
     */
    public function export(Request $request)
    {
        $query = User::with('role');

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('role_id') && $request->role_id) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $users = $query->get();

        // Implementation for export (CSV, Excel, etc.)
        return response()->json(['message' => 'Export functionality not implemented yet']);
    }
}
