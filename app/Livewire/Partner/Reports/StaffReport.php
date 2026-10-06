<?php

namespace App\Livewire\Partner\Reports;

use Livewire\Component;
use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Livewire\WithPagination;

class StaffReport extends Component
{
    use WithPagination;

    public $search = '';
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
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $query = User::where('role', 'employee');
        if ($isSuperAdmin) {
            $query->whereIn('parent_id', User::where('role', 'partner')->select('id'));
        } else {
            $query->where('parent_id', $partnerId);
        }
        
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
        if (auth()->user()->role === 'super_admin') {
            return Department::whereIn('partner_id', User::where('role', 'partner')->select('id'))->get();
        }

        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        return Department::where('partner_id', $partnerId)->get();
    }

    public function getRolesProperty()
    {
        if (auth()->user()->role === 'super_admin') {
            $partnerIds = User::where('role', 'partner')->pluck('id');
            if ($partnerIds->isEmpty()) {
                return collect();
            }

            return Role::where(function ($query) use ($partnerIds) {
                foreach ($partnerIds as $partnerId) {
                    $query->orWhere('name', 'like', '%' . $partnerId . '%');
                }
            })->get();
        }

        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        return Role::where('name', 'like', '%' . $partnerId . '%')->get();
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['department', 'roles'])->latest()->get();
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=staff_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Emp ID', 'Name', 'Email', 'Mobile', 'Role', 'Department', 'Basic Salary', 'Wallet Balance', 'Status'];
        
        $callback = function() use($data, $columns, $partnerId, $isSuperAdmin) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                $rolePartnerId = $isSuperAdmin ? $row->parent_id : $partnerId;
                $roleName = $row->roles->first() ? $row->roles->first()->name : '-';
                if ($roleName !== '-' && $rolePartnerId) {
                    $roleName = str_replace([$rolePartnerId . '_', '_' . $rolePartnerId], '', $roleName);
                }
                fputcsv($file, [
                    $row->employee_code ?? substr($row->id, 0, 8),
                    $row->name,
                    $row->email,
                    $row->mobile,
                    $roleName,
                    optional($row->department)->name,
                    $row->basic_salary,
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
        return view('livewire.partner.reports.staff-report', [
            'reportData' => $this->reportData,
            'departments' => $this->departments,
            'roles' => $this->roles,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Panel',
            'pageTitle'    => 'Staff Report',
            'pageSubtitle' => 'View staff details and performance',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
