<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModulePackage extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function systemModule()
    {
        return $this->belongsTo(SystemModule::class);
    }
}
