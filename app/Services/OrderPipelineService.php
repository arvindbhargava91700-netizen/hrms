<?php

namespace App\Services;

use App\Models\LeadOrder;
use App\Models\PipelineStage;
use Exception;

class OrderPipelineService
{
    /**
     * Move an order to the next stage
     */
    public function approveOrder(LeadOrder $order, $userId, $notes = null)
    {
        $partnerId = $order->partner_id;

        if ($order->approval_status === 'completed' || $order->approval_status === 'rejected') {
            throw new Exception("Order is already " . $order->approval_status);
        }

        $stages = PipelineStage::where('partner_id', $partnerId)->orderBy('order_index')->get();

        if ($stages->isEmpty()) {
            // Fallback if no stages defined
            $order->approval_status = 'completed';
            $order->target_credited = true;
            $order->save();
            return $order;
        }

        // Find current stage index
        $currentIndex = -1;
        if ($order->current_stage_id) {
            foreach ($stages as $index => $stage) {
                if ($stage->id === $order->current_stage_id) {
                    $currentIndex = $index;
                    break;
                }
            }
        }

        // Check if current stage being approved has counts_towards_target
        if ($currentIndex >= 0 && isset($stages[$currentIndex])) {
            $approvedStage = $stages[$currentIndex];
            if ($approvedStage->counts_towards_target) {
                $order->target_credited = true;
            }
        }

        // Move to next stage
        $nextIndex = $currentIndex + 1;

        if (isset($stages[$nextIndex])) {
            $nextStage = $stages[$nextIndex];
            $order->current_stage_id = $nextStage->id;
            $order->approval_status = 'pending';
        } else {
            // No more stages, order is completed
            $order->current_stage_id = null;
            $order->approval_status = 'completed';

            // If no stage had counts_towards_target enabled, credit on completion
            $hasTargetStage = $stages->contains('counts_towards_target', true);
            if (!$hasTargetStage) {
                $order->target_credited = true;
            }
        }

        $order->save();
        return $order;
    }

    public function rejectOrder(LeadOrder $order, $userId, $reason)
    {
        if ($order->approval_status === 'completed' || $order->approval_status === 'rejected') {
            throw new Exception("Order is already " . $order->approval_status);
        }

        $order->approval_status = 'rejected';
        $order->save();
        return $order;
    }
}
