<?php

namespace App\Livewire\Partner\Hrms\Attendance;

use Livewire\Component;
use App\Models\User;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeave;
use App\Models\Holiday;
use App\Models\PartnerSetting;
use Carbon\Carbon;

class AttendanceCalendar extends Component
{
    use \App\Livewire\Partner\Hrms\HasPartnerId;
    
    public $currentMonth;
    public $currentYear;
    public $selectedEmployeeId = null;

    public $selectedAttendance = null;
    
    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->isSuperAdmin() ||
            auth()->user()->canAccess('attendance_viewAny') ||
            auth()->user()->canAccess('attendance_viewBranch') ||
            auth()->user()->canAccess('attendance_viewTeam') ||
            auth()->user()->canAccess('attendance_viewOwn') ||
            auth()->user()->role === 'employee',
            403
        );
        
        $this->currentMonth = date('n');
        $this->currentYear = date('Y');
    }
    
    public function previousMonth()
    {
        if ($this->currentMonth == 1) {
            $this->currentMonth = 12;
            $this->currentYear--;
        } else {
            $this->currentMonth--;
        }
    }
    
    public function nextMonth()
    {
        if ($this->currentMonth == 12) {
            $this->currentMonth = 1;
            $this->currentYear++;
        } else {
            $this->currentMonth++;
        }
    }

    public function viewDetails($day)
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, $day)->format('Y-m-d');
        
        $this->selectedAttendance = EmployeeAttendance::where('employee_id', $this->selectedEmployeeId)
            ->whereDate('date', $date)
            ->first();

        // If no attendance but they want to view details, we can just show empty modal or not open it
        // The modal will be controlled by Alpine/Bootstrap on frontend
        $this->dispatch('open-details-modal');
    }

   public function render()
{
    $partnerId = $this->getPartnerId();

    $user = auth()->user();

    /*
    |--------------------------------------------------------------------------
    | Employee Access
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Attendance Access (hierarchy: viewAny > viewBranch > viewTeam > viewOwn)
    |--------------------------------------------------------------------------
    | View Any    => All employees under partner
    | View Branch => All employees of the user's branch
    | View Team   => Team members + self
    | View Own    => Self only
    */

    $scopeIds = $this->getTeamEmployeeIds('attendance_viewAny');
    $employees = User::whereIn('id', $scopeIds)
        ->orderBy('name')
        ->get();

        // Use first employee if none selected
    if (!$this->selectedEmployeeId && $employees->count() > 0) {
        $this->selectedEmployeeId = $employees->first()->id;
    }

    // Determine days in month
    $daysInMonth = Carbon::create($this->currentYear, $this->currentMonth)->daysInMonth;

    // Get start day of week (0 = Sunday, 1 = Monday)
    $startDayOfWeek = Carbon::create($this->currentYear, $this->currentMonth, 1)->dayOfWeek;

    // Fetch attendances, leaves, holidays
    $attendances = [];
    $dailyStatuses = [];

    if ($this->selectedEmployeeId) {
            $records = EmployeeAttendance::where('employee_id', $this->selectedEmployeeId)
                ->whereYear('date', $this->currentYear)
                ->whereMonth('date', $this->currentMonth)
                ->get()
                ->keyBy(function($item) {
                    return Carbon::parse($item->date)->format('j');
                });
                
            $holidays = Holiday::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
                ->whereYear('date', $this->currentYear)
                ->whereMonth('date', $this->currentMonth)
                ->pluck('name', 'date')
                ->toArray();

            $leaves = EmployeeLeave::where('employee_id', $this->selectedEmployeeId)
                ->where('status', 'approved')
                ->get();
            
            // Generate quick lookup for leaves
            $leaveDays = [];
            foreach($leaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end = Carbon::parse($leave->end_date);
                for($d = $start->copy(); $d->lte($end); $d->addDay()) {
                    if($d->month == $this->currentMonth && $d->year == $this->currentYear) {
                        $leaveDays[$d->format('j')] = $leave->leave_type ?? 'Leave';
                    }
                }
            }
                
            $checkInSetting = \App\Models\PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
                ->where('key', 'fixed_check_in_time')->value('value') ?? '09:00';

            $selEmployee = User::find($this->selectedEmployeeId);

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $currentDateObj = Carbon::create($this->currentYear, $this->currentMonth, $i);
                $dateString = $currentDateObj->format('Y-m-d');
                $isSunday = $currentDateObj->isSunday();
                
                $attendances[$i] = $records[$i] ?? null;
                $dailyStatuses[$i] = 'Absent'; // Default for past non-attendance days

                if ($selEmployee && $selEmployee->hasExitedOnOrBefore($dateString)) {
                    $dailyStatuses[$i] = '';
                    continue;
                }
                
                if (isset($holidays[$dateString])) {
                    $dailyStatuses[$i] = 'Holiday';
                } elseif (isset($leaveDays[$i])) {
                    $dailyStatuses[$i] = 'Leave';
                } elseif (isset($records[$i]) && $records[$i]->check_in) {
                    $status = $records[$i]->status ?? '';
                    if (in_array($status, ['half_day', 'short_leave', 'late'])) {
                        $dailyStatuses[$i] = ucfirst(str_replace('_', ' ', $status));
                    } elseif ($status === 'punch_in') {
                        $dailyStatuses[$i] = 'Punch In';
                    } else {
                        $dailyStatuses[$i] = 'Punch Out';
                        if ($records[$i]->check_out) {
                            $in = Carbon::parse($records[$i]->check_in);
                            $out = Carbon::parse($records[$i]->check_out);
                            if ($in->diffInHours($out) < 4) {
                                $dailyStatuses[$i] = 'Half Day';
                            }
                        } else if ($currentDateObj->isPast() && !$currentDateObj->isToday()) {
                            $dailyStatuses[$i] = 'Half Day';
                        }
                    }
                } elseif ($isSunday) {
                    $dailyStatuses[$i] = 'Week Off';
                } elseif ($currentDateObj->isFuture()) {
                    $dailyStatuses[$i] = ''; // Blank for future days
                } elseif ($currentDateObj->isToday()) {
                    $cutoffTime = Carbon::parse($dateString . ' ' . $checkInSetting)->addHour();
                    if (Carbon::now()->greaterThanOrEqualTo($cutoffTime)) {
                        $dailyStatuses[$i] = 'Absent';
                    } else {
                        $dailyStatuses[$i] = 'Not Punch In';
                    }
                }
            }
        }

        return view('livewire.partner.hrms.attendance.attendancecalendar', [
            'employees' => $employees,
            'daysInMonth' => $daysInMonth,
            'startDayOfWeek' => $startDayOfWeek,
            'attendances' => $attendances,
            'dailyStatuses' => $dailyStatuses,
            'monthName' => Carbon::create($this->currentYear, $this->currentMonth)->format('F'),
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Attendance Calendar',
            'pageSubtitle' => 'View monthly attendance records for employees',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
