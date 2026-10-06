<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskRemark extends Model
{
    use HasFactory;

    protected $fillable = ['task_id', 'user_id', 'remark'];

    public function task()
    {
        return $this->belongsTo(EmployeeTask::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
