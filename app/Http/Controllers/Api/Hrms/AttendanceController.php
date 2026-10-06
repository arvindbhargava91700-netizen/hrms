<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Api\Hrms\Traits\HasHrmsApiFilters;
use App\Http\Controllers\Controller;
use App\Models\AttendanceChecklist;
use App\Models\AttendanceOverride;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeave;
use App\Models\Holiday;
use App\Models\PartnerSetting;
use App\Models\User;
use App\Models\WorkShift;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    use HasHrmsApiFilters;

    public function checklists(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $mode = $request->query('mode', 'punch_in');

        $query = AttendanceChecklist::where('partner_id', $partnerId)
            ->where('is_active', true);

        if ($mode == 'punch_in' || $mode == 'punch_out') {
            $query->whereIn('mode', ['both', $mode]);
        }

        $checklists = $query->get();

        if ($checklists->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No checklists found for this mode.',
                'data' => [],
            ], 200);
        }

        $checklists->transform(function ($checklist) {
            $checklist->is_checked = false;

            return $checklist;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Checklists fetched successfully.',
            'data' => $checklists,
        ]);
    }

    public function submitChecklist(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $request->validate([
            'mode' => 'required|in:punch_in,punch_out',
            'checklistAnswers' => 'required|array',
            'checklistAnswers.*.id' => 'nullable|exists:attendance_checklists,id,partner_id,'.$partnerId,
        ], [
            'checklistAnswers.*.id.exists' => 'One or more selected checklist points are invalid.',
        ]);

        $today = Carbon::today()->toDateString();
        $attendance = EmployeeAttendance::where('employee_id', $user->id)
            ->where('date', $today)
            ->first();

        $checklistIds = collect($request->checklistAnswers)->pluck('id')->filter()->toArray();
        $checklistsList = AttendanceChecklist::whereIn('id', $checklistIds)->get();

        if ($request->mode === 'punch_in') {
            if ($attendance && $attendance->checklist_responses) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have already submitted checklist points.',
                ], 422);
            }

            if (! $attendance || ! $attendance->check_in) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You cannot submit checklist points before punching in.',
                ], 422);
            }

            $responsesToSave = [];
            foreach ($request->checklistAnswers as $ans) {
                $isChecked = isset($ans['is_checked']) ? (bool) $ans['is_checked'] : (isset($ans['answer']) ? (bool) $ans['answer'] : false);
                if (isset($ans['id'])) {
                    $checklist = $checklistsList->firstWhere('id', $ans['id']);
                    $question = $checklist ? $checklist->question : ($ans['question'] ?? null);
                    $responsesToSave[] = [
                        'question' => $question,
                        'answer' => $isChecked,
                    ];
                } elseif (isset($ans['question'])) {
                    $responsesToSave[] = [
                        'question' => $ans['question'],
                        'answer' => $isChecked,
                    ];
                }
            }

            EmployeeAttendance::updateOrCreate(
                [
                    'employee_id' => $user->id,
                    'date' => $today,
                ],
                [
                    'checklist_responses' => count($responsesToSave) > 0 ? $responsesToSave : null,
                ]
            );

        } else {

            if (! $attendance || ! $attendance->check_in) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have not punched in today.',
                ], 422);
            }

            if ($attendance->check_out) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have already punched out for today.',
                ], 422);
            }

            $existingResponses = $attendance->checklist_responses ?? [];
            $newResponses = [];
            foreach ($request->checklistAnswers as $ans) {
                $isChecked = isset($ans['is_checked']) ? (bool) $ans['is_checked'] : (isset($ans['answer']) ? (bool) $ans['answer'] : false);
                if (isset($ans['id'])) {
                    $checklist = $checklistsList->firstWhere('id', $ans['id']);
                    $question = $checklist ? $checklist->question : ($ans['question'] ?? null);
                    $newResponses[] = [
                        'question' => $question,
                        'answer' => $isChecked,
                    ];
                } elseif (isset($ans['question'])) {
                    $newResponses[] = [
                        'question' => $ans['question'],
                        'answer' => $isChecked,
                    ];
                }
            }

            $allResponses = array_merge($existingResponses, $newResponses);
            $attendance->update([
                'checklist_responses' => count($allResponses) > 0 ? $allResponses : null,
            ]);
        }

        $attendance->refresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Checklist submitted successfully.',
            'data' => $attendance,
        ]);
    }

    public function punchIn(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'selfie' => 'required|image|max:2048',
            'working_mode' => 'nullable|in:office,remote,field',
        ]);

        $workingMode = strtolower($request->input('working_mode', 'office'));

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $todayDate = Carbon::today();

        $holiday = Holiday::where('partner_id', $partnerId)->whereDate('date', $todayDate)->first();
        if ($holiday) {
            return response()->json(['status' => 'error', 'message' => 'You cannot mark attendance on a holiday.'], 422);
        }

        $leave = EmployeeLeave::where('employee_id', $user->id)->where('status', 'approved')
            ->whereDate('start_date', '<=', $todayDate)->whereDate('end_date', '>=', $todayDate)->first();
        if ($leave) {
            return response()->json(['status' => 'error', 'message' => 'You cannot mark attendance while on an approved leave.'], 422);
        }

        $today = $todayDate->toDateString();
        $attendance = EmployeeAttendance::where('employee_id', $user->id)->where('date', $today)->first();

        if ($attendance && $attendance->check_in) {
            return response()->json(['status' => 'error', 'message' => 'You have already punched in for today.'], 422);
        }

        // Office Geofence Validation
        if ($workingMode === 'office') {
            $officeLat = PartnerSetting::where('partner_id', $partnerId)->where('key', 'office_latitude')->value('value');
            $officeLng = PartnerSetting::where('partner_id', $partnerId)->where('key', 'office_longitude')->value('value');
            $officeRadius = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'office_radius_meters')->value('value') ?? 200);

            if (! empty($officeLat) && ! empty($officeLng)) {
                $distance = $this->calculateDistanceMeters($request->lat, $request->lng, (float) $officeLat, (float) $officeLng);
                if ($distance > $officeRadius) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Out of Geofence Range! You are {$distance}m away from office (Max allowed radius: {$officeRadius}m). Attendance not allowed in Office Mode.",
                        'data' => [
                            'distance_meters' => $distance,
                            'office_radius_meters' => $officeRadius,
                        ],
                    ], 422);
                }
            }
        }

        $shiftCheckIn = ($user->shift_id && $user->shift) ? $user->shift->start_time : null;
        $shiftLateGrace = ($user->shift_id && $user->shift) ? $user->shift->late_tolerance_minutes : null;

        $fixedCheckIn = $shiftCheckIn ?? PartnerSetting::where('partner_id', $partnerId)->where('key', 'fixed_check_in_time')->value('value') ?? '09:00';
        $lateGracePeriod = (int) ($shiftLateGrace ?? PartnerSetting::where('partner_id', $partnerId)->where('key', 'late_grace_period')->value('value') ?? 15);

        $now = Carbon::now();
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

        $selfiePath = $request->file('selfie')->store('attendance_selfies', 'public');

        $attendance = EmployeeAttendance::updateOrCreate(
            ['employee_id' => $user->id, 'date' => $today],
            [
                'check_in' => $now->format('H:i:s'),
                'check_in_lat' => $request->lat,
                'check_in_lng' => $request->lng,
                'check_in_selfie' => $selfiePath,
                'status' => 'punch_in',
                'working_mode' => $workingMode,
                'late_minutes' => $lateMinutes > 0 ? $lateMinutes : null,
            ]
        );

        $attendance->refresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Punched in successfully ('.ucfirst($workingMode).' Mode).',
            'data' => $attendance,
        ], 201);
    }

    public function punchOut(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'selfie' => 'required|image|max:2048',
        ]);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $todayDate = Carbon::today();

        $holiday = Holiday::where('partner_id', $partnerId)->whereDate('date', $todayDate)->first();
        if ($holiday) {
            return response()->json(['status' => 'error', 'message' => 'You cannot mark attendance on a holiday.'], 422);
        }
        $leave = EmployeeLeave::where('employee_id', $user->id)->where('status', 'approved')
            ->whereDate('start_date', '<=', $todayDate)->whereDate('end_date', '>=', $todayDate)->first();
        if ($leave) {
            return response()->json(['status' => 'error', 'message' => 'You cannot mark attendance while on an approved leave.'], 422);
        }

        $today = $todayDate->toDateString();
        $attendance = EmployeeAttendance::where('employee_id', $user->id)->where('date', $today)->first();

        if (! $attendance || ! $attendance->check_in) {
            return response()->json(['status' => 'error', 'message' => 'You have not punched in today.'], 422);
        }

        if ($attendance->check_out) {
            return response()->json(['status' => 'error', 'message' => 'You have already punched out for today.'], 422);
        }

        $shiftMinPresent = ($user->shift_id && $user->shift) ? $user->shift->min_present_mins : null;
        $shiftMinHalfDay = ($user->shift_id && $user->shift) ? $user->shift->min_half_day_mins : null;

        // Cast: these come back from PartnerSetting as strings.
        $minPresent = (int) ($shiftMinPresent ?? PartnerSetting::where('partner_id', $partnerId)->where('key', 'min_present_mins')->value('value') ?? 480);
        $minShortLeave = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'min_short_leave_mins')->value('value') ?? 420);
        $minHalfDay = (int) ($shiftMinHalfDay ?? PartnerSetting::where('partner_id', $partnerId)->where('key', 'min_half_day_mins')->value('value') ?? 240);

        $now = Carbon::now();
        $checkInTime = Carbon::parse($attendance->check_in);
        $checkInTime->setDate($now->year, $now->month, $now->day);
        $workingMinutes = (int) abs($now->diffInMinutes($checkInTime));

        $status = 'absent';
        if ($workingMinutes >= $minPresent) {
            $status = 'punch_out';
        } elseif ($workingMinutes >= $minShortLeave) {
            $status = 'short_leave';
        } elseif ($workingMinutes >= $minHalfDay) {
            $status = 'half_day';
        }

        $selfiePath = $request->file('selfie')->store('attendance_selfies', 'public');

        $attendance->update([
            'check_out' => $now->format('H:i:s'),
            'check_out_lat' => $request->lat,
            'check_out_lng' => $request->lng,
            'check_out_selfie' => $selfiePath,
            'working_minutes' => $workingMinutes,
            'status' => $status,
        ]);

        $attendance->refresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Punched out successfully.',
            'data' => $attendance,
        ]);
    }

    /**
     * Get Today's Attendance
     */
    public function today(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $today = Carbon::today();
        $todayDate = $today->toDateString();
        //dd($todayDate);

        // 1. Look for an active (no check-out) attendance from today
        $attendance = EmployeeAttendance::where('employee_id', $user->id)
            ->whereNull('check_out')
            ->whereDate('date', $todayDate)
            ->first();

        // 2. If no active attendance today, check yesterday (for night shifts or late checkouts)
        if (! $attendance) {
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
                        $start = Carbon::createFromFormat('H:i:s', $user->shift->start_time);
                        $end = Carbon::createFromFormat('H:i:s', $user->shift->end_time);
                        if ($end->lt($start)) {
                            $end->addDay();
                        }
                        // Shift duration + 6 hours grace period for late checkout
                        $validityHours = $start->diffInHours($end) + 6;
                    } catch (\Exception $e) {
                        // Keep default validity hours on parse error
                    }
                }

                if ($checkInTime->diffInHours(now()) <= $validityHours) {
                    $attendance = $yesterdayAttendance;
                }
            }
        }

        // 3. If none active, load today's record (could be already checked out, or null if not punched in)
        if (! $attendance) {
            $attendance = EmployeeAttendance::where('employee_id', $user->id)
                ->whereDate('date', $todayDate)
                ->first();
        }

        // Status flags (mirror the Web Mark Attendance screen)
        $isHoliday = false;
        $holidayName = null;
        $isWeekOff = false;
        $isFullLeave = false;
        $isHalfLeave = false;
        $isAbsent = false;
        $virtualStatus = 'notPunchIn';

        $holiday = Holiday::where('partner_id', $partnerId)
            ->whereDate('date', $todayDate)
            ->first();
        if ($holiday) {
            $isHoliday = true;
            $holidayName = $holiday->name;
            $virtualStatus = 'holiday';
        }

        $weekOffDays = PartnerSetting::where('partner_id', $partnerId)
            ->where('key', 'week_off_days')
            ->value('value');
        $weekOffDays = $weekOffDays ? json_decode($weekOffDays, true) : ['Sunday'];
        if (! $isHoliday && in_array($today->format('l'), $weekOffDays)) {
            $isWeekOff = true;
            $virtualStatus = 'weekOff';
        }

        $leave = EmployeeLeave::where('employee_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $todayDate)
            ->whereDate('end_date', '>=', $todayDate)
            ->first();
        if ($leave) {
            if ($leave->type === 'half_day') {
                $isHalfLeave = true;
            } else {
                $isFullLeave = true;
                $virtualStatus = 'leave';
            }
        }

        if (! $attendance) {
            if ($isHoliday || $isWeekOff || $isFullLeave) {
                $isAbsent = false;
            } else {
                // Auto-absent cutoff
                $settings = PartnerSetting::where('partner_id', $partnerId)
                    ->whereIn('key', ['fixed_check_in_time', 'auto_absent_mark_mins'])
                    ->pluck('value', 'key');

                $shiftCheckIn = ($user->shift_id && $user->shift) ? $user->shift->start_time : null;
                $shiftAutoAbsentMins = ($user->shift_id && $user->shift) ? $user->shift->auto_absent_mark_mins : null;

                $checkInTime = $shiftCheckIn ?? $settings['fixed_check_in_time'] ?? '09:00';
                $autoAbsentMinutes = (int) ($shiftAutoAbsentMins ?? $settings['auto_absent_mark_mins'] ?? 60);

                $cutoffTime = Carbon::parse($todayDate.' '.$checkInTime)->addMinutes($autoAbsentMinutes);

                if (Carbon::now()->greaterThanOrEqualTo($cutoffTime)) {
                    $isAbsent = true;
                    $virtualStatus = 'absent';
                }
            }
        }

        $workingMode = $user->working_mode ?? 'office';
        if ($attendance) {
            if ($attendance->status === 'absent') {
                $isAbsent = true;
            }
            if ($attendance->working_mode) {
                $workingMode = $attendance->working_mode;
            }
        }

        // Geofence information (mirror the Web Mark Attendance screen)
        $officeLat = null;
        $officeLng = null;
        $officeRadius = 200;
        if ($user->branch_id && $user->branch) {
            $officeLat = $user->branch->lat;
            $officeLng = $user->branch->lng;
            $officeRadius = $user->branch->radius ?: 200;
        } else {
            $officeLat = PartnerSetting::where('partner_id', $partnerId)->where('key', 'office_latitude')->value('value');
            $officeLng = PartnerSetting::where('partner_id', $partnerId)->where('key', 'office_longitude')->value('value');
            $officeRadius = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'office_radius_meters')->value('value') ?? 200);
        }

        $distanceMeters = null;
        $isWithinGeofence = true;
        if ($request->has('lat') && $request->has('lng') && ! empty($officeLat) && ! empty($officeLng)) {
            $distanceMeters = $this->calculateDistanceMeters($request->input('lat'), $request->input('lng'), (float) $officeLat, (float) $officeLng);
            $isWithinGeofence = $distanceMeters <= $officeRadius;
        }

        // Active checklists for the current phase (mirror the Web Mark Attendance screen)
        $isCheckingIn = ! $attendance || ! $attendance->check_in;
        $checklistModes = $isCheckingIn ? ['punch_in', 'both'] : ['punch_out', 'both'];
        $checklists = AttendanceChecklist::where('partner_id', $partnerId)
            ->where('is_active', true)
            ->whereIn('mode', $checklistModes)
            ->get(['id', 'question', 'mode'])
            ->map(function ($checklist) {
                $checklist->is_checked = false;

                return $checklist;
            })
            ->values();

        if (! $attendance) {
            // Return virtual attendance object
            $attendance = [
                'id' => null,
                'employee_id' => $user->id,
                'date' => $todayDate,
                'branch_id' => $user->branch_id,
                'shift_id' => $user->shift_id,
                'check_in' => null,
                'check_out' => null,
                'check_in_lat' => null,
                'check_in_lng' => null,
                'check_in_photo' => null,
                'check_in_selfie' => null,
                'check_out_lat' => null,
                'check_out_lng' => null,
                'check_out_photo' => null,
                'check_out_selfie' => null,
                'status' => $virtualStatus,
                'working_mode' => $workingMode,
                'working_minutes' => null,
                'late_minutes' => null,
                'checklist_responses' => null,
                'check_out_checklist_responses' => null,
                'created_at' => null,
                'updated_at' => null,
                'check_in_selfie_url' => null,
                'check_out_selfie_url' => null,
                'check_in_photo_url' => null,
                'check_out_photo_url' => null,
            ];
        }

        if ($attendance && isset($attendance->working_minutes)) {
            $attendance->working_minutes = abs((int) $attendance->working_minutes) ?? 0;
        }

        // Guard against stale leave rows. Leave approvals write a
        // status='leave'/'half_day' attendance row for each date. If that
        // leave is later rejected/deleted, the row stays behind and wrongly
        // reports today as leave. Only honour it when an approved leave
        // actually covers today (mirrors the Web Mark Attendance screen).
        if ($attendance) {
            $storedStatus = is_array($attendance) ? ($attendance['status'] ?? null) : $attendance->status;

            if (in_array($storedStatus, ['leave', 'half_day']) && ! $isFullLeave && ! $isHalfLeave) {
                $replacement = $isAbsent ? 'absent' : 'notPunchIn';
                if (is_array($attendance)) {
                    $attendance['status'] = $replacement;
                } else {
                    $attendance->status = $replacement;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $attendance,
            'is_checking_in' => $isCheckingIn,
            'flags' => [
                'is_holiday' => $isHoliday,
                'holiday_name' => $holidayName,
                'is_week_off' => $isWeekOff,
                'is_full_leave' => $isFullLeave,
                'is_half_leave' => $isHalfLeave,
                'is_absent' => $isAbsent,
            ],
            'working_mode' => $workingMode,
            'geofence' => [
                'office_latitude' => $officeLat,
                'office_longitude' => $officeLng,
                'office_radius_meters' => $officeRadius,
                'distance_meters' => $distanceMeters,
                'is_within_geofence' => $isWithinGeofence,
            ],
            'checklists' => $checklists,
        ]);
    }

    /**
     * Get Attendance Details by ID
     */
    public function show($id): JsonResponse
    {
        $user = auth('hrms_api')->user();

        $attendance = EmployeeAttendance::whereHas('employee', fn ($q) => $q->activeForHrms())
            ->find($id);

        if (! $attendance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Attendance record not found.',
            ], 404);
        }

        if ($attendance->employee_id !== $user->id) {
            $teamIds = $this->getTeamEmployeeIds('attendance_viewAny');
            if (! in_array($attendance->employee_id, $teamIds)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to view this attendance record.',
                ], 403);
            }
        }

        if ($attendance && isset($attendance->working_minutes)) {
            $attendance->working_minutes = abs((int) $attendance->working_minutes) ?? 0;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance details fetched successfully.',
            'data' => $attendance,
        ]);
    }

    /**
     * Get Attendance History
     */
    public function history(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $employee = $user;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        if ($endDate->isFuture()) {
            $endDate = Carbon::today();
        }

        $attendances = EmployeeAttendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()->keyBy(function ($item) {
                return Carbon::parse($item->date)->toDateString();
            });

        $leaves = EmployeeLeave::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhereBetween('end_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->get();

        $summary = [
            'punch_out' => 0,
            'absent' => 0,
            'half_day' => 0,
            'punch_in' => 0,
            'leave' => 0,
            'holiday' => 0,
            'weekOff' => 0,
        ];

        $history = [];
        $partnerId = $employee->isPartner() ? $employee->id : $employee->parent_id;

        // Pre-fetch holidays for the month
        $holidays = Holiday::where('partner_id', $partnerId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('date')->toArray();

        $shiftCheckIn = ($employee->shift_id && $employee->shift) ? $employee->shift->start_time : null;
        $shiftAutoAbsentMins = ($employee->shift_id && $employee->shift) ? $employee->shift->auto_absent_mark_mins : null;

        $checkInSetting = $shiftCheckIn ?? PartnerSetting::where('partner_id', $partnerId)
            ->where('key', 'fixed_check_in_time')->value('value') ?? '09:00';

        // Cast: PartnerSetting values come back as strings from the DB and must be
        // numeric (int|float) before being passed to Carbon's addMinutes().
        $absentMins = (int) ($shiftAutoAbsentMins ?? PartnerSetting::where('partner_id', $partnerId)
            ->where('key', 'auto_absent_mark_mins')->value('value') ?? 60);

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->toDateString();

            $status = 'absent';
            $statusReason = 'No Attendance';
            $checkIn = null;
            $checkOut = null;
            $workingMinutes = null;
            $attendanceId = null;

            if ($attendances->has($dateString)) {
                $att = $attendances->get($dateString);
                $attendanceId = $att->id;
                $status = $att->status;
                $checkIn = $att->check_in;
                $checkOut = $att->check_out;
                $workingMinutes = null;
                if ($att->working_minutes) {
                    $workingMinutes = abs((int) $att->working_minutes);
                }

                if (in_array($status, ['punch_out', 'half_day', 'short_leave', 'late'])) {
                    $summary['punch_out']++;
                } elseif ($status === 'punch_in') {
                    $summary['punch_in']++;
                } elseif ($status === 'absent') {
                    $summary['absent']++;
                } elseif ($status === 'leave') {
                    $summary['leave']++;
                } elseif ($status === 'weekOff') {
                    $summary['weekOff']++;
                } elseif ($status === 'holiday') {
                    $summary['holiday']++;
                }

                $statusReason = ucfirst(str_replace('_', ' ', $status));
            } else {
                $isLeave = false;
                $isHalfDayLeave = false;
                foreach ($leaves as $leave) {
                    if ($date->between(Carbon::parse($leave->start_date), Carbon::parse($leave->end_date))) {
                        if ($leave->type === 'half_day') {
                            $isHalfDayLeave = true;
                        } else {
                            $isLeave = true;
                            $status = 'leave';
                            $statusReason = $leave->leave_type ?? 'Leave';
                            $summary['leave']++;
                            break;
                        }
                    }
                }

                if (! $isLeave) {
                    $isHoliday = in_array($dateString, $holidays);
                    if ($date->isSunday() || $isHoliday) {
                        $status = $isHoliday ? 'holiday' : 'weekOff';
                        $statusReason = $isHoliday ? 'Holiday' : 'Sunday';
                    } else {
                        if ($dateString === Carbon::today()->toDateString()) {
                            $cutoffMins = $absentMins;
                            if ($isHalfDayLeave) {
                                $shiftMinHalfDay = ($employee->shift_id && $employee->shift) ? $employee->shift->min_half_day_mins : null;
                                $minHalfDay = (int) ($shiftMinHalfDay ?? PartnerSetting::where('partner_id', $partnerId)->where('key', 'min_half_day_mins')->value('value') ?? 240);
                                $cutoffMins += $minHalfDay;
                            }
                            $cutoffTime = Carbon::parse($dateString.' '.$checkInSetting)->addMinutes($cutoffMins);
                            if (Carbon::now()->greaterThanOrEqualTo($cutoffTime)) {
                                $status = 'absent';
                                $summary['absent']++;
                            } else {
                                $status = 'notPunchIn';
                                $statusReason = 'Not Punch In';
                            }
                        } else {
                            $status = 'absent';
                            $summary['absent']++;
                        }
                    }
                }
            }

            // Apply status filter
            $requestedStatus = strtolower($request->input('status', 'all status'));
            $include = true;
            if ($requestedStatus !== 'all status' && $requestedStatus !== 'all_status' && $requestedStatus !== '') {
                if ($requestedStatus === 'punch_out' && ! in_array($status, ['punch_out', 'half_day', 'late'])) {
                    $include = false;
                } elseif ($requestedStatus !== 'punch_out' && strtolower($status) !== $requestedStatus) {
                    $include = false;
                }
            }

            if ($include) {
                $history[] = [
                    'id' => $attendanceId,
                    'date' => $dateString,
                    'status' => $status,
                    'status_reason' => $statusReason,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'working_minutes' => $workingMinutes !== null ? abs((int) $workingMinutes) : null,
                ];
            }
        }

        usort($history, function ($a, $b) {
            return strtotime($b['date']) <=> strtotime($a['date']);
        });

        $message = empty($history) ? 'No attendance history found.' : 'Attendance history fetched successfully.';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'summary' => $summary,
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'profile_image_url' => $employee->profile_image_url,
            ],
            'data' => $history,
        ]);
    }

    /**
     * Get Team Employees List
     */
    public function teamEmployees(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $teamIds = $this->getTeamEmployeeIds('attendance_viewAny');
        $today = Carbon::today()->toDateString();

        $query = User::whereIn('id', $teamIds)->where('role', 'employee')->activeForHrms()
            ->with([
                'attendances' => function ($q) use ($today) {
                    $q->where('date', $today);
                },
                'leaves' => function ($q) use ($today) {
                    $q->where('status', 'approved')
                        ->whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today);
                },
            ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $employees = $query->paginate(20);

        $employees->getCollection()->transform(function ($employee) {
            $status = 'absent';
            $attendance = $employee->attendances->first();
            $leave = $employee->leaves->first();

            if ($attendance) {
                $status = $attendance->status;
            } elseif ($leave) {
                $status = 'leave';
            }
            $employee->attendance_id = $attendance ? $attendance->id : null;
            $employee->today_status = $status;
            unset($employee->attendances);
            unset($employee->leaves);

            return $employee;
        });

        $message = $employees->isEmpty() ? 'No team employees found.' : 'Team employees fetched successfully.';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $employees,
        ]);
    }

    /**
     * Get Team's Today Attendance Summary and List
     */
    public function teamToday(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $teamIds = $this->getTeamEmployeeIds('attendance_viewAny');
        $today = Carbon::today()->toDateString();

        $query = User::whereIn('id', $teamIds)->where('role', 'employee')->activeForHrms()
            ->with([
                'attendances' => function ($q) use ($today) {
                    $q->where('date', $today);
                },
                'leaves' => function ($q) use ($today) {
                    $q->where('status', 'approved')
                        ->whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today);
                },
            ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $employees = $query->get();

        $summary = [
            'punch_out' => 0,
            'absent' => 0,
            'half_day' => 0,
            'punch_in' => 0,
            'leave' => 0,
            'holiday' => 0,
            'weekOff' => 0,
        ];

        $filteredEmployees = [];

        foreach ($employees as $employee) {
            $status = 'absent';
            $statusReason = 'No Attendance';
            $checkIn = null;
            $checkOut = null;

            $attendance = $employee->attendances->first();
            $leave = $employee->leaves->first();

            if ($attendance) {
                $status = $attendance->status;
                if (in_array($status, ['punch_out', 'half_day', 'short_leave', 'late'])) {
                    $summary['punch_out']++;
                } elseif ($status === 'punch_in') {
                    $summary['punch_in']++;
                }
                $statusReason = ucfirst(str_replace('_', ' ', $status));
                $checkIn = $attendance->check_in;
                $checkOut = $attendance->check_out;
            } else {
                $isLeave = false;
                $isHalfDayLeave = false;
                if ($leave) {
                    if ($leave->type === 'half_day') {
                        $isHalfDayLeave = true;
                    } else {
                        $isLeave = true;
                        $status = 'leave';
                        $summary['leave']++;
                        $statusReason = $leave->leave_type ?? 'Leave';
                    }
                }

                if (! $isLeave) {
                    // Determine if today is holiday or sunday
                    $partnerId = $employee->isPartner() ? $employee->id : $employee->parent_id;
                    $isHoliday = Holiday::where('partner_id', $partnerId)->where('date', $today)->exists();
                    if (Carbon::today()->isSunday() || $isHoliday) {
                        $status = $isHoliday ? 'holiday' : 'weekOff';
                        $statusReason = $isHoliday ? 'Holiday' : 'Sunday';
                    } else {
                        $shiftCheckIn = ($employee->shift_id && $employee->shift) ? $employee->shift->start_time : null;
                        $shiftAutoAbsentMins = ($employee->shift_id && $employee->shift) ? $employee->shift->auto_absent_mark_mins : null;

                        $checkInSetting = $shiftCheckIn ?? PartnerSetting::where('partner_id', $partnerId)
                            ->where('key', 'fixed_check_in_time')->value('value') ?? '09:00';
                        // Cast: PartnerSetting values are strings; Carbon's addMinutes()
                        // requires int|float.
                        $absentMins = (int) ($shiftAutoAbsentMins ?? PartnerSetting::where('partner_id', $partnerId)
                            ->where('key', 'auto_absent_mark_mins')->value('value') ?? 60);

                        $cutoffMins = $absentMins;
                        if ($isHalfDayLeave) {
                            $shiftMinHalfDay = ($employee->shift_id && $employee->shift) ? $employee->shift->min_half_day_mins : null;
                            $minHalfDay = (int) ($shiftMinHalfDay ?? PartnerSetting::where('partner_id', $partnerId)
                                ->where('key', 'min_half_day_mins')->value('value') ?? 240);
                            $cutoffMins += $minHalfDay;
                        }

                        $cutoffTime = Carbon::parse($today.' '.$checkInSetting)->addMinutes($cutoffMins);
                        if (Carbon::now()->greaterThanOrEqualTo($cutoffTime)) {
                            $status = 'absent';
                            $summary['absent']++;
                        } else {
                            $status = 'notPunchIn';
                            $statusReason = 'Not Punch In';
                        }
                    }
                }
            }

            // Apply status filter
            $requestedStatus = strtolower($request->input('status', 'all status'));
            $include = true;
            if ($requestedStatus !== 'all status' && $requestedStatus !== 'all_status' && $requestedStatus !== '') {
                if ($requestedStatus === 'punch_out' && ! in_array($status, ['punch_out', 'half_day', 'late'])) {
                    $include = false;
                } elseif ($requestedStatus !== 'punch_out' && strtolower($status) !== $requestedStatus) {
                    $include = false;
                }
            }

            if ($include) {
                $employee->attendance_id = $attendance ? $attendance->id : null;
                $employee->today_status = $status;
                $employee->status_reason = $statusReason;
                $employee->check_in = $checkIn;
                $employee->check_out = $checkOut;
                unset($employee->attendances);
                unset($employee->leaves);
                $filteredEmployees[] = $employee;
            }
        }

        $message = empty($filteredEmployees) ? 'No team attendance found.' : 'Team attendance fetched successfully.';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'summary' => $summary,
            'data' => collect($filteredEmployees)->values(),
        ]);
    }

    /**
     * Get Employee Attendance History (Team)
     */
    public function teamHistory(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $teamIds = $this->getTeamEmployeeIds('attendance_viewAny');

        $request->validate([
            'employee_id' => 'required|string',
        ]);

        if (! in_array($request->employee_id, $teamIds)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized or employee not in team.'], 403);
        }

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $employee = User::where('id', $request->employee_id)->activeForHrms()->first();

        if (! $employee) {
            return response()->json(['status' => 'error', 'message' => 'Employee not found or inactive.'], 403);
        }

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        if ($endDate->isFuture()) {
            $endDate = Carbon::today();
        }

        $attendances = EmployeeAttendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()->keyBy(function ($item) {
                return Carbon::parse($item->date)->toDateString();
            });

        $leaves = EmployeeLeave::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhereBetween('end_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->get();

        $summary = [
            'punch_out' => 0,
            'absent' => 0,
            'half_day' => 0,
            'punch_in' => 0,
            'leave' => 0,
            'holiday' => 0,
            'weekOff' => 0,
            'short_leave' => 0,
        ];

        $history = [];
        $partnerId = $employee->isPartner() ? $employee->id : $employee->parent_id;

        // Pre-fetch holidays for the month
        $holidays = Holiday::where('partner_id', $partnerId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('date')->toArray();

        $checkInSetting = PartnerSetting::where('partner_id', $partnerId)
            ->where('key', 'fixed_check_in_time')->value('value') ?? '09:00';

        // BUG FIX: $absentMins was never fetched in this method (it was previously
        // referenced without being defined, which is itself an undefined-variable
        // error). It's fetched here now and cast to int, since PartnerSetting values
        // come back as strings and Carbon's addMinutes() requires int|float.
        $absentMins = (int) (PartnerSetting::where('partner_id', $partnerId)
            ->where('key', 'auto_absent_mark_mins')->value('value') ?? 60);

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->toDateString();

            $status = 'absent';
            $statusReason = 'No Attendance';
            $checkIn = null;
            $checkOut = null;
            $workingMinutes = null;
            $attendanceId = null;

            if ($attendances->has($dateString)) {
                $att = $attendances->get($dateString);
                $attendanceId = $att->id;
                $status = $att->status;
                $checkIn = $att->check_in;
                $checkOut = $att->check_out;

                if ($att->working_minutes) {
                    $workingMinutes = abs((int) $att->working_minutes);
                }

                if (in_array($status, ['punch_out', 'half_day', 'short_leave', 'late'])) {
                    $summary['punch_out']++;
                } elseif ($status === 'punch_in') {
                    $summary['punch_in']++;
                } elseif ($status === 'absent') {
                    $summary['absent']++;
                } elseif ($status === 'leave') {
                    $summary['leave']++;
                } elseif ($status === 'weekOff') {
                    $summary['weekOff']++;
                } elseif ($status === 'holiday') {
                    $summary['holiday']++;
                }

                $statusReason = ucfirst(str_replace('_', ' ', $status));
            } else {
                $isLeave = false;
                $isHalfDayLeave = false;
                foreach ($leaves as $leave) {
                    if ($date->between(Carbon::parse($leave->start_date), Carbon::parse($leave->end_date))) {
                        if ($leave->type === 'half_day') {
                            $isHalfDayLeave = true;
                        } else {
                            $isLeave = true;
                            $status = 'leave';
                            $statusReason = $leave->leave_type ?? 'Leave';
                            $summary['leave']++;
                            break;
                        }
                    }
                }

                if (! $isLeave) {
                    $isHoliday = in_array($dateString, $holidays);
                    if ($date->isSunday() || $isHoliday) {
                        $status = $isHoliday ? 'holiday' : 'weekOff';
                        $statusReason = $isHoliday ? 'Holiday' : 'Sunday';
                    } else {
                        if ($dateString === Carbon::today()->toDateString()) {
                            $cutoffMins = $absentMins;
                            if ($isHalfDayLeave) {
                                $minHalfDay = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'min_half_day_mins')->value('value') ?? 240);
                                $cutoffMins += $minHalfDay;
                            }
                            $cutoffTime = Carbon::parse($dateString.' '.$checkInSetting)->addMinutes($cutoffMins);
                            if (Carbon::now()->greaterThanOrEqualTo($cutoffTime)) {
                                $status = 'absent';
                                $summary['absent']++;
                            } else {
                                $status = 'notPunchIn';
                                $statusReason = 'Not Punch In';
                            }
                        } else {
                            $status = 'absent';
                            $summary['absent']++;
                        }
                    }
                }
            }

            $history[] = [
                'id' => $attendanceId,
                'date' => $dateString,
                'status' => $status,
                'status_reason' => $statusReason,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'working_minutes' => $workingMinutes !== null ? abs((int) $workingMinutes) : null,
            ];
        }

        usort($history, function ($a, $b) {
            return strtotime($b['date']) <=> strtotime($a['date']);
        });

        // Pagination not strictly necessary for ~30 items, but keeping it an array is simpler for the frontend
        $message = empty($history) ? 'No team history found.' : 'Team history fetched successfully.';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'summary' => $summary,
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'profile_image_url' => $employee->profile_image_url,
            ],
            'data' => $history,
        ]);
    }

    /**
     * Get Attendance Calendar (mirrors /workspace/hrms/attendance/calendar)
     */
    public function calendar(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        if (! $user->isPartner() && ! $user->canAccess('attendance_viewAny')
            && ! $user->canAccess('attendance_viewBranch') && ! $user->canAccess('attendance_viewTeam')
            && ! $user->canAccess('attendance_viewOwn')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = min(12, max(1, $month));
        $year = max(2000, min(2100, $year));

        $partnerId = $this->getPartnerId();

        // Employee scope (mirrors AttendanceCalendar::render, hierarchy: viewAny > viewBranch > viewTeam > viewOwn)
        $scopeIds = $this->getTeamEmployeeIds('attendance_viewAny');
        $employees = User::whereIn('id', $scopeIds)
            ->orderBy('name')
            ->get();

        $requestedEmployeeId = $request->input('employee_id');
        if (! $requestedEmployeeId && $employees->count() > 0) {
            $requestedEmployeeId = $employees->first()->id;
        }

        $days = [];
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $startDayOfWeek = Carbon::create($year, $month, 1)->dayOfWeek;

        if ($requestedEmployeeId) {
            if (! $employees->contains('id', $requestedEmployeeId)) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized or employee not in scope.'], 403);
            }

            $records = EmployeeAttendance::where('employee_id', $requestedEmployeeId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get()
                ->keyBy(fn ($item) => Carbon::parse($item->date)->format('j'));

            $holidays = Holiday::where('partner_id', $partnerId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->pluck('name', 'date')
                ->toArray();

            $leaves = EmployeeLeave::where('employee_id', $requestedEmployeeId)
                ->where('status', 'approved')
                ->get();

            $leaveDays = [];
            foreach ($leaves as $leave) {
                $start = Carbon::parse($leave->start_date);
                $end = Carbon::parse($leave->end_date);
                for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                    if ($d->month == $month && $d->year == $year) {
                        $leaveDays[$d->format('j')] = $leave->leave_type ?? $leave->type ?? 'Leave';
                    }
                }
            }

            $checkInSetting = PartnerSetting::where('partner_id', $partnerId)
                ->where('key', 'fixed_check_in_time')->value('value') ?? '09:00';

            $selEmployee = User::find($requestedEmployeeId);

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $currentDateObj = Carbon::create($year, $month, $i);
                $dateString = $currentDateObj->format('Y-m-d');
                $isSunday = $currentDateObj->isSunday();

                $attendance = $records[$i] ?? null;
                $status = 'Absent';

                if ($selEmployee && $selEmployee->hasExitedOnOrBefore($dateString)) {
                    $status = '';
                } elseif (isset($holidays[$dateString])) {
                    $status = 'Holiday';
                } elseif (isset($leaveDays[$i])) {
                    $status = 'Leave';
                } elseif ($attendance && $attendance->check_in) {
                    $recStatus = $attendance->status ?? '';
                    if (in_array($recStatus, ['half_day', 'short_leave', 'late'])) {
                        $status = ucfirst(str_replace('_', ' ', $recStatus));
                    } elseif ($recStatus === 'punch_in') {
                        $status = 'Punch In';
                    } else {
                        $status = 'Punch Out';
                        if ($attendance->check_out) {
                            $in = Carbon::parse($attendance->check_in);
                            $out = Carbon::parse($attendance->check_out);
                            if ($in->diffInHours($out) < 4) {
                                $status = 'Half Day';
                            }
                        } elseif ($currentDateObj->isPast() && ! $currentDateObj->isToday()) {
                            $status = 'Half Day';
                        }
                    }
                } elseif ($isSunday) {
                    $status = 'Week Off';
                } elseif ($currentDateObj->isFuture()) {
                    $status = '';
                } elseif ($currentDateObj->isToday()) {
                    $cutoffTime = Carbon::parse($dateString.' '.$checkInSetting)->addHour();
                    $status = Carbon::now()->greaterThanOrEqualTo($cutoffTime) ? 'Absent' : 'Not Punch In';
                }

                $days[] = [
                    'day' => $i,
                    'date' => $dateString,
                    'status' => $status,
                    'is_today' => $currentDateObj->isToday(),
                    'is_sunday' => $isSunday,
                    'is_past' => $currentDateObj->isPast(),
                    'clickable' => in_array($status, ['Punch Out', 'Half Day']),
                    'leave_type' => isset($leaveDays[$i]) ? $leaveDays[$i] : null,
                    'holiday_name' => isset($holidays[$dateString]) ? $holidays[$dateString] : null,
                    'attendance' => $attendance ? [
                        'id' => $attendance->id,
                        'status' => $attendance->status,
                        'check_in' => $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i:s') : null,
                        'check_in_time' => $attendance->check_in ? Carbon::parse($attendance->check_in)->format('h:i A') : null,
                        'check_out' => $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i:s') : null,
                        'check_out_time' => $attendance->check_out ? Carbon::parse($attendance->check_out)->format('h:i A') : null,
                        'working_minutes' => $attendance->working_minutes !== null ? abs((int) $attendance->working_minutes) : null,
                        'late_minutes' => $attendance->late_minutes,
                        'check_in_lat' => $attendance->check_in_lat,
                        'check_in_lng' => $attendance->check_in_lng,
                        'check_out_lat' => $attendance->check_out_lat,
                        'check_out_lng' => $attendance->check_out_lng,
                        'check_in_selfie_url' => $attendance->check_in_selfie_url,
                        'check_out_selfie_url' => $attendance->check_out_selfie_url,
                        'check_in_photo_url' => $attendance->check_in_photo_url,
                        'check_out_photo_url' => $attendance->check_out_photo_url,
                    ] : null,
                ];
            }
        }

        $summary = [];
        foreach ($days as $d) {
            $key = str_replace(' ', '_', strtolower($d['status']));
            if ($key !== '') {
                $summary[$key] = ($summary[$key] ?? 0) + 1;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance calendar fetched successfully.',
            'data' => [
                'employees' => $employees->map(fn ($e) => [
                    'id' => $e->id,
                    'name' => $e->name,
                    'profile_image_url' => $e->profile_image_url,
                ])->values(),
                'selected_employee_id' => $requestedEmployeeId,
                'month' => $month,
                'year' => $year,
                'month_name' => Carbon::create($year, $month)->format('F'),
                'days_in_month' => $daysInMonth,
                'start_day_of_week' => $startDayOfWeek,
                'summary' => $summary,
                'days' => $days,
            ],
        ]);
    }

    /**
     * Get Attendance Override form data (mirrors /workspace/hrms/attendance/override)
     * Pass employee_id + date to also get the existing attendance record.
     */
    public function override(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        if (! $user->isPartner() &&
            ! $user->canAccess('attendance_manage') &&
            ! $user->canAccess('attendance_create') &&
            ! $user->canAccess('attendance_update') &&
            ! $user->canAccess('attendance_viewAny')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $this->getPartnerId();

        $employees = User::whereIn('id', $this->getTeamEmployeeIds('attendance_viewAny'))
            ->with(['department:id,name', 'branch:id,name'])
            ->orderBy('name')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->name,
                'employee_code' => $e->employee_code,
                'department_id' => $e->department_id,
                'department' => $e->department ? $e->department->name : 'General',
                'branch_id' => $e->branch_id,
                'branch' => $e->branch ? $e->branch->name : null,
                'shift_id' => $e->shift_id,
                'working_mode' => $e->working_mode,
            ])
            ->values();

        $shifts = WorkShift::where('partner_id', $partnerId)
            ->orderBy('name')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'start_time' => $s->start_time,
                'end_time' => $s->end_time,
                'start_time_display' => $s->start_time ? Carbon::parse($s->start_time)->format('h:i A') : null,
                'end_time_display' => $s->end_time ? Carbon::parse($s->end_time)->format('h:i A') : null,
                'late_tolerance_minutes' => $s->late_tolerance_minutes,
            ])
            ->values();

        $existingAttendance = null;
        if ($request->filled('employee_id') && $request->filled('date')) {
            $att = EmployeeAttendance::where('employee_id', $request->employee_id)
                ->whereDate('date', $request->date)
                ->first();

            if ($att) {
                $existingAttendance = [
                    'id' => $att->id,
                    'employee_id' => $att->employee_id,
                    'date' => Carbon::parse($att->date)->format('Y-m-d'),
                    'status' => $att->status,
                    'check_in' => $att->check_in ? Carbon::parse($att->check_in)->format('H:i:s') : null,
                    'check_in_time' => $att->check_in ? Carbon::parse($att->check_in)->format('h:i A') : null,
                    'check_out' => $att->check_out ? Carbon::parse($att->check_out)->format('H:i:s') : null,
                    'check_out_time' => $att->check_out ? Carbon::parse($att->check_out)->format('h:i A') : null,
                    'shift_id' => $att->shift_id,
                    'working_mode' => $att->working_mode ?? 'office',
                    'working_minutes' => $att->working_minutes !== null ? abs((int) $att->working_minutes) : null,
                    'late_minutes' => $att->late_minutes,
                    'check_in_selfie_url' => $att->check_in_selfie_url,
                    'check_out_selfie_url' => $att->check_out_selfie_url,
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance override data fetched successfully.',
            'data' => [
                'employees' => $employees,
                'shifts' => $shifts,
                'existing_attendance' => $existingAttendance,
            ],
        ]);
    }

    /**
     * Get Attendance Override log list (mirrors the Recent Overrides table)
     * Filters: search (employee name/code/id), employee_id, status, date_from, date_to
     */
    public function overrides(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        if (! $user->isPartner() &&
            ! $user->canAccess('attendance_manage') &&
            ! $user->canAccess('attendance_create') &&
            ! $user->canAccess('attendance_update') &&
            ! $user->canAccess('attendance_viewAny')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $this->getPartnerId();

        $query = AttendanceOverride::where('partner_id', $partnerId)
            ->with(['employee.department:id,name', 'employee.branch:id,name', 'overrider:id,name', 'shift:id,name']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('new_status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 15)));
        $paginator = $query->latest()->paginate($perPage)->withQueryString();

        $items = collect($paginator->items())->map(function ($o) {
            return [
                'id' => $o->id,
                'date' => $o->date ? Carbon::parse($o->date)->format('Y-m-d') : null,
                'date_display' => $o->date ? Carbon::parse($o->date)->format('d M Y') : null,
                'previous_status' => $o->previous_status,
                'new_status' => $o->new_status,
                'previous_check_in_time' => $o->previous_check_in ? Carbon::parse($o->previous_check_in)->format('h:i A') : null,
                'previous_check_out_time' => $o->previous_check_out ? Carbon::parse($o->previous_check_out)->format('h:i A') : null,
                'new_check_in_time' => $o->new_check_in ? Carbon::parse($o->new_check_in)->format('h:i A') : null,
                'new_check_out_time' => $o->new_check_out ? Carbon::parse($o->new_check_out)->format('h:i A') : null,
                'working_minutes' => $o->working_minutes !== null ? abs((int) $o->working_minutes) : null,
                'late_minutes' => $o->late_minutes !== null ? (int) $o->late_minutes : null,
                'notes' => $o->notes,
                'logged_at' => $o->created_at ? $o->created_at->format('d M, h:i A') : null,
                'employee' => [
                    'id' => $o->employee->id ?? null,
                    'name' => $o->employee->name ?? 'Unknown',
                    'employee_code' => $o->employee->employee_code ?? null,
                    'department' => $o->employee?->department?->name ?? 'General',
                    'branch' => $o->employee?->branch?->name ?? null,
                ],
                'shift' => $o->shift ? ['id' => $o->shift->id, 'name' => $o->shift->name] : null,
                'overridden_by' => [
                    'id' => $o->overrider->id ?? null,
                    'name' => $o->overrider->name ?? 'System/Admin',
                ],
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance overrides fetched successfully.',
            'data' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Save / Override Attendance (mirrors ManualAttendanceOverride::saveAttendance)
     * POST /api/hrms/attendance/override
     */
    public function saveOverride(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        if (! $user->isPartner() &&
            ! $user->canAccess('attendance_manage') &&
            ! $user->canAccess('attendance_create') &&
            ! $user->canAccess('attendance_update')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $data = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'date' => 'required|date_format:Y-m-d',
            'status' => 'required|in:present,punch_in,punch_out,half_day,late,short_leave,leave,absent',
            // 'check_in'     => 'required|string',
            // 'check_out'    => 'required|string',
            'shift_id' => 'nullable|exists:work_shifts,id',
            'working_mode' => 'required|in:office,remote,field',
            'notes' => 'nullable|string|max:1000',
            'check_in' => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
            'check_out' => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
        ], [
            'employee_id.required' => 'Please select an employee.',
            'date.required' => 'Please select a date.',
            'status.required' => 'Please select an attendance status.',
            'check_in.regex' => 'Check in time must be HH:MM or HH:MM:SS.',
            'check_out.regex' => 'Check out time must be HH:MM or HH:MM:SS.',
        ]);

        $partnerId = $this->getPartnerId();
        $employee = User::findOrFail($data['employee_id']);

        $teamIds = $this->getTeamEmployeeIds('attendance_viewAny');
        if (! in_array($employee->id, $teamIds)) {
            return response()->json(['status' => 'error', 'message' => 'Employee not in allowed scope.'], 403);
        }

        $workingMinutes = null;
        $lateMinutes = null;

        if ($data['check_in'] && $data['check_out']) {
            try {
                $in = Carbon::parse($data['date'].' '.$data['check_in']);
                $out = Carbon::parse($data['date'].' '.$data['check_out']);
                if ($out->lt($in)) {
                    $out->addDay();
                }
                $workingMinutes = abs((int) $in->diffInMinutes($out));
            } catch (\Exception $e) {
                $workingMinutes = null;
            }
        }

        if ($data['check_in'] && $data['shift_id']) {
            $shift = WorkShift::find($data['shift_id']);
            if ($shift && $shift->start_time) {
                try {
                    $shiftStart = Carbon::parse($data['date'].' '.$shift->start_time);
                    $actualIn = Carbon::parse($data['date'].' '.$data['check_in']);
                    $grace = (int) ($shift->late_tolerance_minutes ?? 15);
                    if ($actualIn->gt($shiftStart)) {
                        $diff = (int) $shiftStart->diffInMinutes($actualIn);
                        if ($diff > $grace) {
                            $lateMinutes = $diff;
                        }
                    }
                } catch (\Exception $e) {
                    $lateMinutes = null;
                }
            }
        }

        $dbStatus = $data['status'];
        if ($dbStatus === 'present') {
            $dbStatus = $data['check_out'] ? 'punch_out' : 'punch_in';
        }

        $normalizeTime = function ($time) {
            if (! $time) {
                return null;
            }

            return strlen($time) == 5 ? $time.':00' : $time;
        };

        DB::beginTransaction();
        try {
            $existing = EmployeeAttendance::where('employee_id', $data['employee_id'])
                ->whereDate('date', $data['date'])
                ->first();

            $prevStatus = $existing ? $existing->status : null;
            $prevCheckIn = $existing ? $existing->check_in : null;
            $prevCheckOut = $existing ? $existing->check_out : null;

            $attendance = EmployeeAttendance::updateOrCreate(
                [
                    'employee_id' => $data['employee_id'],
                    'date' => $data['date'],
                ],
                [
                    'branch_id' => $employee->branch_id,
                    'shift_id' => $data['shift_id'] ?: null,
                    'check_in' => $normalizeTime($data['check_in']),
                    'check_out' => $normalizeTime($data['check_out']),
                    'status' => $dbStatus,
                    'working_mode' => $data['working_mode'],
                    'working_minutes' => $workingMinutes,
                    'late_minutes' => $lateMinutes,
                ]
            );

            $override = AttendanceOverride::create([
                'partner_id' => $partnerId,
                'employee_id' => $data['employee_id'],
                'attendance_id' => $attendance->id,
                'overridden_by' => $user->id,
                'date' => $data['date'],
                'previous_status' => $prevStatus,
                'previous_check_in' => $prevCheckIn,
                'previous_check_out' => $prevCheckOut,
                'new_status' => $dbStatus,
                'new_check_in' => $normalizeTime($data['check_in']),
                'new_check_out' => $normalizeTime($data['check_out']),
                'shift_id' => $data['shift_id'] ?: null,
                'working_minutes' => $workingMinutes,
                'late_minutes' => $lateMinutes,
                'notes' => $data['notes'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Attendance for '.$employee->name.' on '.Carbon::parse($data['date'])->format('d M Y').' has been successfully updated and recorded in the override log.',
                'data' => [
                    'attendance' => [
                        'id' => $attendance->id,
                        'employee_id' => $attendance->employee_id,
                        'date' => Carbon::parse($attendance->date)->format('Y-m-d'),
                        'status' => $attendance->status,
                        'check_in' => $attendance->check_in,
                        'check_in_time' => $attendance->check_in ? Carbon::parse($attendance->check_in)->format('h:i A') : null,
                        'check_out' => $attendance->check_out,
                        'check_out_time' => $attendance->check_out ? Carbon::parse($attendance->check_out)->format('h:i A') : null,
                        'shift_id' => $attendance->shift_id,
                        'working_mode' => $attendance->working_mode,
                        'working_minutes' => $attendance->working_minutes,
                        'late_minutes' => $attendance->late_minutes,
                    ],
                    'override' => [
                        'id' => $override->id,
                        'previous_status' => $override->previous_status,
                        'previous_check_in' => $override->previous_check_in,
                        'previous_check_out' => $override->previous_check_out,
                        'new_status' => $override->new_status,
                        'notes' => $override->notes,
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update attendance: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get HRMS Settings for Attendance & Geofence
     */
    public function settings(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $settings = PartnerSetting::where('partner_id', $partnerId)
            ->whereIn('key', [
                'fixed_check_in_time',
                'late_grace_period',
                'min_present_mins',
                'min_short_leave_mins',
                'min_half_day_mins',
                'office_latitude',
                'office_longitude',
                'office_radius_meters',
                'tds_percentage',
                'commission_type',
            ])
            ->pluck('value', 'key');

        return response()->json([
            'status' => 'success',
            'message' => 'HRMS settings fetched successfully.',
            'data' => [
                'fixed_check_in_time' => $settings['fixed_check_in_time'] ?? '09:00',
                'late_grace_period' => (int) ($settings['late_grace_period'] ?? 15),
                'min_present_mins' => (int) ($settings['min_present_mins'] ?? 480),
                'min_short_leave_mins' => (int) ($settings['min_short_leave_mins'] ?? 420),
                'min_half_day_mins' => (int) ($settings['min_half_day_mins'] ?? 240),
                'office_latitude' => isset($settings['office_latitude']) ? (float) $settings['office_latitude'] : null,
                'office_longitude' => isset($settings['office_longitude']) ? (float) $settings['office_longitude'] : null,
                'office_radius_meters' => isset($settings['office_radius_meters']) ? (int) $settings['office_radius_meters'] : 200,
                'tds_percentage' => isset($settings['tds_percentage']) ? (float) $settings['tds_percentage'] : 0,
                'commission_type' => $settings['commission_type'] ?? 'percentage',
            ],
        ]);
    }

    /**
     * Calculate Distance in Meters between 2 GPS coordinates (Haversine formula)
     */
    private function calculateDistanceMeters($lat1, $lon1, $lat2, $lon2): int
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
}
