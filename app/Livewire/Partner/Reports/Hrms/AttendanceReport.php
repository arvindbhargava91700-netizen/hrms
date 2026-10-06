<?php

namespace App\Livewire\Partner\Reports\Hrms;

use Livewire\Component;
use App\Models\EmployeeAttendance;
use App\Models\User;
use App\Models\HrmsBranch;
use Carbon\Carbon;
use Livewire\WithPagination;

class AttendanceReport extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $branchId = '';
    public $teamId = '';
    public $employeeId = '';
    public $statusFilter = '';
    public $selectedWorkingMode = '';

    protected $paginationTheme = 'bootstrap';

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

    public function updatedSelectedWorkingMode()
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
        $this->selectedWorkingMode = '';
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        
        $query = EmployeeAttendance::whereHas('employee', function($q) use ($partnerId) {
            $q->where('parent_id', $partnerId);

            if ($this->branchId) {
                $q->where('branch_id', $this->branchId);
            }

            if ($this->teamId) {
                $q->where('reporting_to', $this->teamId);
            }

            if ($this->selectedWorkingMode) {
                $q->where('working_mode', $this->selectedWorkingMode);
            }
        });
        
        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }
        
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('date', [$this->startDate, $this->endDate]);
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with(['employee.shift', 'employee.branch', 'employee.reportingTo'])->latest('date')->paginate(100);
    }

    /**
     * Aggregate totals across the full filtered set (not just the current page).
     */
    public function getReportSummaryProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $defaultRequiredMins = (function () use ($partnerId) {
            $val = \App\Models\PartnerSetting::where('partner_id', $partnerId)
                ->where('key', 'min_present_mins')
                ->value('value');

            return $val !== null ? (int) $val : 480;
        })();

        $rows = $this->getBaseQuery()->with('employee.shift')->get();

        $totalMinutes = 0;
        $requiredMinutes = 0;
        $overtimeMinutes = 0;
        $missedPunch = 0;

        foreach ($rows as $r) {
            $worked = (int) ($r->working_minutes ?? 0);

            $totalMinutes += $worked;

            $shift = $r->employee->shift ?? null;
            $req = ($shift && $shift->min_present_mins)
                ? (int) $shift->min_present_mins
                : $defaultRequiredMins;

            $requiredMinutes += $req;
            $overtimeMinutes += max(0, $worked - $req);

            if ($r->check_in && !$r->check_out) {
                $missedPunch++;
            }
        }

        return [
            'defaultRequiredMins' => $defaultRequiredMins,
            'totalHours'          => intdiv($totalMinutes, 60) . 'h ' . ($totalMinutes % 60) . 'm',
            'productiveHours'     => intdiv($requiredMinutes, 60) . 'h ' . ($requiredMinutes % 60) . 'm',
            'overtimeHours'       => intdiv($overtimeMinutes, 60) . 'h ' . ($overtimeMinutes % 60) . 'm',
            'missedPunch'         => $missedPunch,
        ];
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
        $data = $this->getBaseQuery()->with(['employee.branch', 'employee.reportingTo'])->latest('date')->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=attendance_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Date', 'Emp Code', 'Employee', 'Branch', 'Team / Reporting Manager', 'Check In', 'Check Out', 'Working Mins', 'Late Mins', 'Status'];
        
        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->date ? Carbon::parse($row->date)->format('Y-m-d') : '',
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    optional(optional($row->employee)->branch)->name ?: 'Main Branch',
                    optional(optional($row->employee)->reportingTo)->name ?: 'Direct / None',
                    $row->check_in ? \Carbon\Carbon::parse($row->check_in)->format('H:i:s') : '-',
                    $row->check_out ? \Carbon\Carbon::parse($row->check_out)->format('H:i:s') : '-',
                    $row->working_minutes,
                    $row->late_minutes,
                    $row->status
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.partner.reports.hrms.attendance-report', [
            'reportData'    => $this->reportData,
            'branches'      => $this->branches,
            'teams'         => $this->teams,
            'employees'     => $this->employees,
            'reportSummary' => $this->reportSummary,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Panel',
            'pageTitle'    => 'Attendance Report',
            'pageSubtitle' => 'View staff attendance details',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
