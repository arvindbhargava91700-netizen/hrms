<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LeadOrderStageComment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'lead_order_id',
        'pipeline_stage_id',
        'user_id',
        'comment',
    ];

    public function order()
    {
        return $this->belongsTo(LeadOrder::class, 'lead_order_id');
    }

    public function stage()
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
