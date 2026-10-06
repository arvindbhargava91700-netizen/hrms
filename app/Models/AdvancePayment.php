<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvancePayment extends Model
{
    protected $fillable = [
        'partner_id',
        'employee_id',
        'amount',
        'reason',
        'status',
        'remarks',
        'approved_by',
        'deduction_month',
        'deduction_year',
        'is_deducted',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
