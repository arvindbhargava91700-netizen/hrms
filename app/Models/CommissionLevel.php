<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'level_name',
        'level_order',
        'commission_percent',
        'description',
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}
