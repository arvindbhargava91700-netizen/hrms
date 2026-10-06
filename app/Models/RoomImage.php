<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RoomImage extends Model
{
    protected $fillable = ['room_id', 'image_path', 'caption', 'sort_order'];

    public function room() { return $this->belongsTo(Room::class); }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }
}
