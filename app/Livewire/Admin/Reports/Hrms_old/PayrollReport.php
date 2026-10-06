<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use App\Models\EmployeePayroll;
use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Livewire\WithPagination;

class PayrollReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $search = '';
    public $partnerFilter = '';
    public $departmentId = '';
    public $roleName = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedDepartmentId()
    {
        $this->resetPage();
    }

    public function updatedRoleName()
    {
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        return EmployeePayroll::query()->whereHas('employee', function($q) { $q; });
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with(['employee'])->latest()->paginate(20);
    }

    public function getDepartmentsProperty()
    {
        return Department::get();
    }

    public function getRolesProperty()
    {
        return Role::query()->get();
    }

    

    public function render()
    {
        return view('livewire.admin.reports.hrms.payroll-report', [
            'reportData' => $this->reportData,
            'departments' => $this->departments,
            'roles' => $this->roles,
        ])->layout('layouts.app', [
            'panelName'    => 'Admin Panel',
            'pageTitle'    => 'Payroll Report',
            'pageSubtitle' => 'View staff details and performance',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }


    public function getStats($employeeId, $month, $year)
    {
        $presents = \App\Models\EmployeeAttendance::where('employee_id', $employeeId)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'present')->count();
        $absents = \App\Models\EmployeeAttendance::where('employee_id', $employeeId)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'absent')->count();
        $leaves = \App\Models\EmployeeLeave::where('employee_id', $employeeId)->whereMonth('start_date', $month)->whereYear('start_date', $year)->where('status', 'approved')->count();
        $expenses = \App\Models\Expense::where('employee_id', $employeeId)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'approved')->sum('amount');
        
        return [
            'presents' => $presents,
            'absents' => $absents,
            'leaves' => $leaves,
            'expenses' => $expenses
        ];
    }
    
}
