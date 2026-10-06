<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Package extends Model
{
    use SoftDeletes;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id','listing_id','room_id','room_type','name','occupancy_type','duration_days','price','type','features','meal_plans'];
    protected $casts = ['features' => 'array', 'meal_plans' => 'array', 'price' => 'decimal:2'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: (string) Str::uuid());
    }

    public function listing()       { return $this->belongsTo(Listing::class); }
    public function room()          { return $this->belongsTo(Room::class); }
    public function subscriptions() { return $this->hasMany(Subscription::class); }

    public function getDurationLabelAttribute(): string
    {
        return match($this->type) {
            'monthly'     => 'Monthly',
            'quarterly'   => 'Quarterly (3 mo)',
            'half_yearly' => 'Half Yearly (6 mo)',
            'yearly'      => 'Yearly',
            default       => "{$this->duration_days} days",
        };
    }
}
