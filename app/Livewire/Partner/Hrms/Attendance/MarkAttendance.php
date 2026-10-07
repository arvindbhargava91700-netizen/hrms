<?php

namespace App\Livewire\Partner\Hrms\Attendance;

use App\Models\AttendanceChecklist;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeave;
use App\Models\Holiday;
use App\Models\PartnerSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class MarkAttendance extends Component
{
    public $todayAttendance = null;

    // Captured Data
    public $photoData;

    public $latitude;

    public $longitude;

    public $working_mode = 'office'; // office, remote, field

    // Geofence Information for UI
    public $officeLat;

    public $officeLng;

    public $officeRadius;

    public $distanceMeters = null;

    public $isWithinGeofence = true;

    // Checklist
    public $checklists = [];

    public $checklistAnswers = [];

    // Status Overrides
    public $isHoliday = false;

    public $holidayName = '';

    public $isWeekOff = false;

    public $isFullLeave = false;

    public $isHalfLeave = false;

    public $isAbsent = false;

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('attendance_viewOwn'), 403);

        $this->working_mode = auth()->user()->working_mode ?? 'office';

        $this->loadOfficeGeofence();
        $this->loadTodayAttendance();
        $this->loadChecklists();
        $this->checkLeaveAndHoliday();

        if (auth()->user()->hasExitedOnOrBefore(Carbon::today())) {
            $lwd = auth()->user()->getLastWorkingDate();
            $lwdText = $lwd ? $lwd->format('d M, Y') : 'your last working date';
            session()->flash('error', "Your employment ended on {$lwdText}. Attendance marking is no longer allowed.");
        }
    }

    public function loadOfficeGeofence()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $user = auth()->user();

        if ($user->branch_id && $user->branch) {
            $this->officeLat = $user->branch->lat;
            $this->officeLng = $user->branch->lng;
            $this->officeRadius = $user->branch->radius ?: 200;
        } else {
            $this->officeLat = PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'office_latitude')->value('value');
            $this->officeLng = PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'office_longitude')->value('value');
            $this->officeRadius = (int) (PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'office_radius_meters')->value('value') ?? 200);
        }
    }

    public function loadTodayAttendance()
    {
        $user = auth()->user();

        // 1. Look for an active (no check-out) attendance from today
        $this->todayAttendance = EmployeeAttendance::where('employee_id', $user->id)
            ->whereNull('check_out')
            ->whereDate('date', Carbon::today())
            ->first();

        // 2. If no active attendance today, check yesterday (for night shifts or late checkouts)
        if (! $this->todayAttendance) {
            $yesterdayAttendance = EmployeeAttendance::where('employee_id', $user->id)
                ->whereNull('check_out')
                ->whereDate('date', Carbon::yesterday())
                ->first();

            if ($yesterdayAttendance) {
                $checkInTime = Carbon::parse(Carbon::yesterday()->format('Y-m-d').' '.$yesterdayAttendance->check_in);

                // Determine validity window based on current working shift
                $validityHours = 14; // Default fallback
                if ($user->shift_id && $user->shift) {
                    try {
                        // Use string manipulation or Carbon parsing safely
                        $startTime = $user->shift->start_time;
                        $endTime = $user->shift->end_time;
                        if ($startTime && $endTime) {
                            $start = Carbon::createFromFormat('H:i:s', $startTime);
                            $end = Carbon::createFromFormat('H:i:s', $endTime);
                            if ($end->lt($start)) {
                                $end->addDay();
                            }
                            $shiftDuration = $start->diffInHours($end);
                            // Shift duration + 6 hours grace period for late checkout
                            $validityHours = $shiftDuration + 6;
                        }
                    } catch (\Exception $e) {
                        // Keep default validity hours on parse error
                    }
                }

                // Allow check-out for yesterday ONLY if it is within the calculated validity window
                if ($checkInTime->diffInHours(now()) <= $validityHours) {
                    $this->todayAttendance = $yesterdayAttendance;
                }
            }
        }

        // 3. If none active, load today's record (could be already checked out, or null if not punched in)
        if (! $this->todayAttendance) {
            $this->todayAttendance = EmployeeAttendance::where('employee_id', $user->id)
                ->whereDate('date', Carbon::today())
                ->first();
        }

        if ($this->todayAttendance && $this->todayAttendance->working_mode) {
            $this->working_mode = $this->todayAttendance->working_mode;
        } else {
            $this->working_mode = $user->working_mode ?? 'office';
        }
    }

    public function updatedWorkingMode()
    {
        // If already checked in today, lock working mode to check-in mode
        if ($this->todayAttendance && $this->todayAttendance->check_in) {
            $this->working_mode = $this->todayAttendance->working_mode ?: 'office';
        }

        if ($this->latitude && $this->longitude) {
            $this->checkGeofenceStatus($this->latitude, $this->longitude);
        }
    }

    public function checkGeofenceStatus($lat, $lng)
    {
        $this->latitude = (float) $lat;
        $this->longitude = (float) $lng;

        if ($this->working_mode === 'office') {
            if (! empty($this->officeLat) && ! empty($this->officeLng)) {
                $this->distanceMeters = $this->calculateDistanceMeters($lat, $lng, $this->officeLat, $this->officeLng);
                $this->isWithinGeofence = ($this->distanceMeters <= $this->officeRadius);
            } else {
                $this->distanceMeters = null;
                $this->isWithinGeofence = true;
            }
        } else {
            $this->distanceMeters = null;
            $this->isWithinGeofence = true;
        }
    }

    public function checkLeaveAndHoliday()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $holiday = Holiday::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
            ->whereDate('date', Carbon::today())
            ->first();
        if ($holiday) {
            $this->isHoliday = true;
            $this->holidayName = $holiday->name;
        }

        $leave = EmployeeLeave::where('employee_id', auth()->id())
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', Carbon::today())
            ->whereDate('end_date', '>=', Carbon::today())
            ->first();
        if ($leave) {
            if ($leave->type === 'half_day') {
                $this->isHalfLeave = true;
            } else {
                $this->isFullLeave = true;
            }
        }

        $weekOffDays = PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'week_off_days')->value('value');
        $weekOffDays = $weekOffDays ? json_decode($weekOffDays, true) : ['Sunday'];
        $todayDayName = Carbon::today()->format('l');

        if (in_array($todayDayName, $weekOffDays)) {
            $this->isWeekOff = true;
        }

        if ($this->todayAttendance && $this->todayAttendance->status === 'absent') {
            $this->isAbsent = true;
        }
    }

    public function loadChecklists()
    {
        // Get the partner ID. If the current user is a partner, it's their ID, otherwise it's their parent_id.
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $isCheckingIn = ! $this->todayAttendance || ! $this->todayAttendance->check_in;
        $modes = $isCheckingIn ? ['punch_in', 'both'] : ['punch_out', 'both'];

        $this->checklists = AttendanceChecklist::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
            ->where('is_active', true)
            ->whereIn('mode', $modes)
            ->get();

        // Initialize checklist answers
        $this->checklistAnswers = [];
        foreach ($this->checklists as $checklist) {
            $this->checklistAnswers[$checklist->id] = null; // null means unanswered
        }
    }

    private function calculateDistanceMeters($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad((float) $lat2 - (float) $lat1);
        $dLon = deg2rad((float) $lon2 - (float) $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad((float) $lat1)) * cos(deg2rad((float) $lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($earthRadius * $c);
    }

    public function processCheckInOut()
    {
        if ($this->isHoliday || $this->isWeekOff || $this->isFullLeave || $this->isAbsent) {
            session()->flash('error', 'You cannot mark attendance on a holiday, week off, full leave, or if you are already marked absent.');

            return;
        }

        $rules = [
            'photoData' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'working_mode' => 'required|in:office,remote,field',
        ];

        $messages = [
            'photoData.required' => 'You must capture a selfie to check in/out.',
            'latitude.required' => 'GPS location is required for all working modes. Please allow location access.',
            'longitude.required' => 'GPS location is required for all working modes. Please allow location access.',
            'working_mode.required' => 'Please select a working mode.',
        ];

        if (auth()->user()->hasExitedOnOrBefore(Carbon::today())) {
            $lwd = auth()->user()->getLastWorkingDate();
            $lwdText = $lwd ? $lwd->format('d M, Y') : 'your last working date';
            session()->flash('error', "Your employment ended on {$lwdText}. Attendance marking is no longer allowed.");

            return;
        }

        // If Working Mode is Office, check geofence radius range
        if ($this->working_mode === 'office') {
            if (! empty($this->officeLat) && ! empty($this->officeLng)) {
                $distance = $this->calculateDistanceMeters($this->latitude, $this->longitude, $this->officeLat, $this->officeLng);
                $this->distanceMeters = $distance;

                if ($distance > $this->officeRadius) {
                    $this->isWithinGeofence = false;
                    session()->flash('error', "Out of Geofence Range! You are {$distance}m away from office (Allowed radius: {$this->officeRadius}m). Attendance not allowed in Office Mode.");

                    return;
                }
            }
        }

        // If checking in, validate checklist
        $isCheckingIn = ! $this->todayAttendance || ! $this->todayAttendance->check_in;

        if (count($this->checklists) > 0) {
            foreach ($this->checklists as $checklist) {
                $rules['checklistAnswers.'.$checklist->id] = 'required|boolean';
                $messages['checklistAnswers.'.$checklist->id.'.required'] = 'Please answer: '.$checklist->question;
            }
        }

        $this->validate($rules, $messages);

        // Save Photo
        $imageParts = explode(';base64,', $this->photoData);
        $imageTypeAux = explode('image/', $imageParts[0]);
        $imageType = $imageTypeAux[1] ?? 'png';
        $imageBase64 = base64_decode($imageParts[1]);
        $fileName = 'attendance/'.auth()->id().'/'.uniqid().'.'.$imageType;

        Storage::disk('public')->put($fileName, $imageBase64);
        $photoUrl = Storage::url($fileName);

        $responsesToSave = [];
        foreach ($this->checklists as $checklist) {
            $responsesToSave[] = [
                'question' => $checklist->question,
                'answer' => $this->checklistAnswers[$checklist->id] ? 'Yes' : 'No',
            ];
        }

        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $user = auth()->user();

        // 1. Get Global Fallbacks
        $globalCheckIn = PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'fixed_check_in_time')->value('value') ?? '09:00';
        $globalLateGrace = (int) (PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'late_grace_period')->value('value') ?? 15);
        $globalMinPresent = (int) (PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'min_present_mins')->value('value') ?? 480);
        $globalMinHalfDay = (int) (PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'min_half_day_mins')->value('value') ?? 240);

        // 2. Fetch Employee's Assigned Shift
        $shift = $user->shift ?? ($this->todayAttendance?->shift_id ? \App\Models\WorkShift::find($this->todayAttendance->shift_id) : null);

        $fixedCheckIn = ($shift && $shift->start_time) ? $shift->start_time : $globalCheckIn;
        $lateGracePeriod = (int) ($shift ? ($shift->late_tolerance_minutes ?? 15) : $globalLateGrace);

        $now = Carbon::now();

        if ($isCheckingIn) {
            // Check In
            $expectedCheckIn = Carbon::parse($fixedCheckIn);
            // Ensure same date for correct diff
            $nowTime = $now->format('H:i:s');
            $expectedTime = Carbon::parse($fixedCheckIn)->format('H:i:s');

            $lateMinutes = 0;
            if ($nowTime > $expectedTime) {
                $diff = Carbon::parse($expectedTime)->diffInMinutes(Carbon::parse($nowTime));
                $diff = (int) abs($diff);
                if ($diff > $lateGracePeriod) {
                    $lateMinutes = $diff;
                }
            }

            EmployeeAttendance::updateOrCreate(
                [
                    'employee_id' => auth()->id(),
                    'date' => Carbon::today(),
                ],
                [
                    'branch_id' => $user->branch_id,
                    'shift_id' => $user->shift_id,
                    'check_in' => $now->format('H:i:s'),
                    'check_in_selfie' => $photoUrl,
                    'check_in_lat' => $this->latitude,
                    'check_in_lng' => $this->longitude,
                    'status' => 'punch_in',
                    'working_mode' => $this->working_mode,
                    'late_minutes' => $lateMinutes > 0 ? $lateMinutes : null,
                    'checklist_responses' => count($responsesToSave) > 0 ? $responsesToSave : null,
                ]
            );

            $modeLabel = ucfirst($this->working_mode);
            $msg = "Checked In successfully ($modeLabel Mode)! Status is now Punch In.";
            if ($lateMinutes > 0) {
                $msg .= " (Late by $lateMinutes mins)";
            }
            session()->flash('success', $msg);
        } else {
            // Check Out: Lock working mode to original Punch In working mode
            $lockedMode = $this->todayAttendance->working_mode ?: $this->working_mode;
            $this->working_mode = $lockedMode;

            // Combine the actual date of the attendance record with the check_in time to support night shifts correctly
            $dateString = $this->todayAttendance->date instanceof Carbon
                ? $this->todayAttendance->date->format('Y-m-d')
                : Carbon::parse($this->todayAttendance->date)->format('Y-m-d');

            $checkInTime = Carbon::parse($dateString.' '.$this->todayAttendance->check_in);
            $workingMinutes = (int) abs($now->diffInMinutes($checkInTime));

            // Calculate shift duration and dynamic thresholds
            $shiftDurationMins = null;
            $shiftEndTimeToday = null;

            if ($shift && $shift->start_time && $shift->end_time) {
                try {
                    $sStart = Carbon::parse($dateString . ' ' . $shift->start_time);
                    $sEnd = Carbon::parse($dateString . ' ' . $shift->end_time);

                    if ($sEnd->lt($sStart)) {
                        $sEnd12 = $sEnd->copy()->addHours(12);
                        if ($sEnd12->gt($sStart) && $sStart->diffInMinutes($sEnd12) <= 720) {
                            $sEnd = $sEnd12;
                        } else {
                            $sEnd->addDay();
                        }
                    }

                    $shiftDurationMins = (int) abs($sStart->diffInMinutes($sEnd));
                    $shiftEndTimeToday = $sEnd;
                } catch (\Exception $e) {}
            }

            if ($shiftDurationMins && $shiftDurationMins > 0) {
                // Half-day is considered if working at least half the shift or reaching half-day requirement
                if (!empty($shift->min_half_day_mins) && $shift->min_half_day_mins > 0 && $shift->min_half_day_mins < $shiftDurationMins) {
                    $minHalfDay = (int) $shift->min_half_day_mins;
                } else {
                    $minHalfDay = (int) round($shiftDurationMins / 2);
                }

                // Full-day requirement
                if (!empty($shift->min_present_mins) && $shift->min_present_mins > 0 && $shift->min_present_mins <= $shiftDurationMins) {
                    $minPresent = (int) $shift->min_present_mins;
                } else {
                    $minPresent = $shiftDurationMins;
                }
            } else {
                $minPresent = (int) $globalMinPresent;
                $minHalfDay = (int) $globalMinHalfDay;
            }

            // Full day threshold allowing late/early tolerance
            $fullDayThreshold = max($minHalfDay + 1, $minPresent - $lateGracePeriod);

            // Check if employee punched out at or after scheduled shift end (minus tolerance)
            $punchedOutAtShiftEnd = false;
            if ($shiftEndTimeToday) {
                $earliestShiftEnd = $shiftEndTimeToday->copy()->subMinutes($lateGracePeriod);
                if ($now->greaterThanOrEqualTo($earliestShiftEnd)) {
                    $punchedOutAtShiftEnd = true;
                }
            }

            // Determine status:
            // - If working hours >= fullDayThreshold (completed full shift duration minus grace) => punch_out (Present)
            // - If working hours >= minHalfDay (e.g. at least 2 hours / 2 hours before shift end) => half_day
            // - If working hours < minHalfDay (less than 2 hours) => absent
            $status = 'absent';
            if ($workingMinutes >= $fullDayThreshold) {
                $status = 'punch_out';
            } elseif ($workingMinutes >= $minHalfDay) {
                $status = 'half_day';
            }

            $this->todayAttendance->update([
                'check_out' => $now->format('H:i:s'),
                'check_out_selfie' => $photoUrl,
                'check_out_lat' => $this->latitude,
                'check_out_lng' => $this->longitude,
                'status' => $status,
                'working_mode' => $lockedMode,
                'working_minutes' => $workingMinutes,
                'check_out_checklist_responses' => count($responsesToSave) > 0 ? $responsesToSave : null,
            ]);

            $statusLabel = $status === 'punch_out' ? 'Present' : ucfirst(str_replace('_', ' ', $status));
            $hours = floor($workingMinutes / 60);
            $mins = $workingMinutes % 60;
            $timeWorkedText = $hours > 0 ? "{$hours}h {$mins}m" : "{$mins}m";
            session()->flash('success', "Checked Out successfully! Final Status: {$statusLabel} (Worked: {$timeWorkedText})");
        }

        $this->loadTodayAttendance();
        $this->reset(['photoData', 'latitude', 'longitude', 'checklistAnswers']);
        $this->loadChecklists(); // Reload in case we need it, though they can't check in again
    }

    public function render()
    {
        return view('livewire.partner.hrms.attendance.mark-attendance')
            ->layout('layouts.app', [
                'panelName' => 'Partner Panel',
                'pageTitle' => 'Mark Attendance',
                'pageSubtitle' => 'Capture your selfie and location',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
