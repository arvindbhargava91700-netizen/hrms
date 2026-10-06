<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\EmployeeExit;

class ExitController extends Controller
{
    /**
     * Get Resignation & Exit Records
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $query = EmployeeExit::where('partner_id', $partnerId)->with(['employee', 'creator']);

        if ($user->role === 'employee' && !$user->canAccess('resignationexit_viewAny')) {
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

        if ($request->filled('clearance_status')) {
            $query->where('clearance_status', $request->clearance_status);
        }

        if ($request->filled('fnf_status')) {
            $query->where('fnf_status', $request->fnf_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('exit_reason', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        $exits = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status'  => 'success',
            'message' => $exits->isEmpty() ? 'No exit records found.' : 'Exit records fetched successfully.',
            'data'    => $exits
        ]);
    }

    /**
     * Show single Exit record
     */
    public function show($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $exit = EmployeeExit::where('partner_id', $partnerId)->with(['employee', 'creator'])->findOrFail($id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Exit record details fetched successfully.',
            'data'    => $exit
        ]);
    }

    /**
     * Store new Exit record
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $request->validate([
            'employee_id'          => 'required|exists:users,id',
            'resignation_date'     => 'required|date',
            'notice_period_days'   => 'required|integer|min:0',
            'last_working_date'    => 'required|date|after_or_equal:resignation_date',
            'exit_reason'          => 'required|string',
            'clearance_status'     => 'required|in:pending,in_progress,cleared,held',
            'fnf_status'           => 'required|in:pending,calculated,paid,disputed',
            'fnf_amount'           => 'required|numeric|min:0',
            'fnf_settlement_date'  => 'nullable|date',
            'status'               => 'required|in:pending,in_notice_period,completed,cancelled',
            'exit_interview_notes' => 'nullable|string',
            'remarks'              => 'nullable|string',
        ]);

        $exit = EmployeeExit::create([
            'partner_id'           => $partnerId,
            'created_by'           => $user->id,
            'employee_id'          => $request->employee_id,
            'resignation_date'     => $request->resignation_date,
            'notice_period_days'   => $request->notice_period_days,
            'last_working_date'    => $request->last_working_date,
            'exit_reason'          => $request->exit_reason,
            'clearance_status'     => $request->clearance_status,
            'fnf_status'           => $request->fnf_status,
            'fnf_amount'           => $request->fnf_amount,
            'fnf_settlement_date'  => $request->fnf_settlement_date ?: null,
            'status'               => $request->status,
            'exit_interview_notes' => $request->exit_interview_notes,
            'remarks'              => $request->remarks,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Exit record created successfully.',
            'data'    => $exit
        ], 201);
    }

    /**
     * Update Exit record
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $exit = EmployeeExit::where('partner_id', $partnerId)->findOrFail($id);

        $request->validate([
            'employee_id'          => 'sometimes|required|exists:users,id',
            'resignation_date'     => 'sometimes|required|date',
            'notice_period_days'   => 'sometimes|required|integer|min:0',
            'last_working_date'    => 'sometimes|required|date|after_or_equal:resignation_date',
            'exit_reason'          => 'sometimes|required|string',
            'clearance_status'     => 'sometimes|required|in:pending,in_progress,cleared,held',
            'fnf_status'           => 'sometimes|required|in:pending,calculated,paid,disputed',
            'fnf_amount'           => 'sometimes|required|numeric|min:0',
            'fnf_settlement_date'  => 'nullable|date',
            'status'               => 'sometimes|required|in:pending,in_notice_period,completed,cancelled',
            'exit_interview_notes' => 'nullable|string',
            'remarks'              => 'nullable|string',
        ]);

        $exit->update($request->only([
            'employee_id', 'resignation_date', 'notice_period_days', 'last_working_date',
            'exit_reason', 'clearance_status', 'fnf_status', 'fnf_amount',
            'fnf_settlement_date', 'status', 'exit_interview_notes', 'remarks'
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Exit record updated successfully.',
            'data'    => $exit
        ]);
    }

    /**
     * Delete Exit record
     */
    public function destroy($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $exit = EmployeeExit::where('partner_id', $partnerId)->findOrFail($id);
        $exit->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Exit record deleted successfully.'
        ]);
    }
}
