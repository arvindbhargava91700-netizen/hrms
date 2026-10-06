<?php

namespace App\Livewire\Partner\Hrms\Payroll;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmployeePayroll;
use App\Models\SalaryAdjustment;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeave;
use Carbon\Carbon;

class PayrollDrafts extends Component
{
    use WithPagination, \App\Livewire\Partner\Hrms\HasPartnerId;
    
    protected $paginationTheme = 'bootstrap';

    public $editingPayrollId = null;
    public $editingDeductions = 0;
    public $editingBonuses = 0;

    public $viewingPayrollId = null;

    public $editBasicSalary = 0;
    public $editAllowances = [];
    public $editBonuses = 0;
    public $editDeductions = [];

    public $editPresentDays = 0;
    public $editHalfDays = 0;
    public $editPaidLeaveDays = 0;
    public $editUnpaidLeaveDays = 0;
    public $editAbsentDays = 0;
    public $editAdvancePay = 0;

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('payroll_viewAny') ||  auth()->user()->canAccess('payroll_viewBranch') || auth()->user()->canAccess('payroll_viewteam'), 403);
    }
    public function editPayroll($payrollId, $deductions, $bonuses)
    {
        $this->editingPayrollId = $payrollId;
        $this->editingDeductions = $deductions;
        $this->editingBonuses = $bonuses;
    }

    public function savePayroll()
    {
        $this->validate([
            'editingDeductions' => 'required|numeric|min:0',
            'editingBonuses' => 'required|numeric|min:0',
        ]);


        $payroll = EmployeePayroll::findOrFail($this->editingPayrollId);
        
        // Ensure this payroll belongs to an employee of this partner/manager
        if (!in_array($payroll->employee_id, $this->getTeamEmployeeIds())) {
            abort(403);
        }

        $totalAllowances = 0;
        if (is_array($payroll->allowances_breakdown)) {
            $totalAllowances = array_sum(array_map('floatval', $payroll->allowances_breakdown));
        }
        $netPay = $payroll->net_pay - $this->editingDeductions + $this->editingBonuses;
        
        $payroll->update([
            'deductions' => $this->editingDeductions,
            'bonuses' => $this->editingBonuses,
            'net_pay' => $netPay
        ]);
        
        session()->flash('message', "Payroll adjustments saved for {$payroll->employee->name}.");
        
        $this->editingPayrollId = null;
        $this->editingDeductions = 0;
        $this->editingBonuses = 0;
    }

    public function cancelEdit()
    {
        $this->editingPayrollId = null;
    }

    public function viewPayroll($payrollId)
    {
        $this->viewingPayrollId = $payrollId;

        $payroll = EmployeePayroll::with('salaryAdjustments')->find($payrollId);
        if (!$payroll) {
            return;
        }

        $this->editBasicSalary = (float) $payroll->basic_salary;
        $this->editAllowances = is_array($payroll->allowances_breakdown) ? $payroll->allowances_breakdown : [];
        $this->editBonuses = (float) $payroll->bonuses;
        $this->editDeductions = is_array($payroll->deductions_breakdown) ? $payroll->deductions_breakdown : [];

        $monthData = $this->calculateMonthSalary($payroll);
        $adj = $payroll->salaryAdjustments->keyBy(fn($a) => $a->adjustment_type . '|' . ($a->field_key ?? ''));

        $this->editPresentDays = (int) ($adj['present_days|']->adjusted_amount ?? $monthData['present_days']);
        $this->editHalfDays = (int) ($adj['half_days|']->adjusted_amount ?? $monthData['half_day_days']);
        $this->editPaidLeaveDays = (int) ($adj['paid_leave_days|']->adjusted_amount ?? $monthData['paid_leave_days']);
        $this->editUnpaidLeaveDays = (int) ($adj['unpaid_leave_days|']->adjusted_amount ?? $monthData['unpaid_leave_days']);
        $this->editAbsentDays = (int) ($adj['absent_days|']->adjusted_amount ?? $monthData['absent_days']);

        $advanceFromBreakdown = (float) ($payroll->deductions_breakdown['salary_advance'] ?? 0);

        $advanceFromTable = (float) \App\Models\AdvancePayment::where('employee_id', $payroll->employee_id)
            ->where('deduction_month', $payroll->month)
            ->where('deduction_year', $payroll->year)
            ->whereIn('status', ['approved', 'deducted'])
            ->sum('amount');

        $advanceBase = $advanceFromBreakdown ?: $advanceFromTable;
        $this->editAdvancePay = (float) ($adj['advance_pay|']->adjusted_amount ?? $advanceBase);
    }

    public function saveAdjustments()
    {
        $this->validate([
            'editBasicSalary' => 'required|numeric|min:0',
            'editBonuses' => 'required|numeric|min:0',
            'editAllowances.*' => 'nullable|numeric|min:0',
            'editDeductions.*' => 'nullable|numeric|min:0',
            'editPresentDays' => 'required|integer|min:0|max:31',
            'editHalfDays' => 'required|integer|min:0|max:31',
            'editPaidLeaveDays' => 'required|integer|min:0|max:31',
            'editUnpaidLeaveDays' => 'required|integer|min:0|max:31',
            'editAbsentDays' => 'required|integer|min:0|max:31',
            'editAdvancePay' => 'required|numeric|min:0',
        ]);

        $payroll = EmployeePayroll::with('salaryAdjustments')->findOrFail($this->viewingPayrollId);

        if (!in_array($payroll->employee_id, $this->getTeamEmployeeIds())) {
            abort(403);
        }

        $allowances = [];
        foreach (($this->editAllowances ?? []) as $key => $val) {
            $allowances[$key] = round((float) $val, 2);
        }
        $deductions = [];
        foreach (($this->editDeductions ?? []) as $key => $val) {
            $deductions[$key] = round((float) $val, 2);
        }

        $origBasic = (float) $payroll->basic_salary;
        $origAllowances = is_array($payroll->allowances_breakdown) ? $payroll->allowances_breakdown : [];
        $origBonuses = (float) $payroll->bonuses;
        $origDeductions = is_array($payroll->deductions_breakdown) ? $payroll->deductions_breakdown : [];
        $monthData = $this->calculateMonthSalary($payroll);

        $this->storeAdjustment($payroll->id, 'basic_salary', null, $origBasic, (float) $this->editBasicSalary);

        foreach ($allowances as $key => $val) {
            $orig = (float) ($origAllowances[$key] ?? 0);
            if (abs($orig - $val) > 0.001) {
                $this->storeAdjustment($payroll->id, 'allowance', $key, $orig, $val);
            }
        }

        if (abs($origBonuses - (float) $this->editBonuses) > 0.001) {
            $this->storeAdjustment($payroll->id, 'bonus', null, $origBonuses, (float) $this->editBonuses);
        }

        foreach ($deductions as $key => $val) {
            if ($key === 'salary_advance') continue;
            $orig = (float) ($origDeductions[$key] ?? 0);
            if (abs($orig - $val) > 0.001) {
                $this->storeAdjustment($payroll->id, 'deduction', $key, $orig, $val);
            }
        }

        // Day counts
        $this->storeAdjustment($payroll->id, 'present_days', null, $monthData['present_days'], (int) $this->editPresentDays);
        $this->storeAdjustment($payroll->id, 'half_days', null, $monthData['half_day_days'], (int) $this->editHalfDays);
        $this->storeAdjustment($payroll->id, 'paid_leave_days', null, $monthData['paid_leave_days'], (int) $this->editPaidLeaveDays);
        $this->storeAdjustment($payroll->id, 'unpaid_leave_days', null, $monthData['unpaid_leave_days'], (int) $this->editUnpaidLeaveDays);
        $this->storeAdjustment($payroll->id, 'absent_days', null, $monthData['absent_days'], (int) $this->editAbsentDays);

        $advanceOrig = (float) ($payroll->deductions_breakdown['salary_advance'] ?? 0);
        $this->storeAdjustment($payroll->id, 'advance_pay', null, $advanceOrig, (float) $this->editAdvancePay);

        // Apply adjustments to the payroll record so the table shows updated values
        if ((float) $this->editAdvancePay > 0) {
            $deductions['salary_advance'] = round((float) $this->editAdvancePay, 2);
        } else {
            unset($deductions['salary_advance']);
        }
        $totalAllowances = array_sum($allowances);

        // Mirror the popup live calculation (attendance-based gross)
        $rateBase = array_diff_key($allowances, ['expense_reimbursement' => 0]);
        $rateBaseTotal = array_sum(array_map('floatval', $rateBase));
        $daysInMonth = max(1, $monthData['days_in_month']);
        $dailyRate = round(((float) $this->editBasicSalary + $rateBaseTotal) / $daysInMonth, 2);

        $thisMonthSalary = ($dailyRate * (int) $this->editPresentDays)
            + ($dailyRate * 0.5 * (int) $this->editHalfDays)
            + ($dailyRate * (int) $this->editPaidLeaveDays);

        $unpaidLeaveDeduction = $dailyRate * (int) $this->editUnpaidLeaveDays;
        $absentDeduction = $dailyRate * (int) $this->editAbsentDays;

        if ($unpaidLeaveDeduction > 0) {
            $deductions['unpaid_leave'] = round($unpaidLeaveDeduction, 2);
        } else {
            unset($deductions['unpaid_leave']);
        }
        if ($absentDeduction > 0) {
            $deductions['absent'] = round($absentDeduction, 2);
        } else {
            unset($deductions['absent']);
        }

        $grossPay = round($thisMonthSalary + $totalAllowances + (float) $this->editBonuses, 2);
        $totalDeductions = array_sum($deductions);
        $netPay = round(max(0, $grossPay - $totalDeductions), 2);

        $payroll->update([
            'basic_salary' => round((float) $this->editBasicSalary, 2),
            'allowances_breakdown' => $allowances,
            'bonuses' => round((float) $this->editBonuses, 2),
            'deductions' => $totalDeductions,
            'deductions_breakdown' => $deductions,
            'gross_pay' => $grossPay,
            'net_pay' => $netPay,
        ]);

        session()->flash('message', "Payroll adjustments stored for {$payroll->employee->name}. Gross ₹" . number_format($grossPay, 2) . " / Net ₹" . number_format($netPay, 2) . ".");

        $this->viewingPayrollId = null;
        $this->reset(['editBasicSalary', 'editAllowances', 'editBonuses', 'editDeductions', 'editPresentDays', 'editHalfDays', 'editPaidLeaveDays', 'editUnpaidLeaveDays', 'editAbsentDays', 'editAdvancePay']);
    }

    private function storeAdjustment($payrollId, $type, $key, $original, $adjusted)
    {
        SalaryAdjustment::updateOrCreate(
            [
                'employee_payroll_id' => $payrollId,
                'adjustment_type' => $type,
                'field_key' => $key,
            ],
            [
                'original_amount' => round($original, 2),
                'adjusted_amount' => round($adjusted, 2),
            ]
        );
    }

    public function closeView()
    {
        $this->viewingPayrollId = null;
        $this->reset(['editBasicSalary', 'editAllowances', 'editBonuses', 'editDeductions', 'editPresentDays', 'editHalfDays', 'editPaidLeaveDays', 'editUnpaidLeaveDays', 'editAbsentDays', 'editAdvancePay']);
    }

    private function calculateMonthSalary($payroll)
    {
        $month = (int) $payroll->month;
        $year = (int) $payroll->year;

        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $allowances = is_array($payroll->allowances_breakdown) ? $payroll->allowances_breakdown : [];
        $allowances = array_diff_key($allowances, ['expense_reimbursement' => 0]);
        $totalAllowances = array_sum(array_map('floatval', array_values($allowances)));
        $grossForRate = (float) $payroll->basic_salary + $totalAllowances;
        $dailyRate = $daysInMonth > 0 ? $grossForRate / $daysInMonth : 0;

        $attendances = EmployeeAttendance::where('employee_id', $payroll->employee_id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $presentDays = 0;
        $halfDayAttendanceDays = 0;
        $absentDays = 0;
        foreach ($attendances as $att) {
            $attDate = Carbon::parse($att->date);
            if ($attDate->isSunday()) continue;
            if (in_array($att->status, ['leave', 'weekOff', 'holiday'])) continue;
            if ($att->status === 'absent') {
                $absentDays += 1;
                continue;
            }
            if (in_array($att->status, ['punch_out', 'short_leave', 'late']) && $att->check_in) {
                $presentDays += 1;
            } elseif (in_array($att->status, ['half_day', 'punch_in'])) {
                $halfDayAttendanceDays += 1;
            }
        }

        $approvedLeaves = EmployeeLeave::where('employee_id', $payroll->employee_id)
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)->where('end_date', '>=', $endDate);
                  });
            })
            ->get();

        $paidLeaveDays = 0;
        $unpaidLeaveDays = 0;
        $halfDayPaidLeaves = 0;
        foreach ($approvedLeaves as $leave) {
            $from = Carbon::parse($leave->start_date)->max($startDate);
            $to = Carbon::parse($leave->end_date)->min($endDate);
            $isHalfDay = $this->leaveIsHalfDay($leave);
            $isPaid = $this->leaveIsPaid($leave);

            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                if ($d->isSunday()) continue;
                if ($isHalfDay) {
                    if ($isPaid) $halfDayPaidLeaves += 1;
                } else {
                    if ($isPaid) $paidLeaveDays += 1;
                    else $unpaidLeaveDays += 1;
                }
            }
        }

        $monthSalary = ($dailyRate * $presentDays)
            + ($dailyRate * 0.5 * $halfDayAttendanceDays)
            + ($dailyRate * $paidLeaveDays)
            + ($dailyRate * 0.5 * $halfDayPaidLeaves);

        return [
            'amount' => round($monthSalary, 2),
            'present_days' => $presentDays,
            'half_day_days' => $halfDayAttendanceDays,
            'paid_leave_days' => $paidLeaveDays,
            'unpaid_leave_days' => $unpaidLeaveDays,
            'absent_days' => $absentDays,
            'half_day_paid_leaves' => $halfDayPaidLeaves,
            'daily_rate' => round($dailyRate, 2),
            'days_in_month' => $daysInMonth,
        ];
    }

    private function leaveIsPaid($leave)
    {
        $unpaidMarkers = ['unpaid', 'loss of pay', 'lop', 'without pay', 'no pay'];
        $name = strtolower((string) $leave->type);

        if ($leave->leaveCategory && $leave->leaveCategory->name) {
            $name = strtolower($leave->leaveCategory->name);
        }

        foreach ($unpaidMarkers as $marker) {
            if (str_contains($name, $marker)) return false;
        }

        return true;
    }

    private function leaveIsHalfDay($leave)
    {
        if (strtolower((string) $leave->type) === 'half_day') return true;
        if ($leave->leaveCategory && str_contains(strtolower($leave->leaveCategory->name), 'half')) return true;
        return false;
    }

    public function render()
    {
        $drafts = EmployeePayroll::whereIn('employee_id', $this->getTeamEmployeeIds())
            ->where('status', 'pending')
            ->with('employee')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(15);

        $viewingPayroll = null;
        $monthSalaryData = null;
        if ($this->viewingPayrollId) {
            $viewingPayroll = EmployeePayroll::with('employee')->find($this->viewingPayrollId);
            if ($viewingPayroll) {
                $monthSalaryData = $this->calculateMonthSalary($viewingPayroll);
            }
        }

        return view('livewire.partner.hrms.payroll.payroll-drafts', [
            'drafts' => $drafts,
            'viewingPayroll' => $viewingPayroll,
            'monthSalaryData' => $monthSalaryData,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Payroll Drafts',
            'pageSubtitle' => 'Adjust deductions and bonuses for pending payrolls',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
