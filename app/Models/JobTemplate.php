<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class JobTemplate extends Model
{
    /** @use HasFactory<\Database\Factories\JobTemplateFactory> */
    use HasFactory, SoftDeletes;
    
    protected $guarded = [];
    
    protected $casts = [
        'responsibilities' => 'array',
        'skills' => 'array',
        'default_screening_questions' => 'array',
        'is_active' => 'boolean',
        'interview_information' => 'array',
        'additional_perks' => 'array',
        'joining_fee_required' => 'boolean',
    ];
}
