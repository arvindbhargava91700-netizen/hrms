<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class Listing extends Model
{
    use SoftDeletes;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id','partner_id','created_by','category_id','gender_type','title','description','address','city','state','pincode','landmark','opening_time','closing_time','phone','lat','lng','status','search_keywords', 'security_deposit'
    ];
    protected $casts = ['lat' => 'decimal:8', 'lng' => 'decimal:8', 'security_deposit' => 'decimal:2'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($m) {
            $m->id = $m->id ?: (string) Str::uuid();
            if (empty($m->listing_number)) {
                $m->listing_number = 'LST-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
            }
        });
    }

    public function partner()   { return $this->belongsTo(User::class, 'partner_id'); }
    public function category()  { return $this->belongsTo(Category::class); }
    public function packages()  { return $this->hasMany(Package::class); }
    public function images()    { return $this->hasMany(ListingImage::class)->orderBy('sort_order'); }
    public function shifts()    { return $this->hasMany(ListingShift::class)->orderBy('shift_name'); }
    public function trainers()  { return $this->hasMany(ListingTrainer::class); }
    public function floors()    { return $this->hasMany(Floor::class)->orderBy('floor_number'); }
    public function meta()      { return $this->hasMany(ListingMeta::class); }
    public function reviews()   { return $this->hasMany(ListingReview::class); }
    public function visitBookings() { return $this->hasMany(VisitBooking::class); }
    public function coupons() { return $this->hasMany(Coupon::class); }
    public function subscriptions() { return $this->hasManyThrough(Subscription::class, Package::class); }
    public function rooms() { return $this->hasManyThrough(Room::class, Floor::class); }

    public function getMetaValue(int $fieldId): ?string
    {
        return $this->meta->where('custom_field_id', $fieldId)->first()?->value;
    }

    public function scopeApproved($q) 
    { 
        return $q->where('status', 'approved')
            ->whereHas('partner.partnerSubscriptions', function ($subQuery) {
                $subQuery->where('status', 'active')
                    ->where(function ($sq) {
                        $sq->whereNull('expires_at')
                          ->orWhere('expires_at', '>=', now()->startOfDay());
                    });
            });
    }
    public function scopePending($q)  { return $q->where('status', 'pending'); }
    public function scopeForPartner($q, $partnerId) { return $q->where('partner_id', $partnerId); }
}
