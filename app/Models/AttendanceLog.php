<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subscription_id',
        'customer_id',
        'listing_id',
        'shift_id',
        'date',
        'punch_in_at',
        'punch_out_at',
        'lat',
        'lng',
        'punch_out_lat',
        'punch_out_lng',
        'duration_minutes',
        'is_late',
        'late_minutes',
        'note',
    ];

    protected $casts = [
        'date'          => 'date',
        'punch_in_at'   => 'datetime',
        'punch_out_at'  => 'datetime',
        'is_late'       => 'boolean',
        'lat'           => 'decimal:8',
        'lng'           => 'decimal:8',
        'punch_out_lat' => 'decimal:8',
        'punch_out_lng' => 'decimal:8',
    ];

    // ── Relationships ─────────────────────────────────────────────
    public function customer()     { return $this->belongsTo(User::class, 'customer_id'); }
    public function subscription() { return $this->belongsTo(Subscription::class); }
    public function listing()      { return $this->belongsTo(Listing::class); }
    public function shift()        { return $this->belongsTo(ListingShift::class, 'shift_id'); }

    // ── Helpers ───────────────────────────────────────────────────
    /** Returns true when the customer has already punched out today */
    public function isDone(): bool
    {
        return $this->punch_out_at !== null;
    }

    /** Returns true when punched in but not yet out */
    public function isOpen(): bool
    {
        return $this->punch_in_at !== null && $this->punch_out_at === null;
    }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeToday($q)
    {
        return $q->whereDate('date', today());
    }

    public function scopeForCustomer($q, string $customerId)
    {
        return $q->where('customer_id', $customerId);
    }

    public function scopeForListing($q, string $listingId)
    {
        return $q->where('listing_id', $listingId);
    }
}
