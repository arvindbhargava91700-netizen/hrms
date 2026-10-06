<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\CommissionLevel;
use App\Models\CommissionPayout;
use App\Models\PartnerSetting;

class CommissionSettingController extends Controller
{
    //===================================================== Commission Level Api===========================================================//

    /**
     * List commission levels for the partner.
     */
    public function commissionLevels(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $levels = CommissionLevel::where('partner_id', $partnerId)
            ->orderBy('level_order', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $levels
        ]);
    }

    /**
     * Create a new commission level.
     */
    public function storeCommissionLevel(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'level_name' => 'required|string|max:255',
            'level_order' => 'required|integer|min:1',
            'commission_percent' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $level = CommissionLevel::create([
            'partner_id' => $partnerId,
            'level_name' => $request->level_name,
            'level_order' => $request->level_order,
            'commission_percent' => $request->commission_percent,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Commission Level added successfully.',
            'data' => $level
        ], 201);
    }

    /**
     * Update a commission level.
     */
    public function updateCommissionLevel(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'level_name' => 'sometimes|required|string|max:255',
            'level_order' => 'sometimes|required|integer|min:1',
            'commission_percent' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $level = CommissionLevel::where('partner_id', $partnerId)->findOrFail($id);

        $level->update([
            'level_name' => $request->filled('level_name') ? $request->level_name : $level->level_name,
            'level_order' => $request->filled('level_order') ? $request->level_order : $level->level_order,
            'commission_percent' => $request->filled('commission_percent') ? $request->commission_percent : $level->commission_percent,
            'description' => $request->has('description') ? $request->description : $level->description,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Commission Level updated successfully.',
            'data' => $level
        ]);
    }

    /**
     * Delete a commission level.
     */
    public function deleteCommissionLevel($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $level = CommissionLevel::where('partner_id', $partnerId)->findOrFail($id);
        $level->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Commission Level removed successfully.'
        ]);
    }


    //===================================================== Commission Tds Api===========================================================//

    /**
     * Get the commission TDS rate (%).
     */
    public function getCommissionTds(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $value = PartnerSetting::where('partner_id', $partnerId)
            ->where('key', 'commission_tds_percent')
            ->value('value');

        return response()->json([
            'status' => 'success',
            'data' => [
                'commission_tds_percent' => $value !== null ? (float) $value : 5.00
            ]
        ]);
    }

    /**
     * Update the commission TDS rate (%).
     */
    public function updateCommissionTds(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'commission_tds_percent' => 'required|numeric|min:0|max:100',
        ]);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        PartnerSetting::updateOrCreate(
            ['partner_id' => $partnerId, 'key' => 'commission_tds_percent'],
            ['value' => $request->commission_tds_percent]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Commission TDS percentage saved successfully.',
            'data' => [
                'commission_tds_percent' => (float) $request->commission_tds_percent
            ]
        ]);
    }

    //===================================================== Commission Process Api===========================================================//

    /**
     * Process (generate) monthly commission payouts for the partner's team.
     * Mirrors the HRMS "Process Commissions" screen.
     */
    public function processCommissions(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('payroll_viewAny')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'month' => 'required|numeric|min:1|max:12',
            'year' => 'required|numeric|min:2020|max:2099',
        ]);

        $month = (int) $request->month;
        $year = (int) $request->year;
        $formattedMonth = str_pad($month, 2, '0', STR_PAD_LEFT);
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        // Resolve team employee IDs (replicates HasPartnerId::getTeamEmployeeIds with payroll_viewAny)
        if ($user->role === 'partner' || $user->canAccess('payroll_viewAny')) {
            $employeeIds = \App\Models\User::where('parent_id', $partnerId)
                ->where('role', 'employee')
                ->pluck('id')
                ->toArray();
        } else {
            $employeeIds = $user->getTeamIds();
        }

        $employees = \App\Models\User::whereIn('id', $employeeIds)
            ->whereHas('payrolls')
            ->get();

        $processedCount = 0;
        $commissionService = new \App\Services\CommissionService();

        // Fetch Partner TDS rate
        $tdsPercent = (float) (PartnerSetting::where('partner_id', $partnerId)
            ->where('key', 'commission_tds_percent')
            ->value('value') ?? 5.00);

        foreach ($employees as $employee) {
            // Calculate direct commissions
            $commissionData = $commissionService->calculateEmployeeCommission($employee, $month, $year);
            $totalDirectCommission = $commissionData['total_commission'];
            $newBusiness = $commissionData['new_business'];
            $recoveryBusiness = $commissionData['recovery_business'];

            // Calculate upline (hierarchy) commissions earned by this employee from downlines
            $uplineData = $commissionService->calculateUplineCommissionForManager($employee, $month, $year);
            $uplineEarned = $uplineData['total'];

            $commissionData['upline_commission'] = $uplineEarned;
            $commissionData['upline_details'] = $uplineData['details'];
            $grossPayout = $totalDirectCommission + $uplineEarned;

            $tdsAmount = round(($grossPayout * $tdsPercent) / 100, 2);
            $netPayout = max(0, round($grossPayout - $tdsAmount, 2));

            $commissionData['tds_percent'] = $tdsPercent;
            $commissionData['tds_amount'] = $tdsAmount;
            $commissionData['net_payout'] = $netPayout;

            // Record commission if the employee has a target or earned something
            if ($commissionData['target_required'] > 0 || $grossPayout > 0) {
                $exists = CommissionPayout::where('employee_id', $employee->id)
                    ->where('month', $formattedMonth)
                    ->where('year', $year)
                    ->exists();

                if (!$exists) {
                    CommissionPayout::create([
                        'employee_id' => $employee->id,
                        'month' => $formattedMonth,
                        'year' => $year,
                        'total_new_business' => $newBusiness,
                        'total_recovery_business' => $recoveryBusiness,
                        'target_amount' => $commissionData['target_required'],
                        'commission_earned' => $commissionData['base_commission'],
                        'recovery_earned' => $commissionData['recovery_commission'],
                        'upline_commission_earned' => $uplineEarned,
                        'tds_percent' => $tdsPercent,
                        'tds_amount' => $tdsAmount,
                        'net_payout' => $netPayout,
                        'total_payout' => $grossPayout,
                        'status' => 'pending',
                        'calculation_details' => $commissionData
                    ]);

                    $processedCount++;
                }
            }
        }

        if ($processedCount > 0) {
            return response()->json([
                'status' => 'success',
                'message' => "Successfully generated {$processedCount} commission records for {$formattedMonth}/{$year}.",
                'data' => ['processed_count' => $processedCount]
            ]);
        }

        return response()->json([
            'status' => 'info',
            'message' => 'No new commissions to generate for this period.',
            'data' => ['processed_count' => 0]
        ]);
    }


    //===================================================== Commission History Api===========================================================//

    /**
     * List commission payout history for the partner's team.
     * Mirrors the HRMS "Commission History" screen, with optional
     * month / year / employee_id filters.
     */
    public function history(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        // Resolve team employee IDs (mirrors HasPartnerId::getTeamEmployeeIds with no permission)
        if ($user->role === 'partner') {
            $teamIds = \App\Models\User::where('parent_id', $partnerId)
                ->where('role', 'employee')
                ->pluck('id')
                ->toArray();
        } else {
            $teamIds = $user->getTeamIds();
        }

        $query = CommissionPayout::with(['employee', 'employee.designation'])
            ->whereIn('employee_id', $teamIds);

        if ($request->filled('month')) {
            $query->where('month', str_pad((int) $request->month, 2, '0', STR_PAD_LEFT));
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $perPage = (int) $request->input('per_page', 15);
        $payouts = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $payouts
        ]);
    }
}
