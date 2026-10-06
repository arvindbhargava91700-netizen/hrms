<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemModule extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function partnerPackages()
    {
        return $this->belongsToMany(PartnerPackage::class, 'partner_package_system_module');
    }

    public function packages()
    {
        return $this->hasMany(ModulePackage::class);
    }
}
