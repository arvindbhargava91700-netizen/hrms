<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'designation_id');
    }
}
