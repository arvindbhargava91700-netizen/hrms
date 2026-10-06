<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExitReason extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function scopeVoluntary($query)
    {
        return $query->where('type', 'voluntary');
    }

    public function scopeInvoluntary($query)
    {
        return $query->where('type', 'involuntary');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
