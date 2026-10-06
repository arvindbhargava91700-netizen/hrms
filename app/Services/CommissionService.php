<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmployeeSalaryStructure;
use App\Models\CommissionPayout;
use Carbon\Carbon;

class CommissionService
{
    /**
     * Calculate new business amount for an employee in a given month/year.
     * Only orders that have been approved at a target-credited stage (or completed) count.
     */
    public function calculateNewBusiness(User $employee, $month, $year)
    {
        $startDate = Carbon::createFromDate((int)$year, (int)$month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        return (float) \App\Models\LeadOrder::where('employee_id', $employee->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where(function ($query) {
                $query->where('target_credited', true)
                      ->orWhere('approval_status', 'completed');
            })
            ->sum('paid_amount');
    }

    /**
     * Calculate commissions for an employee in a given month/year.
     */
    public function calculateEmployeeCommission(User $employee, $month, $year)
    {
        $structure = EmployeeSalaryStructure::where('employee_id', $employee->id)->first();
        
        if (!$structure) {
            return [
                'base_commission' => 0,
                'recovery_commission' => 0,
                'hierarchy_commission' => 0,
                'total_commission' => 0,
                'target_achieved' => 0,
                'target_required' => 0,
                'new_business' => 0,
                'recovery_business' => 0,
            ];
        }

        $startDate = Carbon::createFromDate((int)$year, (int)$month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        // Target for the month
        $monthlyTarget = (float)($structure->monthly_target ?? 0);

        // ─── 1. Calculate New Business ─────────────────────────────────────────
        $newBusiness = $this->calculateNewBusiness($employee, $month, $year);

        // ─── 2. Calculate Recovery Business ────────────────────────────────────
        // Recovery = orders created BEFORE this month that received payment this month.
        $recoveryBusiness = (float) \App\Models\LeadOrder::where('employee_id', $employee->id)
            ->where('created_at', '<', $startDate)
            ->where('paid_amount', '>', 0)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->sum('paid_amount');

        // ─── 3. Base Commission ─────────────────────────────────────────────────
        $baseCommission = 0;
        $targetAchieved = false;

        if ($newBusiness >= $monthlyTarget && $monthlyTarget > 0) {
            $targetAchieved = true;
            $excess = $newBusiness - $monthlyTarget;
            $baseCommission = ($excess * (float)($structure->commission_percent ?? 0)) / 100;
        } elseif ($structure->salary_type === 'commission_only' && $newBusiness > 0 && $monthlyTarget == 0) {
            // commission_only with no target: earn commission on all business
            $baseCommission = ($newBusiness * (float)($structure->commission_percent ?? 0)) / 100;
        }

        // ─── 4. Recovery Commission ─────────────────────────────────────────────
        // Recovery commission is ONLY earned if the current month's new business target is achieved.
        $recoveryCommission = 0;
        if ($targetAchieved && $recoveryBusiness > 0) {
            $recoveryCommission = ($recoveryBusiness * (float)($structure->recovery_percent ?? 0)) / 100;
        }

        $totalDirectCommission = $baseCommission + $recoveryCommission;

        return [
            'base_commission'    => $baseCommission,
            'recovery_commission'=> $recoveryCommission,
            'hierarchy_commission'=> 0,
            'total_commission'   => $totalDirectCommission,
            'target_achieved'    => $newBusiness,   // amount achieved
            'target_required'    => $monthlyTarget,
            'recovery_business'  => $recoveryBusiness,
            'new_business'       => $newBusiness,
            'basic_salary'       => (float)($structure->basic_salary ?? 0),
            'est_total_earning'  => (float)($structure->basic_salary ?? 0) + $totalDirectCommission,
        ];
    }

    /**
     * Calculate hierarchy commissions for a given manager based on their downline's business.
     */
    public function calculateUplineCommissionForManager(User $manager, $month, $year)
    {
        $uplineCommission = 0;
        $downlineDetails = [];
        
        $managerStructure = EmployeeSalaryStructure::where('employee_id', $manager->id)->first();
        if (!$managerStructure || !$managerStructure->commission_level_id) {
            return ['total' => 0, 'details' => []];
        }
        
        $level = \App\Models\CommissionLevel::find($managerStructure->commission_level_id);
        if (!$level || $level->commission_percent <= 0) {
            return ['total' => 0, 'details' => []];
        }
        
        $downlineIds = $manager->getTeamIds();
        $downlineIds = array_diff($downlineIds, [$manager->id]);
        
        if (count($downlineIds) > 0) {
            $formattedMonth = str_pad($month, 2, '0', STR_PAD_LEFT);
            $downlines = User::whereIn('id', $downlineIds)->get();
            
            foreach ($downlines as $downline) {
                $downlineNewBusiness = $this->calculateNewBusiness($downline, $formattedMonth, $year);
                
                if ($downlineNewBusiness > 0) {
                    $earnedFromDownline = ($downlineNewBusiness * $level->commission_percent) / 100;
                    $uplineCommission += $earnedFromDownline;
                    
                    $downlineDetails[] = [
                        'employee_id' => $downline->id,
                        'employee_name' => $downline->name,
                        'business_generated' => $downlineNewBusiness,
                        'commission_earned' => $earnedFromDownline
                    ];
                }
            }
        }
        
        return [
            'total' => $uplineCommission,
            'details' => $downlineDetails
        ];
    }
}
