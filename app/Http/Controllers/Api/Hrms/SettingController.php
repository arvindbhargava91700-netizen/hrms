<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Holiday;
use App\Models\HrmsBranch;
use App\Models\CommissionLevel;
use App\Models\PartnerSetting;
use App\Models\PipelineStage;
use App\Models\Department;
use App\Models\AttendanceChecklist;
use App\Models\LeaveCategory;
use App\Models\ExpenseCategory;
use App\Models\TaskStatus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class SettingController extends Controller
{


//===================================================== Commission Level Api===========================================================
    /**
     * List holidays for the partner (optionally filtered by branch/year/month).
     */
    public function holidays(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = Holiday::with('branch')->where('partner_id', $partnerId);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        $holidays = $query->orderBy('date', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $holidays
        ]);
    }

    /**
     * Create a new holiday.
     */
    public function storeHoliday(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        if ($request->branch_id) {
            HrmsBranch::where('id', $request->branch_id)
                ->where('partner_id', $partnerId)
                ->firstOrFail();
        }

        $holiday = Holiday::create([
            'partner_id' => $partnerId,
            'branch_id' => $request->branch_id ?: null,
            'name' => $request->name,
            'date' => $request->date,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Holiday added successfully.',
            'data' => $holiday
        ], 201);
    }
      /**
     * Update a holiday.
     */
    public function updateHoliday(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'date' => 'sometimes|required|date',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $holiday = Holiday::where('partner_id', $partnerId)->findOrFail($id);

        if ($request->filled('branch_id')) {
            HrmsBranch::where('id', $request->branch_id)
                ->where('partner_id', $partnerId)
                ->firstOrFail();
        }

        $holiday->update([
            'name' => $request->filled('name') ? $request->name : $holiday->name,
            'date' => $request->filled('date') ? $request->date : $holiday->date,
            'branch_id' => $request->has('branch_id') ? ($request->branch_id ?: null) : $holiday->branch_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Holiday updated successfully.',
            'data' => $holiday
        ]);
    }


    /**
     * Delete a holiday.
     */
    public function deleteHoliday($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $holiday = Holiday::where('partner_id', $partnerId)->findOrFail($id);
        $holiday->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Holiday removed successfully.'
        ]);
    }


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


    //===================================================== pipe line stag===========================================================//

    /**
     * List pipeline stages.
     */
    public function pipelineStages(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $stages = PipelineStage::where('partner_id', $partnerId)
            ->with(['department:id,name', 'assignedUser:id,name,employee_code'])
            ->orderBy('order_index', 'asc')
            ->get();

        $data = $stages->map(function ($stage) {
            return [
                'id' => $stage->id,
                'name' => $stage->name,
                'department_id' => $stage->department_id,
                'assigned_to' => $stage->assigned_to,
                'counts_towards_target' => (bool) $stage->counts_towards_target,
                'order_index' => $stage->order_index,
                'department' => $stage->department ? [
                    'id' => $stage->department->id,
                    'name' => $stage->department->name,
                ] : null,
                'assigned_user' => $stage->assignedUser ? [
                    'id' => $stage->assignedUser->id,
                    'name' => $stage->assignedUser->name,
                    'employee_code' => $stage->assignedUser->employee_code,
                ] : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Create a pipeline stage.
     */
    public function storePipelineStage(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'assigned_to' => 'nullable|exists:users,id',
            'counts_towards_target' => 'sometimes|boolean',
        ]);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        Department::where('id', $request->department_id)
            ->where('partner_id', $partnerId)
            ->firstOrFail();

        $countsTowardsTarget = (bool) $request->input('counts_towards_target', false);
        if ($countsTowardsTarget) {
            PipelineStage::where('partner_id', $partnerId)
                ->update(['counts_towards_target' => false]);
        }

        $maxOrder = PipelineStage::where('partner_id', $partnerId)->max('order_index');

        $stage = PipelineStage::create([
            'partner_id' => $partnerId,
            'name' => $request->name,
            'department_id' => $request->department_id,
            'assigned_to' => $request->filled('assigned_to') ? $request->assigned_to : null,
            'counts_towards_target' => $countsTowardsTarget,
            'order_index' => $maxOrder !== null ? $maxOrder + 1 : 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pipeline stage added successfully.',
            'data' => $stage
        ], 201);
    }

    /**
     * Update a pipeline stage.
     */
    public function updatePipelineStage(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'department_id' => 'sometimes|required|exists:departments,id',
            'assigned_to' => 'nullable|exists:users,id',
            'counts_towards_target' => 'sometimes|boolean',
        ]);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $stage = PipelineStage::where('partner_id', $partnerId)->findOrFail($id);

        if ($request->filled('department_id')) {
            Department::where('id', $request->department_id)
                ->where('partner_id', $partnerId)
                ->firstOrFail();
        }

        $countsTowardsTarget = $request->filled('counts_towards_target')
            ? (bool) $request->counts_towards_target
            : $stage->counts_towards_target;

        if ($countsTowardsTarget && !$stage->counts_towards_target) {
            PipelineStage::where('partner_id', $partnerId)
                ->where('id', '!=', $stage->id)
                ->update(['counts_towards_target' => false]);
        }

        $stage->update([
            'name' => $request->filled('name') ? $request->name : $stage->name,
            'department_id' => $request->filled('department_id') ? $request->department_id : $stage->department_id,
            'assigned_to' => $request->exists('assigned_to') ? ($request->input('assigned_to') ?: null) : $stage->assigned_to,
            'counts_towards_target' => $countsTowardsTarget,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pipeline stage updated successfully.',
            'data' => $stage
        ]);
    }

    /**
     * Delete a pipeline stage (reorders remaining stages).
     */
    public function deletePipelineStage($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $stage = PipelineStage::where('partner_id', $partnerId)->findOrFail($id);
        $stage->delete();

        $remaining = PipelineStage::where('partner_id', $partnerId)
            ->orderBy('order_index', 'asc')
            ->get();
        $index = 1;
        foreach ($remaining as $s) {
            $s->update(['order_index' => $index]);
            $index++;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pipeline stage deleted successfully.'
        ]);
    }

//===================================================== Attendence checklist api ===========================================================//
     /**
     * List all attendance checklist questions (management view).
     */
    public function attendanceChecklists(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $checklists = AttendanceChecklist::where('partner_id', $partnerId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $checklists
        ]);
    }

    /**
     * Create an attendance checklist question.
     */
    public function storeAttendanceChecklist(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('attendance_update')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'question' => 'required|string|max:255',
            'mode' => 'required|in:punch_in,punch_out,both',
        ]);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $checklist = AttendanceChecklist::create([
            'partner_id' => $partnerId,
            'question' => $request->question,
            'mode' => $request->mode,
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Checklist question added successfully.',
            'data' => $checklist
        ], 201);
    }

    /**
     * Toggle active status of a checklist question.
     */
    public function toggleAttendanceChecklist($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('attendance_update')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $checklist = AttendanceChecklist::where('partner_id', $partnerId)->findOrFail($id);
      //  dd($checklist);
        $checklist->update(['is_active' => !$checklist->is_active]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status updated successfully.',
            'data' => $checklist
        ]);
    }

    /**
     * Delete a checklist question.
     */
    public function deleteAttendanceChecklist($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('attendance_update')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $checklist = AttendanceChecklist::where('partner_id', $partnerId)->findOrFail($id);
        $checklist->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Checklist question deleted successfully.'
        ]);
    }
   //===================================================== Leave Categories Api===========================================================//

    /**
     * List all leave categories for the partner.
     */
    public function leaveCategories(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $categories = LeaveCategory::where('partner_id', $partnerId)
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Create a new leave category.
     */
    public function storeLeaveCategory(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'days' => 'required|integer|min:0',
            'status' => 'sometimes|boolean',
        ]);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $category = LeaveCategory::create([
            'partner_id' => $partnerId,
            'name' => $request->name,
            'days' => $request->days,
            'status' => $request->has('status') ? (bool) $request->status : true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Leave category created successfully.',
            'data' => $category
        ], 201);
    }

    /**
     * Update a leave category.
     */
    public function updateLeaveCategory(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'days' => 'sometimes|required|integer|min:0',
            'status' => 'sometimes|boolean',
        ]);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $category = LeaveCategory::where('partner_id', $partnerId)->findOrFail($id);

        $category->update([
            'name' => $request->filled('name') ? $request->name : $category->name,
            'days' => $request->filled('days') ? $request->days : $category->days,
            'status' => $request->has('status') ? (bool) $request->status : $category->status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Leave category updated successfully.',
            'data' => $category
        ]);
    }

    /**
     * Delete a leave category.
     */
    public function deleteLeaveCategory($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $category = LeaveCategory::where('partner_id', $partnerId)->findOrFail($id);
        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Leave category deleted successfully.'
        ]);
    }
//===================================================== Expense Categories Api===========================================================//

    /**
     * List expense categories for the partner.
     */
    public function expenseCategories(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $categories = ExpenseCategory::where('partner_id', $partnerId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Create a new expense category.
     */
    public function storeExpenseCategory(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'type' => 'required|in:per_unit,max_limit,actual',
            'status' => 'sometimes|in:active,inactive',
        ];

        if ($request->input('type') === 'per_unit') {
            $rules['unit_name'] = 'required|string|max:50';
            $rules['rate_per_unit'] = 'required|numeric|min:0.01';
        } elseif ($request->input('type') === 'max_limit') {
            $rules['max_limit_amount'] = 'required|numeric|min:0.01';
        }

        $request->validate($rules);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $category = ExpenseCategory::create([
            'partner_id' => $partnerId,
            'name' => $request->name,
            'type' => $request->type,
            'unit_name' => $request->type === 'per_unit' ? $request->unit_name : null,
            'rate_per_unit' => $request->type === 'per_unit' ? (float) $request->rate_per_unit : 0,
            'max_limit_amount' => $request->type === 'max_limit' ? (float) $request->max_limit_amount : null,
            'status' => $request->input('status', 'active'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Expense category created successfully.',
            'data' => $category
        ], 201);
    }

    /**
     * Update an expense category.
     */
    public function updateExpenseCategory(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:per_unit,max_limit,actual',
            'status' => 'sometimes|in:active,inactive',
        ];

        if ($request->input('type') === 'per_unit') {
            $rules['unit_name'] = 'sometimes|required|string|max:50';
            $rules['rate_per_unit'] = 'sometimes|required|numeric|min:0.01';
        } elseif ($request->input('type') === 'max_limit') {
            $rules['max_limit_amount'] = 'sometimes|required|numeric|min:0.01';
        }

        $request->validate($rules);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $category = ExpenseCategory::where('partner_id', $partnerId)->findOrFail($id);

        $type = $request->filled('type') ? $request->type : $category->type;

        $category->update([
            'name' => $request->filled('name') ? $request->name : $category->name,
            'type' => $type,
            'unit_name' => $type === 'per_unit' ? ($request->filled('unit_name') ? $request->unit_name : $category->unit_name) : null,
            'rate_per_unit' => $type === 'per_unit' ? ($request->filled('rate_per_unit') ? (float) $request->rate_per_unit : $category->rate_per_unit) : 0,
            'max_limit_amount' => $type === 'max_limit' ? ($request->filled('max_limit_amount') ? (float) $request->max_limit_amount : $category->max_limit_amount) : null,
            'status' => $request->filled('status') ? $request->status : $category->status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Expense category updated successfully.',
            'data' => $category
        ]);
    }

    /**
     * Delete an expense category.
     */
    public function deleteExpenseCategory($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $category = ExpenseCategory::where('partner_id', $partnerId)->findOrFail($id);
        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Expense category deleted successfully.'
        ]);
    }
 //===================================================== Task Status Api===========================================================//

    /**
     * List task statuses for the partner (mirrors the Task Statuses settings tab).
     * Seeds default statuses on first access, same as the Livewire page mount.
     */
    public function taskStatuses(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('task_viewAny') && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $this->ensureDefaultTaskStatuses($partnerId);

        $statuses = TaskStatus::where('partner_id', $partnerId)
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $statuses
        ]);
    }

    /**
     * Create a new task status.
     */
    public function storeTaskStatus(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'sometimes|required|string|max:50',
            'order' => 'sometimes|integer|min:0',
            'is_completed' => 'sometimes|boolean',
            'status' => 'sometimes|boolean',
        ]);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $name = $request->name;
        $isCompleted = ($request->has('is_completed') && $request->is_completed)
            || Str::contains(strtolower($name), ['complete', 'done', 'finish', 'closed']);

        $status = TaskStatus::create([
            'partner_id' => $partnerId,
            'name' => $name,
            'slug' => Str::slug($name, '_'),
            'color' => $request->filled('color') ? $request->color : 'primary',
            'order' => $request->filled('order') ? (int) $request->order : TaskStatus::where('partner_id', $partnerId)->count() + 1,
            'is_completed' => (bool) $isCompleted,
            'status' => $request->has('status') ? (bool) $request->status : true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Task status created successfully.',
            'data' => $status
        ], 201);
    }

    /**
     * Update a task status.
     */
    public function updateTaskStatus(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'color' => 'sometimes|required|string|max:50',
            'order' => 'sometimes|integer|min:0',
            'is_completed' => 'sometimes|boolean',
            'status' => 'sometimes|boolean',
        ]);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $item = TaskStatus::where('partner_id', $partnerId)->findOrFail($id);

        $data = [
            'name' => $request->filled('name') ? $request->name : $item->name,
            'color' => $request->filled('color') ? $request->color : $item->color,
            'order' => $request->filled('order') ? (int) $request->order : $item->order,
            'status' => $request->has('status') ? (bool) $request->status : (bool) $item->status,
        ];

        $data['slug'] = Str::slug($data['name'], '_');

        $isCompleted = ($request->has('is_completed') && $request->is_completed)
            || Str::contains(strtolower($data['name']), ['complete', 'done', 'finish', 'closed']);
        $data['is_completed'] = (bool) $isCompleted;

        $item->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Task status updated successfully.',
            'data' => $item
        ]);
    }

    /**
     * Delete a task status.
     */
    public function deleteTaskStatus($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $item = TaskStatus::where('partner_id', $partnerId)->findOrFail($id);
        $item->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Task status deleted successfully.'
        ]);
    }

    /**
     * Seed default task statuses when a partner has none configured (mirrors page mount).
     */
    private function ensureDefaultTaskStatuses($partnerId): void
    {
        if (TaskStatus::where('partner_id', $partnerId)->count() > 0) {
            return;
        }

        $defaults = [
            ['name' => 'Pending', 'slug' => 'pending', 'color' => 'warning', 'order' => 1, 'is_completed' => false],
            ['name' => 'In Progress', 'slug' => 'in_progress', 'color' => 'primary', 'order' => 2, 'is_completed' => false],
            ['name' => 'Under Review', 'slug' => 'under_review', 'color' => 'info', 'order' => 3, 'is_completed' => false],
            ['name' => 'Completed', 'slug' => 'completed', 'color' => 'success', 'order' => 4, 'is_completed' => true],
            ['name' => 'Cancelled', 'slug' => 'cancelled', 'color' => 'danger', 'order' => 5, 'is_completed' => false],
        ];

        foreach ($defaults as $d) {
            TaskStatus::create([
                'partner_id' => $partnerId,
                'name' => $d['name'],
                'slug' => $d['slug'],
                'color' => $d['color'],
                'order' => $d['order'],
                'is_completed' => $d['is_completed'],
                'status' => true,
            ]);
        }
    }
 //===================================================== Payslip Config Api===========================================================//

    /**
     * Build the payslip configuration array for a partner.
     */
    private function payslipConfigArray($partnerId): array
    {
        $get = fn($key, $default = null) =>
            PartnerSetting::where('partner_id', $partnerId)->where('key', $key)->value('value') ?? $default;

        $logo = $get('payslip_logo');
        $signature = $get('payslip_signature');

        return [
            'company_name' => $get('payslip_company_name', 'Your Company Name'),
            'company_address' => $get('payslip_company_address', 'Your Company Address'),
            'authorized_signatory' => $get('payslip_authorized_signatory', 'HR Manager'),
            'terms_conditions' => $get('payslip_terms_conditions'),
            'logo' => $logo ? Storage::url($logo) : null,
            'logo_path' => $logo,
            'signature' => $signature ? Storage::url($signature) : null,
            'signature_path' => $signature,
        ];
    }

    /**
     * Get the current payslip configuration.
     */
    public function getPayslipConfig(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        return response()->json([
            'status' => 'success',
            'data' => $this->payslipConfigArray($partnerId)
        ]);
    }

    /**
     * Update the payslip configuration (text fields and optional logo/signature uploads).
     */
    public function updatePayslipConfig(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user->isPartner() && !$user->canAccess('hrmssetting_manage')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'company_name' => 'sometimes|required|string|max:255',
            'company_address' => 'sometimes|required|string',
            'authorized_signatory' => 'sometimes|required|string|max:255',
            'terms_conditions' => 'nullable|string',
            'payslip_logo' => 'nullable|image|max:2048|dimensions:max_width=1024,max_height=1024',
            'payslip_signature' => 'nullable|image|max:2048|dimensions:max_width=1024,max_height=1024',
        ], [
            'payslip_logo.dimensions' => 'The logo image must not exceed 1024x1024 pixels.',
            'payslip_signature.dimensions' => 'The signature image must not exceed 1024x1024 pixels.',
        ]);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        if ($request->filled('company_name')) {
            PartnerSetting::updateOrCreate(
                ['partner_id' => $partnerId, 'key' => 'payslip_company_name'],
                ['value' => $request->company_name]
            );
        }
        if ($request->filled('company_address')) {
            PartnerSetting::updateOrCreate(
                ['partner_id' => $partnerId, 'key' => 'payslip_company_address'],
                ['value' => $request->company_address]
            );
        }
        if ($request->filled('authorized_signatory')) {
            PartnerSetting::updateOrCreate(
                ['partner_id' => $partnerId, 'key' => 'payslip_authorized_signatory'],
                ['value' => $request->authorized_signatory]
            );
        }
        if ($request->has('terms_conditions')) {
            PartnerSetting::updateOrCreate(
                ['partner_id' => $partnerId, 'key' => 'payslip_terms_conditions'],
                ['value' => $request->terms_conditions]
            );
        }

        if ($request->hasFile('payslip_logo')) {
            $logoPath = $request->file('payslip_logo')->store('payslips/logos', 'public');
            PartnerSetting::updateOrCreate(
                ['partner_id' => $partnerId, 'key' => 'payslip_logo'],
                ['value' => $logoPath]
            );
        }

        if ($request->hasFile('payslip_signature')) {
            $sigPath = $request->file('payslip_signature')->store('payslips/signatures', 'public');
            PartnerSetting::updateOrCreate(
                ['partner_id' => $partnerId, 'key' => 'payslip_signature'],
                ['value' => $sigPath]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Payslip configuration saved successfully.',
            'data' => $this->payslipConfigArray($partnerId)
        ]);
    }
}








