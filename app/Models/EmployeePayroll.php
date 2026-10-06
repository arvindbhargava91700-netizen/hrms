<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePayroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'basic_salary',
        'allowances_breakdown',
        'deductions',
        'deductions_breakdown',
        'bonuses',
        'commissions',
        'commissions_breakdown',
        'gross_pay',
        'net_pay',
        'status',
    ];

    protected $casts = [
        'allowances_breakdown' => 'array',
        'deductions_breakdown' => 'array',
        'commissions_breakdown' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function salaryAdjustments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SalaryAdjustment::class, 'employee_payroll_id');
    }
}
