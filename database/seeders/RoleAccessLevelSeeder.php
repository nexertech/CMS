<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RoleAccessLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define all roles - basic and access level roles
        $roles = [
            [
                'role_name' => 'admin',
                'description' => 'System Administrator - Full access to all modules',
            ],
            [
                'role_name' => 'manager',
                'description' => 'Manager - Management level access',
            ],
            [
                'role_name' => 'employee',
                'description' => 'Employee - Standard employee access',
            ],
            [
                'role_name' => 'client',
                'description' => 'Client - Client access',
            ],
            [
                'role_name' => 'director',
                'description' => 'Director - Head Office (Islamabad) - Can view all GEs and their complaints',
            ],
            [
                'role_name' => 'garrison_engineer',
                'description' => 'Garrison Engineer (GE) - per city - Can view/manage complaint centers under his city',
            ],
            [
                'role_name' => 'complaint_center',
                'description' => 'Complaint Center (Helpdesk staff) - Can register and track complaints for their area only',
            ],
            [
                'role_name' => 'department_staff',
                'description' => 'Trade/Department Staff - Receive and register complaint, assign to concerned department',
            ],
        ];

        // All modules available
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

        foreach ($roles as $roleData) {
            // Check if role already exists
            $role = Role::where('role_name', $roleData['role_name'])->first();
            
            if (!$role) {
                $role = Role::create($roleData);
            }

            // Assign permissions based on role
            $permissions = [];
            
            switch ($roleData['role_name']) {
                case 'admin':
                    // Admin gets all permissions
                    $permissions = $modules;
                    break;
                    
                case 'manager':
                    // Manager gets most permissions except some admin-only modules
                    $permissions = [
                        'dashboard',
                        'users',
                        'employees',
                        'clients',
                        'complaints',
                        'spares',
                        'approvals',
                        'reports',
                    ];
                    break;
                    
                case 'employee':
                    // Employee gets limited permissions
                    $permissions = [
                        'dashboard',
                        'complaints',
                        'clients',
                        'spares',
                    ];
                    break;
                    
                case 'client':
                    // Client gets minimal permissions
                    $permissions = [
                        'dashboard',
                        'complaints',
                    ];
                    break;
                    
                case 'director':
                    // Director gets all permissions
                    $permissions = $modules;
                    break;
                    
                case 'garrison_engineer':
                    // GE gets limited permissions - can view/manage complaints and reports for their city
                    $permissions = [
                        'dashboard',
                        'complaints',
                        'reports',
                        'clients',
                        'approvals',
                    ];
                    break;
                    
                case 'complaint_center':
                    // Complaint Center can only register and track complaints
                    $permissions = [
                        'dashboard',
                        'complaints',
                        'clients',
                    ];
                    break;
                    
                case 'department_staff':
                    // Department Staff can receive, register, assign, and update complaints
                    $permissions = [
                        'dashboard',
                        'complaints',
                        'employees',
                        'clients',
                    ];
                    break;
            }

            // Assign permissions to role
            foreach ($permissions as $module) {
                RolePermission::updateOrCreate(
                    [
                        'role_id' => $role->id,
                        'module_name' => $module,
                    ],
                    [
                        'role_id' => $role->id,
                        'module_name' => $module,
                    ]
                );
            }
        }
    }
}
