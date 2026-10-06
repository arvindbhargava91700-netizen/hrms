<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ListingMeta extends Model
{
    protected $table = 'listing_meta';
    protected $fillable = ['listing_id', 'custom_field_id', 'value'];

    public function listing()     { return $this->belongsTo(Listing::class); }
    public function customField() { return $this->belongsTo(CustomField::class); }
}
