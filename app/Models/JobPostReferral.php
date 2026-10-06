<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPostReferral extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'referral_date' => 'datetime',
    ];

    public function jobPost()
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }
}
