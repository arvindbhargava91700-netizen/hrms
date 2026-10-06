<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'question',
        'mode',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}
