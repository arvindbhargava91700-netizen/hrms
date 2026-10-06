<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TpiMerchant extends Model
{
    protected $fillable = [
        'user_id',
        'tpi_merchant_id',
        'tpi_kyc_id',
        'tpi_kyc_status',
        'tpi_password',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
