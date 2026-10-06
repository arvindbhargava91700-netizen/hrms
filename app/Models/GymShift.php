<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GymShift extends Model
{
    protected $fillable = ['listing_id', 'shift_name', 'start_time', 'end_time', 'max_members', 'fee'];
    protected $casts = ['fee' => 'decimal:2'];

    public function listing() { return $this->belongsTo(Listing::class); }

    public function getShiftLabelAttribute(): string
    {
        return match($this->shift_name) {
            'morning' => '🌅 Morning',
            'evening' => '🌆 Evening',
            'night'   => '🌙 Night',
            default   => ucfirst($this->shift_name),
        };
    }
}
