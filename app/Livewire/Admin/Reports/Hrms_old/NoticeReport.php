<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use App\Models\Notice;
use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Livewire\WithPagination;

class NoticeReport extends Component
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
        return Notice::query();
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->latest()->paginate(20);
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
        $data = $this->getBaseQuery()->latest()->get();
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=notice_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['ID', 'Title', 'Type', 'Start Date', 'End Date', 'Created At'];
        
        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($data as $row) {
                fputcsv($file, $this->mapCsvRow($row));
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.admin.reports.hrms.notice-report', [
            'reportData' => $this->reportData,
            'departments' => $this->departments,
            'roles' => $this->roles,
        ])->layout('layouts.app', [
            'panelName'    => 'Admin Panel',
            'pageTitle'    => 'Notice Report',
            'pageSubtitle' => 'View staff details and performance',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
