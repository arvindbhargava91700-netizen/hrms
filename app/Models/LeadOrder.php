<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadOrder extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'target_credited' => 'boolean',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function items()
    {
        return $this->hasMany(LeadOrderItem::class, 'lead_order_id');
    }

    public function currentStage()
    {
        return $this->belongsTo(PipelineStage::class, 'current_stage_id');
    }

    public function stageComments()
    {
        return $this->hasMany(LeadOrderStageComment::class, 'lead_order_id')->latest();
    }

    public function stageApprovals()
    {
        return $this->hasMany(LeadOrderStageApproval::class, 'lead_order_id');
    }

    // Approval relationships
    public function financeApprover()
    {
        return $this->belongsTo(User::class, 'finance_approved_by');
    }

    public function documentApprover()
    {
        return $this->belongsTo(User::class, 'document_approved_by');
    }

    public function accountsApprover()
    {
        return $this->belongsTo(User::class, 'accounts_approved_by');
    }

    public function operationsApprover()
    {
        return $this->belongsTo(User::class, 'operations_approved_by');
    }
        public function payments()
    {
        return $this->hasMany(LeadOrderPayment::class, 'lead_order_id')->latest();
    }
}
