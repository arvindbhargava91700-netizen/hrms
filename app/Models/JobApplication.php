<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'post_id',
        'referral_id',
        'referral_code',
        'apply_user_id',
        'designation',
        'address',
        'resume',
        'status',
        'remark',
        'education_level',
        'experience_years',
        'english_proficiency',
        'age',
        'gender',
        'screening_answers',
    ];

    protected $appends = ['resume_url'];


    public function getResumeUrlAttribute(): ?string
    {
        if (empty($this->attributes['resume'])) {
            return null;
        }
        $val = $this->attributes['resume'];

        return str_starts_with($val, 'http') ? $val : asset('storage/'.$val);
    }

    public const STATUS_APPLIED = 'applied';
    public const STATUS_VIEWED = 'viewed';
    public const STATUS_SHORTLISTED = 'shortlisted';
    public const STATUS_INTERVIEW_SCHEDULED = 'interview_scheduled';
    public const STATUS_SELECTED = 'selected';
    public const STATUS_HIRED = 'hired';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';

    public static function statuses(): array
    {
        return [
            self::STATUS_APPLIED,
            self::STATUS_VIEWED,
            self::STATUS_SHORTLISTED,
            self::STATUS_INTERVIEW_SCHEDULED,
            self::STATUS_SELECTED,
            self::STATUS_HIRED,
            self::STATUS_COMPLETED,
            self::STATUS_REJECTED,
        ];
    }

    public function jobPost()
    {
        return $this->belongsTo(JobPost::class, 'post_id');
    }

    public function applicant()
    {
        return $this->belongsTo(User::class, 'apply_user_id');
    }

    public function referral()
    {
        return $this->belongsTo(SharedReferral::class, 'referral_id');
    }

    public function history()
    {
        return $this->hasMany(ApplicationStatusHistory::class, 'application_id');
    }
}