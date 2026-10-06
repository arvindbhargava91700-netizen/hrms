<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadOrderPayment extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(LeadOrder::class, 'lead_order_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
    public function leadOrder()
{
    return $this->belongsTo(
        \App\Models\LeadOrder::class,
        'lead_order_id'
    );
}
}
