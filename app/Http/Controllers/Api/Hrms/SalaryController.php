<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\EmployeeSalaryStructure;
use App\Models\User;
use App\Models\EmployeePayroll;

class SalaryController extends Controller
{
    public static function getDefaultAllowances(): array
    {
        return [
            'hra' => 0,
            'da' => 0,
            'conveyance' => 0,
            'medical' => 0,
            'special' => 0,
            'travel' => 0,
            'internet' => 0,
            'food' => 0,
            'performance_incentive' => 0,
            'sales_incentive' => 0,
            'bonus' => 0,
            'overtime' => 0,
            'shift' => 0,
            'other' => 0,
        ];
    }

    public static function getDefaultDeductions(): array
    {
        return [
            'pf' => 0,
            'esi' => 0,
            'pt' => 0,
            'tds' => 0,
            'lwf' => 0,
            'notice_period' => 0,
            'other' => 0,
        ];
    }

    public static function formatAllowances($allowances): array
    {
        $defaults = self::getDefaultAllowances();
        if (is_array($allowances)) {
            foreach ($allowances as $key => $value) {
                $defaults[$key] = is_numeric($value) ? (float)$value : $value;
            }
        }
        return $defaults;
    }

    public static function formatDeductions($deductions): array
    {
        $defaults = self::getDefaultDeductions();
        if (is_array($deductions)) {
            foreach ($deductions as $key => $value) {
                $defaults[$key] = is_numeric($value) ? (float)$value : $value;
            }
        }
        return $defaults;
    }

    /**
     * Get list of employees with their salary structures (matching /workspace/hrms/payroll/salary)
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        if (!$user->isPartner() && !$user->canAccess('salary_viewAny') && !$user->canAccess('salary_viewBranch') && !$user->canAccess('salary_viewTeam') && !$user->canAccess('salary_viewOwn')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.'
            ], 403);
        }

        $query = User::where('parent_id', $partnerId)
            ->with(['department', 'branch', 'roles']);

        if (!$user->isPartner() && !$user->canAccess('salary_viewAny')) {
            if ($user->canAccess('salary_viewBranch') || $user->canAccess('salary_viewTeam')) {
                $query->whereIn('id', $user->getTeamIds());
            } else {
                $query->where('id', $user->id);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) {
            $query->where('employment_status', $request->status);
        }

        $perPage = (int) $request->input('per_page', 10);
        $employees = $query->orderBy('name')->paginate($perPage);

        $employeeIds = $employees->pluck('id')->toArray();
        $structures = EmployeeSalaryStructure::whereIn('employee_id', $employeeIds)
            ->get()
            ->keyBy('employee_id');

        $data = $employees->getCollection()->map(function ($emp) use ($structures) {
            $structure = $structures->get($emp->id);
            $basic = (float)($structure ? $structure->basic_salary : ($emp->basic_salary ?: 0));
            $allowancesList = self::formatAllowances($structure ? $structure->allowances : []);
            $totalAllowances = array_sum(array_map('floatval', $allowancesList));
            $perfIncentive = (float)($allowancesList['performance_incentive'] ?? 0);
            $salesIncentive = (float)($allowancesList['sales_incentive'] ?? 0);
            $totalIncentives = $perfIncentive + $salesIncentive;
            $gross = $structure ? (float)$structure->gross_salary : ($basic + $totalAllowances);

            $deductionsList = self::formatDeductions($structure ? $structure->deductions : []);
            $totalDeductions = array_sum(array_map('floatval', $deductionsList));
            $net = $structure ? (float)$structure->net_salary : ($gross - $totalDeductions);

            if ($perfIncentive > 0 && $salesIncentive > 0) {
                $incentiveText = "₹" . number_format($perfIncentive, 0) . " Performance + ₹" . number_format($salesIncentive, 0) . " Incentive";
            } elseif ($perfIncentive > 0) {
                $incentiveText = "₹" . number_format($perfIncentive, 0) . " Performance Incentive";
            } elseif ($salesIncentive > 0) {
                $incentiveText = "₹" . number_format($salesIncentive, 0) . " Sales Incentive";
            } elseif ($totalAllowances > 0) {
                $incentiveText = "₹" . number_format($totalAllowances, 0) . " Performance & Allowance";
            } else {
                $incentiveText = "₹0 Performance + ₹0 Incentive";
            }

            $incentiveAmount = $totalIncentives > 0 ? $totalIncentives : $totalAllowances;

            return [
                'id' => $emp->id,
                'name' => $emp->name,
                'email' => $emp->email,
                'mobile' => $emp->mobile,
                'employee_code' => $emp->employee_code,
                'department' => $emp->department ? [
                    'id' => $emp->department->id,
                    'name' => $emp->department->name,
                ] : null,
                'branch' => $emp->branch ? [
                    'id' => $emp->branch->id,
                    'name' => $emp->branch->name,
                ] : null,
                'basic_salary' => round($basic, 2),
                'gross_salary' => round($gross, 2),
                'net_salary' => round($net, 2),
                'total_allowances' => round($totalAllowances, 2),
                'total_deductions' => round($totalDeductions, 2),
                'performance_incentive' => round($perfIncentive, 2),
                'sales_incentive' => round($salesIncentive, 2),
                'total_incentives' => round($totalIncentives, 2),
                'incentive_amount' => round($incentiveAmount, 2),
                'incentive_text' => $incentiveText,
                'salary_type' => $structure ? $structure->salary_type : 'base_plus_target',
                'monthly_target' => $structure ? (float)$structure->monthly_target : 0,
                'merchant_target' => $structure ? (float)$structure->merchant_target : 0,
                'commission_percent' => $structure ? (float)$structure->commission_percent : 0,
                'recovery_percent' => $structure ? (float)$structure->recovery_percent : 0,
                'commission_level_id' => $structure ? $structure->commission_level_id : null,
                'allowances' => $allowancesList,
                'deductions' => $deductionsList,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ]
        ]);
    }

    /**
     * Get an employee's salary structure
     */
    public function getSalaryStructure(Request $request, $employeeId): JsonResponse
    {
        $employee = User::where('id', $employeeId)->first();

        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee not found.'
            ], 404);
        }
        
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('salary_viewAny')) {
            $teamIds = $user->getTeamIds();
            if (!in_array($employee->id, $teamIds) && $employee->id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized or employee not in team.'
                ], 403);
            }
        }

        $salaryStructure = EmployeeSalaryStructure::firstOrCreate(
            ['employee_id' => $employee->id],
            [
                'basic_salary' => $employee->basic_salary ?: 0,
                'allowances' => self::getDefaultAllowances(),
                'deductions' => self::getDefaultDeductions(),
                'gross_salary' => $employee->basic_salary ?: 0,
                'net_salary' => $employee->basic_salary ?: 0,
            ]
        );

        $allowancesList = self::formatAllowances($salaryStructure->allowances);
        $deductionsList = self::formatDeductions($salaryStructure->deductions);
        $totalAllowances = array_sum(array_map('floatval', $allowancesList));
        $perfIncentive = (float)($allowancesList['performance_incentive'] ?? 0);
        $salesIncentive = (float)($allowancesList['sales_incentive'] ?? 0);

        if ($perfIncentive > 0 && $salesIncentive > 0) {
            $incentiveText = "₹" . number_format($perfIncentive, 0) . " Performance + ₹" . number_format($salesIncentive, 0) . " Incentive";
        } elseif ($perfIncentive > 0) {
            $incentiveText = "₹" . number_format($perfIncentive, 0) . " Performance Incentive";
        } elseif ($salesIncentive > 0) {
            $incentiveText = "₹" . number_format($salesIncentive, 0) . " Sales Incentive";
        } elseif ($totalAllowances > 0) {
            $incentiveText = "₹" . number_format($totalAllowances, 0) . " Performance & Allowance";
        } else {
            $incentiveText = "₹0 Performance + ₹0 Incentive";
        }

        $responseData = $salaryStructure->toArray();
        $responseData['allowances'] = $allowancesList;
        $responseData['deductions'] = $deductionsList;
        $responseData['incentive_text'] = $incentiveText;
        $responseData['performance_incentive'] = round($perfIncentive, 2);
        $responseData['sales_incentive'] = round($salesIncentive, 2);
        $responseData['total_allowances'] = round($totalAllowances, 2);

        return response()->json([
            'status' => 'success',
            'data' => $responseData
        ]);
    }

    /**
     * Update an employee's salary structure
     */
    public function updateSalaryStructure(Request $request, $employeeId): JsonResponse
    {
        $employee = User::where('id', $employeeId)->first();

        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee not found.'
            ], 404);
        }

        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('salary_update')) {
            $teamIds = $user->getTeamIds();
            if (!in_array($employee->id, $teamIds)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized or employee not in team.'
                ], 403);
            }
        }

        $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|array',
            'deductions' => 'nullable|array',
            'salary_type' => 'nullable|string|in:base_only,base_plus_target,commission_only',
            'monthly_target' => 'nullable|numeric|min:0',
            'merchant_target' => 'nullable|numeric|min:0',
            'commission_percent' => 'nullable|numeric|min:0',
            'recovery_percent' => 'nullable|numeric|min:0',
            'commission_level_id' => 'nullable|exists:commission_levels,id'
        ]);

        $basicSalary = $request->input('basic_salary', 0);
        $allowances = $request->input('allowances', []);
        $deductions = $request->input('deductions', []);
        
        $salaryType = $request->input('salary_type', 'base_plus_target');
        $monthlyTarget = $request->input('monthly_target', 0);
        $merchantTarget = $request->input('merchant_target', 0);
        $commissionPercent = $request->input('commission_percent', 0);
        $recoveryPercent = $request->input('recovery_percent', 0);
        $commissionLevelId = $request->input('commission_level_id');

        // Calculate Gross Salary: Basic + All Allowances
        $grossSalary = $basicSalary;
        if (is_array($allowances)) {
            foreach ($allowances as $key => $val) {
                $grossSalary += floatval($val);
            }
        }

        // Calculate Net Salary: Gross - All Deductions
        $totalDeductions = 0;
        if (is_array($deductions)) {
            foreach ($deductions as $key => $val) {
                $totalDeductions += floatval($val);
            }
        }
        $netSalary = max(0, $grossSalary - $totalDeductions);

        $salaryStructure = EmployeeSalaryStructure::updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'salary_type' => $salaryType,
                'monthly_target' => $monthlyTarget,
                'merchant_target' => $merchantTarget,
                'commission_percent' => $commissionPercent,
                'recovery_percent' => $recoveryPercent,
                'commission_level_id' => $commissionLevelId,
                'basic_salary' => $basicSalary,
                'allowances' => $allowances,
                'deductions' => $deductions,
                'gross_salary' => $grossSalary,
                'net_salary' => $netSalary,
            ]
        );

        // Update fallback basic_salary on User model
        $employee->update(['basic_salary' => $basicSalary]);

        $responseData = $salaryStructure->toArray();
        $responseData['allowances'] = self::formatAllowances($salaryStructure->allowances);
        $responseData['deductions'] = self::formatDeductions($salaryStructure->deductions);

        return response()->json([
            'status' => 'success',
            'message' => 'Salary structure updated successfully.',
            'data' => $responseData
        ]);
    }

    /**
     * Get My Payroll (Employee payslips/history)
     */
    public function getMyPayroll(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        
        $payrolls = EmployeePayroll::where('employee_id', $user->id)
                        ->orderBy('year', 'desc')
                        ->orderBy('month', 'desc')
                        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $payrolls
        ]);
    }
}
