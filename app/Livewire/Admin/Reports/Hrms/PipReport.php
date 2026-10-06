<?php

namespace App\Livewire\Admin\Reports\Hrms;

use App\Models\EmployeePip;
use App\Models\HrmsBranch;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class PipReport extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public $startDate;

    public $endDate;

    public $branchId = '';

    public $teamId = '';

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

        $query = EmployeePip::when($partnerId, fn ($q) => $q->where('partner_id', $partnerId));

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
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $query = User::when($partnerId, fn ($q) => $q->where('parent_id', $partnerId))->where('role', 'employee');

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

        return HrmsBranch::when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))
            ->where('status', 'active')
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
        $data = $this->getBaseQuery()->with(['employee', 'creator'])->latest('start_date')->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=pip_report.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
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
                    $row->review_result ?? '-',
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
            'employees' => $this->employees,
            'branches' => $this->branches,
            'teams' => $this->teams,
        ])->layout('layouts.app', [
            'panelName' => 'HRMS Panel',
            'pageTitle' => 'PIP Report',
            'pageSubtitle' => 'Performance Improvement Plans & Evaluation Outcomes Report',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
