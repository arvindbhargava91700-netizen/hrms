<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PartnerPipelineSeeder extends Seeder
{
    public function run(): void
    {
        $partner = User::where('email', 'demo@gmail.com')->first();
        if (!$partner) {
            $this->command->error("Partner demo@gmail.com not found.");
            return;
        }

        $partnerId = $partner->id;
        $this->command->info("Setting up pipeline for partner: {$partner->email} ({$partnerId})");

        // 1. Delete old departments for this partner
        Department::where('partner_id', $partnerId)->delete();
        $this->command->info("Old departments deleted.");

        // 2. Delete old roles for this partner
        Role::where('name', 'like', $partnerId . '_%')->delete();
        $this->command->info("Old roles deleted.");

        // 3. Create Departments based on pipeline
        $deptNames = ['Manager', 'Sales', 'Finance', 'Documentation', 'Accounts', 'Operations', 'Recovery'];
        foreach ($deptNames as $name) {
            Department::create([
                'partner_id' => $partnerId,
                'name' => $name,
                'description' => "{$name} Department",
            ]);
        }
        $this->command->info("New departments created.");

        // 4. Create Roles based on pipeline
        $rolesData = [
            'Manager' => [
                'lead_viewteam', 'lead_create', 'lead_update', 'lead_delete', 'lead_convert',
                'leadorder_viewteam', 'leadorder_create', 'leadorder_update', 'leadorder_delete', 'leadorder_approve', 'leadorder_reject',
                'target_viewteam', 'target_create', 'target_update', 'target_delete',
                'customervisit_viewteam', 'customervisit_create', 'customervisit_update', 'customervisit_delete', 'customervisit_status_update'
            ],
            'Sales' => [
                'lead_viewown', 'lead_create', 'lead_update',
                'leadorder_viewown', 'leadorder_create', 'leadorder_update',
                'target_viewown',
                'customervisit_viewown', 'customervisit_create', 'customervisit_update'
            ],
            'Finance' => ['leadorder_viewteam', 'leadorder_approve', 'leadorder_reject'],
            'Documentation' => ['leadorder_viewteam', 'leadorder_approve', 'leadorder_reject'],
            'Accounts' => ['leadorder_viewteam', 'leadorder_approve', 'leadorder_reject'],
            'Operations' => ['leadorder_viewteam', 'leadorder_approve', 'leadorder_reject'],
            'Recovery' => ['leadorder_viewteam', 'leadorder_approve', 'leadorder_reject'],
        ];

        foreach ($rolesData as $roleName => $perms) {
            $fullRoleName = $partnerId . '_' . $roleName;
            $role = Role::firstOrCreate(['name' => $fullRoleName, 'guard_name' => 'web']);
            
            // Sync permissions (only existing permissions)
            $validPerms = [];
            foreach ($perms as $permName) {
                if (Permission::where('name', $permName)->exists()) {
                    $validPerms[] = $permName;
                }
            }
            
            $role->syncPermissions($validPerms);
            $this->command->info("Created role {$roleName} with " . count($validPerms) . " permissions.");
        }
        // 5. Create Dummy Employees for each pipeline stage
        $employeeData = [
            ['name' => 'Mike Manager', 'email' => 'manager@demo.com', 'role' => 'Manager', 'dept' => 'Manager'],
            ['name' => 'John Sales', 'email' => 'sales@demo.com', 'role' => 'Sales', 'dept' => 'Sales'],
            ['name' => 'Sarah Finance', 'email' => 'finance@demo.com', 'role' => 'Finance', 'dept' => 'Finance'],
            ['name' => 'Dave Docs', 'email' => 'docs@demo.com', 'role' => 'Documentation', 'dept' => 'Documentation'],
            ['name' => 'Alice Accounts', 'email' => 'accounts@demo.com', 'role' => 'Accounts', 'dept' => 'Accounts'],
            ['name' => 'Oliver Operations', 'email' => 'ops@demo.com', 'role' => 'Operations', 'dept' => 'Operations'],
            ['name' => 'Rachel Recovery', 'email' => 'recovery@demo.com', 'role' => 'Recovery', 'dept' => 'Recovery'],
        ];

        foreach ($employeeData as $data) {
            $dept = Department::where('partner_id', $partnerId)->where('name', $data['dept'])->first();
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => bcrypt('password'),
                    'role' => 'employee',
                    'parent_id' => $partnerId,
                    'department_id' => $dept ? $dept->id : null,
                ]
            );
            
            // Set hierarchy (Sales reports to Manager)
            if ($data['role'] === 'Sales') {
                $manager = User::where('email', 'manager@demo.com')->first();
                if ($manager) {
                    $user->reporting_to = $manager->id;
                    $user->save();
                }
            }
            
            // Re-assign role
            $fullRoleName = $partnerId . '_' . $data['role'];
            $user->syncRoles([$fullRoleName]);
            $this->command->info("Created employee {$data['name']} for department {$data['dept']}");
        }

        $this->command->info("Setup complete.");
    }
}
