<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'total_new_business',
        'total_recovery_business',
        'target_amount',
        'commission_earned',
        'recovery_earned',
        'upline_commission_earned',
        'tds_percent',
        'tds_amount',
        'net_payout',
        'total_payout',
        'status',
        'paid_at',
        'calculation_details',
    ];

    protected $casts = [
        'calculation_details' => 'array',
        'paid_at' => 'datetime',
        'tds_percent' => 'decimal:2',
        'tds_amount' => 'decimal:2',
        'net_payout' => 'decimal:2',
        'total_payout' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
