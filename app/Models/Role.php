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
                    'cmes',
                    'city',
                    'sector',
                    'roles',
                    'employees',
                    'designation',
                    'houses',
                    'complaints',
                    'category',
                    'sub-category',
                    'complaint-titles',
                    'approvals',
                    'spares',
                    'reports',
                    'sla',
                    'registered-devices',
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
        $permissions = $this->rolePermissions->pluck('module_name')->toArray();

        return in_array($module, $permissions, true);
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
