<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class Subscription extends Model
{
    use SoftDeletes;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id','customer_id','created_by','package_id','room_id','occupancy_type','beds_booked',
        'starts_at','expires_at','status', 'leave_status', 'auto_renew','gateway_subscription_id',
        'booking_id', 'subscription_number'
    ];
    protected $casts = [
        'starts_at'       => 'date',
        'expires_at'      => 'date',
        'auto_renew'      => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function($m) {
            $m->id = $m->id ?: (string) Str::uuid();
            if (empty($m->subscription_number)) {
                $m->subscription_number = 'SUB-' . strtoupper(Str::random(8));
            }
        });
        
        static::created(function ($m) {
            if ($m->room_id && $m->beds_booked > 0) {
                $room = Room::find($m->room_id);
                if ($room && $room->available_beds >= $m->beds_booked) {
                    $room->decrement('available_beds', $m->beds_booked);
                }
            }
        });
    }

    public function customer()  { return $this->belongsTo(User::class, 'customer_id'); }
    public function package()   { return $this->belongsTo(Package::class); }
    public function room()      { return $this->belongsTo(Room::class); }
    public function shift()     { return $this->belongsTo(ListingShift::class, 'shift_id'); }
    public function invoices()  { return $this->hasMany(Invoice::class); }
    public function payments()  { return $this->hasMany(Payment::class); }
    public function review()    { return $this->hasOne(ListingReview::class); }
    public function booking()   { return $this->belongsTo(Booking::class); }

    public function isActive()   { return $this->status === 'active'; }
    public function isExpired()  { return $this->status === 'expired'; }

    public function daysRemaining(): int
    {
        return max(0, (int) now()->diffInDays($this->expires_at, false));
    }

    public function isExpiringSoon(int $days = 7): bool
    {
        return $this->isActive() && $this->daysRemaining() <= $days;
    }

    public function scopeActive($q)   { return $q->where('status', 'active'); }
    public function scopeExpiring($q, int $days = 7)
    {
        return $q->active()->whereDate('expires_at', '<=', now()->addDays($days));
    }
}
