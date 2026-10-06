<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\DailyWorkReport;
use App\Models\DailyWorkItem;
use App\Models\EmployeeTask;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class DailyWorkReportController extends Controller
{
    /**
     * Get Daily Work Report History
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        $query = DailyWorkReport::where('employee_id', $user->id)
                                ->withCount('items');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('report_date', $request->date);
        }

        \Log::info('Daily Work Report API hit', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'date' => $request->date,
            'query_sql' => $query->toSql(),
            'query_bindings' => $query->getBindings()
        ]);

        $reports = $query->orderBy('report_date', 'desc')->paginate(10);

        $reports->getCollection()->transform(function ($report) {
            $completedItems = $report->items()->where('status', 'Completed')->count();
            $totalItems = $report->items_count;
            $completion = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;

            return [
                'id' => $report->id,
                'report_date' => $report->report_date->format('Y-m-d'),
                'status' => $report->status,
                'summary' => $report->summary,
                'items_count' => $totalItems,
                'completion_percentage' => $completion,
                'created_at' => $report->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $reports,
        ]);
    }

    /**
     * Get specific report details with items
     */
    public function show($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        
        $report = DailyWorkReport::where('employee_id', $user->id)
                                 ->with('items')
                                 ->find($id);

        if (!$report) {
            return response()->json([
                'status' => 'error',
                'message' => 'Report not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $report,
        ]);
    }

    /**
     * Get pending tasks to link to daily report
     */
    public function pendingTasks(): JsonResponse
    {
        $user = auth('hrms_api')->user();

        $tasks = EmployeeTask::where('employee_id', $user->id)
                             ->whereIn('status', ['pending', 'in_progress'])
                             ->get();

        return response()->json([
            'status' => 'success',
            'data' => $tasks,
        ]);
    }

    /**
     * Store or Update Daily Work Report & Items
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        $validator = Validator::make($request->all(), [
            'report_date' => 'required|date',
            'summary' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string|max:255',
            'items.*.details' => 'nullable|string',
            'items.*.category' => 'required|string',
            'items.*.status' => 'required|string',
            'items.*.priority' => 'required|string',
            'items.*.time_spent_hours' => 'required|integer|min:0',
            'items.*.time_spent_minutes' => 'required|integer|min:0|max:59',
            'items.*.task_id' => 'nullable|exists:employee_tasks,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $reportDate = Carbon::parse($request->report_date)->format('Y-m-d');
        
        // Find existing draft or create new
        $report = DailyWorkReport::firstOrCreate(
            [
                'employee_id' => $user->id,
                'report_date' => $reportDate,
            ],
            [
                'partner_id' => $user->parent_id ?? $user->id,
                'manager_id' => $user->reporting_to,
                'status' => 'draft',
                'summary' => $request->summary ?? '',
            ]
        );

        // Update summary if provided
        if ($request->has('summary')) {
            $report->update(['summary' => $request->summary]);
        }

        // Add items
        foreach ($request->items as $itemData) {
            DailyWorkItem::create([
                'daily_work_report_id' => $report->id,
                'task_id' => $itemData['task_id'] ?? null,
                'title' => $itemData['title'],
                'details' => $itemData['details'] ?? null,
                'category' => $itemData['category'],
                'status' => $itemData['status'],
                'priority' => $itemData['priority'],
                'time_spent_hours' => $itemData['time_spent_hours'],
                'time_spent_minutes' => $itemData['time_spent_minutes'],
            ]);
        }

        // Automatically submit the report after adding items via API
        $report->update(['status' => 'submitted']);

        return response()->json([
            'status' => 'success',
            'message' => 'Report submitted successfully.',
            'data' => $report->load('items'),
        ]);
    }
}
