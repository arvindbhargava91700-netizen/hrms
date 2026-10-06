<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SharedReferral extends Model
{
    protected $fillable = [
        'user_id',
        'post_id',
        'referral_code',
        'app_com_status',
        'post_com_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobPost()
    {
        return $this->belongsTo(JobPost::class, 'post_id');
    }
}
