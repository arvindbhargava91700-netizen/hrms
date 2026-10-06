<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\EmployeeLeave;
use App\Models\LeaveCategory;
use App\Models\User;
use Carbon\Carbon;

class LeaveController extends Controller
{
    /**
     * Get leave categories with employee's current balance
     */
    public function categories(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $categories = LeaveCategory::where('partner_id', $partnerId)->where('status', true)->get();

        $currentYear = date('Y');
        $userLeaves = EmployeeLeave::where('employee_id', $user->id)
            ->whereYear('start_date', $currentYear)
            ->get();

        $data = $categories->map(function ($cat) use ($userLeaves) {
            $catLeaves = $userLeaves->filter(function ($leave) use ($cat) {
                return $leave->leave_category_id == $cat->id || strtolower($leave->type) == strtolower($cat->name);
            });

            $usedDays = 0;
            foreach ($catLeaves->where('status', 'approved') as $leave) {
                $usedDays += Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
            }

            $pendingDays = 0;
            foreach ($catLeaves->where('status', 'pending') as $leave) {
                $pendingDays += Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
            }

            $remainingDays = max(0, $cat->days - $usedDays - $pendingDays);

            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'total_days' => $cat->days,
                'used_days' => $usedDays,
                'pending_days' => $pendingDays,
                'remaining_days' => $remainingDays,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Leave categories fetched successfully.',
            'data' => $data
        ]);
    }

    /**
     * Get own leaves
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $leaves = EmployeeLeave::where('employee_id', $user->id)
            ->with('leaveCategory')
            ->orderBy('created_at', 'desc')
            ->get();

        $message = $leaves->isEmpty() ? 'No leaves found.' : 'Leaves fetched successfully.';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $leaves
        ]);
    }

    /**
     * Apply for leave
     */
    public function apply(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'leave_category_id' => 'nullable|exists:leave_categories,id',
            'type' => 'nullable|string',
            'reason' => 'required|string',
        ]);

        $category = null;
        if ($request->filled('leave_category_id')) {
            $category = LeaveCategory::where('partner_id', $partnerId)->find($request->leave_category_id);
        } elseif ($request->filled('type')) {
            $category = LeaveCategory::where('partner_id', $partnerId)->where('name', $request->type)->first();
        }

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid leave category selected.'
            ], 422);
        }

        $requestedDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;

        // Calculate used + pending days
        $usedDays = EmployeeLeave::where('employee_id', $user->id)
            ->where(function($q) use ($category) {
                $q->where('leave_category_id', $category->id)
                  ->orWhere('type', $category->name);
            })
            ->whereIn('status', ['approved', 'pending'])
            ->whereYear('start_date', date('Y'))
            ->get()
            ->sum(function ($leave) {
                return Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
            });

        $remainingBalance = max(0, $category->days - $usedDays);

        if ($requestedDays > $remainingBalance) {
            return response()->json([
                'status' => 'error',
                'message' => "Insufficient leave balance for {$category->name}. Available: {$remainingBalance} day(s), Requested: {$requestedDays} day(s)."
            ], 422);
        }

        $leave = EmployeeLeave::create([
            'employee_id' => $user->id,
            'leave_category_id' => $category->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'type' => $category->name,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Leave applied successfully.',
            'data' => $leave->load('leaveCategory')
        ], 201);
    }

    /**
     * Get team leaves (for manager/partner)
     */
    public function teamLeaves(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $teamIds = $user->getTeamIds();

        $query = EmployeeLeave::whereIn('employee_id', $teamIds)->with(['employee', 'leaveCategory']);
        
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaves = $query->orderBy('created_at', 'desc')->paginate(15);

        $message = $leaves->isEmpty() ? 'No team leaves found.' : 'Team leaves fetched successfully.';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $leaves
        ]);
    }

    /**
     * Approve or reject a leave
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $teamIds = $user->getTeamIds();

        $leave = EmployeeLeave::findOrFail($id);

        if (!in_array($leave->employee_id, $teamIds)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized or employee not in team.'], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $leave->update([
            'status' => $request->status,
        ]);

        if ($request->status === 'approved') {
            $startDate = \Carbon\Carbon::parse($leave->start_date);
            $endDate = \Carbon\Carbon::parse($leave->end_date);
            while ($startDate->lte($endDate)) {
                \App\Models\EmployeeAttendance::updateOrCreate(
                    [
                        'employee_id' => $leave->employee_id,
                        'date' => $startDate->toDateString(),
                    ],
                    [
                        'status' => 'leave',
                        'working_minutes' => 0,
                    ]
                );
                $startDate->addDay();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Leave status updated to {$request->status}.",
            'data' => $leave
        ]);
    }
}
