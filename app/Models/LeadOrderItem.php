<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadOrderItem extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(LeadOrder::class, 'lead_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
