<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id','subscription_id','created_by','booking_id','invoice_number','amount','tax','total','due_date','status',
    ];
    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'tax'    => 'decimal:2',
        'total'  => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function($m) {
            $m->id = $m->id ?: (string) Str::uuid();
            if (empty($m->invoice_number)) {
                $m->invoice_number = 'INV-' . strtoupper(Str::random(8));
            }
        });
    }

    public function subscription() { return $this->belongsTo(Subscription::class); }
    public function booking()      { return $this->belongsTo(Booking::class); }
    public function payments()     { return $this->hasMany(Payment::class); }

    public function isPaid()    { return $this->status === 'paid'; }
    public function isOverdue() { return $this->status !== 'paid' && $this->due_date->isPast(); }
}
