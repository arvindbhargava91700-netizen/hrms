<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkShift extends Model
{
    protected $fillable = [
        'partner_id',
        'branch_id',
        'name',
        'start_time',
        'end_time',
        'auto_mark_attendance',
        'auto_mark_status',
        'late_tolerance_minutes',
        'min_present_mins',
        'min_half_day_mins',
        'auto_absent_mark_mins',
        'week_off_days',
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function branch()
    {
        return $this->belongsTo(HrmsBranch::class, 'branch_id');
    }

    public function employees()
    {
        return $this->hasMany(User::class, 'shift_id');
    }
}
