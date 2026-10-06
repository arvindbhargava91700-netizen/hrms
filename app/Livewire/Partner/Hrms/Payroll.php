<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use App\Models\User;
use App\Models\EmployeePayroll;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Payroll extends Component
{
    use HasPartnerId;
    public $payrolls = [];
    public $staff = [];
    public $selectedMonth, $selectedYear;
    
    public $isModalOpen = false;
    public $employeeId;
    public $basicSalary = 0;
    public $deductions = 0;
    public $bonuses = 0;
    public $netPay = 0;
    
    public $viewMode = 'all';

    public function mount()
    {
        $this->selectedMonth = date('m');
        $this->selectedYear = date('Y');
        $this->viewMode = request()->query('view', 'all');
        $this->loadData();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['basicSalary', 'deductions', 'bonuses'])) {
            $this->calculateNetPay();
        }
        
        if (in_array($propertyName, ['selectedMonth', 'selectedYear'])) {
            $this->loadData();
        }
        
        if ($propertyName === 'employeeId') {
            $employee = collect($this->staff)->firstWhere('id', $this->employeeId);
            if ($employee) {
                $this->basicSalary = $employee->basic_salary ?? 0;
                $this->calculateNetPay();
            }
        }
    }

    public function loadData()
    {
        $partnerId = $this->getPartnerId();
        $user = auth()->user();

        // Force viewMode to 'my' if they don't have viewAny permission
        if ($this->viewMode === 'all' && !$user->canAccess('payroll_viewAny')) {
            $this->viewMode = 'my';
        }
        
        // Force viewMode to 'all' if they requested 'my' but don't have viewOwn
        if ($this->viewMode === 'my' && !$user->canAccess('payroll_viewOwn')) {
            $this->viewMode = 'all';
        }

        if ($this->viewMode === 'all' && $user->canAccess('payroll_viewAny')) {
            $this->staff = User::whereIn('id', $this->getTeamEmployeeIds())->get();
            $employeeIds = $this->staff->pluck('id')->toArray();
            
            $this->payrolls = EmployeePayroll::whereIn('employee_id', $employeeIds)
                ->where('month', $this->selectedMonth)
                ->where('year', $this->selectedYear)
                ->with('employee')
                ->get();
        } elseif ($this->viewMode === 'my' && $user->canAccess('payroll_viewOwn')) {
            $this->staff = collect([$user]);
            
            $this->payrolls = EmployeePayroll::where('employee_id', $user->id)
                ->where('month', $this->selectedMonth)
                ->where('year', $this->selectedYear)
                ->with('employee')
                ->get();
        } else {
            $this->staff = collect([]);
            $this->payrolls = collect([]);
        }
    }

    public function calculateNetPay()
    {
        $this->netPay = floatval($this->basicSalary) + floatval($this->bonuses) - floatval($this->deductions);
    }

    public function generatePayroll()
    {
        abort_unless(auth()->user()->canAccess('payroll_create'), 403);
        $this->reset(['employeeId', 'basicSalary', 'deductions', 'bonuses', 'netPay']);
        $this->isModalOpen = true;
    }

    public function savePayroll()
    {
        abort_unless(auth()->user()->canAccess('payroll_create'), 403);
        $this->validate([
            'employeeId' => 'required|exists:users,id',
            'basicSalary' => 'required|numeric|min:0',
            'deductions' => 'required|numeric|min:0',
            'bonuses' => 'required|numeric|min:0',
        ]);

        EmployeePayroll::updateOrCreate(
            [
                'employee_id' => $this->employeeId,
                'month' => $this->selectedMonth,
                'year' => $this->selectedYear,
            ],
            [
                'basic_salary' => $this->basicSalary,
                'deductions' => $this->deductions,
                'bonuses' => $this->bonuses,
                'net_pay' => $this->netPay,
                'status' => 'paid',
            ]
        );

        $this->isModalOpen = false;
        session()->flash('success', 'Payroll generated successfully for ' . Carbon::create()->month($this->selectedMonth)->format('F') . ' ' . $this->selectedYear);
        $this->loadData();
    }
    
    public function markAsPaid($payrollId)
    {
        abort_unless(auth()->user()->canAccess('payroll_status_update'), 403);
        EmployeePayroll::findOrFail($payrollId)->update(['status' => 'paid']);
        $this->loadData();
        session()->flash('success', 'Payment marked as paid.');
    }

    public function render()
    {
        return view('livewire.partner.hrms.payroll')
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Payroll Management',
                'pageSubtitle' => 'Manage employee salaries and payments',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
