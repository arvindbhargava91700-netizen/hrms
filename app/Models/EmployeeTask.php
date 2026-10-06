<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'assigned_by', 'title', 'description', 'status', 'due_date', 'start_date', 'end_date', 'remark'
    ];

    public function employee() {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function assigner() {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function remarks() {
        return $this->hasMany(TaskRemark::class, 'task_id');
    }
}
