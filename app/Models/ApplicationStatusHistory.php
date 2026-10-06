<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationStatusHistory extends Model
{
    /** @use HasFactory<\Database\Factories\ApplicationStatusHistoryFactory> */
    use HasFactory;
    
    protected $guarded = [];
}
