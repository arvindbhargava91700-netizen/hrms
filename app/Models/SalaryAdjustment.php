<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_payroll_id',
        'adjustment_type',
        'field_key',
        'original_amount',
        'adjusted_amount',
    ];

    protected $casts = [
        'original_amount' => 'float',
        'adjusted_amount' => 'float',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(EmployeePayroll::class, 'employee_payroll_id');
    }
}