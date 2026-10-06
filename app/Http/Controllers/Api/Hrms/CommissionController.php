<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\CommissionPayout;
use App\Models\EmployeeSalaryStructure;
use App\Services\CommissionService;
use Carbon\Carbon;

class CommissionController extends Controller
{
    /**
     * Get Employee Targets and Current Commission Metrics
     */
    public function getTargets(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        
        $month = $request->input('month', (int) date('m'));
        $year = $request->input('year', (int) date('Y'));

        $commissionService = new CommissionService();
        $metrics = $commissionService->calculateEmployeeCommission($user, $month, $year);

        $structure = EmployeeSalaryStructure::where('employee_id', $user->id)->first();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'metrics' => $metrics,
                'structure' => $structure
            ]
        ]);
    }

    /**
     * Get Commission History
     */
    public function getHistory(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        
        $history = CommissionPayout::where('employee_id', $user->id)
                        ->orderBy('year', 'desc')
                        ->orderBy('month', 'desc')
                        ->limit(12)
                        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }
}
