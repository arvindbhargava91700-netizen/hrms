<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Lead;
use App\Models\LeadOrder;
use App\Models\CustomerVisit;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeave;
use App\Models\Expense;
use App\Models\EmployeePayroll;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class PartnerDummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $partner = User::where('email', 'demo@gmail.com')->first();
        if (!$partner) {
            $this->command->error("Partner demo@gmail.com not found.");
            return;
        }

        $partnerId = $partner->id;
        $this->command->info("Cleaning and seeding dummy data for partner: {$partner->email}");

        // 1. Delete Old Data
        $emails = ['sales@demo.com', 'finance@demo.com', 'manager@demo.com'];
        $oldUsers = User::withTrashed()->whereIn('email', $emails)->get();
        foreach ($oldUsers as $u) {
            EmployeePayroll::where('employee_id', $u->id)->delete();
            Expense::where('employee_id', $u->id)->delete();
            EmployeeLeave::where('employee_id', $u->id)->delete();
            EmployeeAttendance::where('employee_id', $u->id)->delete();
            $u->forceDelete();
        }

        CustomerVisit::where('partner_id', $partnerId)->delete();
        LeadOrder::where('partner_id', $partnerId)->delete();
        Lead::where('partner_id', $partnerId)->delete();
        User::where('parent_id', $partnerId)->where('role', 'employee')->forceDelete();

        $this->command->info("Old data removed.");

        // 2. Create Employees
        $departments = Department::where('partner_id', $partnerId)->pluck('id', 'name');
        
        $employees = [];
        
        // Sales Exec
        $salesUser = User::updateOrCreate(
            ['email' => 'sales@demo.com'],
            [
                'name' => 'John Sales',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'parent_id' => $partnerId,
                'department_id' => $departments['Sales'] ?? null,
            ]
        );
        $salesUser->assignRole('employee'); // default
        if (Role::where('name', $partnerId . '_Sales')->exists()) {
            $salesUser->assignRole($partnerId . '_Sales');
        }
        $employees['sales'] = $salesUser;

        // Finance Mgr
        $financeUser = User::updateOrCreate(
            ['email' => 'finance@demo.com'],
            [
                'name' => 'Sarah Finance',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'parent_id' => $partnerId,
                'department_id' => $departments['Finance'] ?? null,
            ]
        );
        $financeUser->assignRole('employee'); // default
        if (Role::where('name', $partnerId . '_Finance')->exists()) {
            $financeUser->assignRole($partnerId . '_Finance');
        }
        $employees['finance'] = $financeUser;

        // Manager
        $mgrUser = User::updateOrCreate(
            ['email' => 'manager@demo.com'],
            [
                'name' => 'Mike Manager',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'parent_id' => $partnerId,
                'department_id' => $departments['Manager'] ?? null,
            ]
        );
        $mgrUser->assignRole('employee'); // default
        if (Role::where('name', $partnerId . '_Manager')->exists()) {
            $mgrUser->assignRole($partnerId . '_Manager');
        }
        // Set manager as reporting manager for sales
        $salesUser->update(['reporting_to' => $mgrUser->id]);
        $employees['manager'] = $mgrUser;

        $this->command->info("Employees created.");

        // 3. Create Leads
        $leads = [];
        for ($i = 1; $i <= 5; $i++) {
            $leads[] = Lead::create([
                'partner_id' => $partnerId,
                'assigned_to' => $salesUser->id,
                'customer_name' => "Demo Client $i",
                'customer_mobile' => "987654321$i",
                'status' => $i % 2 == 0 ? 'won' : 'new',
            ]);
        }
        $this->command->info("Leads created.");

        // 4. Create Lead Orders
        foreach ($leads as $index => $lead) {
            if ($lead->status === 'won') {
                $status = 'pending';
                if ($index === 1) $status = 'manager_approved';
                if ($index === 3) $status = 'finance_approved';

                LeadOrder::create([
                    'partner_id' => $partnerId,
                    'employee_id' => $salesUser->id,
                    'lead_id' => $lead->id,
                    'total_amount' => rand(1000, 5000),
                    'paid_amount' => 500,
                    'remaining_balance' => rand(500, 4500),
                    'approval_status' => $status,
                ]);
            }
        }
        $this->command->info("Lead orders created.");

        // 5. Create Customer Visits
        foreach ($leads as $lead) {
            CustomerVisit::create([
                'partner_id' => $partnerId,
                'employee_id' => $salesUser->id,
                'lead_id' => $lead->id,
                'visit_date' => Carbon::now()->subDays(rand(1, 10))->format('Y-m-d H:i:s'),
                'purpose' => 'Initial Meeting',
                'location' => 'Client Office',
                'status' => 'completed',
                'notes' => 'Good meeting, interested in product.',
            ]);
        }
        $this->command->info("Customer visits created.");

        // 6. Create Attendance (for past 5 days)
        foreach ($employees as $emp) {
            for ($i = 1; $i <= 5; $i++) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                EmployeeAttendance::create([
                    'employee_id' => $emp->id,
                    'date' => $date,
                    'check_in' => "$date 09:00:00",
                    'check_out' => "$date 18:00:00",
                    'status' => 'punch_out',
                ]);
            }
        }
        $this->command->info("Attendance created.");

        // 7. Create Leaves
        EmployeeLeave::create([
            'employee_id' => $salesUser->id,
            'type' => 'casual',
            'start_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'reason' => 'Family event',
            'status' => 'pending',
        ]);
        $this->command->info("Leaves created.");

        // 8. Create Expenses
        Expense::create([
            'employee_id' => $salesUser->id,
            'category' => 'travel',
            'amount' => 250,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'description' => 'Travel to client office',
            'status' => 'pending',
        ]);
        $this->command->info("Expenses created.");

        // 9. Create Payroll
        EmployeePayroll::create([
            'employee_id' => $salesUser->id,
            'month' => Carbon::now()->subMonth()->month,
            'year' => Carbon::now()->subMonth()->year,
            'basic_salary' => 3000,
            'gross_pay' => 3000,
            'net_pay' => 3000,
            'status' => 'paid',
        ]);
        $this->command->info("Payroll created.");

        $this->command->info("All dummy data successfully seeded for demo@gmail.com!");
    }
}
