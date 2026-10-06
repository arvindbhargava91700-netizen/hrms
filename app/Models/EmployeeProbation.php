<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeProbation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_date'           => 'date',
        'confirmation_due_date' => 'date',
        'extended_due_date'    => 'date',
        'confirmation_date'    => 'date',
        'is_extended'          => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
