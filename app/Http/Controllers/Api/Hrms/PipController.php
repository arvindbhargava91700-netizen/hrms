<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\EmployeePip;

class PipController extends Controller
{
    /**
     * Get PIP Records
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $query = EmployeePip::where('partner_id', $partnerId)->with(['employee', 'creator']);

        if ($user->role === 'employee' && !$user->canAccess('pip_viewAny')) {
            $allowedIds = $user->getTeamIds();
            $query->where(function ($q) use ($allowedIds, $user) {
                $q->whereIn('employee_id', $allowedIds)
                  ->orWhereIn('created_by', $allowedIds)
                  ->orWhere('employee_id', $user->id);
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('improvement_targets', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        $pips = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status'  => 'success',
            'message' => $pips->isEmpty() ? 'No PIP records found.' : 'PIP records fetched successfully.',
            'data'    => $pips
        ]);
    }

    /**
     * Show single PIP record
     */
    public function show($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $pip = EmployeePip::where('partner_id', $partnerId)->with(['employee', 'creator'])->findOrFail($id);

        return response()->json([
            'status'  => 'success',
            'message' => 'PIP record details fetched successfully.',
            'data'    => $pip
        ]);
    }

    /**
     * Store new PIP record
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $request->validate([
            'employee_id'         => 'required|exists:users,id',
            'reason'              => 'required|string',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'improvement_targets' => 'nullable|string',
            'review_result'       => 'nullable|string',
            'status'              => 'required|in:active,under_review,completed_passed,completed_failed,cancelled',
            'remarks'             => 'nullable|string',
        ]);

        $pip = EmployeePip::create([
            'partner_id'          => $partnerId,
            'created_by'          => $user->id,
            'employee_id'         => $request->employee_id,
            'reason'              => $request->reason,
            'start_date'          => $request->start_date,
            'end_date'            => $request->end_date,
            'improvement_targets' => $request->improvement_targets,
            'review_result'       => $request->review_result,
            'status'              => $request->status,
            'remarks'             => $request->remarks,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'PIP record created successfully.',
            'data'    => $pip
        ], 201);
    }

    /**
     * Update PIP record
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $pip = EmployeePip::where('partner_id', $partnerId)->findOrFail($id);

        $request->validate([
            'employee_id'         => 'sometimes|required|exists:users,id',
            'reason'              => 'sometimes|required|string',
            'start_date'          => 'sometimes|required|date',
            'end_date'            => 'sometimes|required|date|after_or_equal:start_date',
            'improvement_targets' => 'nullable|string',
            'review_result'       => 'nullable|string',
            'status'              => 'sometimes|required|in:active,under_review,completed_passed,completed_failed,cancelled',
            'remarks'             => 'nullable|string',
        ]);

        $pip->update($request->only([
            'employee_id', 'reason', 'start_date', 'end_date',
            'improvement_targets', 'review_result', 'status', 'remarks'
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'PIP record updated successfully.',
            'data'    => $pip
        ]);
    }

    /**
     * Delete PIP record
     */
    public function destroy($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $pip = EmployeePip::where('partner_id', $partnerId)->findOrFail($id);
        $pip->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'PIP record deleted successfully.'
        ]);
    }
}
