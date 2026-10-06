<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CustomerKyc extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'customer_id',
        'live_photo',
        'aadhaar_front',
        'aadhaar_back',
        'pan_front',
        'pan_back',
        'bank_statement',
        'passport_front',
        'passport_back',
        'driving_license_front',
        'driving_license_back',
        'status',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: (string) Str::uuid());
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function isPending()  { return $this->status === 'pending'; }
    public function isApproved() { return $this->status === 'approved'; }
    public function isRejected() { return $this->status === 'rejected'; }

    public function getStoredFileUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
