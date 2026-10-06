<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessNote extends Model
{
    protected $fillable = [
        'partner_id',
        'title',
        'description',
        'status',
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}
