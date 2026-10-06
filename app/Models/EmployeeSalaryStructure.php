<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'merchant_target',
        'basic_salary',
        'allowances',
        'deductions',
        'gross_salary',
        'net_salary',
        'salary_type',
        'monthly_target',
        'commission_percent',
        'recovery_percent',
        'commission_level_id',
    ];

    protected $casts = [
        'allowances' => 'array',
        'deductions' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
