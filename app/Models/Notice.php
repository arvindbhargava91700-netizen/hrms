<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'user_id',
        'user_ids',
        'branch_ids',
        'department_ids',
        'start_date',
        'end_date',
        'action_link',
        'action_text',
    ];

    protected $casts = [
        'user_ids'       => 'array',
        'branch_ids'     => 'array',
        'department_ids' => 'array',
        'start_date'     => 'datetime',
        'end_date'       => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d\TH:i:s.u\Z');
    }
}
