<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\EmployeeExit;
use App\Models\ExitReason;
use App\Models\User;
use App\Models\HrmsBranch;
use App\Models\Department;
use App\Models\Designation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AttritionController extends Controller
{
    /**
     * Helper to get partner_id for HRMS API authenticated user
     */
    private function getPartnerId()
    {
        $user = auth('hrms_api')->user();
        return $user->isPartner() ? $user->id : $user->parent_id;
    }

    /**
     * List employee exit records with pagination, filters, and full attrition analytics
     */
    public function index(Request $request): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $filterYear = $request->input('year', date('Y'));
        $filterMonth = $request->input('month', 'all');
        $filterBranchId = $request->input('branch_id');
        $filterDepartmentId = $request->input('department_id');
        $filterExitType = $request->input('exit_type', 'all');
        $filterExitReason = $request->input('exit_reason', 'all');
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        // Base Employees Query
        $employeesQuery = User::where(function ($q) use ($partnerId) {
            $q->where('parent_id', $partnerId)
              ->orWhere('id', $partnerId);
        })->whereIn('role', ['employee', 'manager']);

        // Exits Query
        $exitsQuery = EmployeeExit::with(['employee', 'branch', 'department', 'designation'])
            ->where('partner_id', $partnerId);

        if (!empty($filterYear)) {
            $exitsQuery->whereYear('exit_date', $filterYear);
        }

        if ($filterMonth !== 'all' && !empty($filterMonth)) {
            $exitsQuery->whereMonth('exit_date', $filterMonth);
        }

        if (!empty($filterBranchId)) {
            $exitsQuery->where('branch_id', $filterBranchId);
        }

        if (!empty($filterDepartmentId)) {
            $exitsQuery->where('department_id', $filterDepartmentId);
        }

        if ($filterExitType !== 'all' && !empty($filterExitType)) {
            $exitsQuery->where('exit_type', $filterExitType);
        }

        if ($filterExitReason !== 'all' && !empty($filterExitReason)) {
            $exitsQuery->where('exit_reason', $filterExitReason);
        }

        if (!empty($search)) {
            $exitsQuery->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        // Clone for Analytics
        $analyticsQuery = clone $exitsQuery;
        $totalExits = (clone $analyticsQuery)->count();
        $voluntaryExits = (clone $analyticsQuery)->where('exit_type', 'voluntary')->count();
        $involuntaryExits = (clone $analyticsQuery)->where('exit_type', 'involuntary')->count();

        // Paginated Exits List
        $exits = (clone $exitsQuery)->latest('exit_date')->paginate($perPage);

        // Headcount & Attrition Rate Calculation
        $year = $filterYear ?: date('Y');
        $startDate = ($filterMonth !== 'all' && !empty($filterMonth))
            ? Carbon::createFromDate($year, $filterMonth, 1)->startOfMonth()
            : Carbon::createFromDate($year, 1, 1)->startOfYear();

        $endDate = ($filterMonth !== 'all' && !empty($filterMonth))
            ? Carbon::createFromDate($year, $filterMonth, 1)->endOfMonth()
            : Carbon::createFromDate($year, 12, 31)->endOfYear();

        // Opening Headcount
        $openingHeadcount = (clone $employeesQuery)
            ->where(function ($q) use ($startDate) {
                $q->whereNull('joining_date')
                  ->orWhere('joining_date', '<=', $startDate->format('Y-m-d'));
            })
            ->where(function ($q) use ($startDate) {
                $q->whereNull('resignation_date')
                  ->whereNull('termination_date')
                  ->orWhere('resignation_date', '>=', $startDate->format('Y-m-d'))
                  ->orWhere('termination_date', '>=', $startDate->format('Y-m-d'));
            })
            ->count();

        // New Joiners in period
        $newJoiners = (clone $employeesQuery)
            ->whereBetween('joining_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->count();

        // Exits in period
        $periodExits = (clone $analyticsQuery)->count();

        $closingHeadcount = max(0, $openingHeadcount + $newJoiners - $periodExits);
        $averageHeadcount = ($openingHeadcount + $closingHeadcount) / 2;
        $attritionRate = $averageHeadcount > 0 ? round(($periodExits / $averageHeadcount) * 100, 2) : 0;

        // Current Active Employees
        $currentActiveEmployees = (clone $employeesQuery)->where('status', 'active')->count();

        // Monthly Exits Breakdown
        $monthlyExits = [];
        for ($m = 1; $m <= 12; $m++) {
            $mStart = Carbon::createFromDate($year, $m, 1)->startOfMonth();
            $mEnd = Carbon::createFromDate($year, $m, 1)->endOfMonth();

            $mExits = EmployeeExit::where('partner_id', $partnerId)
                ->whereBetween('exit_date', [$mStart->format('Y-m-d'), $mEnd->format('Y-m-d')])
                ->when(!empty($filterBranchId), fn($q) => $q->where('branch_id', $filterBranchId))
                ->when(!empty($filterDepartmentId), fn($q) => $q->where('department_id', $filterDepartmentId))
                ->count();

            $monthlyExits[] = [
                'month' => $mStart->format('M'),
                'exits' => $mExits
            ];
        }

        // Department-wise Attrition Breakdown
        $deptAttrition = EmployeeExit::selectRaw('department_id, count(*) as exit_count')
            ->where('partner_id', $partnerId)
            ->whereYear('exit_date', $year)
            ->when(!empty($filterBranchId), fn($q) => $q->where('branch_id', $filterBranchId))
            ->groupBy('department_id')
            ->with('department')
            ->get()
            ->map(function ($item) use ($partnerId) {
                $deptName = $item->department?->name ?? 'Unassigned';
                $deptEmpCount = User::where(function ($q) use ($partnerId) {
                        $q->where('parent_id', $partnerId)->orWhere('id', $partnerId);
                    })
                    ->where('department_id', $item->department_id)
                    ->count();
                $rate = $deptEmpCount > 0 ? round(($item->exit_count / $deptEmpCount) * 100, 1) : 0;

                return [
                    'department_id' => $item->department_id,
                    'department' => $deptName,
                    'exits' => $item->exit_count,
                    'employees' => $deptEmpCount,
                    'rate' => $rate
                ];
            });

        // Branch-wise Attrition Breakdown
        $branchAttrition = EmployeeExit::selectRaw('branch_id, count(*) as exit_count')
            ->where('partner_id', $partnerId)
            ->whereYear('exit_date', $year)
            ->groupBy('branch_id')
            ->with('branch')
            ->get()
            ->map(function ($item) use ($partnerId) {
                $branchName = $item->branch?->name ?? 'Main Branch';
                $branchEmpCount = User::where(function ($q) use ($partnerId) {
                        $q->where('parent_id', $partnerId)->orWhere('id', $partnerId);
                    })
                    ->where('branch_id', $item->branch_id)
                    ->count();
                $rate = $branchEmpCount > 0 ? round(($item->exit_count / $branchEmpCount) * 100, 1) : 0;

                return [
                    'branch_id' => $item->branch_id,
                    'branch' => $branchName,
                    'exits' => $item->exit_count,
                    'employees' => $branchEmpCount,
                    'rate' => $rate
                ];
            });

        // Reason Breakdown
        $reasonBreakdown = EmployeeExit::selectRaw('exit_reason, count(*) as count')
            ->where('partner_id', $partnerId)
            ->whereYear('exit_date', $year)
            ->groupBy('exit_reason')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Attrition records and analytics fetched successfully.',
            'analytics' => [
                'current_active_employees' => $currentActiveEmployees,
                'total_exits' => $totalExits,
                'voluntary_exits' => $voluntaryExits,
                'involuntary_exits' => $involuntaryExits,
                'attrition_rate' => $attritionRate,
                'opening_headcount' => $openingHeadcount,
                'closing_headcount' => $closingHeadcount,
                'average_headcount' => $averageHeadcount,
                'new_joiners' => $newJoiners,
                'monthly_exits' => $monthlyExits,
                'dept_attrition' => $deptAttrition,
                'branch_attrition' => $branchAttrition,
                'reason_breakdown' => $reasonBreakdown,
            ],
            'data' => $exits
        ]);
    }

    /**
     * Single exit record details
     */
    public function show($id): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $exit = EmployeeExit::with(['employee', 'branch', 'department', 'designation', 'creator', 'updater'])
            ->where('partner_id', $partnerId)
            ->where('id', $id)
            ->first();

        if (!$exit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee exit record not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Employee exit record fetched successfully.',
            'data' => $exit
        ]);
    }

    /**
     * Record a new employee exit
     */
    public function store(Request $request): JsonResponse
    {
        $partnerId = $this->getPartnerId();
        $user = auth('hrms_api')->user();

        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'exit_date' => 'required|date',
            'last_working_date' => 'nullable|date',
            'resignation_date' => 'nullable|date',
            'exit_type' => 'required|in:voluntary,involuntary',
            'exit_reason' => 'required|string|max:255',
            'notice_period_days' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:pending,completed,cancelled',
            'exit_interview_notes' => 'nullable|string',
            'clearance_status' => 'nullable|string',
            'fnf_status' => 'nullable|string',
            'fnf_amount' => 'nullable|numeric',
            'fnf_settlement_date' => 'nullable|date',
        ]);

        $employee = User::where(function ($q) use ($partnerId) {
            $q->where('parent_id', $partnerId)->orWhere('id', $partnerId);
        })->where('id', $validated['employee_id'])->first();

        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee does not belong to this organization.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $exitDate = $validated['exit_date'];
            $lastWorkingDate = $validated['last_working_date'] ?? $exitDate;

            $data = [
                'employee_id' => $employee->id,
                'partner_id' => $partnerId,
                'branch_id' => $employee->branch_id,
                'department_id' => $employee->department_id,
                'designation_id' => $employee->designation_id,
                'resignation_date' => $validated['resignation_date'] ?? ($validated['exit_type'] === 'voluntary' ? $exitDate : null),
                'exit_date' => $exitDate,
                'last_working_date' => $lastWorkingDate,
                'exit_type' => $validated['exit_type'],
                'exit_reason' => $validated['exit_reason'],
                'notice_period_days' => $validated['notice_period_days'] ?? 0,
                'remarks' => $validated['remarks'] ?? null,
                'status' => $validated['status'] ?? 'completed',
                'exit_interview_notes' => $validated['exit_interview_notes'] ?? null,
                'clearance_status' => $validated['clearance_status'] ?? 'pending',
                'fnf_status' => $validated['fnf_status'] ?? 'pending',
                'fnf_amount' => $validated['fnf_amount'] ?? 0,
                'fnf_settlement_date' => $validated['fnf_settlement_date'] ?? null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ];

            $exit = EmployeeExit::create($data);

            // Save exit reason dynamically to DB if not existing
            if (!empty($validated['exit_reason'])) {
                ExitReason::firstOrCreate([
                    'name' => trim($validated['exit_reason']),
                    'partner_id' => $partnerId,
                ], [
                    'type' => $validated['exit_type'],
                    'is_active' => true,
                ]);
            }

            // Update Employee status & employment details
            $empStatus = ($validated['exit_type'] === 'voluntary') ? 'resigned' : 'terminated';
            $updatePayload = [
                'employment_status' => $empStatus,
                'status' => 'inactive',
            ];
            if ($validated['exit_type'] === 'voluntary') {
                $updatePayload['resignation_date'] = $exitDate;
            } else {
                $updatePayload['termination_date'] = $exitDate;
            }
            $employee->update($updatePayload);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Employee exit record created successfully.',
                'data' => $exit->load(['employee', 'branch', 'department', 'designation'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record exit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an employee exit record
     */
    public function update(Request $request, $id): JsonResponse
    {
        $partnerId = $this->getPartnerId();
        $user = auth('hrms_api')->user();

        $exit = EmployeeExit::where('partner_id', $partnerId)->where('id', $id)->first();

        if (!$exit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee exit record not found.'
            ], 404);
        }

        $validated = $request->validate([
            'employee_id' => 'sometimes|required|exists:users,id',
            'exit_date' => 'sometimes|required|date',
            'last_working_date' => 'nullable|date',
            'resignation_date' => 'nullable|date',
            'exit_type' => 'sometimes|required|in:voluntary,involuntary',
            'exit_reason' => 'sometimes|required|string|max:255',
            'notice_period_days' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:pending,completed,cancelled',
            'exit_interview_notes' => 'nullable|string',
            'clearance_status' => 'nullable|string',
            'fnf_status' => 'nullable|string',
            'fnf_amount' => 'nullable|numeric',
            'fnf_settlement_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only([
                'exit_date', 'last_working_date', 'resignation_date', 'exit_type',
                'exit_reason', 'notice_period_days', 'remarks', 'status',
                'exit_interview_notes', 'clearance_status', 'fnf_status', 'fnf_amount', 'fnf_settlement_date'
            ]);

            $data['updated_by'] = $user->id;

            if ($request->has('employee_id') && $request->employee_id != $exit->employee_id) {
                $employee = User::where(function ($q) use ($partnerId) {
                    $q->where('parent_id', $partnerId)->orWhere('id', $partnerId);
                })->where('id', $request->employee_id)->first();

                if ($employee) {
                    $data['employee_id'] = $employee->id;
                    $data['branch_id'] = $employee->branch_id;
                    $data['department_id'] = $employee->department_id;
                    $data['designation_id'] = $employee->designation_id;
                }
            }

            $exit->update($data);

            // Dynamic Exit Reason check
            if (!empty($exit->exit_reason)) {
                ExitReason::firstOrCreate([
                    'name' => trim($exit->exit_reason),
                    'partner_id' => $partnerId,
                ], [
                    'type' => $exit->exit_type,
                    'is_active' => true,
                ]);
            }

            // Update associated employee status
            $employee = User::find($exit->employee_id);
            if ($employee) {
                $empStatus = ($exit->exit_type === 'voluntary') ? 'resigned' : 'terminated';
                $updatePayload = [
                    'employment_status' => $empStatus,
                    'status' => 'inactive',
                ];
                if ($exit->exit_type === 'voluntary') {
                    $updatePayload['resignation_date'] = $exit->exit_date;
                } else {
                    $updatePayload['termination_date'] = $exit->exit_date;
                }
                $employee->update($updatePayload);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Employee exit record updated successfully.',
                'data' => $exit->fresh(['employee', 'branch', 'department', 'designation'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update exit record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete exit record and revert employee status to active
     */
    public function destroy($id): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $exit = EmployeeExit::where('partner_id', $partnerId)->where('id', $id)->first();

        if (!$exit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee exit record not found.'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $employee = User::find($exit->employee_id);
            if ($employee && $employee->employment_status !== 'active') {
                $employee->update([
                    'employment_status' => 'active',
                    'status' => 'active',
                ]);
            }

            $exit->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Employee exit record deleted and employee status restored to active.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete exit record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exit reasons list
     */
    public function exitReasons(Request $request): JsonResponse
    {
        $partnerId = $this->getPartnerId();
        $type = $request->input('type'); // voluntary or involuntary

        $reasons = ExitReason::where(function ($q) use ($partnerId) {
            $q->whereNull('partner_id')
              ->orWhere('partner_id', $partnerId);
        })
        ->where('is_active', true)
        ->when(!empty($type), function ($q) use ($type) {
            $q->where('type', $type);
        })
        ->orderBy('name')
        ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Exit reasons fetched successfully.',
            'data' => $reasons
        ]);
    }

    /**
     * Store new Exit Reason
     */
    public function storeExitReason(Request $request): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:voluntary,involuntary',
            'is_active' => 'nullable|boolean',
        ]);

        $reason = ExitReason::create([
            'partner_id' => $partnerId,
            'name' => trim($validated['name']),
            'type' => $validated['type'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Exit reason created successfully.',
            'data' => $reason
        ], 201);
    }

    /**
     * Update Exit Reason
     */
    public function updateExitReason(Request $request, $id): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $reason = ExitReason::where(function ($q) use ($partnerId) {
            $q->where('partner_id', $partnerId);
        })->where('id', $id)->first();

        if (!$reason) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exit reason not found or system standard.'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:voluntary,involuntary',
            'is_active' => 'nullable|boolean',
        ]);

        $reason->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Exit reason updated successfully.',
            'data' => $reason
        ]);
    }

    /**
     * Delete Exit Reason
     */
    public function destroyExitReason($id): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $reason = ExitReason::where('partner_id', $partnerId)->where('id', $id)->first();

        if (!$reason) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exit reason not found or cannot be deleted.'
            ], 404);
        }

        $reason->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Exit reason deleted successfully.'
        ]);
    }

    /**
     * Export attrition data as CSV stream
     */
    public function exportCsv(Request $request)
    {
        $partnerId = $this->getPartnerId();

        $filterYear = $request->input('year', date('Y'));
        $filterMonth = $request->input('month', 'all');
        $filterBranchId = $request->input('branch_id');
        $filterDepartmentId = $request->input('department_id');
        $filterExitType = $request->input('exit_type', 'all');

        $exits = EmployeeExit::with(['employee', 'branch', 'department', 'designation'])
            ->where('partner_id', $partnerId)
            ->when(!empty($filterYear), fn($q) => $q->whereYear('exit_date', $filterYear))
            ->when($filterMonth !== 'all' && !empty($filterMonth), fn($q) => $q->whereMonth('exit_date', $filterMonth))
            ->when(!empty($filterBranchId), fn($q) => $q->where('branch_id', $filterBranchId))
            ->when(!empty($filterDepartmentId), fn($q) => $q->where('department_id', $filterDepartmentId))
            ->when($filterExitType !== 'all' && !empty($filterExitType), fn($q) => $q->where('exit_type', $filterExitType))
            ->orderBy('exit_date', 'desc')
            ->get();

        $filename = "attrition_report_" . date('Y_m_d_H_i') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($exits) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Employee Name', 'Employee Code', 'Email', 'Mobile', 'Branch', 'Department', 'Designation', 'Exit Date', 'Last Working Date', 'Exit Type', 'Exit Reason', 'Notice Period (Days)', 'Remarks', 'Status']);

            foreach ($exits as $exit) {
                fputcsv($file, [
                    $exit->employee?->name ?? 'N/A',
                    $exit->employee?->employee_code ?? 'N/A',
                    $exit->employee?->email ?? 'N/A',
                    $exit->employee?->mobile ?? 'N/A',
                    $exit->branch?->name ?? 'N/A',
                    $exit->department?->name ?? 'N/A',
                    $exit->designation?->name ?? 'N/A',
                    $exit->exit_date?->format('Y-m-d') ?? '',
                    $exit->last_working_date?->format('Y-m-d') ?? '',
                    ucfirst($exit->exit_type),
                    $exit->exit_reason,
                    $exit->notice_period_days,
                    $exit->remarks,
                    ucfirst($exit->status)
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
