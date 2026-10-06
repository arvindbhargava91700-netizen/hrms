<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ListingTrainer extends Model
{
    protected $fillable = ['listing_id', 'name', 'specialization', 'experience_years', 'description', 'photo'];

    public function listing() { return $this->belongsTo(Listing::class); }

    public function getPhotoUrlAttribute(): string
    {
        $photos = json_decode($this->photo, true);
        $firstPhoto = is_array($photos) ? ($photos[0] ?? null) : $this->photo;

        return $firstPhoto
            ? asset('storage/' . $firstPhoto)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=2563EB&color=fff';
    }
}
