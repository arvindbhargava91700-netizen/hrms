<?php

namespace App\Livewire\Partner\Reports\Hrms;

use Livewire\Component;
use App\Models\EmployeeLeave;
use App\Models\User;
use App\Models\HrmsBranch;
use Carbon\Carbon;
use Livewire\WithPagination;

class LeaveReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $startDate;
    public $endDate;
    public $branchId = '';
    public $teamId = '';
    public $employeeId = '';
    public $statusFilter = '';

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
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

    public function updatedEmployeeId()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedStartDate()
    {
        $this->resetPage();
    }

    public function updatedEndDate()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->branchId = '';
        $this->teamId = '';
        $this->employeeId = '';
        $this->statusFilter = '';
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        
        $query = EmployeeLeave::whereHas('employee', function($q) use ($partnerId) {
            $q->where('parent_id', $partnerId);

            if ($this->branchId) {
                $q->where('branch_id', $this->branchId);
            }

            if ($this->teamId) {
                $q->where('reporting_to', $this->teamId);
            }
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
        return $this->getBaseQuery()->with(['employee.branch', 'employee.reportingTo'])->latest()->paginate(20);
    }

    public function getBranchesProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        return HrmsBranch::where('partner_id', $partnerId)->where('status', 'active')->orderBy('name')->get();
    }

    public function getTeamsProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        
        $reportingQuery = User::where('parent_id', $partnerId)
            ->where('role', 'employee')
            ->whereNotNull('reporting_to');
            
        if ($this->branchId) {
            $reportingQuery->where('branch_id', $this->branchId);
        }
        
        $leadIds = $reportingQuery->pluck('reporting_to')->unique();

        if ($leadIds->isNotEmpty()) {
            return User::whereIn('id', $leadIds)->orderBy('name')->get();
        }

        return User::where('parent_id', $partnerId)
            ->where('role', 'employee')
            ->whereHas('reportees')
            ->orderBy('name')
            ->get();
    }

    public function getEmployeesProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $query = User::where('parent_id', $partnerId)->where('role', 'employee');

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        if ($this->teamId) {
            $query->where('reporting_to', $this->teamId);
        }

        return $query->orderBy('name')->get();
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['employee.branch', 'employee.reportingTo'])->latest()->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=leave_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Emp ID', 'Employee', 'Branch', 'Team / Reporting Manager', 'Leave Type', 'Start Date', 'End Date', 'Reason', 'Duration (Days)', 'Status'];
        
        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    optional(optional($row->employee)->branch)->name ?: 'Main Branch',
                    optional(optional($row->employee)->reportingTo)->name ?: 'Direct / None',
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

    public function render()
    {
        return view('livewire.partner.reports.hrms.leave-report', [
            'reportData' => $this->reportData,
            'branches'   => $this->branches,
            'teams'      => $this->teams,
            'employees'  => $this->employees,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Panel',
            'pageTitle'    => 'Leave Report',
            'pageSubtitle' => 'View staff leave requests and history',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
