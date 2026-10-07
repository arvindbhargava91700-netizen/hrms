<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use App\Livewire\Partner\Hrms\Traits\HasHrmsFilters;

class Staff extends Component
{
    use HasPartnerId, HasHrmsFilters;
    public $staff = [];
    public $roles = [];
    public $departments = [];
    public $branches = [];
    public $shifts = [];
    
    public $staffName, $staffEmail, $staffMobile, $staffEmployeeCode, $staffPassword, $staffRoleId, $staffSalary, $staffDepartmentId, $staffReportingTo;
    public $staffBranchId, $staffShiftId, $staffJoiningDate, $staffResignationDate, $staffTerminationDate, $staffEmploymentStatus = 'active';
    public $staffWorkingMode = 'office';
    public $staffEmploymentType = 'full_time';
    public $editingStaffId = null;
    
    public $isStaffModalOpen = false;

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->isAdmin() || auth()->user()->canAccess('staff_viewAny') || auth()->user()->canAccess('staff_viewOwn')|| auth()->user()->canAccess('staff_viewBranch') || auth()->user()->canAccess('staff_viewteam'), 403);
        $this->loadData();
    }

    public function loadData()
    {
        $partnerId = $this->getPartnerId();
        
        $query = User::with(['roles', 'department', 'manager', 'branch', 'shift'])
            ->whereNotIn('role', ['super_admin', 'admin'])
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['super_admin', 'admin']);
            });
        $query = $this->applyHrmsFilters($query, 'id', 'staff_viewAny');
        $this->staff = $query->latest()->get();
        // Partner roles are namespaced
        $this->roles = Role::whereNotIn('name', ['super_admin', 'admin'])->get();
        $this->departments = Department::orderBy('sort_order','asc')->get();
        $this->branches = \App\Models\HrmsBranch::all();
        $this->shifts = \App\Models\WorkShift::all();
    }

    public function createStaff()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->isAdmin() || auth()->user()->canAccess('staff_create'), 403);
        $this->reset(['editingStaffId', 'staffName', 'staffEmail', 'staffMobile', 'staffEmployeeCode', 'staffPassword', 'staffSalary', 'staffRoleId', 'staffDepartmentId', 'staffReportingTo', 'staffBranchId', 'staffShiftId', 'staffJoiningDate', 'staffResignationDate', 'staffTerminationDate']);
        $this->staffEmploymentStatus = 'active';
        $this->staffWorkingMode = 'office';
        $this->staffEmploymentType = 'full_time';
        $this->updateShiftsList(null);
        $this->isStaffModalOpen = true;
    }

    public function editStaff($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->isAdmin() || auth()->user()->canAccess('staff_update'), 403);
        $employee = User::findOrFail($id);
        $this->editingStaffId = $employee->id;
        $this->staffName = $employee->name;
        $this->staffEmail = $employee->email;
        $this->staffMobile = $employee->mobile;
        $this->staffEmployeeCode = $employee->employee_code;
        $this->staffSalary = $employee->basic_salary;
        $this->staffDepartmentId = $employee->department_id;
        $this->staffReportingTo = $employee->reporting_to;
        $this->staffBranchId = $employee->branch_id;
        $this->updateShiftsList($this->staffBranchId);
        $this->staffShiftId = $employee->shift_id;
        $this->staffJoiningDate = $employee->joining_date;
        $this->staffResignationDate = $employee->resignation_date;
        $this->staffTerminationDate = $employee->termination_date;
        $this->staffEmploymentStatus = $employee->employment_status ?: 'active';
        $this->staffWorkingMode = $employee->working_mode ?: 'office';
        $this->staffEmploymentType = $employee->employment_type ?: 'full_time';
        $this->staffPassword = ''; // Leave blank unless they want to change it
        
        $partnerRole = $employee->roles->first();
        
        $this->staffRoleId = $partnerRole ? $partnerRole->id : null;
        
        $this->isStaffModalOpen = true;
    }

    public function updatedStaffBranchId($value)
    {
        $this->updateShiftsList($value);
        if ($this->staffShiftId) {
            $validShiftIds = $this->shifts->pluck('id')->toArray();
            if (!in_array($this->staffShiftId, $validShiftIds)) {
                $this->staffShiftId = null;
            }
        }
    }

    private function updateShiftsList($branchId)
    {
        if ($branchId) {
            $this->shifts = \App\Models\WorkShift::where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                })->get();
        } else {
            $this->shifts = \App\Models\WorkShift::all();
        }
    }

    public function saveStaff()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess($this->editingStaffId ? 'staff_update' : 'staff_create'), 403);
        $rules = [
            'staffName' => 'required|string|max:255',
            'staffEmail' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->editingStaffId)],
            'staffMobile' => ['required', 'string', Rule::unique('users', 'mobile')->ignore($this->editingStaffId)],
            'staffEmployeeCode' => ['nullable', 'string', 'max:50', Rule::unique('users', 'employee_code')->ignore($this->editingStaffId)],
            'staffSalary' => 'nullable|numeric|min:0',
            'staffRoleId' => 'required|exists:roles,id',
            'staffDepartmentId' => 'nullable|exists:departments,id',
            'staffReportingTo' => 'nullable|exists:users,id',
            'staffBranchId' => 'nullable|exists:branches,id',
            'staffShiftId' => 'nullable|exists:work_shifts,id',
            'staffJoiningDate' => 'nullable|date',
            'staffResignationDate' => 'nullable|date|after_or_equal:staffJoiningDate',
            'staffTerminationDate' => 'nullable|date|after_or_equal:staffJoiningDate',
            'staffEmploymentStatus' => 'required|in:active,resigned,terminated',
            'staffWorkingMode' => 'required|in:office,remote,field',
            'staffEmploymentType' => 'required|in:full_time,part_time,contract'
        ];
        
        if (!$this->editingStaffId) {
            $rules['staffPassword'] = 'required|min:6';
        }
    
        $this->validate($rules);

        $data = [
            'name' => $this->staffName,
            'email' => $this->staffEmail,
            'mobile' => $this->staffMobile,
            'employee_code' => $this->staffEmployeeCode ?: null,
            'role' => 'employee',
            'parent_id' => $this->getPartnerId(),
            'basic_salary' => $this->staffSalary,
            'department_id' => $this->staffDepartmentId ?: null,
            'reporting_to' => $this->staffReportingTo ?: null,
            'branch_id' => $this->staffBranchId ?: null,
            'shift_id' => $this->staffShiftId ?: null,
            'joining_date' => $this->staffJoiningDate ?: null,
            'resignation_date' => $this->staffResignationDate ?: null,
            'termination_date' => $this->staffTerminationDate ?: null,
            'employment_status' => $this->staffEmploymentStatus,
            'status' => $this->staffEmploymentStatus === 'active' ? 'active' : 'inactive', // Automatically set status based on employment
            'working_mode' => $this->staffWorkingMode,
            'employment_type' => $this->staffEmploymentType,
        ];
        
        if ($this->staffPassword) {
            $data['password'] = Hash::make($this->staffPassword);
        }

        if ($this->editingStaffId) {
            $employee = User::findOrFail($this->editingStaffId);
            $employee->update($data);
        } else {
            $employee = User::create($data);
        }

        $role = Role::findOrFail($this->staffRoleId);
        $employee->syncRoles([$role->name]);

        $this->isStaffModalOpen = false;
        session()->flash('success', 'Staff member saved successfully.');
        $this->loadData();
    }

    public function deleteStaff($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->isAdmin() || auth()->user()->canAccess('staff_delete'), 403);
        User::findOrFail($id)->delete();
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.partner.hrms.staff')
            ->layout('layouts.app', [
                'panelName'    => 'HRMS Module',
                'pageTitle'    => 'Staff Management',
                'pageSubtitle' => 'Manage your employees and assign roles',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
