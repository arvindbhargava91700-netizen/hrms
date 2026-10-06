<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'booking_number',
        'customer_id',
        'partner_id',
        'created_by',
        'listing_id',
        'package_id',
        'room_id',
        'shift_id',
        'occupancy_type',
        'beds_booked',
        'trainer_ids',
        'payment_method',
        'status',
        'otp',
        'otp_verified_at',
        'coupon_id',
        'coupon_type',
        'discount_amount',
        'security_deposit',
        'final_amount',
    ];

    protected $casts = [
        'trainer_ids' => 'array',
        'otp_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->booking_number)) {
                $model->booking_number = 'BKG-' . strtoupper(\Illuminate\Support\Str::random(8));
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function getListingAttribute()
    {
        if ($this->package_id) return $this->package->listing ?? null;
        if ($this->shift_id) return $this->shift->listing ?? null;
        if ($this->room_id) return $this->room->floor->listing ?? null;
        return null;
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ListingShift::class, 'shift_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function getNetEarnAttribute()
    {
        return \App\Models\WalletTransaction::where('reference_type', self::class)
            ->where('reference_id', $this->id)
            ->where('type', 'credit')
            ->sum('amount') ?? 0;
    }
}
