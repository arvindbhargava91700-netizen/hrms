<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    protected $fillable = ['listing_id', 'name', 'floor_number'];

    public function listing() { return $this->belongsTo(Listing::class); }
    public function rooms()   { return $this->hasMany(Room::class)->orderBy('room_number'); }
}
