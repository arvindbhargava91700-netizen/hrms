<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\PartnerSetting;
use App\Models\Department;

class PerformanceSettingController extends Controller
{
    /**
     * Get the authenticated partner ID.
     */
    protected function getPartnerId()
    {
        $user = auth('hrms_api')->user();
        return $user->isPartner() ? $user->id : $user->parent_id;
    }

    /**
     * Get Global/Company Default Performance Score Weights.
     */
    public function getGlobalPerformance(Request $request): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $attendance = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_attendance_weight')->value('value') ?? 25);
        $tasks = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_tasks_weight')->value('value') ?? 25);
        $merchant = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_merchant_target_weight')->value('value') ?? 25);
        $monthly = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_monthly_target_weight')->value('value') ?? 25);

        return response()->json([
            'status' => 'success',
            'data' => [
                'perf_attendance_weight'      => $attendance,
                'perf_tasks_weight'           => $tasks,
                'perf_merchant_target_weight' => $merchant,
                'perf_monthly_target_weight'  => $monthly,
                'total'                       => $attendance + $tasks + $merchant + $monthly,
            ]
        ]);
    }

    /**
     * Update Global/Company Default Performance Score Weights.
     */
    public function updateGlobalPerformance(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'perf_attendance_weight'      => 'required|integer|min:0|max:100',
            'perf_tasks_weight'           => 'required|integer|min:0|max:100',
            'perf_merchant_target_weight' => 'required|integer|min:0|max:100',
            'perf_monthly_target_weight'  => 'required|integer|min:0|max:100',
        ]);

        $att = (int) $request->perf_attendance_weight;
        $task = (int) $request->perf_tasks_weight;
        $merch = (int) $request->perf_merchant_target_weight;
        $month = (int) $request->perf_monthly_target_weight;

        $total = $att + $task + $merch + $month;

        if ($total !== 100) {
            return response()->json([
                'status'  => 'error',
                'message' => "Total score weightage must equal exactly 100 points! (Current sum: {$total} pts)."
            ], 422);
        }

        $partnerId = $this->getPartnerId();

        PartnerSetting::updateOrCreate(['partner_id' => $partnerId, 'key' => 'perf_attendance_weight'], ['value' => (string) $att]);
        PartnerSetting::updateOrCreate(['partner_id' => $partnerId, 'key' => 'perf_tasks_weight'], ['value' => (string) $task]);
        PartnerSetting::updateOrCreate(['partner_id' => $partnerId, 'key' => 'perf_merchant_target_weight'], ['value' => (string) $merch]);
        PartnerSetting::updateOrCreate(['partner_id' => $partnerId, 'key' => 'perf_monthly_target_weight'], ['value' => (string) $month]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Company default performance score weights saved successfully! Total: 100 pts.',
            'data'    => [
                'perf_attendance_weight'      => $att,
                'perf_tasks_weight'           => $task,
                'perf_merchant_target_weight' => $merch,
                'perf_monthly_target_weight'  => $month,
                'total'                       => 100,
            ]
        ]);
    }

    /**
     * List all staff members with performance score distribution mode and summary stats.
     */
    public function staffList(Request $request): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        // Load Global default weights
        $defaultAtt = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_attendance_weight')->value('value') ?? 25);
        $defaultTask = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_tasks_weight')->value('value') ?? 25);
        $defaultMerch = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_merchant_target_weight')->value('value') ?? 25);
        $defaultMonth = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_monthly_target_weight')->value('value') ?? 25);

        // Load Staff Overrides
        $rawOverrides = PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_staff_overrides')->value('value');
        $staffOverrides = !empty($rawOverrides) ? json_decode($rawOverrides, true) : [];

        $query = User::where('parent_id', $partnerId)
            ->where('role', 'employee')
            ->with(['department:id,name', 'designation:id,name', 'branch:id,name']);

        // Search filter
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('employee_code', 'like', $search)
                  ->orWhere('email', 'like', $search);
            });
        }

        // Department filter
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $allEmployees = $query->orderBy('name', 'asc')->get();

        $totalStaff = User::where('parent_id', $partnerId)->where('role', 'employee')->count();
        $customCount = count($staffOverrides);
        $defaultCount = max(0, $totalStaff - $customCount);

        $staffList = $allEmployees->map(function ($staff) use ($staffOverrides, $defaultAtt, $defaultTask, $defaultMerch, $defaultMonth) {
            $hasCustom = isset($staffOverrides[$staff->id]);
            $att = $hasCustom ? (int)($staffOverrides[$staff->id]['attendance'] ?? $defaultAtt) : $defaultAtt;
            $task = $hasCustom ? (int)($staffOverrides[$staff->id]['tasks'] ?? $defaultTask) : $defaultTask;
            $merch = $hasCustom ? (int)($staffOverrides[$staff->id]['merchant'] ?? $defaultMerch) : $defaultMerch;
            $month = $hasCustom ? (int)($staffOverrides[$staff->id]['monthly'] ?? $defaultMonth) : $defaultMonth;

            return [
                'id'            => $staff->id,
                'name'          => $staff->name,
                'employee_code' => $staff->employee_code ?: ('EMP-' . substr($staff->id, 0, 4)),
                'email'         => $staff->email,
                'avatar_url'    => $staff->avatar_url,
                'department'    => $staff->department?->name ?? 'General Department',
                'designation'   => $staff->designation?->name ?? 'Staff',
                'branch'        => $staff->branch?->name ?? null,
                'score_mode'    => $hasCustom ? 'custom' : 'default',
                'weights'       => [
                    'attendance'      => $att,
                    'tasks'           => $task,
                    'merchant_target' => $merch,
                    'monthly_target'  => $month,
                    'total'           => $att + $task + $merch + $month,
                ]
            ];
        });

        return response()->json([
            'status'  => 'success',
            'summary' => [
                'total_staff'   => $totalStaff,
                'custom_count'  => $customCount,
                'default_count' => $defaultCount,
            ],
            'data' => $staffList
        ]);
    }

    /**
     * Get specific employee's active performance weights & presets (for Configure modal).
     */
    public function staffShow(Request $request, $id): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $employee = User::where('parent_id', $partnerId)
            ->where('role', 'employee')
            ->with(['department:id,name', 'designation:id,name', 'branch:id,name'])
            ->findOrFail($id);

        // Load Global default weights
        $defaultAtt = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_attendance_weight')->value('value') ?? 25);
        $defaultTask = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_tasks_weight')->value('value') ?? 25);
        $defaultMerch = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_merchant_target_weight')->value('value') ?? 25);
        $defaultMonth = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_monthly_target_weight')->value('value') ?? 25);

        // Load Staff Overrides
        $rawOverrides = PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_staff_overrides')->value('value');
        $staffOverrides = !empty($rawOverrides) ? json_decode($rawOverrides, true) : [];

        $hasCustom = isset($staffOverrides[$employee->id]);
        $att = $hasCustom ? (int)($staffOverrides[$employee->id]['attendance'] ?? $defaultAtt) : $defaultAtt;
        $task = $hasCustom ? (int)($staffOverrides[$employee->id]['tasks'] ?? $defaultTask) : $defaultTask;
        $merch = $hasCustom ? (int)($staffOverrides[$employee->id]['merchant'] ?? $defaultMerch) : $defaultMerch;
        $month = $hasCustom ? (int)($staffOverrides[$employee->id]['monthly'] ?? $defaultMonth) : $defaultMonth;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'employee' => [
                    'id'            => $employee->id,
                    'name'          => $employee->name,
                    'employee_code' => $employee->employee_code ?: ('EMP-' . substr($employee->id, 0, 4)),
                    'email'         => $employee->email,
                    'avatar_url'    => $employee->avatar_url,
                    'department'    => $employee->department?->name ?? 'General Department',
                    'designation'   => $employee->designation?->name ?? 'Staff',
                    'branch'        => $employee->branch?->name ?? null,
                ],
                'score_mode' => $hasCustom ? 'custom' : 'default',
                'weights'    => [
                    'attendance'      => $att,
                    'tasks'           => $task,
                    'merchant_target' => $merch,
                    'monthly_target'  => $month,
                    'total'           => $att + $task + $merch + $month,
                ],
                'presets' => [
                    [
                        'name'    => 'Equal Split (25/25/25/25)',
                        'weights' => ['attendance' => 25, 'tasks' => 25, 'merchant_target' => 25, 'monthly_target' => 25]
                    ],
                    [
                        'name'    => 'Sales Heavy (15/15/35/35)',
                        'weights' => ['attendance' => 15, 'tasks' => 15, 'merchant_target' => 35, 'monthly_target' => 35]
                    ],
                    [
                        'name'    => 'Tasks Heavy (20/40/20/20)',
                        'weights' => ['attendance' => 20, 'tasks' => 40, 'merchant_target' => 20, 'monthly_target' => 20]
                    ],
                    [
                        'name'    => 'Attendance Heavy (35/25/20/20)',
                        'weights' => ['attendance' => 35, 'tasks' => 25, 'merchant_target' => 20, 'monthly_target' => 20]
                    ],
                ]
            ]
        ]);
    }

    /**
     * Save custom performance score weights for a specific employee.
     */
    public function updateStaffPerformance(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $this->getPartnerId();

        $employee = User::where('parent_id', $partnerId)
            ->where('role', 'employee')
            ->findOrFail($id);

        $request->validate([
            'attendance_weight'      => 'required|integer|min:0|max:100',
            'tasks_weight'           => 'required|integer|min:0|max:100',
            'merchant_target_weight' => 'required|integer|min:0|max:100',
            'monthly_target_weight'  => 'required|integer|min:0|max:100',
        ]);

        $att = (int) $request->attendance_weight;
        $task = (int) $request->tasks_weight;
        $merch = (int) $request->merchant_target_weight;
        $month = (int) $request->monthly_target_weight;

        $total = $att + $task + $merch + $month;

        if ($total !== 100) {
            return response()->json([
                'status'  => 'error',
                'message' => "Total weightage for {$employee->name} must equal exactly 100 points! (Current: {$total} pts)."
            ], 422);
        }

        $rawOverrides = PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_staff_overrides')->value('value');
        $overrides = !empty($rawOverrides) ? json_decode($rawOverrides, true) : [];

        $overrides[$employee->id] = [
            'attendance' => $att,
            'tasks'      => $task,
            'merchant'   => $merch,
            'monthly'    => $month,
        ];

        PartnerSetting::updateOrCreate(
            ['partner_id' => $partnerId, 'key' => 'perf_staff_overrides'],
            ['value' => json_encode($overrides)]
        );

        return response()->json([
            'status'  => 'success',
            'message' => "Custom score weightage saved for {$employee->name}! (Total: 100 pts)",
            'data'    => [
                'employee_id' => $employee->id,
                'score_mode'  => 'custom',
                'weights'     => [
                    'attendance'      => $att,
                    'tasks'           => $task,
                    'merchant_target' => $merch,
                    'monthly_target'  => $month,
                    'total'           => 100,
                ]
            ]
        ]);
    }

    /**
     * Reset a single employee's score weights back to company default.
     */
    public function resetStaffPerformance(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $this->getPartnerId();

        $employee = User::where('parent_id', $partnerId)
            ->where('role', 'employee')
            ->findOrFail($id);

        $rawOverrides = PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_staff_overrides')->value('value');
        $overrides = !empty($rawOverrides) ? json_decode($rawOverrides, true) : [];

        if (isset($overrides[$employee->id])) {
            unset($overrides[$employee->id]);

            PartnerSetting::updateOrCreate(
                ['partner_id' => $partnerId, 'key' => 'perf_staff_overrides'],
                ['value' => json_encode($overrides)]
            );
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Performance weights for {$employee->name} reset to Company Default successfully."
        ]);
    }

    /**
     * Reset ALL staff members back to company default weights.
     */
    public function resetAllStaff(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $this->getPartnerId();

        PartnerSetting::updateOrCreate(
            ['partner_id' => $partnerId, 'key' => 'perf_staff_overrides'],
            ['value' => json_encode([])]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'All staff reset to Company Default weights successfully.'
        ]);
    }
}
