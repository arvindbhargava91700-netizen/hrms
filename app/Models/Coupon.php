<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'title', 'description', 'type', 'value',
        'min_amount', 'max_discount', 'listing_id',
        'max_uses', 'used_count', 'expires_at', 'is_active',
    ];
    protected $casts = [
        'value'        => 'decimal:2',
        'min_amount'   => 'decimal:2',
        'max_discount' => 'decimal:2',
        'expires_at'   => 'datetime',
        'is_active'    => 'boolean',
    ];

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->max_uses > 0 && $this->used_count >= $this->max_uses) return false;
        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if ($amount < $this->min_amount) return 0;

        $discount = $this->type === 'percent'
            ? ($amount * $this->value / 100)
            : $this->value;

        if ($this->max_discount && $discount > $this->max_discount) {
            $discount = $this->max_discount;
        }

        return min($discount, $amount);
    }
}
