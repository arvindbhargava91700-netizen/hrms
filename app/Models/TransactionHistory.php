<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionHistory extends Model
{
    protected $fillable = [
        'transaction_id',
        'user_id',
        'type',
        'reference_id',
        'total_amount',
        'wallet_deducted',
        'online_payable',
        'gst_amount',
        'platform_fee',
        'net_amount',
        'status',
        'description',
        'payment_gateway_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobPost()
    {
        return $this->belongsTo(JobPost::class, 'reference_id');
    }
}
