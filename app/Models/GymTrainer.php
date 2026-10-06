<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GymTrainer extends Model
{
    protected $fillable = ['listing_id', 'name', 'specialization', 'experience_years', 'photo'];

    public function listing() { return $this->belongsTo(Listing::class); }

    public function getPhotoUrlAttribute(): string
    {
        return $this->photo
            ? asset('storage/' . $this->photo)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=2563EB&color=fff';
    }
}
