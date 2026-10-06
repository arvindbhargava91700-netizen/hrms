<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentBranchHead extends Model
{
    protected $fillable = ['department_id', 'branch_id', 'head_id'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function branch()
    {
        return $this->belongsTo(HrmsBranch::class, 'branch_id');
    }

    public function head()
    {
        return $this->belongsTo(User::class, 'head_id');
    }
}
