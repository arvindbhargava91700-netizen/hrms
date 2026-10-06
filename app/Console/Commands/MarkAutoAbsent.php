<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MarkAutoAbsent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hrms:mark-auto-absent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marks employees as absent if they missed their check-in time and have no leave applied';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();
        $employees = \App\Models\User::where('role', 'employee')->with(['shift', 'branch'])->get();
        $markedCount = 0;

        foreach ($employees as $employee) {
            // Only process active employees
            if ($employee->employment_status !== 'active') {
                continue;
            }

            $partnerId = $employee->isPartner() ? $employee->id : $employee->parent_id;

            // 1. Check if already has a COMPLETED attendance record for today
            $attendance = \App\Models\EmployeeAttendance::where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->first();
                
            if ($attendance && $attendance->check_out !== null) {
                continue; // Already completed attendance for today
            }

            // 2. Determine the status to insert if cutoff passes
            $statusToInsert = 'absent';
            $isAutoMarkEnabled = true;
            
            // Check if today is a Holiday or Sunday
            $isHoliday = \App\Models\Holiday::where('partner_id', $partnerId)
                ->whereDate('date', $today)
                ->exists();
                
            // Get Global Fallbacks
            $globalWeekOff = \App\Models\PartnerSetting::where('partner_id', $partnerId)->where('key', 'week_off_days')->value('value');
            $globalWeekOff = $globalWeekOff ? json_decode($globalWeekOff, true) : ['Sunday'];
            
            $globalCheckIn = \App\Models\PartnerSetting::where('partner_id', $partnerId)->where('key', 'fixed_check_in_time')->value('value') ?? '09:00';
            $globalAbsentMins = \App\Models\PartnerSetting::where('partner_id', $partnerId)->where('key', 'auto_absent_mark_mins')->value('value') ?? 60;
            $globalMinHalfDay = \App\Models\PartnerSetting::where('partner_id', $partnerId)->where('key', 'min_half_day_mins')->value('value') ?? 240;
            $globalMinPresent = \App\Models\PartnerSetting::where('partner_id', $partnerId)->where('key', 'min_present_mins')->value('value') ?? 480;

            // Get Shift Settings
            $shiftWeekOff = ($employee->shift_id && $employee->shift && $employee->shift->week_off_days) ? json_decode($employee->shift->week_off_days, true) : null;
            $shiftCheckIn = ($employee->shift_id && $employee->shift) ? $employee->shift->start_time : null;
            $shiftCheckOut = ($employee->shift_id && $employee->shift) ? $employee->shift->end_time : null;
            
            // Fix: Use auto_absent_mark_mins instead of late_tolerance_minutes
            $shiftAbsentMins = ($employee->shift_id && $employee->shift) ? $employee->shift->auto_absent_mark_mins : null;
            $shiftMinHalfDay = ($employee->shift_id && $employee->shift) ? $employee->shift->min_half_day_mins : null;
            $shiftMinPresent = ($employee->shift_id && $employee->shift) ? $employee->shift->min_present_mins : null;

            // Apply Hierarchy
            $weekOffDays = $shiftWeekOff ?? $globalWeekOff;
            $isWeekOff = in_array(now()->format('l'), $weekOffDays);

            if ($isWeekOff || $isHoliday) {
                $statusToInsert = $isHoliday ? 'holiday' : 'weekOff';
            } else {
                // Check if employee has an approved leave for today (including half_day)
                $hasLeave = \App\Models\EmployeeLeave::where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->first();

                $isHalfDayLeave = false;
                if ($hasLeave) {
                    if ($hasLeave->type === 'half_day') {
                        $isHalfDayLeave = true;
                    } else {
                        $statusToInsert = 'leave';
                    }
                }
            }

            // 4. Check if the fixed time + cutoff mins has passed
            // If they checked in, we should check against shift end time (or check in time) instead?
            // Usually, auto absent marks them absent if they didn't show up. 
            // If they did show up (attendance exists), the cutoff to auto-checkout is usually after shift ends + absent mins.
            $checkInSetting = $shiftCheckIn ?? $globalCheckIn;
            $absentMins = $shiftAbsentMins ?? $globalAbsentMins;

            if ($employee->shift_id && $employee->shift) {
                $isAutoMarkEnabled = $employee->shift->auto_mark_attendance;
                if (!$isWeekOff && !$isHoliday && !$hasLeave) {
                    $statusToInsert = $employee->shift->auto_mark_status ?? 'absent';
                }
            }

            if (!$isAutoMarkEnabled && !in_array($statusToInsert, ['holiday', 'weekOff', 'leave'])) {
                continue; // Skip if auto mark is disabled for this shift and it's a regular day
            }

            if (isset($isHalfDayLeave) && $isHalfDayLeave) {
                $minHalfDay = $shiftMinHalfDay ?? $globalMinHalfDay;
                $absentMins += $minHalfDay;
            }
            
            // For forgot punch-in, we check checkInSetting + absentMins
            // For forgot punch-out, we should check shift end time + absentMins
            $baseTimeForCutoff = $attendance ? ($shiftCheckOut ?? '18:00') : $checkInSetting;
            $cutoffTime = \Carbon\Carbon::parse($today . ' ' . $baseTimeForCutoff)->addMinutes((int) $absentMins);

            if (now()->greaterThanOrEqualTo($cutoffTime)) {
                // Calculate check_in, check_out, and working_minutes properly
                $computedCheckIn = null;
                $computedCheckOut = null;
                $computedWorkingMins = null;
                
                // Set default times for present / half_day
                if (in_array($statusToInsert, ['present', 'half_day'])) {
                    $cIn = $shiftCheckIn ?? $globalCheckIn;
                    $cOut = $shiftCheckOut ?? '18:00';
                    $computedCheckIn = $cIn;
                    $computedCheckOut = $cOut;
                    
                    try {
                        $start = \Carbon\Carbon::createFromFormat('H:i:s', strlen($cIn) == 5 ? $cIn . ':00' : $cIn);
                        $end = \Carbon\Carbon::createFromFormat('H:i:s', strlen($cOut) == 5 ? $cOut . ':00' : $cOut);
                        if ($end->lt($start)) {
                            $end->addDay();
                        }
                        
                        if ($statusToInsert === 'half_day') {
                            $computedWorkingMins = $shiftMinHalfDay ?? $globalMinHalfDay;
                            $computedCheckOut = $start->copy()->addMinutes((int) $computedWorkingMins)->format('H:i:s');
                        } else {
                            $computedWorkingMins = $start->diffInMinutes($end);
                            // Set status to punch_out as it represents present/completed in the system
                            $statusToInsert = 'punch_out';
                        }
                    } catch (\Exception $e) {
                        // ignore parse errors
                    }
                }
                
                if ($attendance) {
                    // Update existing attendance (forgot to punch out)
                    // If status is absent, maybe we don't update check_out? 
                    // But if auto mark is absent, and they punched in... maybe we force absent or keep punch_in?
                    // The request says "auto mark status... if forgot to punch in/out".
                    $updCheckOut = $computedCheckOut ?? ($shiftCheckOut ?? '18:00');
                    
                    // calculate working mins from their actual check in
                    try {
                        $actualStart = \Carbon\Carbon::createFromFormat('H:i:s', $attendance->check_in);
                        $actualEnd = \Carbon\Carbon::createFromFormat('H:i:s', strlen($updCheckOut) == 5 ? $updCheckOut . ':00' : $updCheckOut);
                        if ($actualEnd->lt($actualStart)) {
                            $actualEnd->addDay();
                        }
                        $computedWorkingMins = $actualStart->diffInMinutes($actualEnd);
                        
                        // Set status correctly based on minutes worked
                        $minPresent = $shiftMinPresent ?? $globalMinPresent;
                        $minHalfDay = $shiftMinHalfDay ?? $globalMinHalfDay;
                        
                        if ($computedWorkingMins >= $minPresent) {
                            $statusToInsert = 'punch_out';
                        } elseif ($computedWorkingMins >= $minHalfDay) {
                            $statusToInsert = 'half_day';
                        } else {
                            $statusToInsert = 'absent';
                        }
                    } catch (\Exception $e) {}
                    
                    $attendance->update([
                        'check_out' => $updCheckOut,
                        'working_minutes' => $computedWorkingMins,
                        'status' => $statusToInsert,
                    ]);
                } else {
                    // Insert new record (forgot to punch in)
                    \App\Models\EmployeeAttendance::create([
                        'employee_id' => $employee->id,
                        'branch_id' => $employee->branch_id,
                        'shift_id' => $employee->shift_id,
                        'date' => $today,
                        'check_in' => $computedCheckIn,
                        'check_out' => $computedCheckOut,
                        'working_minutes' => $computedWorkingMins,
                        'working_mode' => $employee->working_mode ?? 'office',
                        'status' => $statusToInsert,
                    ]);
                }
                $markedCount++;
            }
        }

        $this->info("Successfully marked $markedCount employees as absent.");
    }
}
