<?php

namespace App\Livewire\Admin\Reports\Hrms;

use App\Models\EmployeeExit;
use App\Models\HrmsBranch;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class ExitReport extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public $startDate;

    public $endDate;

    public $branchId = '';

    public $teamId = '';

    public $employeeId = '';

    public $statusFilter = '';

    public $clearanceFilter = '';

    public $fnfFilter = '';

    public $search = '';

    public function mount()
    {
        // Initialize dates as null so the report shows all resignations by default.
        $this->startDate = null;
        $this->endDate = null;
    }

    public function updated()
    {
        $this->resetPage();
    }

    public function updatedBranchId()
    {
        $this->teamId = '';
        $this->employeeId = '';
        $this->resetPage();
    }

    public function updatedTeamId()
    {
        $this->employeeId = '';
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $query = EmployeeExit::query();

        if ($this->branchId) {
            $query->whereHas('employee', function ($q) {
                $q->where('branch_id', $this->branchId);
            });
        }

        if ($this->teamId) {
            $query->whereHas('employee', function ($q) {
                $q->where('reporting_to', $this->teamId);
            });
        }

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
        } else {
            // When no explicit date range is supplied, ensure we only list employees who have a resignation date
            $query->whereNotNull('resignation_date');
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
        // Show only inactive employees (resigned / exited employees are marked inactive)
        $query = User::where('role', 'employee')->where('status', 'inactive');

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        if ($this->teamId) {
            $query->where('reporting_to', $this->teamId);
        }

        return $query->orderBy('name')->get();
    }

    public function getBranchesProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        return HrmsBranch::where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function getTeamsProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $reportingQuery = User::when($partnerId, fn ($q) => $q->where('parent_id', $partnerId))
            ->where('role', 'employee')
            ->whereNotNull('reporting_to');

        if ($this->branchId) {
            $reportingQuery->where('branch_id', $this->branchId);
        }

        $leadIds = $reportingQuery->pluck('reporting_to')->unique();

        if ($leadIds->isNotEmpty()) {
            return User::whereIn('id', $leadIds)->orderBy('name')->get();
        }

        return User::when($partnerId, fn ($q) => $q->where('parent_id', $partnerId))
            ->where('role', 'employee')
            ->whereHas('reportees')
            ->orderBy('name')
            ->get();
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['employee', 'creator'])->latest('resignation_date')->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=resignation_exit_report.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
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
                    ucwords(str_replace('_', ' ', $row->status)),
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
            'employees' => $this->employees,
            'branches' => $this->branches,
            'teams' => $this->teams,
        ])->layout('layouts.app', [
            'panelName' => 'HRMS Panel',
            'pageTitle' => 'Resignation & Exit Report',
            'pageSubtitle' => 'Resignations, Notice Periods, Clearances & F&F Settlements Report',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
