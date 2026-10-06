<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\EmployeeProbation;

class ProbationController extends Controller
{
    /**
     * Get Probation Records
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $query = EmployeeProbation::where('partner_id', $partnerId)->with(['employee', 'creator']);

        if ($user->role === 'employee' && !$user->canAccess('probation_viewAny')) {
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
                $q->where('asset_allocation', 'like', "%{$search}%")
                  ->orWhere('evaluation_notes', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        $probations = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status'  => 'success',
            'message' => $probations->isEmpty() ? 'No probation records found.' : 'Probation records fetched successfully.',
            'data'    => $probations
        ]);
    }

    /**
     * Show single Probation record
     */
    public function show($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $probation = EmployeeProbation::where('partner_id', $partnerId)->with(['employee', 'creator'])->findOrFail($id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Probation record details fetched successfully.',
            'data'    => $probation
        ]);
    }

    /**
     * Store new Probation record
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $request->validate([
            'employee_id'           => 'required|exists:users,id',
            'start_date'            => 'required|date',
            'confirmation_due_date' => 'required|date|after_or_equal:start_date',
            'extended_due_date'     => 'nullable|date|after_or_equal:confirmation_due_date',
            'is_extended'           => 'boolean',
            'extension_reason'      => 'nullable|string',
            'asset_allocation'      => 'nullable|string',
            'status'                => 'required|in:under_probation,confirmed,extended,failed',
            'confirmation_date'     => 'nullable|date',
            'evaluation_notes'      => 'nullable|string',
        ]);

        $probation = EmployeeProbation::create([
            'partner_id'           => $partnerId,
            'created_by'           => $user->id,
            'employee_id'          => $request->employee_id,
            'start_date'           => $request->start_date,
            'confirmation_due_date'=> $request->confirmation_due_date,
            'extended_due_date'    => $request->extended_due_date ?: null,
            'is_extended'          => $request->is_extended ?? false,
            'extension_reason'     => $request->extension_reason,
            'asset_allocation'     => $request->asset_allocation,
            'status'               => $request->status,
            'confirmation_date'    => $request->confirmation_date ?: null,
            'evaluation_notes'     => $request->evaluation_notes,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Probation record created successfully.',
            'data'    => $probation
        ], 201);
    }

    /**
     * Update Probation record
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $probation = EmployeeProbation::where('partner_id', $partnerId)->findOrFail($id);

        $request->validate([
            'employee_id'           => 'sometimes|required|exists:users,id',
            'start_date'            => 'sometimes|required|date',
            'confirmation_due_date' => 'sometimes|required|date|after_or_equal:start_date',
            'extended_due_date'     => 'nullable|date|after_or_equal:confirmation_due_date',
            'is_extended'           => 'boolean',
            'extension_reason'      => 'nullable|string',
            'asset_allocation'      => 'nullable|string',
            'status'                => 'sometimes|required|in:under_probation,confirmed,extended,failed',
            'confirmation_date'     => 'nullable|date',
            'evaluation_notes'      => 'nullable|string',
        ]);

        $probation->update($request->only([
            'employee_id', 'start_date', 'confirmation_due_date', 'extended_due_date',
            'is_extended', 'extension_reason', 'asset_allocation', 'status',
            'confirmation_date', 'evaluation_notes'
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Probation record updated successfully.',
            'data'    => $probation
        ]);
    }

    /**
     * Delete Probation record
     */
    public function destroy($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $probation = EmployeeProbation::where('partner_id', $partnerId)->findOrFail($id);
        $probation->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Probation record deleted successfully.'
        ]);
    }
}
