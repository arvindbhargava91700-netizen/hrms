<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id','subscription_id','created_by','booking_id','invoice_id','gateway','gateway_ref',
        'amount','status','paid_at',
    ];
    protected $casts = ['paid_at' => 'datetime', 'amount' => 'decimal:2'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($m) {
            $m->id = $m->id ?: (string) Str::uuid();
            if (empty($m->receipt_number)) {
                $m->receipt_number = 'PAY-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
            }
        });
    }

    public function subscription() { return $this->belongsTo(Subscription::class); }
    public function booking()      { return $this->belongsTo(Booking::class); }
    public function invoice()      { return $this->belongsTo(Invoice::class); }
    public function getCustomerAttribute()     { 
        return $this->subscription ? $this->subscription->customer : ($this->booking ? $this->booking->customer : null);
    }

    public function isPaid()     { return $this->status === 'paid'; }
    public function isFailed()   { return $this->status === 'failed'; }
    public function isRefunded() { return $this->status === 'refunded'; }

    public function scopePaid($q)   { return $q->where('status', 'paid'); }
    public function scopeFailed($q) { return $q->where('status', 'failed'); }
}
