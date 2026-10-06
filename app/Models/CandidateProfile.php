<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateProfile extends Model
{
    protected $guarded = [];

    protected $casts = [
        'educations' => 'array',
        'skills' => 'array',
        'preferred_job_roles' => 'array',
        'preferred_locations' => 'array',
        'documents_and_assets' => 'array',
        'work_experiences' => 'array',
        'internships' => 'array',
        'expected_salary' => 'decimal:2',
        'current_monthly_salary' => 'decimal:2',
        'resume_updated_at' => 'datetime',
    ];

    protected $appends = ['resume_url'];

    public function getResumeUrlAttribute(): ?string
    {
        $val = $this->attributes['resume_path'] ?? null;

        if (empty($val)) {
            return null;
        }

        return str_starts_with($val, 'http') ? $val : asset('storage/'.$val);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
