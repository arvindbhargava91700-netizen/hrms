<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use App\Models\EmployeeAttendance;
use App\Models\User;
use Carbon\Carbon;
use Livewire\WithPagination;

class AttendanceReport extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $employeeId = '';
    public $statusFilter = '';
    public $selectedWorkingMode = '';

    protected $paginationTheme = 'bootstrap';

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
        $query = EmployeeAttendance::whereHas('employee', function($q) {
            $q;
        });
        
        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }
        
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->selectedWorkingMode) {
            $query->whereHas('employee', function ($q) {
                $q->where('working_mode', $this->selectedWorkingMode);
            });
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('date', [$this->startDate, $this->endDate]);
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with('employee.shift')->latest('date')->paginate(100);
    }

    /**
     * Aggregate totals across the full filtered set (not just the current page).
     */
    public function getReportSummaryProperty()
    {
        $defaultRequiredMins = (function () {
            $val = \App\Models\PartnerSetting::query()
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

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with('employee')->latest('date')->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=attendance_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Date', 'Emp ID', 'Employee', 'Check In', 'Check Out', 'Working Mins', 'Late Mins', 'Status'];
        
        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->date ? Carbon::parse($row->date)->format('Y-m-d') : '',
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
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

    public function getEmployeesProperty()
    {
        return User::where('role', 'employee')->get();
    }

    public function render()
    {
        return view('livewire.admin.reports.hrms.attendance-report', [
            'reportData'  => $this->reportData,
            'employees'   => $this->employees,
            'reportSummary' => $this->reportSummary,
        ])->layout('layouts.app', [
            'panelName'    => 'Admin Panel',
            'pageTitle'    => 'Attendance Report',
            'pageSubtitle' => 'View staff attendance details',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
