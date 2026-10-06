<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingReview extends Model
{
    protected $fillable = ['listing_id', 'customer_id', 'subscription_id', 'rating', 'comment'];
    protected $casts    = ['rating' => 'integer'];

    public function listing()      { return $this->belongsTo(Listing::class); }
    public function customer()     { return $this->belongsTo(User::class, 'customer_id'); }
    public function subscription() { return $this->belongsTo(Subscription::class); }
}
