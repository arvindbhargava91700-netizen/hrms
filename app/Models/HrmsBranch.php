<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrmsBranch extends Model
{
    protected $table = 'branches';

    protected $fillable = [
        'partner_id',
        'name',
        'address',
        'lat',
        'lng',
        'radius',
        'manager_id',
        'status',
        'company_logo',
        'gst_number',
        'founded_year',
        'website',
        'company_size',
        'company_type',
        'industry',
        'about_company',
        'linkedin_url',
        'facebook_url',
        'instagram_url',
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function shifts()
    {
        return $this->hasMany(WorkShift::class, 'branch_id');
    }

    public function employees()
    {
        return $this->hasMany(User::class, 'branch_id');
    }
}
