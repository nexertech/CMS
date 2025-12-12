<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'role_name',
        'description',
    ];

    /**
     * Boot method to automatically assign all permissions to role_id = 1
     */
    protected static function boot()
    {
        parent::boot();

        // After role is created or updated, if role_id is 1, assign all permissions
        static::saved(function ($role) {
            if ($role->id === 1) {
                $modules = [
                    'dashboard',
                    'users',
                    'frontend-users',
                    'roles',
                    'employees',
                    'clients',
                    'complaints',
                    'spares',
                    'approvals',
                    'reports',
                    'sla',
                ];

                // Get existing permissions
                $existingModules = $role->rolePermissions()->pluck('module_name')->toArray();

                // Add missing permissions
                foreach ($modules as $module) {
                    if (!in_array($module, $existingModules)) {
                        $role->rolePermissions()->updateOrCreate(
                            [
                                'role_id' => 1,
                                'module_name' => $module,
                            ],
                            [
                                'role_id' => 1,
                                'module_name' => $module,
                            ]
                        );
                    }
                }
            }
        });
    }

    /**
     * Get the users for the role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }

    /**
     * Get the role permissions for the role.
     */
    public function rolePermissions(): HasMany
    {
        return $this->hasMany(RolePermission::class, 'role_id', 'id');
    }

    /**
     * Check if role has permission for a module
     */
    public function hasPermission(string $permission): bool
    {
        // Admin role (role_id 1 or role_name 'admin') has access to everything
        if ($this->id === 1 || $this->role_name === 'admin') {
            return true;
        }

        // Extract module name from "module.action" format (e.g., "users.view" -> "users")
        $module = explode('.', $permission)[0];

        // Get all permissions for this role
        $permissions = $this->rolePermissions()->pluck('module_name')->toArray();

        // Map sublinks to parent modules
        $sublinkToParent = [
            'category' => 'complaints',
            'complaint-titles' => 'complaints',
            'complaints' => 'complaints',
            'approvals' => 'complaints',
            'designation' => 'employees',
            'city' => 'employees',
            'sector' => 'employees',
        ];

        // Check if exact module exists in permissions (exact match - highest priority)
        if (in_array($module, $permissions)) {
            return true;
        }

        // If checking for a sublink, check if parent module is granted
        // BUT only if parent module exists AND no individual sublinks are explicitly granted
        // This means: if parent is selected, all sublinks get access
        // But if only individual sublinks are selected, only those get access
        if (isset($sublinkToParent[$module])) {
            $parentModule = $sublinkToParent[$module];
            
            // Check if parent module is explicitly granted
            if (in_array($parentModule, $permissions)) {
                // Check if any individual sublink is also granted
                // If individual sublinks exist, parent doesn't auto-grant all sublinks
                $hasIndividualSublinks = false;
                foreach ($sublinkToParent as $sublink => $parent) {
                    if ($parent === $parentModule && $sublink !== $parentModule && in_array($sublink, $permissions)) {
                        $hasIndividualSublinks = true;
                        break;
                    }
                }
                
                // If individual sublinks exist, only grant if this specific sublink is granted
                // If no individual sublinks exist, parent grants all sublinks
                if (!$hasIndividualSublinks) {
                    return true; // Parent grants all sublinks
                }
                // If individual sublinks exist, we already checked exact match above, so return false
            }
        }

        // If checking for parent module, check if parent is explicitly selected
        // BUT only grant parent access if NO individual sublinks exist (meaning parent was explicitly selected)
        // If individual sublinks exist, don't grant parent access (parent was not explicitly selected)
        foreach ($sublinkToParent as $sublink => $parent) {
            if ($module === $parent) {
                // Check if any individual sublink exists (excluding the parent module itself)
                $hasIndividualSublinks = false;
                foreach ($sublinkToParent as $sublinkCheck => $parentCheck) {
                    if ($parentCheck === $parent && $sublinkCheck !== $parent && in_array($sublinkCheck, $permissions)) {
                        $hasIndividualSublinks = true;
                        break;
                    }
                }
                
                // If individual sublinks exist, parent was not explicitly selected, don't grant access
                if ($hasIndividualSublinks) {
                    return false;
                }
                
                // If no individual sublinks exist and parent is in permissions, grant access
                if (in_array($module, $permissions)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get all permissions for this role
     */
    public function getPermissions(): array
    {
        return $this->rolePermissions()
            ->get()
            ->pluck('module_name')
            ->toArray();
    }
}
