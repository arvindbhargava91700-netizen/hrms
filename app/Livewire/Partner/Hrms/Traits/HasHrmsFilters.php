<?php

namespace App\Livewire\Partner\Hrms\Traits;

use App\Models\User;
use App\Models\HrmsBranch;
use App\Models\Department;

trait HasHrmsFilters
{
    public $filterBranchId = '';
    public $filterDepartmentId = '';
    public $filterEmployeeSearch = '';

    public function applyFilters()
    {
        // This method can be called from the UI to trigger a re-render.
        if (method_exists($this, 'resetPage')) {
            $this->resetPage(); // If pagination is used, reset it.
        }
        
        // Also call loadData if the component relies on it instead of a query property
        if (method_exists($this, 'loadData')) {
            $this->loadData();
        }
    }

    public function clearFilters()
    {
        $this->filterBranchId = '';
        $this->filterDepartmentId = '';
        $this->filterEmployeeSearch = '';
        
        if (method_exists($this, 'resetPage')) {
            $this->resetPage(); // If pagination is used, reset it.
        }

        if (method_exists($this, 'loadData')) {
            $this->loadData();
        }
    }

    public function getFilterEmployees($viewAnyPermission = null)
    {
        $allowedIds = $this->getTeamEmployeeIds($viewAnyPermission);
        
        $query = User::whereIn('id', $allowedIds);
        
        if ($this->filterBranchId) {
            $query->where('branch_id', $this->filterBranchId);
        }
        
        if ($this->filterDepartmentId) {
            $query->where('department_id', $this->filterDepartmentId);
        }
        
        return $query->orderBy('name')->get();
    }

    public function getFilterBranches()
    {
        $query = HrmsBranch::query();

        if (!auth()->user()->isSuperAdmin()) {
            $query->where('partner_id', $this->getPartnerId());
        }

        return $query->orderBy('name')->get();
    }

    public function getFilterDepartments()
    {
        $query = Department::query();

        if (!auth()->user()->isSuperAdmin()) {
            $query->where('partner_id', $this->getPartnerId());
        }

        return $query->orderBy('name')->get();
    }

    public function getFilteredEmployeeIds($viewAnyPermission = null)
    {
        $allowedIds = collect($this->getTeamEmployeeIds($viewAnyPermission));

        if ($this->filterBranchId || $this->filterDepartmentId || $this->filterEmployeeSearch) {
            $userQuery = User::whereIn('id', $allowedIds);
            if ($this->filterBranchId) {
                $userQuery->where('branch_id', $this->filterBranchId);
            }
            if ($this->filterDepartmentId) {
                $userQuery->where('department_id', $this->filterDepartmentId);
            }
            if ($this->filterEmployeeSearch) {
                $search = $this->filterEmployeeSearch;
                $userQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('employee_code', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            $allowedIds = $userQuery->pluck('id');
        }
        
        return $allowedIds;
    }

    public function applyHrmsFilters($query, $employeeIdColumn = 'id', $viewAnyPermission = null)
    {
        $allowedIds = $this->getFilteredEmployeeIds($viewAnyPermission);

        $query->whereIn($employeeIdColumn, $allowedIds);

        return $query;
    }
}
