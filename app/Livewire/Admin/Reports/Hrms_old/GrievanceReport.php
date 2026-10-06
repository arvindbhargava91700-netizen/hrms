<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use App\Models\EmployeeGrievance;
use App\Models\User;
use Carbon\Carbon;
use Livewire\WithPagination;

class GrievanceReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';
    public $partnerFilter = '';


    public $startDate;
    public $endDate;
    public $employeeId = '';
    public $typeFilter = '';
    public $statusFilter = '';
    public $search = '';

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
        $query = EmployeeGrievance::query();
        if ($this->partnerFilter) { $query->where('partner_id', $this->partnerFilter); }


        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }

        if ($this->typeFilter) {
            $query->where('record_type', $this->typeFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with(['employee', 'creator'])->latest()->paginate(20);
    }

    public function getEmployeesProperty()
    {
        return User::where(function ($q) {
            $q
              ->orWhere('id', "");
        })->orderBy('name')->get();
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['employee', 'creator'])->latest()->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=grievance_discipline_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Emp ID', 'Employee Name', 'Record Type', 'Case Title', 'Incident Date', 'Description', 'Action Taken / Warning', 'Resolution Outcome', 'Status'];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    ucwords(str_replace('_', ' ', $row->record_type)),
                    $row->title,
                    $row->incident_date ? $row->incident_date->format('Y-m-d') : '-',
                    $row->description ?? '-',
                    $row->action_taken ?? '-',
                    $row->resolution_notes ?? '-',
                    ucwords(str_replace('_', ' ', $row->status))
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.admin.reports.hrms.grievance-report', [
            'reportData' => $this->reportData,
            'employees'  => $this->employees,
        ])->layout('layouts.app', [
            'panelName'    => 'Admin Panel',
            'pageTitle'    => 'Grievance & Discipline Report',
            'pageSubtitle' => 'Complaints, Official Warnings, Show-Cause Notices & Disciplinary Actions Report',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
