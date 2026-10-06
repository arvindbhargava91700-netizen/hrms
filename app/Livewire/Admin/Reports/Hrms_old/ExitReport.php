<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use App\Models\EmployeeExit;
use App\Models\User;
use Carbon\Carbon;
use Livewire\WithPagination;

class ExitReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';
    public $partnerFilter = '';


    public $startDate;
    public $endDate;
    public $employeeId = '';
    public $statusFilter = '';
    public $clearanceFilter = '';
    public $fnfFilter = '';
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
        $query = EmployeeExit::query();
        if ($this->partnerFilter) { $query->where('partner_id', $this->partnerFilter); }


        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->clearanceFilter) {
            $query->where('clearance_status', $this->clearanceFilter);
        }

        if ($this->fnfFilter) {
            $query->where('fnf_status', $this->fnfFilter);
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('resignation_date', [$this->startDate, $this->endDate]);
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('exit_reason', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
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
        return $this->getBaseQuery()->with(['employee', 'creator'])->latest('resignation_date')->paginate(20);
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
        $data = $this->getBaseQuery()->with(['employee', 'creator'])->latest('resignation_date')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=resignation_exit_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Emp ID', 'Employee Name', 'Resignation Date', 'Notice Period (Days)', 'Last Working Date', 'Exit Reason', 'Clearance Status', 'F&F Status', 'F&F Amount', 'Settlement Date', 'Exit Status'];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    $row->resignation_date ? $row->resignation_date->format('Y-m-d') : '-',
                    $row->notice_period_days,
                    $row->last_working_date ? $row->last_working_date->format('Y-m-d') : '-',
                    $row->exit_reason ?? '-',
                    ucwords(str_replace('_', ' ', $row->clearance_status)),
                    ucwords(str_replace('_', ' ', $row->fnf_status)),
                    $row->fnf_amount,
                    $row->fnf_settlement_date ? $row->fnf_settlement_date->format('Y-m-d') : '-',
                    ucwords(str_replace('_', ' ', $row->status))
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.admin.reports.hrms.exit-report', [
            'reportData' => $this->reportData,
            'employees'  => $this->employees,
        ])->layout('layouts.app', [
            'panelName'    => 'Admin Panel',
            'pageTitle'    => 'Resignation & Exit Report',
            'pageSubtitle' => 'Resignations, Notice Periods, Clearances & F&F Settlements Report',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
