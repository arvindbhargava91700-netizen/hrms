<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerSetting extends Model
{
    protected $fillable = ['partner_id', 'key', 'value'];
}
