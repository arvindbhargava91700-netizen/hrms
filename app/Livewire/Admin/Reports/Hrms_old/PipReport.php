<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use App\Models\EmployeePip;
use App\Models\User;
use Carbon\Carbon;
use Livewire\WithPagination;

class PipReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';
    public $partnerFilter = '';


    public $startDate;
    public $endDate;
    public $employeeId = '';
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
        $query = EmployeePip::query();
        if ($this->partnerFilter) { $query->where('partner_id', $this->partnerFilter); }


        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('start_date', [$this->startDate, $this->endDate]);
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('improvement_targets', 'like', "%{$search}%")
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
        return $this->getBaseQuery()->with(['employee', 'creator'])->latest('start_date')->paginate(20);
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
        $data = $this->getBaseQuery()->with(['employee', 'creator'])->latest('start_date')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=pip_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Emp ID', 'Employee Name', 'Reason / Targets', 'Start Date', 'End Date', 'Status', 'Review Outcome'];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    $row->reason,
                    $row->start_date ? $row->start_date->format('Y-m-d') : '',
                    $row->end_date ? $row->end_date->format('Y-m-d') : '',
                    ucwords(str_replace('_', ' ', $row->status)),
                    $row->review_result ?? '-'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.admin.reports.hrms.pip-report', [
            'reportData' => $this->reportData,
            'employees'  => $this->employees,
        ])->layout('layouts.app', [
            'panelName'    => 'Admin Panel',
            'pageTitle'    => 'PIP Report',
            'pageSubtitle' => 'Performance Improvement Plans & Evaluation Outcomes Report',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
