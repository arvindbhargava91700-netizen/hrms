<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use App\Models\EmployeeTask;
use App\Models\User;
use Carbon\Carbon;
use Livewire\WithPagination;

class TaskReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $startDate;
    public $endDate;
    public $employeeId = '';
    public $statusFilter = '';


    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updated()
    {
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $query = EmployeeTask::whereHas('employee', function($q) {
            $q;
        });
        
        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with('employee')->latest()->paginate(20);
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with('employee')->latest()->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=task_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Task', 'Emp ID', 'Assigned To', 'Priority', 'Description', 'Due Date', 'Status', 'Created At'];
        
        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->title,
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    $row->priority,
                    $row->description,
                    $row->due_date ? Carbon::parse($row->due_date)->format('Y-m-d') : '',
                    $row->status,
                    $row->created_at->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function getEmployeesProperty()
    {
        return User::where('role', 'employee')->get();
    }

    public function render()
    {
        return view('livewire.admin.reports.hrms.task-report', [
            'reportData' => $this->reportData,
            'employees'  => $this->employees,
        ])->layout('layouts.app', [
            'panelName'    => 'Admin Panel',
            'pageTitle'    => 'Task Report',
            'pageSubtitle' => 'View staff tasks and completion status',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
