<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerPackage extends Model
{
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'price', 'duration_days', 'commission_type', 'commission_value', 'commission_ranges', 'is_active',
        'is_free_trial', 'category_limit', 'listing_limit',
        'email_notification', 'app_notification', 'sms_notification', 'whatsapp_notification',
        'payment_gateways', 'offline_commission_type', 'offline_commission_value', 'offline_commission_ranges'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'commission_value' => 'decimal:2',
        'commission_ranges' => 'array',
        'is_active' => 'boolean',
        'is_free_trial' => 'boolean',
        'category_limit' => 'integer',
        'listing_limit' => 'integer',
        'email_notification' => 'boolean',
        'app_notification' => 'boolean',
        'sms_notification' => 'boolean',
        'whatsapp_notification' => 'boolean',
        'payment_gateways' => 'array',
        'offline_commission_value' => 'decimal:2',
        'offline_commission_ranges' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: (string) \Illuminate\Support\Str::uuid());
    }

    public function subscriptions()
    {
        return $this->hasMany(PartnerSubscription::class);
    }

    public function systemModules()
    {
        return $this->belongsToMany(SystemModule::class, 'partner_package_system_module');
    }
}
