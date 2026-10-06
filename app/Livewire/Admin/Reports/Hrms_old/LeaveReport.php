<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use App\Models\EmployeeLeave;
use App\Models\User;
use Carbon\Carbon;
use Livewire\WithPagination;

class LeaveReport extends Component
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
        $query = EmployeeLeave::whereHas('employee', function($q) {
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
            "Content-Disposition" => "attachment; filename=leave_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Emp ID', 'Employee', 'Leave Type', 'Start Date', 'End Date', 'Reason', 'Duration (Days)', 'Status'];
        
        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    $row->leave_type,
                    $row->start_date ? Carbon::parse($row->start_date)->format('Y-m-d') : '',
                    $row->end_date ? Carbon::parse($row->end_date)->format('Y-m-d') : '',
                    $row->reason,
                    $row->start_date && $row->end_date ? Carbon::parse($row->start_date)->diffInDays(Carbon::parse($row->end_date)) + 1 : 0,
                    $row->status
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
        return view('livewire.admin.reports.hrms.leave-report', [
            'reportData' => $this->reportData,
            'employees'  => $this->employees,
        ])->layout('layouts.app', [
            'panelName'    => 'Admin Panel',
            'pageTitle'    => 'Leave Report',
            'pageSubtitle' => 'View staff leave requests and history',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
