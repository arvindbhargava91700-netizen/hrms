<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerModuleSubscription extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function systemModule()
    {
        return $this->belongsTo(SystemModule::class);
    }

    public function modulePackage()
    {
        return $this->belongsTo(ModulePackage::class);
    }
    
    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}
