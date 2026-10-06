<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LeadOrderStageApproval extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'lead_order_id',
        'pipeline_stage_id',
        'approved_by',
        'status',
        'notes',
        'approved_at',
    ];

    protected $casts = ['approved_at' => 'datetime'];

    public function order() { return $this->belongsTo(LeadOrder::class, 'lead_order_id'); }
    public function stage() { return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}
