<?php

namespace App\Livewire\Partner\Reports;

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
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $query = EmployeeAttendance::query();
        if ($isSuperAdmin) {
            $query->whereHas('employee', function($q) {
                $q->where('role', 'employee')
                    ->whereIn('parent_id', User::where('role', 'partner')->select('id'));
            });
        } else {
            $query->whereHas('employee', function($q) use ($partnerId) {
                $q->where('parent_id', $partnerId);
            });
        }
        
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
        return $this->getBaseQuery()->with('employee')->latest('date')->paginate(100);
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
        
        $callback = function() use($data, $columns) {
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
        if (auth()->user()->role === 'super_admin') {
            return User::where('role', 'employee')
                ->whereIn('parent_id', User::where('role', 'partner')->select('id'))
                ->get();
        }

        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        return User::where('parent_id', $partnerId)->where('role', 'employee')->get();
    }

    public function render()
    {
        return view('livewire.partner.reports.attendance-report', [
            'reportData' => $this->reportData,
            'employees'  => $this->employees,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Panel',
            'pageTitle'    => 'Attendance Report',
            'pageSubtitle' => 'View staff attendance details',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
