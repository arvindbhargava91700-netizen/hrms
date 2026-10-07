<?php

namespace App\Livewire\Partner\Reports\Hrms;

use Livewire\Component;
use App\Models\EmployeeAttendance;
use App\Models\User;
use App\Models\HrmsBranch;
use App\Livewire\Partner\Hrms\HasPartnerId;
use Carbon\Carbon;
use Livewire\WithPagination;

class AttendanceReport extends Component
{
    use WithPagination;
    use HasPartnerId;

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
        $allowedIds = $this->getTeamEmployeeIds('attendance_viewAny');

        $query = EmployeeAttendance::whereIn('employee_id', $allowedIds);

        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        } else {
            if ($this->branchId || $this->teamId || $this->selectedWorkingMode) {
                $query->whereHas('employee', function ($q) {
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
            }
        }

        if ($this->statusFilter) {
            if ($this->statusFilter === 'present') {
                $query->whereIn('status', ['punch_out', 'punch_in', 'present']);
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('date', [$this->startDate, $this->endDate]);
        } elseif ($this->startDate) {
            $query->whereDate('date', '>=', $this->startDate);
        } elseif ($this->endDate) {
            $query->whereDate('date', '<=', $this->endDate);
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with(['employee.shift', 'employee.branch', 'employee.reportingTo'])->latest('date')->paginate(100);
    }

    /**
     * Aggregate totals across the full filtered set.
     */
    public function getReportSummaryProperty()
    {
        $partnerId = $this->getPartnerId();

        $defaultRequiredMins = (function () use ($partnerId) {
            $val = \App\Models\PartnerSetting::where('key', 'min_present_mins')->value('value');

            return $val !== null ? (int) $val : 480;
        })();

        $rows = $this->getBaseQuery()->with(['employee.shift'])->get();

        $totalRecords = $rows->count();
        $presentCount = 0;
        $halfDayCount = 0;
        $absentCount = 0;
        $leaveCount = 0;
        $lateCount = 0;
        $missedPunch = 0;
        $totalMinutes = 0;
        $productiveMinutes = 0;
        $overtimeMinutes = 0;

        foreach ($rows as $r) {
            $st = strtolower($r->status ?? '');

            if (in_array($st, ['punch_out', 'punch_in', 'present'])) {
                $presentCount++;
            } elseif ($st === 'half_day') {
                $halfDayCount++;
            } elseif ($st === 'absent') {
                $absentCount++;
            } elseif ($st === 'leave') {
                $leaveCount++;
            }

            if (($r->late_minutes ?? 0) > 0 || $st === 'late') {
                $lateCount++;
            }

            if ($r->check_in && !$r->check_out) {
                $missedPunch++;
            }

            $worked = (int) ($r->working_minutes ?? 0);
            $totalMinutes += $worked;

            $shift = $r->employee->shift ?? null;
            $req = ($shift && $shift->min_present_mins)
                ? (int) $shift->min_present_mins
                : $defaultRequiredMins;

            $productive = min($worked, $req);
            $productiveMinutes += $productive;
            $overtimeMinutes += max(0, $worked - $req);
        }

        $avgWorkingMins = $totalRecords > 0 ? (int) round($totalMinutes / $totalRecords) : 0;

        return [
            'totalRecords'        => $totalRecords,
            'presentCount'        => $presentCount,
            'halfDayCount'        => $halfDayCount,
            'absentCount'         => $absentCount,
            'leaveCount'          => $leaveCount,
            'lateCount'           => $lateCount,
            'missedPunch'         => $missedPunch,
            'avgWorkingMins'      => $avgWorkingMins,
            'totalHours'          => intdiv($totalMinutes, 60) . 'h ' . ($totalMinutes % 60) . 'm',
            'productiveHours'     => intdiv($productiveMinutes, 60) . 'h ' . ($productiveMinutes % 60) . 'm',
            'overtimeHours'       => intdiv($overtimeMinutes, 60) . 'h ' . ($overtimeMinutes % 60) . 'm',
            'defaultRequiredMins' => $defaultRequiredMins,
        ];
    }

    public function getBranchesProperty()
    {
        $partnerId = $this->getPartnerId();
        return HrmsBranch::where('status', 'active')->orderBy('name')->get();
    }

    public function getTeamsProperty()
    {
        $allowedIds = $this->getTeamEmployeeIds('attendance_viewAny');
        $leadIds = User::whereIn('id', $allowedIds)
            ->whereNotNull('reporting_to')
            ->pluck('reporting_to')
            ->unique();

        return User::whereIn('id', $leadIds)->orderBy('name')->get();
    }

    public function getEmployeesProperty()
    {
        $allowedIds = $this->getTeamEmployeeIds('attendance_viewAny');
        $query = User::whereIn('id', $allowedIds);

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
        $data = $this->getBaseQuery()->with(['employee.branch', 'employee.reportingTo', 'employee.shift'])->latest('date')->get();
        $defaultReq = $this->reportSummary['defaultRequiredMins'];

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=attendance_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Date', 'Emp Code', 'Employee', 'Branch', 'Team / Reporting Manager', 'Check In', 'Check Out', 'Working Mins', 'Late Mins', 'Status', 'Total Hours', 'Productive Hours', 'Overtime'];
        
        $callback = function() use($data, $columns, $defaultReq) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                $worked = (int) ($row->working_minutes ?? 0);
                $shift = optional($row->employee)->shift;
                $req = ($shift && $shift->min_present_mins) ? (int) $shift->min_present_mins : $defaultReq;
                $prod = min($worked, $req);
                $ov = max(0, $worked - $req);

                fputcsv($file, [
                    $row->date ? Carbon::parse($row->date)->format('Y-m-d') : '',
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    optional(optional($row->employee)->branch)->name ?: 'Main Branch',
                    optional(optional($row->employee)->reportingTo)->name ?: 'Direct / None',
                    $row->check_in ? \Carbon\Carbon::parse($row->check_in)->format('H:i:s') : '-',
                    $row->check_out ? \Carbon\Carbon::parse($row->check_out)->format('H:i:s') : '-',
                    $row->working_minutes ?? 0,
                    $row->late_minutes ?? 0,
                    ucfirst(str_replace('_', ' ', $row->status ?? '')),
                    intdiv($worked, 60) . 'h ' . ($worked % 60) . 'm',
                    intdiv($prod, 60) . 'h ' . ($prod % 60) . 'm',
                    intdiv($ov, 60) . 'h ' . ($ov % 60) . 'm'
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
