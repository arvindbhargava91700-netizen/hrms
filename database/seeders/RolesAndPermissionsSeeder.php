<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define modular permission groups
        $permissionGroups = [
            'Listing' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            'Customer' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            'Visit' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            'Booking' => ['viewAny', 'viewOwn', 'create', 'update', 'delete', 'status_update'],
            'Subscription' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            'Invoice' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            'Wallet' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            'Payment' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            'Package' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            'Review' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            'PlatformPlan' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            
            // HRMS Modules
            'Leave' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete', 'status_update'],
            'Attendance' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete', 'manage'],
            'Payroll' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'Department' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'Branch' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete', 'manage'],
            'Shift' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete', 'manage'],
            'Staff' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'Role' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'Lead' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'LeadOrder' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete', 'approve', 'reject'],
            'Target' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'Task' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete', 'status_update'],
            'Expense' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete', 'status_update'],
            'Notice' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'CustomerVisit' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete', 'status_update'],
            
            // New HRMS Modules
            'Attrition' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam'], // read-only analytics
            'ExitReason' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'EmployeeCost' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'Training' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'Asset' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'Pip' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'Recruitment' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'Probation' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'ResignationExit' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'Document' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'Grievance' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'Commission' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'ProductCategory' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'Product' => ['viewAny', 'viewOwn', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],

            'HrmsSetting' => ['manage'],
        ];

        $allPermissions = [];
        foreach ($permissionGroups as $module => $actions) {
            foreach ($actions as $action) {
                $permName = strtolower($module) . '_' . strtolower($action);
                $allPermissions[] = $permName;
                Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            }
        }

        // Additional legacy/specific permissions
        $additionalPermissions = [
            'verify-kyc', 'manage-categories', 'manage-custom-fields', 
            'manage-commissions', 'manage-reports', 'manage-settings', 'manage-support', 'manage-users',
            'view-own-reports', 'create-support-ticket', 'manage-profile', 'create-subscription', 'view-invoices', 'view-payments', 'browse-listings'
        ];

        foreach ($additionalPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $partner    = Role::firstOrCreate(['name' => 'partner',     'guard_name' => 'web']);
        $customer   = Role::firstOrCreate(['name' => 'customer',    'guard_name' => 'web']);
        
        // Employee role (global default base)
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        
        // Admin role (sub-admins)
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Create standard admin permissions
        $adminPermissions = [
            'admin_dashboard',
            'admin_system_modules',
            'admin_system_settings',
            'admin_user_management',
            'admin_operations_kyc',
            'admin_platform_content',
            'admin_finance_earnings',
            'admin_reports',
            'admin_hrms_reports',
            'admin_staff_manage', // Manage sub-admins
            'admin_view_contact_info', // View unmasked email and mobile
        ];

        foreach ($adminPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        
        // Note: adminRole gets permissions assigned via the UI when created, so we don't sync all of them here.
        
        $superAdmin->syncPermissions(Permission::all());
        
        // Give partner all the modular permissions + partner specific
        $partner->syncPermissions(array_merge($allPermissions, [
            'view-own-reports','create-support-ticket',
        ]));

        $customer->syncPermissions([
            'browse-listings','create-subscription','view-invoices',
            'view-payments','manage-profile','create-support-ticket',
        ]);

        $user = \App\Models\User::where('email', 'admin@feetrak.com')->first();
        if ($user) {
            $user->assignRole('super_admin');
        }   
    }
}
