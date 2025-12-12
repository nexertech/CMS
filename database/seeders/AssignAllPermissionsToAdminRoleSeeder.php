<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Support\Facades\DB;

class AssignAllPermissionsToAdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all available modules
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

        // Find role with ID 1
        $adminRole = Role::find(1);

        if (!$adminRole) {
            $this->command->warn('Role with ID 1 not found. Creating admin role...');
            $adminRole = Role::create([
                'role_name' => 'admin',
                'description' => 'Administrator role with full access',
            ]);
        }

        $this->command->info("Assigning all permissions to role: {$adminRole->role_name} (ID: {$adminRole->id})");

        // Delete existing permissions for role_id 1 to avoid duplicates
        RolePermission::where('role_id', 1)->delete();

        // Assign all permissions
        foreach ($modules as $module) {
            RolePermission::updateOrCreate(
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

        $this->command->info('Successfully assigned all ' . count($modules) . ' permissions to role ID 1');
    }
}

