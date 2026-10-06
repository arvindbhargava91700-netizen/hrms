<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerSubscription extends Model
{
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'partner_id', 'partner_package_id', 'starts_at', 'expires_at', 'status'
    ];

    protected $casts = [
        'starts_at' => 'date',
        'expires_at' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: (string) \Illuminate\Support\Str::uuid());
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function package()
    {
        return $this->belongsTo(PartnerPackage::class, 'partner_package_id');
    }
}
