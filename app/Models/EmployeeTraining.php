<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTraining extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'branch_id',
        'department_id',
        'employee_id',
        'training_id',
        'assigned_at',
        'start_date',
        'completion_date',
        'status',
        'total_sessions',
        'sessions_attended',
        'attendance_percentage',
        'maximum_score',
        'obtained_score',
        'assessment_score',
        'result',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'start_date' => 'date',
        'completion_date' => 'date',
        'attendance_percentage' => 'decimal:2',
        'maximum_score' => 'decimal:2',
        'obtained_score' => 'decimal:2',
        'assessment_score' => 'decimal:2',
    ];

    public function training()
    {
        return $this->belongsTo(Training::class, 'training_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function branch()
    {
        return $this->belongsTo(HrmsBranch::class, 'branch_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function attendances()
    {
        return $this->hasMany(TrainingAttendance::class, 'employee_training_id');
    }

    public function assessments()
    {
        return $this->hasMany(TrainingAssessment::class, 'employee_training_id');
    }
}
