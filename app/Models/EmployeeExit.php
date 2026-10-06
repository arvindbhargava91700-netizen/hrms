<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmployeeExit extends Model
{
    use HasFactory;

    protected $table = 'employee_exits';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'employee_id',
        'partner_id',
        'branch_id',
        'department_id',
        'designation_id',

        'resignation_date',
        'exit_date',
        'last_working_date',

        'exit_type',
        'exit_reason',
        'notice_period_days',

        'clearance_status',

        'fnf_status',
        'fnf_amount',
        'fnf_settlement_date',

        'status',

        'exit_interview_notes',
        'remarks',

        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'resignation_date'    => 'date',
        'exit_date'           => 'date',
        'last_working_date'   => 'date',
        'fnf_settlement_date' => 'date',

        'notice_period_days'  => 'integer',
        'fnf_amount'          => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function branch()
    {
        return $this->belongsTo(HrmsBranch::class, 'branch_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}