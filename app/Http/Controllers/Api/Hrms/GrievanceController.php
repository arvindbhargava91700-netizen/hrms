<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\EmployeeGrievance;
use Illuminate\Support\Facades\Storage;

class GrievanceController extends Controller
{
    /**
     * Get Grievance & Disciplinary Records
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $query = EmployeeGrievance::where('partner_id', $partnerId)->with(['employee', 'creator']);

        if ($user->role === 'employee' && !$user->canAccess('grievance_viewAny')) {
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

        if ($request->filled('record_type')) {
            $query->where('record_type', $request->record_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        $grievances = $query->orderBy('created_at', 'desc')->paginate(15);

        // Map file URL
        $grievances->getCollection()->transform(function ($item) {
            $item->file_url = $item->file_path ? asset('storage/' . $item->file_path) : null;
            return $item;
        });

        return response()->json([
            'status'  => 'success',
            'message' => $grievances->isEmpty() ? 'No grievance records found.' : 'Grievance records fetched successfully.',
            'data'    => $grievances
        ]);
    }

    /**
     * Show single Grievance record detail
     */
    public function show($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $grievance = EmployeeGrievance::where('partner_id', $partnerId)->with(['employee', 'creator'])->findOrFail($id);
        $grievance->file_url = $grievance->file_path ? asset('storage/' . $grievance->file_path) : null;

        return response()->json([
            'status'  => 'success',
            'message' => 'Grievance record details fetched successfully.',
            'data'    => $grievance
        ]);
    }

    /**
     * Store new Grievance record
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $request->validate([
            'employee_id'      => 'required|exists:users,id',
            'record_type'      => 'required|in:complaint,warning,show_cause,disciplinary_action',
            'title'            => 'required|string|max:255',
            'description'      => 'required|string',
            'incident_date'    => 'nullable|date',
            'action_taken'     => 'nullable|string',
            'resolution_notes' => 'nullable|string',
            'status'           => 'required|in:open,under_investigation,resolved,closed',
            'file'             => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('grievance_documents', 'public');
        }

        $grievance = EmployeeGrievance::create([
            'partner_id'       => $partnerId,
            'created_by'       => $user->id,
            'employee_id'      => $request->employee_id,
            'record_type'      => $request->record_type,
            'title'            => $request->title,
            'description'      => $request->description,
            'incident_date'    => $request->incident_date ?: null,
            'action_taken'     => $request->action_taken,
            'resolution_notes' => $request->resolution_notes,
            'status'           => $request->status,
            'file_path'        => $filePath,
        ]);

        $grievance->file_url = $grievance->file_path ? asset('storage/' . $grievance->file_path) : null;

        return response()->json([
            'status'  => 'success',
            'message' => 'Grievance record created successfully.',
            'data'    => $grievance
        ], 201);
    }

    /**
     * Update Grievance record
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $grievance = EmployeeGrievance::where('partner_id', $partnerId)->findOrFail($id);

        $request->validate([
            'employee_id'      => 'sometimes|required|exists:users,id',
            'record_type'      => 'sometimes|required|in:complaint,warning,show_cause,disciplinary_action',
            'title'            => 'sometimes|required|string|max:255',
            'description'      => 'sometimes|required|string',
            'incident_date'    => 'nullable|date',
            'action_taken'     => 'nullable|string',
            'resolution_notes' => 'nullable|string',
            'status'           => 'sometimes|required|in:open,under_investigation,resolved,closed',
            'file'             => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        if ($request->hasFile('file')) {
            if ($grievance->file_path && Storage::disk('public')->exists($grievance->file_path)) {
                Storage::disk('public')->delete($grievance->file_path);
            }
            $grievance->file_path = $request->file('file')->store('grievance_documents', 'public');
        }

        $grievance->update($request->only([
            'employee_id', 'record_type', 'title', 'description',
            'incident_date', 'action_taken', 'resolution_notes', 'status'
        ]));

        $grievance->file_url = $grievance->file_path ? asset('storage/' . $grievance->file_path) : null;

        return response()->json([
            'status'  => 'success',
            'message' => 'Grievance record updated successfully.',
            'data'    => $grievance
        ]);
    }

    /**
     * Delete Grievance record
     */
    public function destroy($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $grievance = EmployeeGrievance::where('partner_id', $partnerId)->findOrFail($id);

        if ($grievance->file_path && Storage::disk('public')->exists($grievance->file_path)) {
            Storage::disk('public')->delete($grievance->file_path);
        }

        $grievance->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Grievance record deleted successfully.'
        ]);
    }
}
