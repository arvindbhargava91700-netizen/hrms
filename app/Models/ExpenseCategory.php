<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ExpenseCategory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'partner_id',
        'name',
        'type',
        'unit_name',
        'rate_per_unit',
        'max_limit_amount',
        'status',
    ];

    protected $casts = [
        'rate_per_unit' => 'decimal:2',
        'max_limit_amount' => 'decimal:2',
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}
