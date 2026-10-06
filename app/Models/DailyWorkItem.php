<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyWorkItem extends Model
{
    protected $fillable = [
        'daily_work_report_id',
        'task_id',
        'title',
        'details',
        'category',
        'status',
        'priority',
        'time_spent_hours',
        'time_spent_minutes',
    ];

    public function report()
    {
        return $this->belongsTo(DailyWorkReport::class, 'daily_work_report_id');
    }

    public function task()
    {
        return $this->belongsTo(EmployeeTask::class, 'task_id');
    }
}
