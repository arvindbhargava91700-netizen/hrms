<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoHrmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $partner = \App\Models\User::where('email', 'demo@gmail.com')->first();
        if (!$partner) {
            $this->command->error("Partner demo@gmail.com not found!");
            return;
        }

        $partnerId = $partner->id;

        // Create Branches
        $branch1 = \App\Models\HrmsBranch::updateOrCreate(
            ['partner_id' => $partnerId, 'name' => 'Main Branch - New York'],
            [
                'address' => '123 Main St, New York, NY',
                'lat' => 40.712776,
                'lng' => -74.005974,
                'radius' => 500,
                'fixed_check_in_time' => '09:00:00',
                'fixed_check_out_time' => '17:00:00',
                'late_grace_period' => 15,
                'min_present_mins' => 450,
                'min_half_day_mins' => 240,
                'auto_absent_mark_mins' => 60,
                'week_off_days' => json_encode(['Saturday', 'Sunday']),
                'status' => 'active'
            ]
        );

        $branch2 = \App\Models\HrmsBranch::updateOrCreate(
            ['partner_id' => $partnerId, 'name' => 'West Coast Branch - LA'],
            [
                'address' => '456 West Blvd, Los Angeles, CA',
                'lat' => 34.052235,
                'lng' => -118.243683,
                'radius' => 300,
                'fixed_check_in_time' => '10:00:00',
                'fixed_check_out_time' => '18:00:00',
                'late_grace_period' => 20,
                'min_present_mins' => 420,
                'min_half_day_mins' => 210,
                'auto_absent_mark_mins' => 90,
                'week_off_days' => json_encode(['Sunday']),
                'status' => 'active'
            ]
        );

        // Create Shifts
        $shift1 = \App\Models\WorkShift::updateOrCreate(
            ['partner_id' => $partnerId, 'name' => 'Morning Shift'],
            [
                'branch_id' => $branch1->id,
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'late_tolerance_minutes' => 10,
                'auto_mark_attendance' => true,
                'auto_mark_status' => 'absent'
            ]
        );

        $shift2 = \App\Models\WorkShift::updateOrCreate(
            ['partner_id' => $partnerId, 'name' => 'Night Shift'],
            [
                'branch_id' => $branch2->id,
                'start_time' => '20:00:00',
                'end_time' => '04:00:00',
                'late_tolerance_minutes' => 15,
                'auto_mark_attendance' => true,
                'auto_mark_status' => 'absent'
            ]
        );

        // Create Departments
        $dept1 = \App\Models\Department::updateOrCreate(['partner_id' => $partnerId, 'name' => 'Engineering']);
        $dept2 = \App\Models\Department::updateOrCreate(['partner_id' => $partnerId, 'name' => 'Sales']);

        // Create Designations
        $desig1 = \App\Models\Designation::updateOrCreate(['partner_id' => $partnerId, 'name' => 'Software Engineer']);
        $desig2 = \App\Models\Designation::updateOrCreate(['partner_id' => $partnerId, 'name' => 'Sales Executive']);

        // Create Employees
        $employee1 = \App\Models\User::updateOrCreate(
            ['email' => 'emp1@demo.com'],
            [
                'name' => 'John Doe (NY)',
                'password' => bcrypt('password'),
                'role' => 'employee',
                'parent_id' => $partnerId,
                'branch_id' => $branch1->id,
                'shift_id' => $shift1->id,
                'department_id' => $dept1->id,
                'designation_id' => $desig1->id,
                'employment_status' => 'active',
                'basic_salary' => 5000,
            ]
        );

        $employee2 = \App\Models\User::updateOrCreate(
            ['email' => 'emp2@demo.com'],
            [
                'name' => 'Jane Smith (LA)',
                'password' => bcrypt('password'),
                'role' => 'employee',
                'parent_id' => $partnerId,
                'branch_id' => $branch2->id,
                'shift_id' => $shift2->id,
                'department_id' => $dept2->id,
                'designation_id' => $desig2->id,
                'employment_status' => 'active',
                'basic_salary' => 4500,
            ]
        );
        
        $employee3 = \App\Models\User::updateOrCreate(
            ['email' => 'emp3@demo.com'],
            [
                'name' => 'Alice Johnson (NY - No Shift)',
                'password' => bcrypt('password'),
                'role' => 'employee',
                'parent_id' => $partnerId,
                'branch_id' => $branch1->id,
                'department_id' => $dept1->id,
                'designation_id' => $desig1->id,
                'employment_status' => 'active',
                'basic_salary' => 6000,
            ]
        );

        $this->command->info('Demo HRMS data seeded successfully for demo@gmail.com!');
    }
}
