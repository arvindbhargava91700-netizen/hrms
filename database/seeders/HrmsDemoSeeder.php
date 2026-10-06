<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\HrmsBranch;
use App\Models\Department;
use App\Models\DepartmentBranchHead;
use App\Models\WorkShift;
use App\Models\Holiday;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeave;
use App\Models\EmployeeTask;
use App\Models\Lead;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class HrmsDemoSeeder extends Seeder
{
    public function run()
    {
        // 1. Identify Partner
        $partner = User::where('email', 'demo@gmail.com')->first();
        if (!$partner) {
            $this->command->error("Partner demo@gmail.com not found!");
            return;
        }

        $partnerId = $partner->id;
        $this->command->info("Starting data reset and seed for Partner ID: $partnerId");

        // PHASE 1: Data Cleansing
        $this->cleanExistingData($partnerId);

        // PHASE 2 & 3: Foundation Setup & Staff Generation
        $branches = $this->seedFoundationAndStaff($partnerId);

        // PHASE 4: Historical Data Generation
        $this->seedHistoricalData($partnerId, $branches);
        
        $this->command->info("Demo data seeding completed successfully.");
    }

    private function cleanExistingData($partnerId)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $teamIds = User::where('parent_id', $partnerId)->pluck('id')->toArray();
        $teamIds[] = $partnerId; // Include partner for any leads/tasks assigned to them

        EmployeeAttendance::whereIn('employee_id', $teamIds)->delete();
        EmployeeLeave::whereIn('employee_id', $teamIds)->delete();
        EmployeeTask::whereIn('employee_id', $teamIds)->delete();
        Lead::whereIn('assigned_to', $teamIds)->delete();
        
        DepartmentBranchHead::whereIn('branch_id', HrmsBranch::where('partner_id', $partnerId)->pluck('id'))->delete();
        Department::where('partner_id', $partnerId)->delete();
        HrmsBranch::where('partner_id', $partnerId)->delete();
        WorkShift::where('partner_id', $partnerId)->delete();
        Holiday::where('partner_id', $partnerId)->delete();
        
        // Delete all custom roles for this partner
        Role::where('name', 'like', '%_' . $partnerId)->delete();

        // Delete all employees of this partner
        User::where('parent_id', $partnerId)->forceDelete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info("Cleaned existing data.");
    }

    private function seedFoundationAndStaff($partnerId)
    {
        // Branches
        $branch1 = HrmsBranch::create(['partner_id' => $partnerId, 'name' => 'Mumbai HQ', 'address' => 'Mumbai, India', 'status' => 'active']);
        $branch2 = HrmsBranch::create(['partner_id' => $partnerId, 'name' => 'Delhi Branch', 'address' => 'New Delhi, India', 'status' => 'active']);

        // Departments
        $deptHR = Department::create(['partner_id' => $partnerId, 'name' => 'Human Resources']);
        $deptIT = Department::create(['partner_id' => $partnerId, 'name' => 'Information Technology']);
        $deptSales = Department::create(['partner_id' => $partnerId, 'name' => 'Sales & Marketing']);

        // Shifts
        $shiftStandard = WorkShift::create([
            'partner_id' => $partnerId,
            'name' => 'Standard Shift',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'min_present_mins' => 480,
            'min_half_day_mins' => 240,
            'late_tolerance_minutes' => 15,
            'week_off_days' => json_encode(['Sunday', 'Saturday']),
            'auto_mark_attendance' => false,
        ]);

        // Roles & Permissions
        $roleManager = Role::create(['name' => 'Manager_' . $partnerId, 'guard_name' => 'web']);
        $roleLead = Role::create(['name' => 'Team Lead_' . $partnerId, 'guard_name' => 'web']);
        $roleEmployee = Role::create(['name' => 'Employee_' . $partnerId, 'guard_name' => 'web']);

        $roleManager->givePermissionTo([
            'leave_viewany', 'leave_create', 'leave_update', 'leave_status_update',
            'attendance_viewany', 'attendance_create', 'attendance_update', 'attendance_manage',
            'staff_viewany', 'staff_create', 'staff_update', 'staff_delete',
            'branch_viewany', 'branch_manage',
            'department_viewany', 'department_create', 'department_update', 'department_delete',
            'shift_viewany', 'shift_manage',
            'lead_viewany', 'lead_create', 'lead_update', 'lead_delete',
            'task_viewany', 'task_create', 'task_update', 'task_status_update', 'task_delete'
        ]);

        $roleLead->givePermissionTo([
            'leave_viewteam', 'leave_create', 'leave_status_update',
            'attendance_viewteam', 'attendance_create',
            'staff_viewteam',
            'lead_viewteam', 'lead_create', 'lead_update',
            'task_viewteam', 'task_create', 'task_update', 'task_status_update'
        ]);

        $roleEmployee->givePermissionTo([
            'leave_viewown', 'leave_create',
            'attendance_viewown', 'attendance_create',
            'lead_viewown', 'lead_create', 'lead_update',
            'task_viewown', 'task_status_update'
        ]);
        
        $password = Hash::make('12345678'); // User requested standard password

        // ==== MUMBAI BRANCH HIERARCHY ====
        
        // Sales Dept
        $mumbaiManager = User::create(['name' => 'Amit Sharma (Mgr)', 'email' => 'amit.mumbai@demo.com', 'password' => $password, 'role' => 'employee', 'parent_id' => $partnerId, 'branch_id' => $branch1->id, 'department_id' => $deptSales->id, 'shift_id' => $shiftStandard->id]);
        $mumbaiManager->assignRole($roleManager);
        DepartmentBranchHead::create(['department_id' => $deptSales->id, 'branch_id' => $branch1->id, 'head_id' => $mumbaiManager->id]);

        $mumbaiLead = User::create(['name' => 'Priya Patel (Lead)', 'email' => 'priya.mumbai@demo.com', 'password' => $password, 'role' => 'employee', 'parent_id' => $partnerId, 'branch_id' => $branch1->id, 'department_id' => $deptSales->id, 'shift_id' => $shiftStandard->id, 'reporting_to' => $mumbaiManager->id]);
        $mumbaiLead->assignRole($roleLead);

        $mumbaiEmp1 = User::create(['name' => 'Rahul Verma', 'email' => 'rahul.mumbai@demo.com', 'password' => $password, 'role' => 'employee', 'parent_id' => $partnerId, 'branch_id' => $branch1->id, 'department_id' => $deptSales->id, 'shift_id' => $shiftStandard->id, 'reporting_to' => $mumbaiLead->id]);
        $mumbaiEmp1->assignRole($roleEmployee);

        $mumbaiEmp2 = User::create(['name' => 'Sneha Desai', 'email' => 'sneha.mumbai@demo.com', 'password' => $password, 'role' => 'employee', 'parent_id' => $partnerId, 'branch_id' => $branch1->id, 'department_id' => $deptSales->id, 'shift_id' => $shiftStandard->id, 'reporting_to' => $mumbaiLead->id]);
        $mumbaiEmp2->assignRole($roleEmployee);

        // IT Dept
        $mumbaiITManager = User::create(['name' => 'Vikram Singh (IT)', 'email' => 'vikram.it@demo.com', 'password' => $password, 'role' => 'employee', 'parent_id' => $partnerId, 'branch_id' => $branch1->id, 'department_id' => $deptIT->id, 'shift_id' => $shiftStandard->id]);
        $mumbaiITManager->assignRole($roleManager);
        DepartmentBranchHead::create(['department_id' => $deptIT->id, 'branch_id' => $branch1->id, 'head_id' => $mumbaiITManager->id]);
        
        $mumbaiITEmp = User::create(['name' => 'Neha Gupta', 'email' => 'neha.it@demo.com', 'password' => $password, 'role' => 'employee', 'parent_id' => $partnerId, 'branch_id' => $branch1->id, 'department_id' => $deptIT->id, 'shift_id' => $shiftStandard->id, 'reporting_to' => $mumbaiITManager->id]);
        $mumbaiITEmp->assignRole($roleEmployee);

        // ==== DELHI BRANCH HIERARCHY ====
        
        // Sales Dept
        $delhiManager = User::create(['name' => 'Rajeev Kumar (Mgr)', 'email' => 'rajeev.delhi@demo.com', 'password' => $password, 'role' => 'employee', 'parent_id' => $partnerId, 'branch_id' => $branch2->id, 'department_id' => $deptSales->id, 'shift_id' => $shiftStandard->id]);
        $delhiManager->assignRole($roleManager);
        DepartmentBranchHead::create(['department_id' => $deptSales->id, 'branch_id' => $branch2->id, 'head_id' => $delhiManager->id]);

        $delhiLead = User::create(['name' => 'Kavita Singh (Lead)', 'email' => 'kavita.delhi@demo.com', 'password' => $password, 'role' => 'employee', 'parent_id' => $partnerId, 'branch_id' => $branch2->id, 'department_id' => $deptSales->id, 'shift_id' => $shiftStandard->id, 'reporting_to' => $delhiManager->id]);
        $delhiLead->assignRole($roleLead);

        $delhiEmp1 = User::create(['name' => 'Arjun Das', 'email' => 'arjun.delhi@demo.com', 'password' => $password, 'role' => 'employee', 'parent_id' => $partnerId, 'branch_id' => $branch2->id, 'department_id' => $deptSales->id, 'shift_id' => $shiftStandard->id, 'reporting_to' => $delhiLead->id]);
        $delhiEmp1->assignRole($roleEmployee);
        
        // HR Dept
        $delhiHRManager = User::create(['name' => 'Pooja Joshi (HR)', 'email' => 'pooja.hr@demo.com', 'password' => $password, 'role' => 'employee', 'parent_id' => $partnerId, 'branch_id' => $branch2->id, 'department_id' => $deptHR->id, 'shift_id' => $shiftStandard->id]);
        $delhiHRManager->assignRole($roleManager);
        DepartmentBranchHead::create(['department_id' => $deptHR->id, 'branch_id' => $branch2->id, 'head_id' => $delhiHRManager->id]);

        $this->command->info("Created foundation data (branches, departments, shifts, roles) and staff.");

        return [$branch1, $branch2];
    }

    private function seedHistoricalData($partnerId, $branches)
    {
        $users = User::where('parent_id', $partnerId)->get();
        
        $today = Carbon::today();
        $startDate = $today->copy()->subMonths(2)->startOfMonth();

        // 1. Holidays
        $holidayDate = $startDate->copy()->addDays(15);
        Holiday::create(['partner_id' => $partnerId, 'name' => 'Public Holiday', 'date' => $holidayDate->format('Y-m-d')]);

        for ($date = $startDate->copy(); $date->lte($today); $date->addDay()) {
            $isWeekend = $date->isWeekend();
            $isHoliday = $date->isSameDay($holidayDate);
            
            foreach ($users as $user) {
                if ($isWeekend || $isHoliday) {
                    if (rand(1, 100) <= 5) { // 5% chance of weekend work
                        $this->createAttendance($user, $date, true);
                    }
                    continue;
                }

                $leaveChance = rand(1, 100);
                
                if ($leaveChance <= 3) {
                    $this->createLeaveAndAbsent($user, $date);
                } else {
                    $this->createAttendance($user, $date, false);
                }
                
                if ($user->department && $user->department->name == 'Sales & Marketing' && rand(1, 100) <= 15) { 
                    $this->createLead($user, $partnerId, $date);
                }

                if (rand(1, 100) <= 10) { 
                    $this->createTask($user, $partnerId, $date);
                }
            }
        }
        
        $this->command->info("Created historical attendance, leaves, leads, and tasks for past 2 months.");
    }

    private function createAttendance($user, $date, $isOvertime)
    {
        $status = 'present';
        $inTime = $date->copy()->setTime(9, rand(0, 30), 0); 
        
        if ($isOvertime) {
            $outTime = $date->copy()->setTime(20, rand(0, 30), 0); 
        } else {
            if (rand(1, 100) <= 10) {
                $status = 'half_day';
                $outTime = $date->copy()->setTime(13, rand(0, 30), 0); 
            } else {
                $outTime = $date->copy()->setTime(18, rand(0, 59), 0); 
            }
        }
        
        $totalMins = $inTime->diffInMinutes($outTime);

        EmployeeAttendance::create([
            'employee_id' => $user->id,
            'date' => $date->format('Y-m-d'),
            'check_in' => $inTime->format('H:i:s'),
            'check_out' => $outTime->format('H:i:s'),
            'status' => 'punch_out',
            'working_minutes' => $totalMins,
            'working_mode' => 'office',
            'created_at' => $outTime,
            'updated_at' => $outTime,
        ]);
    }

    private function createLeaveAndAbsent($user, $date)
    {
        EmployeeAttendance::create([
            'employee_id' => $user->id,
            'date' => $date->format('Y-m-d'),
            'status' => 'absent',
            'created_at' => $date->copy()->setTime(10, 0, 0),
            'updated_at' => $date->copy()->setTime(10, 0, 0),
        ]);

        EmployeeLeave::create([
            'employee_id' => $user->id,
            'type' => ['casual', 'sick', 'earned'][rand(0, 2)],
            'start_date' => $date->format('Y-m-d'),
            'end_date' => $date->format('Y-m-d'),
            'reason' => 'Personal work or unwell.',
            'status' => 'approved',
            'created_at' => $date->copy()->subDays(rand(1, 5)), 
        ]);
    }
    
    private function createLead($user, $partnerId, $date)
    {
        $statuses = ['new', 'first_call', 'interested', 'meeting_scheduled', 'customer_visit', 'quotation', 'negotiation', 'won', 'lost'];
        
        Lead::create([
            'partner_id' => $partnerId,
            'assigned_to' => $user->id,
            'customer_name' => 'Demo Lead ' . rand(1000, 9999),
            'customer_mobile' => '98' . rand(10000000, 99999999),
            'status' => $statuses[rand(0, count($statuses) - 1)],
            'notes' => 'Follow up required. Met through campaign.',
            'created_at' => $date->copy()->setTime(rand(10, 17), rand(0, 59), 0),
            'updated_at' => $date->copy()->setTime(rand(10, 17), rand(0, 59), 0)->addDays(rand(1, 5)),
        ]);
    }

    private function createTask($user, $partnerId, $date)
    {
        $statuses = ['pending', 'in_progress', 'completed'];
        $dueDate = $date->copy()->addDays(rand(1, 7));
        
        EmployeeTask::create([
            'employee_id' => $user->id,
            'assigned_by' => $partnerId,
            'title' => 'Important Task ' . rand(100, 999),
            'description' => 'Please complete this document and submit to management.',
            'due_date' => $dueDate->format('Y-m-d'),
            'status' => $date->diffInDays(Carbon::today()) > 10 ? 'completed' : $statuses[rand(0, 2)],
            'created_at' => $date->copy()->setTime(rand(9, 11), rand(0, 59), 0),
            'updated_at' => $date->copy()->setTime(rand(14, 18), rand(0, 59), 0)->addDays(rand(0, 3)),
        ]);
    }
}
