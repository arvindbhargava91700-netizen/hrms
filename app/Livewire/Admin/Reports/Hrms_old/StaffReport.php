<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Livewire\WithPagination;

class StaffReport extends Component
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
        $query = User::where('role', 'employee');
        
        if ($this->partnerFilter) { $query->where(function($q) { $q->where('partner_id', $this->partnerFilter)->orWhere('parent_id', $this->partnerFilter); }); }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->departmentId) {
            $query->where('department_id', $this->departmentId);
        }

        if ($this->roleName) {
            $query->role($this->roleName);
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with(['department', 'roles'])->latest()->paginate(20);
    }

    public function getDepartmentsProperty()
    {
        return Department::get();
    }

    public function getRolesProperty()
    {
        return Role::query()->get();
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['department', 'roles'])->latest()->get();
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=staff_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Emp ID', 'Name', 'Email', 'Mobile', 'Role', 'Department', 'Employment Type', 'Basic Salary', 'Joining Date', 'Wallet Balance', 'Status'];
        
        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                $roleName = $row->roles->first() ? preg_replace("/^[0-9]+_/", "", $row->roles->first()->name) : '-';
                fputcsv($file, [
                    $row->employee_code ?? substr($row->id, 0, 8),
                    $row->name,
                    $row->email,
                    $row->mobile,
                    $roleName,
                    optional($row->department)->name,
                    ucfirst(str_replace('_', ' ', $row->employment_type ?? 'full_time')),
                    $row->basic_salary,
                    $row->joining_date ?? '-',
                    $row->wallet_balance,
                    $row->status
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.admin.reports.hrms.staff-report', [
            'reportData' => $this->reportData,
            'departments' => $this->departments,
            'roles' => $this->roles,
        ])->layout('layouts.app', [
            'panelName'    => 'Admin Panel',
            'pageTitle'    => 'Staff Report',
            'pageSubtitle' => 'View staff details and performance',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
