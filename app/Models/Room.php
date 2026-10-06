<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Room extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id','floor_id','room_number','room_type','capacity','available_beds',
        'full_rent','per_bed_rent','security_deposit',
    ];
    protected $casts = [
        'full_rent' => 'decimal:2', 'per_bed_rent' => 'decimal:2', 'security_deposit' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: (string) Str::uuid());
    }

    public function floor() { return $this->belongsTo(Floor::class); }
    public function images() { return $this->hasMany(RoomImage::class)->orderBy('sort_order'); }
    public function getOccupiedBedsAttribute(): int { return $this->capacity - $this->available_beds; }
    public function isAvailable(): bool { return $this->available_beds > 0; }
    
    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Subscription::class, 'room_id');
    }
}
