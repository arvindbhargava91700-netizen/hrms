<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_training_id',
        'training_id',
        'employee_id',
        'date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date',
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
