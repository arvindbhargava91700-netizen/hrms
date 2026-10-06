<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'name',
        'days',
        'is_unlimited',
        'status',
    ];

    protected $casts = [
        'days' => 'integer',
        'is_unlimited' => 'boolean',
        'status' => 'boolean',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(EmployeeLeave::class, 'leave_category_id');
    }
}
