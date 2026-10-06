<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Expense;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeave;
use App\Models\EmployeePayroll;
use App\Models\Holiday;
use App\Models\EmployeeSalaryStructure;
use Carbon\Carbon;

class HrmsSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch all existing employees
        $employees = User::where('role', 'employee')->get();

        if ($employees->isEmpty()) {
            $this->command->warn('No employees found. Please create partners and employees first before running this seeder.');
            return;
        }

        // Get unique partner IDs from employees
        $partnerIds = $employees->pluck('parent_id')->unique()->filter()->toArray();

        // Delete existing HRMS data for these employees to avoid duplicates on re-run
        $employeeIds = $employees->pluck('id')->toArray();
        EmployeeAttendance::whereIn('employee_id', $employeeIds)->delete();
        EmployeeLeave::whereIn('employee_id', $employeeIds)->delete();
        EmployeePayroll::whereIn('employee_id', $employeeIds)->delete();
        Expense::whereIn('employee_id', $employeeIds)->delete();
        Holiday::whereIn('partner_id', $partnerIds)->delete();

        // Setup Salary Structure for all existing employees
        foreach ($employees as $emp) {
            EmployeeSalaryStructure::updateOrCreate(
                ['employee_id' => $emp->id],
                [
                    'basic_salary' => 50000,
                    'allowances' => ['hra' => 10000, 'medical' => 5000],
                    'deductions' => ['pf' => 1800, 'tax' => 1200]
                ]
            );
        }

        // Generate data for the past 3 months up to today
        $startDate = Carbon::today()->subMonths(3)->startOfMonth();
        $endDate = Carbon::today();
        
        // Let's create some holidays explicitly per partner
        $currentDate = $startDate->copy();
        $holidayMonths = [];
        
        while ($currentDate->lte($endDate)) {
            $monthKey = $currentDate->format('Y-m');
            // One holiday per month (random weekday)
            if (!isset($holidayMonths[$monthKey]) && !$currentDate->isSunday() && rand(1, 10) === 1) {
                foreach ($partnerIds as $partnerId) {
                    Holiday::create([
                        'partner_id' => $partnerId,
                        'name' => 'Company Holiday ' . $currentDate->format('F'),
                        'date' => $currentDate->toDateString(),
                    ]);
                }
                $holidayMonths[$monthKey] = $currentDate->toDateString();
            }
            $currentDate->addDay();
        }

        $currentDate = $startDate->copy();
        // Keep track of which employees are on leave
        $employeeLeaves = [];
        
        while ($currentDate->lte($endDate)) {
            $isSunday = $currentDate->isSunday(); 
            $dateString = $currentDate->toDateString();
            
            foreach ($employees as $emp) {
                // Check if today is a holiday for this employee's partner
                $isHoliday = Holiday::where('partner_id', $emp->parent_id)->where('date', $dateString)->exists();
                
                $isOnLeave = false;
                
                // If they are on a multi-day leave
                if (isset($employeeLeaves[$emp->id]) && $employeeLeaves[$emp->id] >= $dateString) {
                    $isOnLeave = true;
                }
                
                // Start a new leave occasionally
                if (!$isSunday && !$isHoliday && !$isOnLeave && rand(1, 40) === 1) {
                    $leaveDays = rand(1, 3);
                    $leaveEndDate = $currentDate->copy()->addDays($leaveDays - 1)->toDateString();
                    $employeeLeaves[$emp->id] = $leaveEndDate;
                    $isOnLeave = true;
                    
                    EmployeeLeave::create([
                        'employee_id' => $emp->id,
                        'start_date' => $dateString,
                        'end_date' => $leaveEndDate,
                        'reason' => 'Personal work / Sick',
                        'status' => 'approved',
                        'type' => ['casual', 'sick', 'earned'][array_rand(['casual', 'sick', 'earned'])]
                    ]);
                }

                // We only seed attendance history if the date is in the past, or today
                if ($currentDate->isPast() || $currentDate->isSameDay(Carbon::today())) {
                    $checkIn = null;
                    $checkOut = null;
                    $workingMinutes = 0;
                    
                    if ($isSunday) {
                        $selectedStatus = 'weekOff';
                    } elseif ($isHoliday) {
                        $selectedStatus = 'holiday';
                    } elseif ($isOnLeave) {
                        $selectedStatus = 'leave';
                    } else {
                        // Normal working day
                        $statusOptions = ['punch_out', 'punch_out', 'punch_out', 'punch_out', 'short_leave', 'half_day', 'absent'];
                        $selectedStatus = $statusOptions[array_rand($statusOptions)];
                        
                        if ($selectedStatus !== 'absent') {
                            $checkIn = $currentDate->copy()->setHour(9)->setMinute(rand(0, 30));
                            
                            if ($selectedStatus === 'punch_out') {
                                $checkOut = $checkIn->copy()->addHours(9)->addMinutes(rand(0, 30));
                                $workingMinutes = $checkOut->diffInMinutes($checkIn);
                            } elseif ($selectedStatus === 'short_leave') {
                                $checkOut = $checkIn->copy()->addHours(7)->addMinutes(rand(0, 30));
                                $workingMinutes = $checkOut->diffInMinutes($checkIn);
                            } elseif ($selectedStatus === 'half_day') {
                                $checkOut = $checkIn->copy()->addHours(4)->addMinutes(rand(0, 30));
                                $workingMinutes = $checkOut->diffInMinutes($checkIn);
                            }
                        }
                    }

                    // Explicitly add attendance records for every status
                    EmployeeAttendance::create([
                        'employee_id' => $emp->id,
                        'date' => $dateString,
                        'status' => $selectedStatus,
                        'check_in' => $checkIn ? $checkIn->format('H:i:s') : null,
                        'check_out' => $checkOut ? $checkOut->format('H:i:s') : null,
                        'working_minutes' => $workingMinutes,
                    ]);
                }

                // Occasional Expenses
                if (rand(1, 30) === 1) {
                    Expense::create([
                        'employee_id' => $emp->id,
                        'date' => $dateString,
                        'amount' => rand(200, 3000),
                        'category' => ['Travel', 'Meals', 'Office Supplies'][array_rand(['Travel', 'Meals', 'Office Supplies'])],
                        'description' => 'Business expense',
                        'status' => ['pending', 'approved', 'rejected'][array_rand(['pending', 'approved', 'rejected'])]
                    ]);
                }
            }
            
            // At the end of each month, generate payroll
            if ($currentDate->copy()->endOfMonth()->isSameDay($currentDate) || ($currentDate->isSameMonth($endDate) && $currentDate->isSameDay($endDate))) {
                foreach ($employees as $emp) {
                    EmployeePayroll::updateOrCreate(
                        [
                            'employee_id' => $emp->id,
                            'month' => $currentDate->month,
                            'year' => $currentDate->year,
                        ],
                        [
                            'basic_salary' => 50000,
                            'allowances_breakdown' => ['hra' => 10000, 'medical' => 5000],
                            'deductions' => 3000,
                            'deductions_breakdown' => ['pf' => 1800, 'tax' => 1200],
                            'bonuses' => rand(0, 5000),
                            'gross_pay' => 65000,
                            'net_pay' => 62000,
                            'status' => ['paid', 'processing'][array_rand(['paid', 'processing'])],
                        ]
                    );
                }
            }
            
            $currentDate->addDay();
        }

        $this->command->info('HRMS Seed Data generated. Attendance history explicitly includes leave, holiday, and weekOff records.');
    }
}
