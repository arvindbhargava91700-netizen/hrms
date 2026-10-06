<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobSetting extends Model
{
    protected $fillable = ['type', 'name', 'depends_on'];
}
