<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class HrmsRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Fetch all modular permissions from database (they are seeded by RolesAndPermissionsSeeder)
        $allPermissions = Permission::where('guard_name', 'web')->pluck('name')->toArray();
        
        $hrmsPermissions = array_filter($allPermissions, function($perm) {
            return str_starts_with($perm, 'role_') || 
                   str_starts_with($perm, 'department_') || 
                   str_starts_with($perm, 'staff_') || 
                   str_starts_with($perm, 'attendance_') || 
                   str_starts_with($perm, 'leave_') || 
                   str_starts_with($perm, 'payroll_') ||
                   str_starts_with($perm, 'lead_') ||
                   str_starts_with($perm, 'target_') ||
                   str_starts_with($perm, 'task_') ||
                   str_starts_with($perm, 'expense_') ||
                   str_starts_with($perm, 'notice_') ||
                   str_starts_with($perm, 'merchantvisit_');
        });

        // Split into "Any" (manager level) and "Own" (staff level)
        $managerPermissions = array_filter($hrmsPermissions, fn($p) => !str_ends_with($p, 'viewown'));
        
        // Staff should not have access to Roles, Departments, or Staff management modules
        $staffPermissions = array_filter($hrmsPermissions, function($p) {
            if (str_starts_with($p, 'role_') || str_starts_with($p, 'department_') || str_starts_with($p, 'staff_')) {
                return false;
            }
            return str_ends_with($p, 'viewown') || str_ends_with($p, 'create') || str_ends_with($p, 'update');
        });

        // 2. Find the dummy partner
        $partner = User::where('role', 'partner')->first();
        
        if ($partner) {
            // 3. Create Custom Roles
            $managerRoleName = $partner->id . '_Department Manager';
            $managerRole = Role::firstOrCreate(['name' => $managerRoleName, 'guard_name' => 'web']);
            // Managers can view any (which we scope to their team in UI) and manage everything
            $managerRole->syncPermissions($managerPermissions);

            $teamLeaderRoleName = $partner->id . '_Team Leader';
            $teamLeaderRole = Role::firstOrCreate(['name' => $teamLeaderRoleName, 'guard_name' => 'web']);
            // Team Leaders have staff permissions + some viewAny if needed, but 'viewOwn' + UI logic handles their team
            $teamLeaderRole->syncPermissions($staffPermissions);

            $staffRoleName = $partner->id . '_Staff / Junior';
            $staffRole = Role::firstOrCreate(['name' => $staffRoleName, 'guard_name' => 'web']);
            // Staff can only view their own
            $staffRole->syncPermissions($staffPermissions);

            // 4. Assign these roles to our dummy employees from HrmsSeeder
            $itManager = User::where('email', 'it.manager@demo.com')->first();
            if ($itManager) $itManager->assignRole($managerRole);

            $devLead = User::where('email', 'dev.lead@demo.com')->first();
            if ($devLead) $devLead->assignRole($teamLeaderRole);

            $jrDev = User::where('email', 'jr.dev@demo.com')->first();
            if ($jrDev) $jrDev->assignRole($staffRole);

            $salesManager = User::where('email', 'sales.manager@demo.com')->first();
            if ($salesManager) $salesManager->assignRole($managerRole);
            
            $this->command->info('HRMS Hierarchical Roles & Permissions seeded successfully for the demo partner!');
        } else {
            $this->command->warn('No partner found. Please run HrmsDataSeeder first.');
        }
    }
}
