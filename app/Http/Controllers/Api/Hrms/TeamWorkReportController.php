<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\DailyWorkReport;

class TeamWorkReportController extends Controller
{
    private function getPartnerId()
    {
        $user = auth('hrms_api')->user();
        return $user->parent_id ?? $user->id;
    }

    /**
     * Get Team Work Reports
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        $query = DailyWorkReport::with(['employee', 'items'])
            ->where('partner_id', $this->getPartnerId());

        if ($user->role === 'employee') {
            $query->whereHas('employee', function ($q) use ($user) {
                $q->where('reporting_to', $user->id);
            })->whereIn('status', ['submitted', 'manager_approved', 'partner_approved', 'rejected']);
        } else {
            $query->whereIn('status', ['submitted', 'manager_approved', 'partner_approved', 'rejected']);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('employee_code', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('report_date', $request->date);
        }

        $reports = $query->orderBy('report_date', 'desc')->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $reports,
        ]);
    }

    /**
     * Approve Report
     */
    public function approve($id): JsonResponse
    {
        $user = auth('hrms_api')->user();

        // Check if report exists and belongs to the partner's organization
        $report = DailyWorkReport::where('partner_id', $this->getPartnerId())->find($id);

        if (!$report) {
            return response()->json([
                'status' => 'error',
                'message' => 'Report not found',
            ], 404);
        }

        // Security check for manager access
        if ($user->role === 'employee' && $report->manager_id !== $user->id) {
             return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. This employee does not report to you.',
            ], 403);
        }

        if ($user->role === 'employee') {
            $report->update([
                'status' => 'manager_approved',
                'manager_id' => $user->id,
            ]);
        } else {
            $report->update([
                'status' => 'partner_approved',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Report approved successfully.',
        ]);
    }

    /**
     * Reject Report
     */
    public function reject($id): JsonResponse
    {
        $user = auth('hrms_api')->user();

        $report = DailyWorkReport::where('partner_id', $this->getPartnerId())->find($id);

        if (!$report) {
            return response()->json([
                'status' => 'error',
                'message' => 'Report not found',
            ], 404);
        }

        if ($user->role === 'employee' && $report->manager_id !== $user->id) {
             return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. This employee does not report to you.',
            ], 403);
        }

        $report->update([
            'status' => 'rejected',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Report rejected successfully.',
        ]);
    }
}
