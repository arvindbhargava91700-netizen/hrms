<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function visits()
    {
        return $this->hasMany(CustomerVisit::class, 'lead_id');
    }

    public function orders()
    {
        return $this->hasMany(LeadOrder::class, 'lead_id');
    }
}
