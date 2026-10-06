<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPlan extends Model
{
    /** @use HasFactory<\Database\Factories\JobPlanFactory> */
    use HasFactory;
    
    protected $guarded = [];
    
    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'features' => 'array',
    ];
}
