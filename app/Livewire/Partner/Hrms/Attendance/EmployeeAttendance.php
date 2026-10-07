<?php

namespace App\Livewire\Partner\Hrms\Attendance;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmployeeAttendance as EmployeeAttendanceModel;
use App\Models\User;
use Carbon\Carbon;

use App\Livewire\Partner\Hrms\Traits\HasHrmsFilters;

class EmployeeAttendance extends Component
{
    use WithPagination,
        \App\Livewire\Partner\Hrms\HasPartnerId,
        HasHrmsFilters;

    protected $paginationTheme = 'bootstrap';

    public $selectedDate = '';
    public $selectedMonth = '';
    public $selectedStatus = '';
    public $selectedWorkingMode = '';

    public $expandedKey = null;

    public $selectedUsers = [];
    public $pageEmployeeIds = [];

    /**
     * Mount
     */
    public function mount()
    {
        $user = auth()->user();

        abort_unless(
            $user->isPartner() ||
            $user->isSuperAdmin() ||
            $user->canAccess('attendance_viewAny') ||
            $user->canAccess('attendance_viewBranch') ||
            $user->canAccess('attendance_viewTeam') ||
            $user->canAccess('attendance_viewOwn') ||
            $user->role === 'employee',
            403
        );

        // Default to today's date
        $this->selectedDate = Carbon::today()->format('Y-m-d');

        $this->selectedMonth = '';
    }

    /**
     * Toggle inline details row for an attendance record.
     * Accepts the row key (real id, or "v_{employee}_{date}" for virtual rows).
     */
    public function toggleDetails($key)
    {
        $this->expandedKey = $this->expandedKey === $key
            ? null
            : $key;
    }

    /**
     * Date filter updated
     */
    public function updatedSelectedDate()
    {
        if ($this->selectedDate) {
            $this->selectedMonth = '';
        }

        $this->resetPage();
    }

    /**
     * Month filter updated
     */
    public function updatedSelectedMonth()
    {
        if ($this->selectedMonth) {
            $this->selectedDate = '';
        }

        $this->resetPage();
    }

    /**
     * Status filter updated
     */
    public function updatedSelectedStatus()
    {
        $this->resetPage();
    }

    public function updatedSelectedWorkingMode()
    {
        $this->resetPage();
    }

    /**
     * Toggle selection of all employees visible on the current page.
     */
    public function toggleSelectAll()
    {
        $pageIds = $this->pageEmployeeIds;

        $allSelected = count($pageIds) > 0 &&
            collect($pageIds)->every(
                fn ($id) => in_array($id, $this->selectedUsers)
            );

        if ($allSelected) {
            $this->selectedUsers = array_values(
                array_diff($this->selectedUsers, $pageIds)
            );
        } else {
            $this->selectedUsers = array_values(
                array_unique(
                    array_merge($this->selectedUsers, $pageIds)
                )
            );
        }
    }

    /**
     * Format minutes into a human-readable duration (e.g. "8h 30m").
     */
    protected function formatDuration(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0h 0m';
        }

        $hours   = intdiv($minutes, 60);
        $remains = $minutes % 60;

        return $hours . 'h ' . $remains . 'm';
    }

    /**
     * Resolve the attendance scope (View Any / View Branch / View Team / View Own)
     * for the current user and the employee IDs they are allowed to see.
     *
     * @return array{viewAny: bool, viewBranch: bool, viewTeam: bool, employeeIds: \Illuminate\Support\Collection}
     */
    protected function resolveAttendanceScope($user, $partnerId): array
    {
        $viewAny    = $user->isPartner() || $user->canAccess('attendance_viewAny');
        $viewBranch = $user->canAccess('attendance_viewBranch');
        $viewTeam   = $user->canAccess('attendance_viewTeam');

        if ($viewAny) {
            $employeeIds = collect($this->getFilteredEmployeeIds('attendance_viewAny'));
        } elseif ($viewBranch) {
            $branchUserIds = User::where(function ($q) use ($partnerId) {
                if (!auth()->user()->isSuperAdmin()) {
                    $q->where('parent_id', $partnerId);
                }
            })
            ->where('role', 'employee')
            ->where('branch_id', $user->branch_id)
            ->pluck('id');

            if (!$user->isAdmin()) {
                $branchUserIds->push($user->id);
            }

            $employeeIds = $branchUserIds->unique()->values();
        } elseif ($viewTeam) {
            $teamIds = $user->getTeamIds();
            if (!$user->isAdmin()) {
                $teamIds = array_merge($teamIds, [$user->id]);
            }
            $employeeIds = collect(array_unique($teamIds));
        } else {
            // View Own (also the fallback for staff members without an explicit permission)
            $employeeIds = $user->isAdmin() ? collect() : collect([$user->id]);
        }

        // Strictly exclude admin and super_admin from employee attendance
        $adminIds = User::whereIn('role', ['super_admin', 'admin'])
            ->orWhereHas('roles', fn ($q) => $q->whereIn('name', ['super_admin', 'admin']))
            ->pluck('id')
            ->toArray();

        $employeeIds = $employeeIds->reject(fn ($id) => in_array($id, $adminIds))->values();

        return [
            'viewAny'     => (bool) $viewAny,
            'viewBranch'  => !$viewAny && (bool) $viewBranch,
            'viewTeam'    => !$viewAny && !$viewBranch && (bool) $viewTeam,
            'employeeIds' => $employeeIds,
        ];
    }

    /**
     * Render
     */
    public function render()
    {
        $user = auth()->user();

        $partnerId = $this->getPartnerId();

        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */

        $query = EmployeeAttendanceModel::with(['employee.shift'])
            ->whereHas('employee', function ($q) {
                $q->whereNotIn('role', ['super_admin', 'admin'])
                  ->whereDoesntHave('roles', fn ($r) => $r->whereIn('name', ['super_admin', 'admin']));
            });

        /*
        |--------------------------------------------------------------------------
        | Attendance Access
        |--------------------------------------------------------------------------
        |
        | View Any   => All employees
        | View Branch=> All employees of the user's branch
        | View Team  => Team members + self
        | View Own   => Self only
        |
        */

        $scope = $this->resolveAttendanceScope($user, $partnerId);

        $query->whereIn(
            'employee_id',
            $scope['employeeIds']
        );

        /*
        |--------------------------------------------------------------------------
        | Date Filter
        |--------------------------------------------------------------------------
        */

        if ($this->selectedDate) {

            $query->whereDate(
                'date',
                $this->selectedDate
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Month Filter
        |--------------------------------------------------------------------------
        */

        if ($this->selectedMonth) {

            $date = Carbon::parse(
                $this->selectedMonth
            );

            $query
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month);
        }

        /*
        |--------------------------------------------------------------------------
        | Base Query For Summary
        |--------------------------------------------------------------------------
        |
        | Clone before applying status filter.
        |
        */

        $baseQuery = clone $query;

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = [
            'punch_out' => 0,
            'absent'    => 0,
            'half_day'  => 0,
            'punch_in'  => 0,
            'leave'     => 0,
            'holiday'   => 0,
            'weekOff'   => 0,
            'late'      => 0,
            'notPunchIn' => 0,
            'missedPunch' => 0,
        ];

        /*
        |--------------------------------------------------------------------------
        | Status Counts
        |--------------------------------------------------------------------------
        */

        $statusCounts = $baseQuery
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        foreach ($statusCounts as $status => $count) {
            if ($status && array_key_exists($status, $summary)) {
                $summary[$status] = $count;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Punch In Count (All employees who have checked in)
        |--------------------------------------------------------------------------
        */
        $summary['punch_in'] = (clone $baseQuery)
            ->whereNotNull('check_in')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Punch Out Count (All employees who have checked out)
        |--------------------------------------------------------------------------
        */
        $summary['punch_out'] = (clone $baseQuery)
            ->whereNotNull('check_out')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Late Count
        |--------------------------------------------------------------------------
        */

        $summary['late'] = (clone $baseQuery)
            ->whereNotNull('late_minutes')
            ->where('late_minutes', '>', 0)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Missed Punch Count (checked in but never punched out)
        |--------------------------------------------------------------------------
        */

        $summary['missedPunch'] = (clone $baseQuery)
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($this->selectedStatus) {

            if ($this->selectedStatus === 'late') {

                $query
                    ->whereNotNull('late_minutes')
                    ->where('late_minutes', '>', 0);

            } elseif ($this->selectedStatus === 'punch_in') {

                $query->whereNotNull('check_in');

            } elseif ($this->selectedStatus === 'punch_out') {

                $query->whereNotNull('check_out');

            } else {

                $query->where(
                    'status',
                    $this->selectedStatus
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Working Mode Filter
        |--------------------------------------------------------------------------
        */

        if ($this->selectedWorkingMode) {
            $query->whereHas('employee', function ($q) {
                $q->where('working_mode', $this->selectedWorkingMode);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Working Hours Summary (Total / Productive / Overtime)
        |--------------------------------------------------------------------------
        |
        | Total      => Sum of actual worked minutes (working_minutes or check_in to check_out/now)
        | Productive => Productive shift minutes completed (min(worked, required))
        | Overtime   => Sum of (worked - required) for positive deltas
        |
        */

        $defaultRequiredMins = (function () use ($partnerId) {
            $val = \App\Models\PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
                ->where('key', 'min_present_mins')
                ->value('value');

            return $val !== null ? (int) $val : 480;
        })();

        $hoursStats = (clone $query)
            ->with('employee.shift')
            ->get()
            ->reduce(function ($carry, $rec) use ($defaultRequiredMins) {
                if (!empty($rec->working_minutes)) {
                    $worked = (int) $rec->working_minutes;
                } elseif ($rec->check_in && $rec->check_out) {
                    $dateStr = Carbon::parse($rec->date)->format('Y-m-d');
                    $inTime = Carbon::parse($dateStr . ' ' . $rec->check_in);
                    $outTime = Carbon::parse($dateStr . ' ' . $rec->check_out);
                    $worked = (int) abs($outTime->diffInMinutes($inTime));
                } elseif ($rec->check_in && Carbon::parse($rec->date)->isToday()) {
                    $inTime = Carbon::parse(Carbon::today()->format('Y-m-d') . ' ' . $rec->check_in);
                    $worked = (int) max(0, Carbon::now()->diffInMinutes($inTime));
                } else {
                    $worked = 0;
                }

                $carry['total'] += $worked;

                $shift = $rec->employee->shift ?? null;
                $required = ($shift && $shift->min_present_mins)
                    ? (int) $shift->min_present_mins
                    : $defaultRequiredMins;

                $productive = min($worked, $required);
                $carry['productive'] += $productive;
                $carry['overtime'] += max(0, $worked - $required);

                return $carry;
            }, ['total' => 0, 'productive' => 0, 'overtime' => 0]);

        $summary['total_hours']      = $this->formatDuration($hoursStats['total']);
        $summary['productive_hours'] = $this->formatDuration($hoursStats['productive']);
        $summary['required_hours']   = $this->formatDuration($hoursStats['productive']);
        $summary['overtime_hours']   = $this->formatDuration($hoursStats['overtime']);

        /*
        |--------------------------------------------------------------------------
        | Attendance Pagination
        |--------------------------------------------------------------------------
        */

        $attendances = $query
            ->latest('date')
            ->paginate(100);

        /*
        |--------------------------------------------------------------------------
        | Track employee IDs visible on current page (for select-all checkbox)
        |--------------------------------------------------------------------------
        */

        $this->pageEmployeeIds = $attendances
            ->getCollection()
            ->pluck('employee_id')
            ->unique()
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Virtual Today's Attendance
        |--------------------------------------------------------------------------
        |
        | If attendance record doesn't exist for today,
        | generate a temporary attendance record.
        |
        */

        $employeeIdsForVirtual = $scope['employeeIds'];

        /*
        |--------------------------------------------------------------------------
        | Working Mode Filter (virtual records)
        |--------------------------------------------------------------------------
        |
        | Filter the not-yet-punched-in employees by their profile working mode
        | so the "Working Mode" filter also applies to today's virtual rows.
        |
        */

        if ($this->selectedWorkingMode) {
            $employeeIdsForVirtual = $employeeIdsForVirtual->filter(
                fn ($id) => optional(\App\Models\User::find($id))->working_mode === $this->selectedWorkingMode
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Only inject virtual records on first page
        |--------------------------------------------------------------------------
        */

        if (
            $attendances->currentPage() === 1 &&
            $employeeIdsForVirtual->count() > 0
        ) {

            /*
            |--------------------------------------------------------------------------
            | Determine Virtual Date
            |--------------------------------------------------------------------------
            |
            | Virtual rows are generated for the selected date (any date), or for
            | today when the current month is selected. Other months have no
            | virtual rows (too sparse / expensive to generate per day).
            |
            */

            if ($this->selectedDate) {
                $virtualDate = $this->selectedDate;
            } elseif (
                $this->selectedMonth &&
                $this->selectedMonth === Carbon::today()->format('Y-m')
            ) {
                $virtualDate = Carbon::today()->toDateString();
            } else {
                $virtualDate = null;
            }

            $includeVirtual = !is_null($virtualDate);

            /*
            |--------------------------------------------------------------------------
            | Status Filter
            |--------------------------------------------------------------------------
            */

            if (
                $this->selectedStatus &&
                !in_array(
                    $this->selectedStatus,
                    [
                        'notPunchIn',
                        'absent',
                        'holiday',
                        'weekOff',
                        'leave',
                    ]
                )
                ) {
                $includeVirtual = false;
            }

            /*
            |--------------------------------------------------------------------------
            | Generate Virtual Attendance
            |--------------------------------------------------------------------------
            */

            if ($includeVirtual) {

                $virtualRecords = collect();

                foreach ($employeeIdsForVirtual as $employeeId) {

                    /*
                    |--------------------------------------------------------------------------
                    | Check if today's record already exists
                    |--------------------------------------------------------------------------
                    */

                    $hasToday = $attendances->contains(
                        function ($attendance) use (
                            $virtualDate,
                            $employeeId
                        ) {

                            return
                                $attendance->employee_id == $employeeId &&
                                Carbon::parse(
                                    $attendance->date
                                )->toDateString() === $virtualDate;
                        }
                    );

                    if ($hasToday) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Employee
                    |--------------------------------------------------------------------------
                    */

                    $employee = \App\Models\User::find(
                        $employeeId
                    );

                    if (!$employee || $employee->isAdmin() || in_array($employee->role, ['super_admin', 'admin']) || $employee->hasExitedOnOrBefore($virtualDate)) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Default Status
                    |--------------------------------------------------------------------------
                    */

                    $status = 'absent';

                    $statusReason = 'Absent';

                    $isLeave = false;

                    $isHalfDayLeave = false;

                    /*
                    |--------------------------------------------------------------------------
                    | Check Leave
                    |--------------------------------------------------------------------------
                    */

                    $leave = \App\Models\EmployeeLeave::where(
                        'employee_id',
                        $employeeId
                    )
                        ->where(
                            'status',
                            'approved'
                        )
                        ->whereDate(
                            'start_date',
                            '<=',
                            $virtualDate
                        )
                        ->whereDate(
                            'end_date',
                            '>=',
                            $virtualDate
                        )
                        ->first();

                    if ($leave) {

                        if ($leave->type === 'half_day') {

                            $isHalfDayLeave = true;

                        } else {

                            $isLeave = true;

                            $status = 'leave';

                            $statusReason =
                                $leave->leave_type ?? 'Leave';
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Holiday / Sunday
                    |--------------------------------------------------------------------------
                    */

                    if (!$isLeave) {

                        $isHoliday =
                            \App\Models\Holiday::where(
                                'partner_id',
                                $partnerId
                            )
                                ->whereDate(
                                    'date',
                                    $virtualDate
                                )
                                ->exists();

                        if (
                            Carbon::parse($virtualDate)->isSunday() ||
                            $isHoliday
                        ) {

                            $status =
                                $isHoliday
                                    ? 'holiday'
                                    : 'weekOff';

                            $statusReason =
                                $isHoliday
                                    ? 'Holiday'
                                    : 'Sunday';

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | Check Auto Absent Setting
                            |--------------------------------------------------------------------------
                            */

                            $checkInSetting =
                                \App\Models\PartnerSetting::where(
                                    'partner_id',
                                    $partnerId
                                )
                                    ->where(
                                        'key',
                                        'fixed_check_in_time'
                                    )
                                    ->value('value')
                                    ?? '09:00';

                            $absentMins =
                                \App\Models\PartnerSetting::where(
                                    'partner_id',
                                    $partnerId
                                )
                                    ->where(
                                        'key',
                                        'auto_absent_mark_mins'
                                    )
                                    ->value('value')
                                    ?? 60;

                            $cutoffMins =
                                (int) $absentMins;

                            /*
                            |--------------------------------------------------------------------------
                            | Half Day Leave
                            |--------------------------------------------------------------------------
                            */

                            if ($isHalfDayLeave) {

                                $minHalfDay =
                                    \App\Models\PartnerSetting::where(
                                        'partner_id',
                                        $partnerId
                                    )
                                        ->where(
                                            'key',
                                            'min_half_day_mins'
                                        )
                                        ->value('value')
                                        ?? 240;

                                $cutoffMins +=
                                    (int) $minHalfDay;
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | Calculate Cutoff
                            |--------------------------------------------------------------------------
                            */

                            $cutoffTime =
                                Carbon::parse(
                                    $virtualDate .
                                    ' ' .
                                    $checkInSetting
                                )
                                    ->addMinutes(
                                        $cutoffMins
                                    );

                            /*
                            |--------------------------------------------------------------------------
                            | Determine Status
                            |--------------------------------------------------------------------------
                            |
                            | - Today   => compare current time against cutoff
                            | - Past    => no record exists, mark Absent
                            | - Future  => not yet punched in
                            |
                            */

                            if (
                                $virtualDate ===
                                Carbon::today()->toDateString()
                            ) {

                                if (
                                    Carbon::now()
                                        ->greaterThanOrEqualTo(
                                            $cutoffTime
                                        )
                                ) {

                                    $status = 'absent';

                                    $statusReason = 'Absent';

                                } else {

                                    $status = 'notPunchIn';

                                    $statusReason =
                                        'Not Punch In';
                                }

                            } elseif (
                                Carbon::parse($virtualDate)
                                    ->lessThan(
                                        Carbon::today()
                                    )
                            ) {

                                $status = 'absent';

                                $statusReason = 'Absent';

                            } else {

                                $status = 'notPunchIn';

                                $statusReason =
                                    'Not Punch In';
                            }
                        }
                    }

                    // Count virtual status in summary
                    if (array_key_exists($status, $summary)) {
                        $summary[$status]++;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Selected Status Check
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $this->selectedStatus &&
                        $this->selectedStatus !== $status
                    ) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Create Virtual Attendance
                    |--------------------------------------------------------------------------
                    */

                    $virtualAttendance =
                        new EmployeeAttendanceModel([
                            'employee_id' =>
                                $employeeId,

                            'date' =>
                                $virtualDate,

                            'status' =>
                                $status,

                            'status_reason' =>
                                $statusReason,

                            'working_mode' =>
                                $employee->working_mode ?? 'office',
                        ]);

                    $virtualAttendance->setRelation(
                        'employee',
                        $employee
                    );

                    $virtualRecords->push(
                        $virtualAttendance
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Add Virtual Records
                |--------------------------------------------------------------------------
                */

                if ($virtualRecords->isNotEmpty()) {

                    $items =
                        $virtualRecords
                            ->merge(
                                $attendances->items()
                            );

                    $attendances->setCollection(
                        $items
                    );
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Page Title
        |--------------------------------------------------------------------------
        */

        $hasViewAny  = $scope['viewAny'];

        $hasViewTeam = $scope['viewBranch'] || $scope['viewTeam'];

        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view(
            'livewire.partner.hrms.attendance.employee-attendance',
            [
                'attendances' => $attendances,
                'summary'     => $summary,
                'defaultRequiredMins' => $defaultRequiredMins,
            ]
        )->layout(
            'layouts.app',
            [
                'panelName' =>
                    'Partner Panel',

                'pageTitle' =>
                    $hasViewAny
                        ? 'Manage Employee Attendance'
                        : ($hasViewTeam
                            ? 'Team Attendance'
                            : 'My Attendance History'),

                'pageSubtitle' =>
                    $hasViewAny
                        ? 'View attendance records, selfies, and GPS for all employees'
                        : ($hasViewTeam
                            ? 'View attendance records of your team members'
                            : 'View your attendance records'),

                'sidebarLinks' =>
                    view(
                        'partials.sidebar-partner'
                    ),
            ]
        );
    }
}