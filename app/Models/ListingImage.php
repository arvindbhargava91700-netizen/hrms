<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ListingImage extends Model
{
    protected $fillable = ['listing_id', 'image_path', 'caption', 'sort_order'];
    protected $appends = ['url'];
    protected $hidden = ['created_at', 'updated_at'];

    public function listing() { return $this->belongsTo(Listing::class); }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }
}
