<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function __construct()
    {
        // Middleware is applied in routes
    }

    /**
     * Display a listing of roles
     */
    public function index(Request $request)
    {
        $query = Role::with(['rolePermissions', 'users'])->withCount(['users', 'rolePermissions']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('role_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }


        $roles = $query->orderBy('id', 'asc')->paginate(15);

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_name' => 'required|string|max:100|unique:roles',
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $role = Role::create([
            'role_name' => $request->role_name,
            'description' => $request->description,
        ]);

        // Add permissions - only add what is explicitly selected
        if ($request->has('permissions') && is_array($request->permissions)) {
            foreach ($request->permissions as $module) {
                $role->rolePermissions()->create([
                    'module_name' => $module,
                ]);
            }
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        if (request()->get('format') === 'html') {
            $role->load(['rolePermissions', 'users']);
            return view('admin.roles.show', compact('role'));
        }

        $role->load(['rolePermissions', 'users']);
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the role
     */
    public function edit(Role $role)
    {
        $role->load('rolePermissions');

        return view('admin.roles.edit', compact('role'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        $validator = Validator::make($request->all(), [
            'role_name' => 'required|string|max:100|unique:roles,role_name,' . $role->id,
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Update role basic information
            $role->update([
                'role_name' => $request->role_name,
                'description' => $request->description,
            ]);

            // Update permissions - only add what is explicitly selected
            $role->rolePermissions()->delete();
            
            if ($request->has('permissions') && is_array($request->permissions)) {
                foreach ($request->permissions as $module) {
                    $role->rolePermissions()->create([
                        'module_name' => $module,
                    ]);
                }
            }

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating role: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        try {
            // Soft delete - no need to check for related records as soft delete preserves them
            $role->delete(); // This will now soft delete due to SoftDeletes trait

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role deleted successfully.'
                ]);
            }

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role deleted successfully.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting role: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error deleting role: ' . $e->getMessage());
        }
    }

    /**
     * Toggle role status
     */


    /**
     * Get role statistics
     */
    public function getStatistics()
    {
        $stats = [
            'total' => Role::count(),
            'with_users' => Role::whereHas('users')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get role usage statistics
     */
    public function getUsageStatistics()
    {
        $usage = Role::withCount('users')
            ->orderBy('users_count', 'desc')
            ->get();

        return response()->json($usage);
    }

    /**
     * Bulk actions on roles
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:delete',
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $roleIds = $request->role_ids;
        $action = $request->action;

        switch ($action) {
            case 'delete':
                // Check for users
                $rolesWithUsers = Role::whereIn('id', $roleIds)
                    ->whereHas('users')
                    ->count();

                if ($rolesWithUsers > 0) {
                    return redirect()->back()
                        ->with('error', 'Some roles cannot be deleted due to assigned users.');
                }

                Role::whereIn('id', $roleIds)->delete();
                $message = 'Selected roles deleted successfully.';
                break;
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Export roles data
     */
    public function export(Request $request)
    {
        $query = Role::with('rolePermissions');

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('role_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }


        $roles = $query->get();

        // Implementation for export
        return response()->json(['message' => 'Export functionality not implemented yet']);
    }
}
