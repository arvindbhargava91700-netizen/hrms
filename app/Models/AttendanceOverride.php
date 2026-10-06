<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'employee_id',
        'attendance_id',
        'overridden_by',
        'date',
        'previous_status',
        'previous_check_in',
        'previous_check_out',
        'new_status',
        'new_check_in',
        'new_check_out',
        'shift_id',
        'working_minutes',
        'late_minutes',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function overrider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(EmployeeAttendance::class, 'attendance_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class, 'shift_id');
    }
}
