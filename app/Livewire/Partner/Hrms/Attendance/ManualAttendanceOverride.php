<?php

namespace App\Livewire\Partner\Hrms\Attendance;

use Livewire\Component;
use App\Models\User;
use App\Models\EmployeeAttendance;
use App\Models\AttendanceOverride;
use App\Models\WorkShift;
use App\Livewire\Partner\Hrms\HasPartnerId;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ManualAttendanceOverride extends Component
{
    use HasPartnerId;

    public $employee_id = '';
    public $date = '';
    public $check_in = '';
    public $check_out = '';
    public $shift_id = '';
    public $status = 'present';
    public $working_mode = 'office';
    public $notes = '';

    public $existingAttendance = null;

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('attendance_manage') ||
            auth()->user()->canAccess('attendance_create') ||
            auth()->user()->canAccess('attendance_update') ||
            auth()->user()->canAccess('attendance_viewAny'),
            403
        );

        $this->date = Carbon::today()->format('Y-m-d');
    }

    public function updatedEmployeeId($value)
    {
        if ($value) {
            $emp = User::find($value);
            if ($emp && $emp->shift_id) {
                $this->shift_id = $emp->shift_id;
            }
            if ($emp && $emp->working_mode) {
                $this->working_mode = $emp->working_mode;
            }
        }
        $this->checkExistingAttendance();
    }

    public function updatedDate($value)
    {
        $this->checkExistingAttendance();
    }

    public function checkExistingAttendance()
    {
        if ($this->employee_id && $this->date) {
            $this->existingAttendance = EmployeeAttendance::where('employee_id', $this->employee_id)
                ->whereDate('date', $this->date)
                ->first();

            if ($this->existingAttendance) {
                $this->status = $this->existingAttendance->status ?? 'present';
                $this->check_in = $this->existingAttendance->check_in ? substr($this->existingAttendance->check_in, 0, 5) : '';
                $this->check_out = $this->existingAttendance->check_out ? substr($this->existingAttendance->check_out, 0, 5) : '';
                $this->shift_id = $this->existingAttendance->shift_id ?? $this->shift_id;
                $this->working_mode = $this->existingAttendance->working_mode ?? 'office';
            }
        } else {
            $this->existingAttendance = null;
        }
    }

    public function saveAttendance()
    {
        $this->validate([
            'employee_id'   => 'required|exists:users,id',
            'date'          => 'required|date',
            'status'        => 'required|in:present,punch_in,punch_out,half_day,late,short_leave,leave,absent',
            'check_in'      => 'nullable|string',
            'check_out'     => 'nullable|string',
            'shift_id'      => 'nullable|exists:work_shifts,id',
            'working_mode'  => 'required|in:office,remote,field',
            'notes'         => 'nullable|string|max:1000',
        ], [
            'employee_id.required' => 'Please select an employee.',
            'date.required'        => 'Please select a date.',
            'status.required'      => 'Please select an attendance status.',
        ]);

        $employee = User::findOrFail($this->employee_id);
        $partnerId = $this->getPartnerId();

        // Calculate working minutes and late minutes if times are provided
        $workingMinutes = null;
        $lateMinutes = null;

        if ($this->check_in && $this->check_out) {
            try {
                $in = Carbon::parse($this->date . ' ' . $this->check_in);
                $out = Carbon::parse($this->date . ' ' . $this->check_out);
                if ($out->lt($in)) {
                    $out->addDay();
                }
                $workingMinutes = (int)abs($in->diffInMinutes($out));
            } catch (\Exception $e) {
                // Ignore parse errors
            }
        }

        if ($this->check_in && $this->shift_id) {
            $shift = WorkShift::find($this->shift_id);
            if ($shift && $shift->start_time) {
                try {
                    $shiftStart = Carbon::parse($this->date . ' ' . $shift->start_time);
                    $actualIn = Carbon::parse($this->date . ' ' . $this->check_in);
                    $grace = (int)($shift->late_tolerance_minutes ?? 15);
                    if ($actualIn->gt($shiftStart)) {
                        $diff = $shiftStart->diffInMinutes($actualIn);
                        if ($diff > $grace) {
                            $lateMinutes = $diff;
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore parse errors
                }
            }
        }

        // Map status for DB
        $dbStatus = $this->status;
        if ($dbStatus === 'present') {
            $dbStatus = $this->check_out ? 'punch_out' : 'punch_in';
        }

        DB::beginTransaction();
        try {
            $existing = EmployeeAttendance::where('employee_id', $this->employee_id)
                ->whereDate('date', $this->date)
                ->first();

            $prevStatus = $existing ? $existing->status : null;
            $prevCheckIn = $existing ? $existing->check_in : null;
            $prevCheckOut = $existing ? $existing->check_out : null;

            $attendance = EmployeeAttendance::updateOrCreate(
                [
                    'employee_id' => $this->employee_id,
                    'date'        => $this->date,
                ],
                [
                    'branch_id'       => $employee->branch_id,
                    'shift_id'        => $this->shift_id ?: null,
                    'check_in'        => $this->check_in ? (strlen($this->check_in) == 5 ? $this->check_in . ':00' : $this->check_in) : null,
                    'check_out'       => $this->check_out ? (strlen($this->check_out) == 5 ? $this->check_out . ':00' : $this->check_out) : null,
                    'status'          => $dbStatus,
                    'working_mode'    => $this->working_mode,
                    'working_minutes' => $workingMinutes,
                    'late_minutes'    => $lateMinutes,
                ]
            );

            // Create override audit record
            AttendanceOverride::create([
                'partner_id'         => $this->requirePartnerId(),
                'employee_id'        => $this->employee_id,
                'attendance_id'      => $attendance->id,
                'overridden_by'      => auth()->id(),
                'date'               => $this->date,
                'previous_status'    => $prevStatus,
                'previous_check_in'  => $prevCheckIn,
                'previous_check_out' => $prevCheckOut,
                'new_status'         => $dbStatus,
                'new_check_in'       => $this->check_in ? (strlen($this->check_in) == 5 ? $this->check_in . ':00' : $this->check_in) : null,
                'new_check_out'      => $this->check_out ? (strlen($this->check_out) == 5 ? $this->check_out . ':00' : $this->check_out) : null,
                'shift_id'           => $this->shift_id ?: null,
                'working_minutes'    => $workingMinutes,
                'late_minutes'       => $lateMinutes,
                'notes'              => $this->notes,
            ]);

            DB::commit();

            session()->flash('success', "Attendance for {$employee->name} on " . Carbon::parse($this->date)->format('d M Y') . " has been successfully updated and recorded in the override log.");
            
            $this->reset(['notes']);
            $this->checkExistingAttendance();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to update attendance: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $partnerId = $this->getPartnerId();

        // Get employees under this partner
        $employees = User::whereIn('id', $this->getTeamEmployeeIds('attendance_viewAny'))
            ->whereNotIn('role', ['super_admin', 'admin'])
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['super_admin', 'admin']);
            })
            ->orderBy('name')
            ->get();

        // Get work shifts
        $shifts = WorkShift::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->orderBy('name')->get();

        // Get recent override history
        $recentOverrides = AttendanceOverride::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
            ->with(['employee.department', 'employee.branch', 'overrider', 'shift'])
            ->latest()
            ->take(15)
            ->get();

        return view('livewire.partner.hrms.attendance.manual-attendance-override', [
            'employees'       => $employees,
            'shifts'          => $shifts,
            'recentOverrides' => $recentOverrides,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'Mark Attendance (Manual Override)',
            'pageSubtitle' => 'Manually set and override employee attendance records',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
