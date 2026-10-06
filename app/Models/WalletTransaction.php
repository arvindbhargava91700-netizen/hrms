<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'user_id', 'amount', 'type', 'description', 'reference_type', 'reference_id'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: (string) \Illuminate\Support\Str::uuid());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
