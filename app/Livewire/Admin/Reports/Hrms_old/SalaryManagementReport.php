<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use App\Models\EmployeePayroll;
use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Livewire\WithPagination;

class SalaryManagementReport extends Component
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
        return view('livewire.admin.reports.hrms.salary-management-report', [
            'reportData' => $this->reportData,
            'departments' => $this->departments,
            'roles' => $this->roles,
        ])->layout('layouts.app', [
            'panelName'    => 'Admin Panel',
            'pageTitle'    => 'Salary Management Report',
            'pageSubtitle' => 'View staff details and performance',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }


    public function getAllowances($breakdown)
    {
        if (is_array($breakdown)) {
            return array_sum(array_column($breakdown, 'amount'));
        }
        $decoded = json_decode($breakdown, true);
        if (is_array($decoded)) {
            return array_sum(array_column($decoded, 'amount'));
        }
        return 0;
    }
    
}
