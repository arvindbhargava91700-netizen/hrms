<?php

namespace App\Livewire\Partner\Hrms\Payroll;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\EmployeeSalaryStructure;

use App\Livewire\Partner\Hrms\Traits\HasHrmsFilters;

class SalaryManagement extends Component
{
    use WithPagination, \App\Livewire\Partner\Hrms\HasPartnerId, HasHrmsFilters;
    
    protected $paginationTheme = 'bootstrap';

    public $editingEmployeeId = null;
    public $employeeName = '';
    public $viewMode = false;
    
    public $salary_type = 'base_plus_target';
    public $monthly_target = 0;
    public $merchant_target = 0;
    public $commission_percent = 0;
    public $recovery_percent = 0;
    public $commission_level_id = null;
    
    public $basic_salary = 0;
    
    public $allowances = [
        'hra' => 0,
        'da' => 0,
        'conveyance' => 0,
        'medical' => 0,
        'special' => 0,
        'travel' => 0,
        'internet' => 0,
        'food' => 0,
        'performance_incentive' => 0,
        'sales_incentive' => 0,
        'bonus' => 0,
        'overtime' => 0,
        'shift' => 0,
        'other' => 0,
    ];

    public $deductions = [
        'pf' => 0,
        'esi' => 0,
        'pt' => 0,
        'tds' => 0,
        'lwf' => 0,
        'notice_period' => 0,
        'other' => 0,
    ];

    public $showModal = false;

    public function getTotalAllowancesProperty()
    {
        return array_sum(array_map('floatval', $this->allowances ?? []));
    }

    public function getTotalIncentivesProperty()
    {
        $perf = floatval($this->allowances['performance_incentive'] ?? 0);
        $sales = floatval($this->allowances['sales_incentive'] ?? 0);
        return $perf + $sales;
    }

    public function getTotalDeductionsProperty()
    {
        return array_sum(array_map('floatval', $this->deductions ?? []));
    }

    public function getGrossSalaryProperty()
    {
        return floatval($this->basic_salary ?: 0) + $this->totalAllowances;
    }

    public function getNetSalaryProperty()
    {
        return max(0, $this->grossSalary - $this->totalDeductions);
    }

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('salary_viewAny') || auth()->user()->canAccess('salary_viewBranch') || auth()->user()->canAccess('salary_viewteam') || auth()->user()->canAccess('salary_viewOwn'), 403);
    }

    private function canAccessEmployee($employeeId)
    {
        if (auth()->user()->isPartner() || auth()->user()->canAccess('salary_viewAny')) {
            return true;
        }
        if (auth()->user()->canAccess('salary_viewBranch') || auth()->user()->canAccess('salary_viewteam')) {
            return in_array($employeeId, $this->getTeamEmployeeIds('salary_viewAny'));
        }
        return $employeeId == auth()->id();
    }

    public function editSalary($employeeId)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('salary_update'), 403);
        $employee = User::findOrFail($employeeId);
        
        abort_unless($this->canAccessEmployee($employee->id), 403);

        $this->editingEmployeeId = $employeeId;
        $this->employeeName = $employee->name;
        $this->viewMode = false;
        
        $structure = EmployeeSalaryStructure::where('employee_id', $employeeId)->first();
        
        $this->basic_salary = $structure ? $structure->basic_salary : ($employee->basic_salary ?: 0);
        $this->salary_type = $structure ? $structure->salary_type : 'base_plus_target';
        $this->monthly_target = $structure ? $structure->monthly_target : 0;
        $this->merchant_target = $structure ? $structure->merchant_target : 0;
        $this->commission_percent = $structure ? $structure->commission_percent : 0;
        $this->recovery_percent = $structure ? $structure->recovery_percent : 0;
        $this->commission_level_id = $structure ? $structure->commission_level_id : null;
        
        // Reset and populate
        $this->resetArrays();
        
        if ($structure) {
            if (is_array($structure->allowances)) {
                foreach ($structure->allowances as $key => $val) {
                    if (array_key_exists($key, $this->allowances)) {
                        $this->allowances[$key] = $val;
                    }
                }
            }
            if (is_array($structure->deductions)) {
                foreach ($structure->deductions as $key => $val) {
                    if (array_key_exists($key, $this->deductions)) {
                        $this->deductions[$key] = $val;
                    }
                }
            }
        }
        
        $this->showModal = true;
    }

    public function viewSalary($employeeId)
    {
        $employee = User::findOrFail($employeeId);
        
        abort_unless($this->canAccessEmployee($employee->id), 403);

        $this->editingEmployeeId = $employeeId;
        $this->employeeName = $employee->name;
        $this->viewMode = true;
        
        $structure = EmployeeSalaryStructure::where('employee_id', $employeeId)->first();
        
        $this->basic_salary = $structure ? $structure->basic_salary : ($employee->basic_salary ?: 0);
        $this->salary_type = $structure ? $structure->salary_type : 'base_plus_target';
        $this->monthly_target = $structure ? $structure->monthly_target : 0;
        $this->merchant_target = $structure ? $structure->merchant_target : 0;
        $this->commission_percent = $structure ? $structure->commission_percent : 0;
        $this->recovery_percent = $structure ? $structure->recovery_percent : 0;
        $this->commission_level_id = $structure ? $structure->commission_level_id : null;
        
        // Reset and populate
        $this->resetArrays();
        
        if ($structure) {
            if (is_array($structure->allowances)) {
                foreach ($structure->allowances as $key => $val) {
                    if (array_key_exists($key, $this->allowances)) {
                        $this->allowances[$key] = $val;
                    }
                }
            }
            if (is_array($structure->deductions)) {
                foreach ($structure->deductions as $key => $val) {
                    if (array_key_exists($key, $this->deductions)) {
                        $this->deductions[$key] = $val;
                    }
                }
            }
        }
        
        $this->showModal = true;
    }

    private function resetArrays()
    {
        foreach ($this->allowances as $key => $val) {
            $this->allowances[$key] = 0;
        }
        foreach ($this->deductions as $key => $val) {
            $this->deductions[$key] = 0;
        }
    }

    public function closeEdit()
    {
        $this->showModal = false;
        $this->editingEmployeeId = null;
    }

    public function saveSalary()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('salary_update'), 403);
        $this->validate([
            'basic_salary' => 'required|numeric|min:0',
        ]);

        $employee = User::findOrFail($this->editingEmployeeId);
        
        abort_unless($this->canAccessEmployee($employee->id), 403);

        $gross = floatval($this->basic_salary);
        foreach ($this->allowances as $val) {
            $gross += floatval($val ?: 0);
        }

        $totalDed = 0;
        foreach ($this->deductions as $val) {
            $totalDed += floatval($val ?: 0);
        }

        $net = $gross - $totalDed;

        EmployeeSalaryStructure::updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'salary_type' => $this->salary_type,
                'monthly_target' => $this->monthly_target,
                'merchant_target' => $this->merchant_target,
                'commission_percent' => $this->commission_percent,
                'recovery_percent' => $this->recovery_percent,
                'commission_level_id' => $this->commission_level_id ?: null,
                'basic_salary' => $this->basic_salary,
                'allowances' => $this->allowances,
                'deductions' => $this->deductions,
                'gross_salary' => $gross,
                'net_salary' => $net,
            ]
        );

        // Also update the fallback basic_salary on User model just in case
        $employee->update(['basic_salary' => $this->basic_salary]);
        
        session()->flash('message', "Salary structure updated for {$employee->name}");
        
        $this->closeEdit();
    }

    public function render()
    {
        
        $query = User::query();
        $query = $this->applyHrmsFilters($query, 'id', 'salary_viewAny');
        $employees = $query->orderBy('name')->paginate(10);
            
        $employeeIds = $employees->pluck('id')->toArray();
        $structures = EmployeeSalaryStructure::whereIn('employee_id', $employeeIds)
            ->get()
            ->keyBy('employee_id');
            
        $commissionLevels = \App\Models\CommissionLevel::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->orderBy('level_order', 'asc')->get();

        return view('livewire.partner.hrms.payroll.salary-management', [
            'employees' => $employees,
            'structures' => $structures,
            'commissionLevels' => $commissionLevels
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Salary Management',
            'pageSubtitle' => 'Manage employee salary structures',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
