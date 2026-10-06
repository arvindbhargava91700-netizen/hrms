<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AbcEmployeeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employee = \App\Models\User::where('email', 'abc@gmail.com')->first();
        if (!$employee) {
            $this->command->error("Employee abc@gmail.com not found!");
            return;
        }

        $partnerId = $employee->isPartner() ? $employee->id : $employee->parent_id;

        // Current month is July 2026, so previous month is June 2026
        $previousMonth = \Carbon\Carbon::now()->subMonth();
        $year = $previousMonth->year;
        $month = $previousMonth->month;

        $daysInMonth = $previousMonth->daysInMonth;

        // 1. Seed Attendances (Skip Sundays)
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = \Carbon\Carbon::create($year, $month, $i);
            
            if ($date->isSunday()) {
                \App\Models\EmployeeAttendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date->format('Y-m-d'),
                    'status' => 'weekOff',
                ]);
                continue;
            }

            // Create some random check-ins
            // E.g., working from 09:00 to 18:00
            \App\Models\EmployeeAttendance::create([
                'employee_id' => $employee->id,
                'date' => $date->format('Y-m-d'),
                'check_in' => '09:00:00',
                'check_out' => '18:00:00',
                'status' => 'present',
                'working_minutes' => 9 * 60,
                'check_in_lat' => '26.8521712',
                'check_in_lng' => '81.055084',
            ]);
        }

        // 2. Seed Leaves
        \App\Models\EmployeeLeave::create([
            'employee_id' => $employee->id,
            'start_date' => \Carbon\Carbon::create($year, $month, 10)->format('Y-m-d'),
            'end_date' => \Carbon\Carbon::create($year, $month, 11)->format('Y-m-d'),
            'type' => 'sick',
            'status' => 'approved',
            'reason' => 'Fever and cold',
        ]);

        // Overwrite attendance for leave days
        \App\Models\EmployeeAttendance::where('employee_id', $employee->id)
            ->whereIn('date', [\Carbon\Carbon::create($year, $month, 10)->format('Y-m-d'), \Carbon\Carbon::create($year, $month, 11)->format('Y-m-d')])
            ->update([
                'status' => 'leave',
                'check_in' => null,
                'check_out' => null,
                'working_minutes' => null,
            ]);

        // 3. Seed Expenses
        \App\Models\Expense::create([
            'employee_id' => $employee->id,
            'amount' => 1500,
            'category' => 'Travel',
            'date' => \Carbon\Carbon::create($year, $month, 15)->format('Y-m-d'),
            'description' => 'Client meeting travel',
            'status' => 'approved',
        ]);
        
        \App\Models\Expense::create([
            'employee_id' => $employee->id,
            'amount' => 500,
            'category' => 'Food',
            'date' => \Carbon\Carbon::create($year, $month, 16)->format('Y-m-d'),
            'description' => 'Lunch with client',
            'status' => 'pending',
        ]);

        // 4. Seed Payroll
        \App\Models\EmployeePayroll::create([
            'employee_id' => $employee->id,
            'month' => $month,
            'year' => $year,
            'basic_salary' => 50000,
            'allowances_breakdown' => json_encode(['HRA' => 10000, 'Travel' => 5000]),
            'deductions' => 2000,
            'deductions_breakdown' => json_encode(['Tax' => 2000]),
            'bonuses' => 0,
            'gross_pay' => 65000,
            'net_pay' => 63000,
            'status' => 'paid',
        ]);

        $this->command->info("Seeded previous month data for abc@gmail.com successfully.");
    }
}
