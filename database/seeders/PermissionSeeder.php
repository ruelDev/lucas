<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionGroup = [
            'auditLogs' => [
                "audit_logs.view",
                "audit_logs.adminview",
                "audit_logs.export",
            ],
            'userManagementPermissions' => [
                "user_management.view",
                "user_management.edit",
                "user_management.create",
                "user_management.delete",
                "user_management.export",
                "user_management.reset",
            ],
            'roleManagementPermissions' => [
                "role_management.view",
                "role_management.edit",
                "role_management.create",
                "role_management.delete",
            ],
            'branchManagementPermissions' => [
                "branch_management.view",
                "branch_management.edit",
                "branch_management.create",
                "branch_management.delete",
            ],
            'dealerBranchManagementPermissions' => [
                "dealer_management.view",
                "dealer_management.edit",
                "dealer_management.create",
                "dealer_management.delete",
            ],
            'groupManagementPermissions' => [
                "group_management.view",
                "group_management.edit",
                "group_management.create",
                "group_management.delete",
            ],
            'divisionManagementPermissions' => [
                "division_management.view",
                "division_management.edit",
                "division_management.create",
                "division_management.delete",
            ],
            'departmentManagementPermissions' => [
                "department_management.view",
                "department_management.edit",
                "department_management.create",
                "department_management.delete",
            ],
            'sectionManagementPermissions' => [
                "section_management.view",
                "section_management.edit",
                "section_management.create",
                "section_management.delete",
            ],
        ];

        $allPermissions = collect($permissionGroup)->flatten();

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        /**
         * Creating Default Roles
         */
        $roles = [
            'Admin' => [
                'groups' => [
                    'auditLogs',
                    'userManagementPermissions',
                    'roleManagementPermissions',
                    'branchManagementPermissions',
                    'dealerBranchManagementPermissions',
                    'groupManagementPermissions',
                    'divisionManagementPermissions',
                    'departmentManagementPermissions',
                    'sectionManagementPermissions'
                ],
                'description' => 'Admin with the Highest Authority on the System.'
            ],
            'SAPS-Admin' => [
                'groups' => [
                    'auditLogs',
                    'userManagementPermissions',
                    'roleManagementPermissions',
                    'branchManagementPermissions',
                    'dealerBranchManagementPermissions',
                    'groupManagementPermissions',
                    'divisionManagementPermissions',
                    'departmentManagementPermissions',
                    'sectionManagementPermissions'
                ],
                'description' => 'SAP Admin with full access to User Management and Access.'
            ],
            'SAPS-UserManagement' => [
                'groups' => [
                    'auditLogs',
                    'userManagementPermissions',
                    'roleManagementPermissions'
                ],
                'description' => 'SAPS User focused on User Management and Role Management.'
            ],
            'SAPS-MasterSetup' => [
                'groups' => [
                    'auditLogs',
                    'branchManagementPermissions',
                    'dealerBranchManagementPermissions',
                    'groupManagementPermissions',
                    'divisionManagementPermissions',
                    'departmentManagementPermissions',
                    'sectionManagementPermissions',
                ],
                'description' => 'SAPS User focused on Master Setup on Branch, Dealer, and Head Office Organizations.'
            ],
        ];

        foreach ($roles as $roleName => $roleData) {
            $permissions = collect($roleData['groups'])
                ->flatMap(fn ($group) => $permissionGroup[$group])
                ->unique()
                ->values()
                ->toArray();

            $role = Role::firstOrCreate([
                'name' => $roleName,
                'description' => $roleData['description'],
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($permissions);
        }
    }
}
