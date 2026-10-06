<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'branch_id',
        'shift_id',
        'date',
        'check_in',
        'check_out',
        'check_in_lat',
        'check_in_lng',
        'check_in_photo',
        'check_in_selfie',
        'check_out_lat',
        'check_out_lng',
        'check_out_photo',
        'check_out_selfie',
        'status', // punch_out, absent, half_day, leave, short_leave, late, punch_in
        'working_mode', // office, remote, field
        'working_minutes',
        'late_minutes',
        'checklist_responses',
        'check_out_checklist_responses',
    ];

    protected $casts = [
        'date' => 'date',
        'checklist_responses' => 'array',
        'check_out_checklist_responses' => 'array',
    ];

    protected $appends = [
        'check_in_selfie_url',
        'check_out_selfie_url',
        'check_in_photo_url',
        'check_out_photo_url'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function getCheckInSelfieUrlAttribute(): ?string
    {
        if (empty($this->attributes['check_in_selfie'])) return null;
        $val = $this->attributes['check_in_selfie'];
        return str_starts_with($val, 'http') ? $val : asset('storage/' . $val);
    }

    public function getCheckOutSelfieUrlAttribute(): ?string
    {
        if (empty($this->attributes['check_out_selfie'])) return null;
        $val = $this->attributes['check_out_selfie'];
        return str_starts_with($val, 'http') ? $val : asset('storage/' . $val);
    }

    public function getCheckInPhotoUrlAttribute(): ?string
    {
        if (empty($this->attributes['check_in_photo'])) return null;
        $val = $this->attributes['check_in_photo'];
        return str_starts_with($val, 'http') ? $val : asset('storage/' . $val);
    }

    public function getCheckOutPhotoUrlAttribute(): ?string
    {
        if (empty($this->attributes['check_out_photo'])) return null;
        $val = $this->attributes['check_out_photo'];
        return str_starts_with($val, 'http') ? $val : asset('storage/' . $val);
    }
}
