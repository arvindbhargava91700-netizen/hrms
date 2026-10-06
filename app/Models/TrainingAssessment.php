<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_training_id',
        'training_id',
        'employee_id',
        'assessment_date',
        'maximum_score',
        'obtained_score',
        'score_percentage',
        'result',
        'remarks',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'maximum_score' => 'decimal:2',
        'obtained_score' => 'decimal:2',
        'score_percentage' => 'decimal:2',
    ];

    public function employeeTraining()
    {
        return $this->belongsTo(EmployeeTraining::class, 'employee_training_id');
    }

    public function training()
    {
        return $this->belongsTo(Training::class, 'training_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
