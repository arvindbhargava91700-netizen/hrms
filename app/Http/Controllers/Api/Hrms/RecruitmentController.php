<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Recruitment;

class RecruitmentController extends Controller
{
    /**
     * Get Recruitment Records
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $query = Recruitment::where('partner_id', $partnerId)->with(['department', 'creator', 'employee']);

        if ($user->role === 'employee' && !$user->canAccess('recruitment_viewAny')) {
            $allowedIds = $user->getTeamIds();
            $query->where(function ($q) use ($allowedIds, $user) {
                $q->whereIn('created_by', $allowedIds)
                  ->orWhereIn('employee_id', $allowedIds)
                  ->orWhere('created_by', $user->id);
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('interview_stage')) {
            $query->where('interview_stage', $request->interview_stage);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('candidate_name', 'like', "%{$search}%")
                  ->orWhere('candidate_email', 'like', "%{$search}%")
                  ->orWhere('candidate_phone', 'like', "%{$search}%")
                  ->orWhere('job_title', 'like', "%{$search}%");
            });
        }

        $recruitments = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status'  => 'success',
            'message' => $recruitments->isEmpty() ? 'No recruitment records found.' : 'Recruitment records fetched successfully.',
            'data'    => $recruitments
        ]);
    }

    /**
     * Show single Recruitment record
     */
    public function show($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $recruitment = Recruitment::where('partner_id', $partnerId)->with(['department', 'creator', 'employee'])->findOrFail($id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Recruitment record details fetched successfully.',
            'data'    => $recruitment
        ]);
    }

    /**
     * Store new Recruitment record
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $request->validate([
            'job_title'       => 'required|string|max:255',
            'vacancies_count' => 'required|integer|min:1',
            'candidate_name'  => 'required|string|max:255',
            'candidate_email' => 'nullable|email|max:255',
            'candidate_phone' => 'nullable|string|max:50',
            'employee_id'     => 'nullable|exists:users,id',
            'department_id'   => 'nullable|exists:departments,id',
            'interview_stage' => 'required|in:applied,screening,technical_round,hr_round,final_round',
            'status'          => 'required|in:under_review,selected,rejected,on_hold',
            'offer_status'    => 'required|in:pending,offered,offer_accepted,offer_declined',
            'joining_status'  => 'required|in:pending,joined,not_joined',
            'cost_per_hire'   => 'required|numeric|min:0',
            'interview_date'  => 'nullable|date',
            'remarks'         => 'nullable|string',
        ]);

        $recruitment = Recruitment::create([
            'partner_id'      => $partnerId,
            'created_by'      => $user->id,
            'job_title'       => $request->job_title,
            'vacancies_count' => $request->vacancies_count,
            'candidate_name'  => $request->candidate_name,
            'candidate_email' => $request->candidate_email,
            'candidate_phone' => $request->candidate_phone,
            'employee_id'     => $request->employee_id ?: null,
            'department_id'   => $request->department_id ?: null,
            'interview_stage' => $request->interview_stage,
            'status'          => $request->status,
            'offer_status'    => $request->offer_status,
            'joining_status'  => $request->joining_status,
            'cost_per_hire'   => $request->cost_per_hire,
            'interview_date'  => $request->interview_date ?: null,
            'remarks'         => $request->remarks,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Recruitment record created successfully.',
            'data'    => $recruitment
        ], 201);
    }

    /**
     * Update Recruitment record
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $recruitment = Recruitment::where('partner_id', $partnerId)->findOrFail($id);

        $request->validate([
            'job_title'       => 'sometimes|required|string|max:255',
            'vacancies_count' => 'sometimes|required|integer|min:1',
            'candidate_name'  => 'sometimes|required|string|max:255',
            'candidate_email' => 'nullable|email|max:255',
            'candidate_phone' => 'nullable|string|max:50',
            'employee_id'     => 'nullable|exists:users,id',
            'department_id'   => 'nullable|exists:departments,id',
            'interview_stage' => 'sometimes|required|in:applied,screening,technical_round,hr_round,final_round',
            'status'          => 'sometimes|required|in:under_review,selected,rejected,on_hold',
            'offer_status'    => 'sometimes|required|in:pending,offered,offer_accepted,offer_declined',
            'joining_status'  => 'sometimes|required|in:pending,joined,not_joined',
            'cost_per_hire'   => 'sometimes|required|numeric|min:0',
            'interview_date'  => 'nullable|date',
            'remarks'         => 'nullable|string',
        ]);

        $recruitment->update($request->only([
            'job_title', 'vacancies_count', 'candidate_name', 'candidate_email',
            'candidate_phone', 'employee_id', 'department_id', 'interview_stage',
            'status', 'offer_status', 'joining_status', 'cost_per_hire',
            'interview_date', 'remarks'
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Recruitment record updated successfully.',
            'data'    => $recruitment
        ]);
    }

    /**
     * Delete Recruitment record
     */
    public function destroy($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->role === 'employee' ? $user->parent_id : $user->id;

        $recruitment = Recruitment::where('partner_id', $partnerId)->findOrFail($id);
        $recruitment->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Recruitment record deleted successfully.'
        ]);
    }
}
