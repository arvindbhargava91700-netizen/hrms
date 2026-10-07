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

    public static function formatImageUrl(?string $val): ?string
    {
        if (empty($val)) {
            return null;
        }

        // 1. Data URLs (base64)
        if (str_starts_with($val, 'data:image')) {
            return $val;
        }

        // 2. If it contains /storage/ from any host or relative path
        if (str_contains($val, '/storage/')) {
            $parts = explode('/storage/', $val);
            $relativePath = end($parts);
            return asset('storage/' . ltrim($relativePath, '/'));
        }

        // 3. Full HTTP(S) URL
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
            return str_replace('/storage//storage/', '/storage/', $val);
        }

        // 4. Relative storage path
        $trimmed = ltrim($val, '/');
        if (str_starts_with($trimmed, 'storage/')) {
            return asset($trimmed);
        }

        return asset('storage/' . $trimmed);
    }

    public function getCheckInSelfieUrlAttribute(): ?string
    {
        return self::formatImageUrl($this->attributes['check_in_selfie'] ?? null);
    }

    public function getCheckOutSelfieUrlAttribute(): ?string
    {
        return self::formatImageUrl($this->attributes['check_out_selfie'] ?? null);
    }

    public function getCheckInPhotoUrlAttribute(): ?string
    {
        return self::formatImageUrl($this->attributes['check_in_photo'] ?? null);
    }

    public function getCheckOutPhotoUrlAttribute(): ?string
    {
        return self::formatImageUrl($this->attributes['check_out_photo'] ?? null);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class, 'shift_id');
    }
}
