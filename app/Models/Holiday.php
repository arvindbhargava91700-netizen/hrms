<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['partner_id', 'branch_id', 'name', 'date'];

    public function branch()
    {
        return $this->belongsTo(HrmsBranch::class, 'branch_id');
    }
}