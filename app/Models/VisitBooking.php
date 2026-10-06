<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class VisitBooking extends Model
{
    protected $table = 'visit_bookings';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id','created_by','customer_id','listing_id','partner_id','visit_date','visit_time','status','note'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->id = $m->id ?: (string) Str::uuid());
    }

    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function partner()   { return $this->belongsTo(User::class, 'partner_id'); }
    public function listing()   { return $this->belongsTo(Listing::class, 'listing_id'); }
}
