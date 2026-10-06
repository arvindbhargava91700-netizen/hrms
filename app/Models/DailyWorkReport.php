<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyWorkReport extends Model
{
    protected $fillable = [
        'employee_id',
        'partner_id',
        'report_date',
        'summary',
        'status',
        'manager_id',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function items()
    {
        return $this->hasMany(DailyWorkItem::class);
    }

    public function getCompletionPercentageAttribute()
    {
        $total = $this->items()->count();
        if ($total === 0) return 0;
        
        $completed = $this->items()->where('status', 'Completed')->count();
        return round(($completed / $total) * 100);
    }
}
