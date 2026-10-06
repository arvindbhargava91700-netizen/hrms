<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'branch_id',
        'department_id',
        'asset_code',
        'category',
        'name',
        'brand',
        'model',
        'serial_number',
        'imei_number',
        'mobile_number',
        'sim_number',
        'card_number',
        'purchase_date',
        'purchase_cost',
        'condition',
        'status',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(HrmsBranch::class, 'branch_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
