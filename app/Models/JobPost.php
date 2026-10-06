<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPost extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'application_deadline' => 'date',
        'referral_budget' => 'decimal:2',
        'referral_amount' => 'decimal:2',
        'wallet_deducted' => 'decimal:2',
        'online_payable' => 'decimal:2',
        'vacancies_count' => 'integer',
        'additional_perks' => 'array',
        'is_work_from_home' => 'boolean',
        'joining_fee_required' => 'boolean',
        'expires_at' => 'datetime',
        'published_at' => 'datetime',
        'screening_questions' => 'array',
        'interview_information' => 'array',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->vacancies_count > 0 && $model->referral_budget > 0) {
                $model->referral_amount = round($model->referral_budget / $model->vacancies_count, 2);
            } else {
                $model->referral_amount = 0;
            }
        });
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function branch()
    {
        return $this->belongsTo(HrmsBranch::class, 'branch_id');
    }

    public function template()
    {
        return $this->belongsTo(JobTemplate::class, 'template_id');
    }

    public function plan()
    {
        return $this->belongsTo(JobPlan::class, 'job_plan_id');
    }

    public function payments()
    {
        return $this->hasMany(JobPostPayment::class, 'job_post_id');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'post_id');
    }
}
