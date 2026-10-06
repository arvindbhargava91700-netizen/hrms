<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReserveHistory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'partner_id',
        'customer_id',
        'booking_id',
        'amount',
        'status', // active, refunded_wallet, refunded_cash
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
