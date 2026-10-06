<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'title',
        'description',
        'trainer',
        'training_type',
        'start_date',
        'end_date',
        'duration',
        'passing_score',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'passing_score' => 'decimal:2',
    ];

    public function assignments()
    {
        return $this->hasMany(EmployeeTraining::class, 'training_id');
    }

    public function employees()
    {
        return $this->belongsToMany(User::class, 'employee_trainings', 'training_id', 'employee_id')
                    ->withPivot(['status', 'attendance_percentage', 'assessment_score', 'result', 'completion_date'])
                    ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
