<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Department;
use App\Models\CommissionPayout;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeave;
use App\Models\EmployeePayroll;
use App\Models\EmployeePip;
use App\Models\EmployeeProbation;
use App\Models\EmployeeTask;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\LeadOrder;
use App\Models\Notice;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Recruitment;
use App\Models\EmployeeSalaryStructure;
use App\Models\Training;
use App\Models\EmployeeTraining;
use App\Models\Asset;
use App\Models\EmployeeDocument;
use App\Models\EmployeeExit;
use App\Models\EmployeeGrievance;
use App\Models\HrmsBranch;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Carbon\Carbon;

class HrmsReportController extends Controller
{
    //============================================ Report Option Lists ===========================//

    /**
     * Active branches for the partner (report filter options).
     */
    public function reportBranches(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $branches = HrmsBranch::where('partner_id', $partnerId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'status' => 'success',
            'message' => 'Report branches fetched successfully.',
            'data' => $branches,
        ]);
    }

    /**
     * Teams / reporting managers available as report filter options.
     * Mirrors the report pages' team dropdown logic; optional branch_id narrows the options.
     */
    public function reportTeams(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $reportingQuery = User::where('parent_id', $partnerId)
            ->where('role', 'employee')
            ->whereNotNull('reporting_to');

        if ($request->filled('branch_id')) {
            $reportingQuery->where('branch_id', $request->branch_id);
        }

        $leadIds = $reportingQuery->pluck('reporting_to')->unique();

        if ($leadIds->isNotEmpty()) {
            $teams = User::whereIn('id', $leadIds)->orderBy('name')->get(['id', 'name', 'employee_code']);
        } else {
            $teams = User::where('parent_id', $partnerId)
                ->where('role', 'employee')
                ->whereHas('reportees')
                ->orderBy('name')
                ->get(['id', 'name', 'employee_code']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Report teams fetched successfully.',
            'data' => $teams,
        ]);
    }

    /**
     * Staff report (paginated, filterable, with summary).
     * Mirrors the workspace HRMS Staff Report screen.
     */

    //============================================ staff Report ===========================//
    public function staffReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $teamIds = $this->getTeamEmployeeIds('staff_report_viewAny');

        $query = User::where('parent_id', $partnerId)
            ->where('role', 'employee')
            ->whereIn('id', $teamIds);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->where('reporting_to', $teamVal);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('role_name')) {
            $query->whereHas('roles', fn ($rq) => $this->roleNameWhere($rq, $request->role_name, $partnerId));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('employment_status')) {
            $query->where('employment_status', $request->employment_status);
        }

        $perPage = (int) $request->input('per_page', 20);
        $staff = $query->with(['department', 'roles', 'branch', 'shift'])
            ->latest()
            ->paginate($perPage);

        // Summary across the same filtered set (ignoring pagination)
        $summaryBase = (clone $query)->get();
        $summary = [
            'total_staff'        => $summaryBase->count(),
            'active_staff'       => $summaryBase->where('status', 'active')->count(),
            'total_basic_salary' => round($summaryBase->sum('basic_salary'), 2),
            'total_wallet'       => round($summaryBase->sum('wallet_balance'), 2),
        ];

        $staff->through(function ($row) use ($partnerId) {
            $roleName = $row->roles->first()
                ? str_replace([$partnerId . '_', '_' . $partnerId], '', $row->roles->first()->name)
                : '-';

            return [
                'id'             => $row->id,
                'employee_code'  => $row->employee_code,
                'name'           => $row->name,
                'email'          => $row->email,
                'mobile'         => $row->mobile,
                'role'           => $roleName,
                'department'     => optional($row->department)->name,
                'branch'         => optional($row->branch)->name,
                'shift'          => optional($row->shift)->name,
                'basic_salary'   => (float) $row->basic_salary,
                'wallet_balance' => (float) $row->wallet_balance,
                'employment_status' => $row->employment_status,
                'status'         => $row->status,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Staff report fetched successfully.',
            'data'    => $staff,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the staff report as a CSV stream.
     */
    public function staffReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $teamIds = $this->getTeamEmployeeIds('staff_report_viewAny');

        $query = User::where('parent_id', $partnerId)
            ->where('role', 'employee')
            ->whereIn('id', $teamIds);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->where('reporting_to', $teamVal);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('role_name')) {
            $query->whereHas('roles', fn ($rq) => $this->roleNameWhere($rq, $request->role_name, $partnerId));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('employment_status')) {
            $query->where('employment_status', $request->employment_status);
        }

        $data = $query->with(['department', 'roles'])->latest()->get();

        $columns = ['Emp ID', 'Name', 'Email', 'Mobile', 'Role', 'Department', 'Basic Salary', 'Wallet Balance', 'Status'];

        $callback = function () use ($data, $columns, $partnerId) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                $roleName = $row->roles->first()
                    ? str_replace([$partnerId . '_', '_' . $partnerId], '', $row->roles->first()->name)
                    : '-';

                fputcsv($file, [
                    $row->employee_code ?? substr($row->id, 0, 8),
                    $row->name,
                    $row->email,
                    $row->mobile,
                    $roleName,
                    optional($row->department)->name,
                    $row->basic_salary,
                    $row->wallet_balance,
                    $row->status,
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="staff_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

//============================================ leave Report ===========================//
    public function leaveReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $teamIds = $this->getTeamEmployeeIds('leave_report_viewAny');

        $query = EmployeeLeave::whereHas('employee', function ($q) use ($partnerId, $request) {
            $q->where('parent_id', $partnerId);

            if ($request->filled('branch_id')) {
                $q->where('branch_id', $request->branch_id);
            }

            if ($request->filled('team_id') || $request->filled('reporting_to')) {
                $teamVal = $request->input('team_id', $request->input('reporting_to'));
                $q->where('reporting_to', $teamVal);
            }
        })->whereIn('employee_id', $teamIds);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $startDate = $request->filled('start_date')
            ? $request->start_date
            : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date')
            ? $request->end_date
            : Carbon::now()->endOfMonth()->format('Y-m-d');

        $query->whereBetween('created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59',
        ]);

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->with('employee')->latest()->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_leaves' => $summaryBase->count(),
            'approved'     => $summaryBase->where('status', 'approved')->count(),
            'rejected'     => $summaryBase->where('status', 'rejected')->count(),
            'pending'      => $summaryBase->where('status', 'pending')->count(),
        ];

        $rows->through(function ($row) {
            $duration = ($row->start_date && $row->end_date)
                ? Carbon::parse($row->start_date)->diffInDays(Carbon::parse($row->end_date)) + 1
                : 0;

            return [
                'id'              => $row->id,
                'employee_id'     => $row->employee_id,
                'employee_code'   => optional($row->employee)->employee_code,
                'employee_name'   => optional($row->employee)->name,
                'leave_type'      => $row->leave_type,
                'leave_type_label' => str_replace('_', ' ', Str::title($row->leave_type)),
                'start_date'      => $row->start_date,
                'end_date'        => $row->end_date,
                'reason'          => $row->reason,
                'duration_days'   => $duration,
                'status'          => $row->status,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Leave report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the leave report as a CSV stream.
     */
    public function leaveReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $teamIds = $this->getTeamEmployeeIds('leave_report_viewAny');

        $query = EmployeeLeave::whereHas('employee', function ($q) use ($partnerId, $request) {
            $q->where('parent_id', $partnerId);

            if ($request->filled('branch_id')) {
                $q->where('branch_id', $request->branch_id);
            }

            if ($request->filled('team_id') || $request->filled('reporting_to')) {
                $teamVal = $request->input('team_id', $request->input('reporting_to'));
                $q->where('reporting_to', $teamVal);
            }
        })->whereIn('employee_id', $teamIds);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $startDate = $request->filled('start_date')
            ? $request->start_date
            : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date')
            ? $request->end_date
            : Carbon::now()->endOfMonth()->format('Y-m-d');

        $query->whereBetween('created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59',
        ]);

        $data = $query->with('employee')->latest()->get();

        $columns = [
            'Emp ID', 'Employee', 'Leave Type', 'Start Date', 'End Date',
            'Reason', 'Duration (Days)', 'Status',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                $duration = ($row->start_date && $row->end_date)
                    ? Carbon::parse($row->start_date)->diffInDays(Carbon::parse($row->end_date)) + 1
                    : 0;

                fputcsv($file, [
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    str_replace('_', ' ', Str::title($row->leave_type)),
                    $row->start_date ? Carbon::parse($row->start_date)->format('Y-m-d') : '',
                    $row->end_date ? Carbon::parse($row->end_date)->format('Y-m-d') : '',
                    $row->reason,
                    $duration,
                    $row->status,
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leave_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

//============================================ Attendence  Report ===========================//
    public function attendanceReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $teamIds = $this->getTeamEmployeeIds('attendance_report_viewAny');

        $query = EmployeeAttendance::whereHas('employee', function ($q) use ($partnerId, $request) {
            $q->where('parent_id', $partnerId);

            if ($request->filled('branch_id')) {
                $q->where('branch_id', $request->branch_id);
            }

            if ($request->filled('team_id') || $request->filled('reporting_to')) {
                $teamVal = $request->input('team_id', $request->input('reporting_to'));
                $q->where('reporting_to', $teamVal);
            }

            if ($request->filled('working_mode')) {
                $q->where('working_mode', $request->working_mode);
            }
        })->whereIn('employee_id', $teamIds);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $startDate = $request->filled('start_date')
            ? $request->start_date
            : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date')
            ? $request->end_date
            : Carbon::now()->endOfMonth()->format('Y-m-d');

        $query->whereBetween('date', [$startDate, $endDate]);

        $perPage = (int) $request->input('per_page', 100);
        $rows = $query->with('employee.shift')->latest('date')->paginate($perPage);

        $defaultRequiredMins = (function () use ($partnerId) {
            $val = \App\Models\PartnerSetting::where('partner_id', $partnerId)
                ->where('key', 'min_present_mins')
                ->value('value');

            return $val !== null ? (int) $val : 480;
        })();

        $summaryBase = (clone $query)->with('employee.shift')->get();

        $totalMinutes = 0;
        $requiredMinutes = 0;
        $overtimeMinutes = 0;

        foreach ($summaryBase as $r) {
            $worked = (int) ($r->working_minutes ?? 0);
            $totalMinutes += $worked;

            $shift = $r->employee->shift ?? null;
            $req = ($shift && $shift->min_present_mins)
                ? (int) $shift->min_present_mins
                : $defaultRequiredMins;

            $requiredMinutes += $req;
            $overtimeMinutes += max(0, $worked - $req);
        }

        $summary = [
            'total_records'   => $summaryBase->count(),
            'present'         => $summaryBase->filter(fn ($r) => str_contains($r->status, 'punch_in'))->count(),
            'late_records'    => $summaryBase->filter(fn ($r) => ($r->late_minutes ?? 0) > 0)->count(),
            'missed_punch'    => $summaryBase->filter(fn ($r) => $r->check_in && !$r->check_out)->count(),
            'total_hours'     => intdiv($totalMinutes, 60) . 'h ' . ($totalMinutes % 60) . 'm',
            'productive_hours' => intdiv($requiredMinutes, 60) . 'h ' . ($requiredMinutes % 60) . 'm',
            'overtime_hours'  => intdiv($overtimeMinutes, 60) . 'h ' . ($overtimeMinutes % 60) . 'm',
        ];

        $rows->through(function ($row) use ($defaultRequiredMins) {
            $worked = (int) ($row->working_minutes ?? 0);
            $shift = $row->employee->shift ?? null;
            $req = ($shift && $shift->min_present_mins)
                ? (int) $shift->min_present_mins
                : $defaultRequiredMins;
            $ov = max(0, $worked - $req);

            return [
                'id'              => $row->id,
                'date'            => $row->date,
                'employee_id'     => $row->employee_id,
                'employee_code'   => optional($row->employee)->employee_code,
                'employee_name'   => optional($row->employee)->name,
                'check_in'        => $row->check_in,
                'check_out'       => $row->check_out,
                'working_minutes' => $worked,
                'late_minutes'    => $row->late_minutes,
                'status'          => $row->status,
                'working_mode'    => $row->working_mode,
                'total_hours'     => intdiv($worked, 60) . 'h ' . ($worked % 60) . 'm',
                'productive_hours' => intdiv($req, 60) . 'h ' . ($req % 60) . 'm',
                'overtime_hours'  => intdiv($ov, 60) . 'h ' . ($ov % 60) . 'm',
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Attendance report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the attendance report as a CSV stream.
     */
    public function attendanceReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $teamIds = $this->getTeamEmployeeIds('attendance_report_viewAny');

        $query = EmployeeAttendance::whereHas('employee', function ($q) use ($partnerId, $request) {
            $q->where('parent_id', $partnerId);

            if ($request->filled('branch_id')) {
                $q->where('branch_id', $request->branch_id);
            }

            if ($request->filled('team_id') || $request->filled('reporting_to')) {
                $teamVal = $request->input('team_id', $request->input('reporting_to'));
                $q->where('reporting_to', $teamVal);
            }

            if ($request->filled('working_mode')) {
                $q->where('working_mode', $request->working_mode);
            }
        })->whereIn('employee_id', $teamIds);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $startDate = $request->filled('start_date')
            ? $request->start_date
            : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date')
            ? $request->end_date
            : Carbon::now()->endOfMonth()->format('Y-m-d');

        $query->whereBetween('date', [$startDate, $endDate]);

        $defaultRequiredMins = (function () use ($partnerId) {
            $val = \App\Models\PartnerSetting::where('partner_id', $partnerId)
                ->where('key', 'min_present_mins')
                ->value('value');

            return $val !== null ? (int) $val : 480;
        })();

        $data = $query->with('employee.shift')->latest('date')->get();

        $columns = [
            'Date', 'Emp ID', 'Employee', 'Check In', 'Check Out',
            'Working Mins', 'Late Mins', 'Status', 'Working Mode',
            'Total Hours', 'Productive Hours', 'Overtime',
        ];

        $callback = function () use ($data, $columns, $defaultRequiredMins) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                $worked = (int) ($row->working_minutes ?? 0);
                $shift = $row->employee->shift ?? null;
                $req = ($shift && $shift->min_present_mins)
                    ? (int) $shift->min_present_mins
                    : $defaultRequiredMins;
                $ov = max(0, $worked - $req);

                fputcsv($file, [
                    $row->date ? Carbon::parse($row->date)->format('Y-m-d') : '',
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    $row->check_in ? Carbon::parse($row->check_in)->format('H:i:s') : '-',
                    $row->check_out ? Carbon::parse($row->check_out)->format('H:i:s') : '-',
                    $worked,
                    $row->late_minutes ?? 0,
                    $row->status,
                    $row->working_mode ?? 'office',
                    intdiv($worked, 60) . 'h ' . ($worked % 60) . 'm',
                    intdiv($req, 60) . 'h ' . ($req % 60) . 'm',
                    intdiv($ov, 60) . 'h ' . ($ov % 60) . 'm',
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Expense report (paginated, filterable, with summary).
     * Mirrors the workspace HRMS Expense Report screen.
     */
    public function expenseReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $teamIds = $this->getTeamEmployeeIds('expense_report_viewAny');

        $query = Expense::whereHas('employee', function ($q) use ($partnerId, $request) {
            $q->where('parent_id', $partnerId);

            if ($request->filled('branch_id')) {
                $q->where('branch_id', $request->branch_id);
            }

            if ($request->filled('team_id') || $request->filled('reporting_to')) {
                $teamVal = $request->input('team_id', $request->input('reporting_to'));
                $q->where('reporting_to', $teamVal);
            }
        })->whereIn('employee_id', $teamIds);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $startDate = $request->filled('start_date')
            ? $request->start_date
            : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date')
            ? $request->end_date
            : Carbon::now()->endOfMonth()->format('Y-m-d');

        $query->whereBetween('date', [$startDate, $endDate]);

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->with('employee')->latest('date')->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_expenses' => $summaryBase->count(),
            'total_amount'   => round($summaryBase->sum('amount'), 2),
            'approved'       => $summaryBase->where('status', 'approved')->count(),
            'pending'        => $summaryBase->where('status', 'pending')->count(),
            'rejected'       => $summaryBase->where('status', 'rejected')->count(),
        ];

        $rows->through(function ($row) {
            return [
                'id'            => $row->id,
                'date'          => $row->date,
                'employee_id'   => $row->employee_id,
                'employee_code' => optional($row->employee)->employee_code,
                'employee_name' => optional($row->employee)->name,
                'category'      => $row->category,
                'description'   => $row->description,
                'amount'        => (float) $row->amount,
                'status'        => $row->status,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Expense report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the expense report as a CSV stream.
     */
    public function expenseReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $teamIds = $this->getTeamEmployeeIds('expense_report_viewAny');

        $query = Expense::whereHas('employee', function ($q) use ($partnerId, $request) {
            $q->where('parent_id', $partnerId);

            if ($request->filled('branch_id')) {
                $q->where('branch_id', $request->branch_id);
            }

            if ($request->filled('team_id') || $request->filled('reporting_to')) {
                $teamVal = $request->input('team_id', $request->input('reporting_to'));
                $q->where('reporting_to', $teamVal);
            }
        })->whereIn('employee_id', $teamIds);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $startDate = $request->filled('start_date')
            ? $request->start_date
            : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date')
            ? $request->end_date
            : Carbon::now()->endOfMonth()->format('Y-m-d');

        $query->whereBetween('date', [$startDate, $endDate]);

        $data = $query->with('employee')->latest('date')->get();

        $columns = [
            'Date', 'Emp ID', 'Employee', 'Category', 'Description', 'Amount', 'Status',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->date ? Carbon::parse($row->date)->format('Y-m-d') : '',
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    $row->category,
                    $row->description,
                    $row->amount,
                    $row->status,
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="expense_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Task report (paginated, filterable, with summary).
     * Mirrors the workspace HRMS Task Report screen.
     */
    public function taskReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $teamIds = $this->getTeamEmployeeIds('task_report_viewAny');

        $query = EmployeeTask::whereHas('employee', function ($q) use ($partnerId, $request) {
            $q->where('parent_id', $partnerId);

            if ($request->filled('branch_id')) {
                $q->where('branch_id', $request->branch_id);
            }

            if ($request->filled('team_id') || $request->filled('reporting_to')) {
                $teamVal = $request->input('team_id', $request->input('reporting_to'));
                $q->where('reporting_to', $teamVal);
            }
        })->whereIn('employee_id', $teamIds);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $startDate = $request->filled('start_date')
            ? $request->start_date
            : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date')
            ? $request->end_date
            : Carbon::now()->endOfMonth()->format('Y-m-d');

        $query->whereBetween('created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59',
        ]);

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->with('employee')->latest()->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_tasks'   => $summaryBase->count(),
            'status_counts' => $summaryBase->groupBy('status')->map->count()->toArray(),
        ];

        $rows->through(function ($row) {
            return [
                'id'            => $row->id,
                'title'         => $row->title,
                'employee_id'   => $row->employee_id,
                'employee_code' => optional($row->employee)->employee_code,
                'employee_name' => optional($row->employee)->name,
                'priority'      => $row->priority,
                'description'   => $row->description,
                'due_date'      => $row->due_date,
                'status'        => $row->status,
                'created_at'    => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Task report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the task report as a CSV stream.
     */
    public function taskReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $teamIds = $this->getTeamEmployeeIds('task_report_viewAny');

        $query = EmployeeTask::whereHas('employee', function ($q) use ($partnerId, $request) {
            $q->where('parent_id', $partnerId);

            if ($request->filled('branch_id')) {
                $q->where('branch_id', $request->branch_id);
            }

            if ($request->filled('team_id') || $request->filled('reporting_to')) {
                $teamVal = $request->input('team_id', $request->input('reporting_to'));
                $q->where('reporting_to', $teamVal);
            }
        })->whereIn('employee_id', $teamIds);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $startDate = $request->filled('start_date')
            ? $request->start_date
            : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date')
            ? $request->end_date
            : Carbon::now()->endOfMonth()->format('Y-m-d');

        $query->whereBetween('created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59',
        ]);

        $data = $query->with('employee')->latest()->get();

        $columns = [
            'Task', 'Emp ID', 'Assigned To', 'Priority', 'Description', 'Due Date', 'Status', 'Created At',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->title,
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    $row->priority,
                    $row->description,
                    $row->due_date ? Carbon::parse($row->due_date)->format('Y-m-d') : '',
                    $row->status,
                    $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="task_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Lead report (paginated, filterable, with summary).
     * Mirrors the workspace HRMS Lead Report screen.
     */
    public function leadReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = Lead::where('partner_id', $partnerId);

        if ($request->filled('branch_id')) {
            $query->whereHas('assignedTo', fn ($q) => $q->where('branch_id', $request->branch_id));
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('assignedTo', fn ($q) => $q->where('reporting_to', $teamVal));
        }

        if ($request->filled('employee_id')) {
            $query->where('assigned_to', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $startDate = $request->filled('start_date')
            ? $request->start_date
            : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date')
            ? $request->end_date
            : Carbon::now()->endOfMonth()->format('Y-m-d');

        $query->whereBetween('created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59',
        ]);

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->with('assignedTo')->latest()->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_leads'   => $summaryBase->count(),
            'status_counts' => $summaryBase->groupBy('status')->map->count()->toArray(),
        ];

        $rows->through(function ($row) {
            return [
                'id'             => $row->id,
                'customer_name'   => $row->customer_name,
                'customer_mobile' => $row->customer_mobile,
                'assigned_to_id'  => $row->assigned_to,
                'employee_code'   => optional($row->assignedTo)->employee_code,
                'assigned_to'     => optional($row->assignedTo)->name ?? 'Unassigned',
                'status'          => $row->status,
                'notes'           => $row->notes,
                'created_at'      => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Lead report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the lead report as a CSV stream.
     */
    public function leadReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = Lead::where('partner_id', $partnerId);

        if ($request->filled('branch_id')) {
            $query->whereHas('assignedTo', fn ($q) => $q->where('branch_id', $request->branch_id));
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('assignedTo', fn ($q) => $q->where('reporting_to', $teamVal));
        }

        if ($request->filled('employee_id')) {
            $query->where('assigned_to', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $startDate = $request->filled('start_date')
            ? $request->start_date
            : Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->filled('end_date')
            ? $request->end_date
            : Carbon::now()->endOfMonth()->format('Y-m-d');

        $query->whereBetween('created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59',
        ]);

        $data = $query->with('assignedTo')->latest()->get();

        $columns = [
            'Lead Name', 'Mobile', 'Emp ID', 'Assigned To', 'Status', 'Notes', 'Created At',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->customer_name ?? '-',
                    $row->customer_mobile ?? '-',
                    optional($row->assignedTo)->employee_code
                        ?? (optional($row->assignedTo)->id ? substr(optional($row->assignedTo)->id, 0, 8) : ''),
                    optional($row->assignedTo)->name ?? 'Unassigned',
                    $row->status,
                    $row->notes ?? '-',
                    $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="lead_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Order report (paginated, filterable, with summary).
     * Mirrors the workspace HRMS Order Report screen.
     * Note: the web report does NOT default the date range, so neither does this.
     */
    public function orderReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = LeadOrder::where('partner_id', $partnerId);

        if ($request->filled('branch_id')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('employee', fn ($sub) => $sub->where('branch_id', $request->branch_id))
                  ->orWhereHas('lead.assignedTo', fn ($sub) => $sub->where('branch_id', $request->branch_id));
            });
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->where(function ($q) use ($teamVal) {
                $q->whereHas('employee', fn ($sub) => $sub->where('reporting_to', $teamVal))
                  ->orWhereHas('lead.assignedTo', fn ($sub) => $sub->where('reporting_to', $teamVal));
            });
        }

        if ($request->filled('employee_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('employee_id', $request->employee_id)
                  ->orWhereHas('lead', fn ($sub) => $sub->where('assigned_to', $request->employee_id));
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        if ($request->filled('status')) {
            $query->where('approval_status', $request->status);
        }

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->with([
            'lead', 'lead.assignedTo.branch', 'lead.assignedTo.reportingTo',
            'employee.branch', 'employee.reportingTo', 'currentStage',
        ])->latest()->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_orders'       => $summaryBase->count(),
            'total_revenue'      => round($summaryBase->sum('final_amount'), 2),
            'collected_amount'   => round($summaryBase->sum('paid_amount'), 2),
            'pending_balance'    => round($summaryBase->sum('remaining_balance'), 2),
            'total_final_amount' => round($summaryBase->sum('final_amount'), 2),
            'approval_counts'    => $summaryBase->groupBy('approval_status')->map->count()->toArray(),
            'payment_counts'     => $summaryBase->groupBy('payment_status')->map->count()->toArray(),
        ];

        $rows->through(function ($row) {
            $emp = $row->employee ?? optional($row->lead)->assignedTo;

            return [
                'id'                => $row->id,
                'employee_id'       => $row->employee_id,
                'lead_id'           => $row->lead_id,
                'customer_name'     => optional($row->lead)->customer_name,
                'customer_mobile'   => optional($row->lead)->customer_mobile,
                'employee_code'     => optional($emp)->employee_code,
                'assigned_to'       => optional($emp)->name ?? 'Unassigned',
                'branch_name'       => optional($emp)->branch ? optional($emp)->branch->name : ($emp ? 'Main Branch' : null),
                'team_name'         => optional($emp)->reportingTo ? optional($emp)->reportingTo->name : ($emp ? 'Direct / None' : null),
                'current_stage'     => optional($row->currentStage)->name,
                'total_amount'      => (float) $row->total_amount,
                'discount'          => (float) $row->discount,
                'final_amount'      => (float) $row->final_amount,
                'paid_amount'       => (float) $row->paid_amount,
                'remaining_balance' => (float) $row->remaining_balance,
                'payment_status'    => $row->payment_status,
                'approval_status'   => $row->approval_status,
                'created_at'        => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Order report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the order report as a CSV stream.
     */
    public function orderReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = LeadOrder::where('partner_id', $partnerId);

        if ($request->filled('branch_id')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('employee', fn ($sub) => $sub->where('branch_id', $request->branch_id))
                  ->orWhereHas('lead.assignedTo', fn ($sub) => $sub->where('branch_id', $request->branch_id));
            });
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->where(function ($q) use ($teamVal) {
                $q->whereHas('employee', fn ($sub) => $sub->where('reporting_to', $teamVal))
                  ->orWhereHas('lead.assignedTo', fn ($sub) => $sub->where('reporting_to', $teamVal));
            });
        }

        if ($request->filled('employee_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('employee_id', $request->employee_id)
                  ->orWhereHas('lead', fn ($sub) => $sub->where('assigned_to', $request->employee_id));
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        if ($request->filled('status')) {
            $query->where('approval_status', $request->status);
        }

        $data = $query->with([
            'lead', 'lead.assignedTo.branch', 'lead.assignedTo.reportingTo',
            'employee.branch', 'employee.reportingTo', 'currentStage',
        ])->latest()->get();

        $columns = [
            'Lead Name', 'Mobile', 'Emp ID', 'Assigned To / Employee', 'Branch',
            'Team / Reporting Manager', 'Current Stage', 'Total Amount', 'Discount',
            'Final Amount', 'Paid Amount', 'Remaining Balance', 'Payment Status',
            'Approval Status', 'Created At',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                $emp = $row->employee ?? optional($row->lead)->assignedTo;

                fputcsv($file, [
                    optional($row->lead)->customer_name ?? '-',
                    optional($row->lead)->customer_mobile ?? '-',
                    optional($emp)->employee_code
                        ?? (optional($emp)->id ? substr(optional($emp)->id, 0, 8) : ''),
                    optional($emp)->name ?? 'Unassigned',
                    optional($emp)->branch ? optional($emp)->branch->name : ($emp ? 'Main Branch' : '-'),
                    optional($emp)->reportingTo ? optional($emp)->reportingTo->name : ($emp ? 'Direct / None' : '-'),
                    optional($row->currentStage)->name ?? '-',
                    $row->total_amount,
                    $row->discount,
                    $row->final_amount,
                    $row->paid_amount,
                    $row->remaining_balance,
                    $row->payment_status,
                    $row->approval_status,
                    $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="order_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Payroll report (paginated, filterable, with summary).
     * Mirrors the workspace HRMS Payroll Report screen.
     * Note: the web screen defines search/department/role filters but does not
     * actually apply them in its query; this API wires them up for usefulness.
     */
    public function payrollReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = EmployeePayroll::query()
            ->whereHas('employee', function ($q) use ($partnerId, $request) {
                $q->where('parent_id', $partnerId);

                if ($request->filled('branch_id')) {
                    $q->where('branch_id', $request->branch_id);
                }

                if ($request->filled('team_id') || $request->filled('reporting_to')) {
                    $teamVal = $request->input('team_id', $request->input('reporting_to'));
                    $q->where('reporting_to', $teamVal);
                }
            })
            ->with(['employee']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('role_name')) {
            $query->whereHas('employee', fn ($q) => $q->whereHas('roles', fn ($rq) => $this->roleNameWhere($rq, $request->role_name, $partnerId)));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->latest()->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_payrolls' => $summaryBase->count(),
            'total_paid'     => $summaryBase->where('status', 'paid')->count(),
            'total_pending'  => $summaryBase->where('status', 'pending')->count(),
            'total_net_pay'  => round($summaryBase->sum('net_pay'), 2),
        ];

        $rows->through(function ($row) {
            $presents = EmployeeAttendance::where('employee_id', $row->employee_id)
                ->whereMonth('date', $row->month)
                ->whereYear('date', $row->year)
                ->where('status', 'present')->count();
            $absents = EmployeeAttendance::where('employee_id', $row->employee_id)
                ->whereMonth('date', $row->month)
                ->whereYear('date', $row->year)
                ->where('status', 'absent')->count();
            $leaves = EmployeeLeave::where('employee_id', $row->employee_id)
                ->whereMonth('start_date', $row->month)
                ->whereYear('start_date', $row->year)
                ->where('status', 'approved')->count();
            $expenses = Expense::where('employee_id', $row->employee_id)
                ->whereMonth('date', $row->month)
                ->whereYear('date', $row->year)
                ->where('status', 'approved')->sum('amount');

            return [
                'id'               => $row->id,
                'employee_id'      => $row->employee_id,
                'employee_name'    => optional($row->employee)->name,
                'employee_email'   => optional($row->employee)->email,
                'month'            => $row->month,
                'year'             => $row->year,
                'basic_salary'     => (float) $row->basic_salary,
                'gross_pay'        => (float) $row->gross_pay,
                'net_pay'          => (float) $row->net_pay,
                'status'           => $row->status,
                'presents'         => $presents,
                'absents'          => $absents,
                'leaves'           => $leaves,
                'expenses'         => round((float) $expenses, 2),
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Payroll report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the payroll report as a CSV stream.
     */
    public function payrollReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = EmployeePayroll::query()
            ->whereHas('employee', function ($q) use ($partnerId, $request) {
                $q->where('parent_id', $partnerId);

                if ($request->filled('branch_id')) {
                    $q->where('branch_id', $request->branch_id);
                }

                if ($request->filled('team_id') || $request->filled('reporting_to')) {
                    $teamVal = $request->input('team_id', $request->input('reporting_to'));
                    $q->where('reporting_to', $teamVal);
                }
            })
            ->with(['employee']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('role_name')) {
            $query->whereHas('employee', fn ($q) => $q->whereHas('roles', fn ($rq) => $this->roleNameWhere($rq, $request->role_name, $partnerId)));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $data = $query->latest()->get();

        $columns = [
            'ID', 'Employee Name', 'Employee Email', 'Month', 'Year', 'Basic Salary',
            'Gross Pay', 'Net Pay', 'Status', 'Presents', 'Absents', 'Leaves', 'Expenses',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                $presents = EmployeeAttendance::where('employee_id', $row->employee_id)
                    ->whereMonth('date', $row->month)
                    ->whereYear('date', $row->year)
                    ->where('status', 'present')->count();
                $absents = EmployeeAttendance::where('employee_id', $row->employee_id)
                    ->whereMonth('date', $row->month)
                    ->whereYear('date', $row->year)
                    ->where('status', 'absent')->count();
                $leaves = EmployeeLeave::where('employee_id', $row->employee_id)
                    ->whereMonth('start_date', $row->month)
                    ->whereYear('start_date', $row->year)
                    ->where('status', 'approved')->count();
                $expenses = Expense::where('employee_id', $row->employee_id)
                    ->whereMonth('date', $row->month)
                    ->whereYear('date', $row->year)
                    ->where('status', 'approved')->sum('amount');

                fputcsv($file, [
                    $row->id,
                    optional($row->employee)->name ?? '-',
                    optional($row->employee)->email ?? '-',
                    $row->month,
                    $row->year,
                    $row->basic_salary,
                    $row->gross_pay,
                    $row->net_pay,
                    $row->status,
                    $presents,
                    $absents,
                    $leaves,
                    round((float) $expenses, 2),
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="payroll_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Recovery report (outstanding balances). Mirrors the workspace HRMS
     * Recovery Report screen (LeadOrder where remaining_balance > 0).
     * The web screen defines search/department/role filters but ignores them;
     * this API wires them up for usefulness.
     */
    public function recoveryReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = LeadOrder::query()
            ->where('partner_id', $partnerId)
            ->where('remaining_balance', '>', 0)
            ->with(['lead', 'employee']);

        if ($request->filled('branch_id')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('employee', fn ($sub) => $sub->where('branch_id', $request->branch_id))
                  ->orWhereHas('lead.assignedTo', fn ($sub) => $sub->where('branch_id', $request->branch_id));
            });
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->where(function ($q) use ($teamVal) {
                $q->whereHas('employee', fn ($sub) => $sub->where('reporting_to', $teamVal))
                  ->orWhereHas('lead.assignedTo', fn ($sub) => $sub->where('reporting_to', $teamVal));
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('lead', function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_mobile', 'like', "%{$search}%");
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('role_name')) {
            $query->whereHas('employee', fn ($q) => $q->whereHas('roles', fn ($rq) => $this->roleNameWhere($rq, $request->role_name, $partnerId)));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->latest()->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_orders'          => $summaryBase->count(),
            'total_amount'          => round($summaryBase->sum('total_amount'), 2),
            'total_paid'            => round($summaryBase->sum('paid_amount'), 2),
            'total_remaining'       => round($summaryBase->sum('remaining_balance'), 2),
            'payment_status_counts' => $summaryBase->groupBy('payment_status')->map->count()->toArray(),
        ];

        $rows->through(function ($row) {
            return [
                'id'                 => $row->id,
                'order_id'           => $row->id,
                'employee_id'        => $row->employee_id,
                'employee_name'      => optional($row->employee)->name,
                'customer_name'      => optional($row->lead)->customer_name,
                'customer_mobile'    => optional($row->lead)->customer_mobile,
                'total_amount'       => (float) $row->total_amount,
                'paid_amount'        => (float) $row->paid_amount,
                'remaining_balance'  => (float) $row->remaining_balance,
                'payment_status'     => $row->payment_status,
                'approval_status'    => $row->approval_status,
                'created_at'         => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Recovery report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the recovery report as a CSV stream.
     */
    public function recoveryReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = LeadOrder::query()
            ->where('partner_id', $partnerId)
            ->where('remaining_balance', '>', 0)
            ->with(['lead', 'employee']);

        if ($request->filled('branch_id')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('employee', fn ($sub) => $sub->where('branch_id', $request->branch_id))
                  ->orWhereHas('lead.assignedTo', fn ($sub) => $sub->where('branch_id', $request->branch_id));
            });
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->where(function ($q) use ($teamVal) {
                $q->whereHas('employee', fn ($sub) => $sub->where('reporting_to', $teamVal))
                  ->orWhereHas('lead.assignedTo', fn ($sub) => $sub->where('reporting_to', $teamVal));
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('lead', function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_mobile', 'like', "%{$search}%");
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('role_name')) {
            $query->whereHas('employee', fn ($q) => $q->whereHas('roles', fn ($rq) => $this->roleNameWhere($rq, $request->role_name, $partnerId)));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $data = $query->latest()->get();

        $columns = [
            'Order ID', 'Lead Name', 'Total Amount', 'Paid Amount', 'Remaining Balance', 'Payment Status',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->id,
                    optional($row->lead)->customer_name ?? '-',
                    $row->total_amount,
                    $row->paid_amount,
                    $row->remaining_balance,
                    $row->payment_status,
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="recovery_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Notice report. Mirrors the workspace HRMS Notice Report screen
     * (Notice where user_id = partner). The web screen defines
     * search/department/role filters but ignores them; this API wires up
     * search + department targeting + type for usefulness.
     */
    public function noticeReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = Notice::query()->where('user_id', $partnerId);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('branch_id')) {
            $branchUserIds = User::where('parent_id', $partnerId)
                ->where('branch_id', $request->branch_id)
                ->pluck('id')
                ->toArray();

            $bId = $request->branch_id;
            $query->where(function ($q) use ($bId, $branchUserIds) {
                $q->whereJsonContains('branch_ids', (int) $bId)
                  ->orWhereJsonContains('branch_ids', (string) $bId);

                if (!empty($branchUserIds)) {
                    $q->orWhere(function ($sub) use ($branchUserIds) {
                        foreach ($branchUserIds as $uId) {
                            $sub->orWhereJsonContains('user_ids', (int) $uId)
                                ->orWhereJsonContains('user_ids', (string) $uId);
                        }
                    });
                }
            });
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $teamUserIds = User::where('parent_id', $partnerId)
                ->where('reporting_to', $teamVal)
                ->pluck('id')
                ->push((int) $teamVal)
                ->toArray();

            $query->where(function ($q) use ($teamUserIds) {
                foreach ($teamUserIds as $uId) {
                    $q->orWhereJsonContains('user_ids', (int) $uId)
                      ->orWhereJsonContains('user_ids', (string) $uId);
                }
            });
        }

        if ($request->filled('employee_id')) {
            $empId = $request->employee_id;
            $query->where(function ($q) use ($empId) {
                $q->whereJsonContains('user_ids', (int) $empId)
                  ->orWhereJsonContains('user_ids', (string) $empId);
            });
        }

        if ($request->filled('department_id')) {
            $query->whereJsonContains('department_ids', (int) $request->department_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_date', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->latest()->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_notices' => $summaryBase->count(),
            'type_counts'   => $summaryBase->groupBy('type')->map->count()->toArray(),
        ];

        $rows->through(function ($row) {
            return [
                'id'          => $row->id,
                'title'       => $row->title,
                'type'        => $row->type,
                'content'     => $row->content,
                'start_date'  => $row->start_date ? $row->start_date->format('Y-m-d H:i:s') : null,
                'end_date'    => $row->end_date ? $row->end_date->format('Y-m-d H:i:s') : null,
                'department_ids' => $row->department_ids,
                'created_at'  => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Notice report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the notice report as a CSV stream.
     */
    public function noticeReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = Notice::query()->where('user_id', $partnerId);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('branch_id')) {
            $branchUserIds = User::where('parent_id', $partnerId)
                ->where('branch_id', $request->branch_id)
                ->pluck('id')
                ->toArray();

            $bId = $request->branch_id;
            $query->where(function ($q) use ($bId, $branchUserIds) {
                $q->whereJsonContains('branch_ids', (int) $bId)
                  ->orWhereJsonContains('branch_ids', (string) $bId);

                if (!empty($branchUserIds)) {
                    $q->orWhere(function ($sub) use ($branchUserIds) {
                        foreach ($branchUserIds as $uId) {
                            $sub->orWhereJsonContains('user_ids', (int) $uId)
                                ->orWhereJsonContains('user_ids', (string) $uId);
                        }
                    });
                }
            });
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $teamUserIds = User::where('parent_id', $partnerId)
                ->where('reporting_to', $teamVal)
                ->pluck('id')
                ->push((int) $teamVal)
                ->toArray();

            $query->where(function ($q) use ($teamUserIds) {
                foreach ($teamUserIds as $uId) {
                    $q->orWhereJsonContains('user_ids', (int) $uId)
                      ->orWhereJsonContains('user_ids', (string) $uId);
                }
            });
        }

        if ($request->filled('employee_id')) {
            $empId = $request->employee_id;
            $query->where(function ($q) use ($empId) {
                $q->whereJsonContains('user_ids', (int) $empId)
                  ->orWhereJsonContains('user_ids', (string) $empId);
            });
        }

        if ($request->filled('department_id')) {
            $query->whereJsonContains('department_ids', (int) $request->department_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_date', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        $data = $query->latest()->get();

        $columns = [
            'ID', 'Title', 'Type', 'Start Date', 'End Date', 'Created At',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->id,
                    $row->title,
                    $row->type,
                    $row->start_date ? $row->start_date->format('Y-m-d H:i:s') : '',
                    $row->end_date ? $row->end_date->format('Y-m-d H:i:s') : '',
                    $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="notice_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Product Category report. Mirrors the workspace HRMS Product Category
     * Report screen (ProductCategory where partner_id = partner). The web
     * screen defines search/department/role filters but ignores them; this API
     * wires up search (name) + status for usefulness.
     */
    public function productCategoryReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = ProductCategory::query()->where('partner_id', $partnerId);

        if ($request->filled('search') || $request->filled('name')) {
            $searchTerm = $request->input('search', $request->input('name'));
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->withCount('products')->latest()->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_categories' => $summaryBase->count(),
            'active'           => $summaryBase->where('status', 'active')->count(),
            'inactive'         => $summaryBase->where('status', 'inactive')->count(),
        ];

        $rows->through(function ($row) {
            return [
                'id'             => $row->id,
                'name'           => $row->name,
                'products_count' => (int) ($row->products_count ?? 0),
                'status'         => $row->status,
                'created_at'     => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Product category report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the product category report as a CSV stream.
     */
    public function productCategoryReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = ProductCategory::query()->where('partner_id', $partnerId);

        if ($request->filled('search') || $request->filled('name')) {
            $searchTerm = $request->input('search', $request->input('name'));
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->withCount('products')->latest()->get();

        $columns = ['ID', 'Category Name', 'Total Products', 'Status', 'Created At'];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->id,
                    $row->name,
                    $row->products_count ?? 0,
                    $row->status,
                    $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product-category_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Product report. Mirrors the workspace HRMS Product Report screen
     * (Product where partner_id = partner). The web screen defines
     * search/department/role filters but ignores them; this API wires up
     * search (name) + status + category_id for usefulness.
     */
    public function productReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = Product::query()->where('partner_id', $partnerId)->with(['category']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('product_id') || $request->filled('id')) {
            $prodId = $request->input('product_id', $request->input('id'));
            $query->where('id', $prodId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->latest()->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_products' => $summaryBase->count(),
            'active'         => $summaryBase->where('status', 'active')->count(),
            'inactive'       => $summaryBase->where('status', 'inactive')->count(),
            'total_amount'   => round($summaryBase->sum('amount'), 2),
        ];

        $rows->through(function ($row) {
            return [
                'id'           => $row->id,
                'name'         => $row->name,
                'category_id'  => $row->category_id,
                'category'     => optional($row->category)->name,
                'amount'       => (float) $row->amount,
                'status'       => $row->status,
                'created_at'   => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Product report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the product report as a CSV stream.
     */
    public function productReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = Product::query()->where('partner_id', $partnerId)->with(['category']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('product_id') || $request->filled('id')) {
            $prodId = $request->input('product_id', $request->input('id'));
            $query->where('id', $prodId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->latest()->get();

        $columns = ['ID', 'Product Name', 'Category', 'Amount', 'Status', 'Created At'];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->id,
                    $row->name,
                    optional($row->category)->name ?? '-',
                    $row->amount,
                    $row->status,
                    $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Salary Management report. Mirrors the workspace HRMS Salary Management
     * Report screen (EmployeePayroll where employee parent_id = partner).
     * The web screen defines search/department/role filters but ignores them;
     * this API wires them up (plus status/month/year) for usefulness.
     */
    public function salaryManagementReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = EmployeePayroll::query()
            ->whereHas('employee', function ($q) use ($partnerId, $request) {
                $q->where('parent_id', $partnerId);

                if ($request->filled('branch_id')) {
                    $q->where('branch_id', $request->branch_id);
                }

                if ($request->filled('team_id') || $request->filled('reporting_to')) {
                    $teamVal = $request->input('team_id', $request->input('reporting_to'));
                    $q->where('reporting_to', $teamVal);
                }
            })
            ->with(['employee']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('role_name')) {
            $query->whereHas('employee', fn ($q) => $q->whereHas('roles', fn ($rq) => $this->roleNameWhere($rq, $request->role_name, $partnerId)));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->latest()->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_records'     => $summaryBase->count(),
            'total_gross_pay'   => round($summaryBase->sum('gross_pay'), 2),
            'total_deductions'  => round($summaryBase->sum('deductions'), 2),
            'total_net_pay'     => round($summaryBase->sum('net_pay'), 2),
        ];

        $rows->through(function ($row) {
            $allowances = 0;
            $breakdown = is_array($row->allowances_breakdown)
                ? $row->allowances_breakdown
                : json_decode($row->allowances_breakdown, true);
            if (is_array($breakdown)) {
                $allowances = array_sum(array_column($breakdown, 'amount'));
            }

            return [
                'id'              => $row->id,
                'employee_id'     => $row->employee_id,
                'employee_name'   => optional($row->employee)->name,
                'employee_email'  => optional($row->employee)->email,
                'month'           => $row->month,
                'year'            => $row->year,
                'basic_salary'    => (float) $row->basic_salary,
                'allowances'      => round((float) $allowances, 2),
                'bonuses'         => (float) $row->bonuses,
                'commissions'     => (float) $row->commissions,
                'gross_pay'       => (float) $row->gross_pay,
                'deductions'      => (float) $row->deductions,
                'net_pay'         => (float) $row->net_pay,
                'status'          => $row->status,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Salary management report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the salary management report as a CSV stream.
     */
    public function salaryManagementReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = EmployeePayroll::query()
            ->whereHas('employee', function ($q) use ($partnerId, $request) {
                $q->where('parent_id', $partnerId);

                if ($request->filled('branch_id')) {
                    $q->where('branch_id', $request->branch_id);
                }

                if ($request->filled('team_id') || $request->filled('reporting_to')) {
                    $teamVal = $request->input('team_id', $request->input('reporting_to'));
                    $q->where('reporting_to', $teamVal);
                }
            })
            ->with(['employee']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('role_name')) {
            $query->whereHas('employee', fn ($q) => $q->whereHas('roles', fn ($rq) => $this->roleNameWhere($rq, $request->role_name, $partnerId)));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $data = $query->latest()->get();

        $columns = [
            'ID', 'Employee Name', 'Employee Email', 'Month', 'Year', 'Basic Salary',
            'Allowances', 'Bonuses', 'Commissions', 'Gross Pay', 'Deductions', 'Net Pay', 'Status',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                $allowances = 0;
                $breakdown = is_array($row->allowances_breakdown)
                    ? $row->allowances_breakdown
                    : json_decode($row->allowances_breakdown, true);
                if (is_array($breakdown)) {
                    $allowances = array_sum(array_column($breakdown, 'amount'));
                }

                fputcsv($file, [
                    $row->id,
                    optional($row->employee)->name ?? '-',
                    optional($row->employee)->email ?? '-',
                    $row->month,
                    $row->year,
                    $row->basic_salary,
                    round((float) $allowances, 2),
                    $row->bonuses,
                    $row->commissions,
                    $row->gross_pay,
                    $row->deductions,
                    $row->net_pay,
                    $row->status,
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="salary-management_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Commissions report. Mirrors the workspace HRMS Commissions Report screen
     * (CommissionPayout where employee parent_id = partner). The web screen
     * defines search/department/role filters but ignores them; this API wires
     * them up (plus status/month/year) for usefulness.
     */
    public function commissionsReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = CommissionPayout::query()
            ->whereHas('employee', function ($q) use ($partnerId, $request) {
                $q->where('parent_id', $partnerId);

                if ($request->filled('branch_id')) {
                    $q->where('branch_id', $request->branch_id);
                }

                if ($request->filled('team_id') || $request->filled('reporting_to')) {
                    $teamVal = $request->input('team_id', $request->input('reporting_to'));
                    $q->where('reporting_to', $teamVal);
                }
            })
            ->with(['employee']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('role_name')) {
            $query->whereHas('employee', fn ($q) => $q->whereHas('roles', fn ($rq) => $this->roleNameWhere($rq, $request->role_name, $partnerId)));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->latest()->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_records'           => $summaryBase->count(),
            'total_commission_earned' => round($summaryBase->sum('commission_earned'), 2),
            'total_recovery_earned'   => round($summaryBase->sum('recovery_earned'), 2),
            'total_net_payout'        => round($summaryBase->sum('net_payout'), 2),
            'status_counts'           => $summaryBase->groupBy('status')->map->count()->toArray(),
        ];

        $rows->through(function ($row) {
            return [
                'id'                        => $row->id,
                'employee_id'               => $row->employee_id,
                'employee_name'             => optional($row->employee)->name,
                'employee_email'            => optional($row->employee)->email,
                'month'                     => $row->month,
                'year'                      => $row->year,
                'target_amount'             => (float) $row->target_amount,
                'total_new_business'        => (float) $row->total_new_business,
                'total_recovery_business'   => (float) $row->total_recovery_business,
                'commission_earned'         => (float) $row->commission_earned,
                'recovery_earned'           => (float) $row->recovery_earned,
                'upline_commission_earned'  => (float) $row->upline_commission_earned,
                'tds_percent'               => (float) $row->tds_percent,
                'tds_amount'                => (float) $row->tds_amount,
                'net_payout'                => (float) $row->net_payout,
                'total_payout'              => (float) $row->total_payout,
                'status'                    => $row->status,
                'paid_at'                   => $row->paid_at ? $row->paid_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Commissions report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the commissions report as a CSV stream.
     */
    public function commissionsReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = CommissionPayout::query()
            ->whereHas('employee', function ($q) use ($partnerId, $request) {
                $q->where('parent_id', $partnerId);

                if ($request->filled('branch_id')) {
                    $q->where('branch_id', $request->branch_id);
                }

                if ($request->filled('team_id') || $request->filled('reporting_to')) {
                    $teamVal = $request->input('team_id', $request->input('reporting_to'));
                    $q->where('reporting_to', $teamVal);
                }
            })
            ->with(['employee']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('role_name')) {
            $query->whereHas('employee', fn ($q) => $q->whereHas('roles', fn ($rq) => $this->roleNameWhere($rq, $request->role_name, $partnerId)));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $data = $query->latest()->get();

        $columns = [
            'ID', 'Employee Name', 'Month/Year', 'Target', 'Commission Earned',
            'Recovery Earned', 'Net Payout', 'Status',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->id,
                    optional($row->employee)->name ?? '-',
                    $row->month . '/' . $row->year,
                    $row->target_amount,
                    $row->commission_earned,
                    $row->recovery_earned,
                    $row->net_payout,
                    $row->status,
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="commissions_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * PIP (Performance Improvement Plan) report. Mirrors the workspace HRMS
     * PIP Report screen (EmployeePip where partner_id = partner). Supports
     * employee/status/date-range/search filtering with a status summary.
     */
    public function pipReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $teamIds = $this->getTeamEmployeeIds('pip_report_viewAny');

        $query = EmployeePip::query()
            ->where('partner_id', $partnerId)
            ->whereIn('employee_id', $teamIds);

        if ($request->filled('branch_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('branch_id', $request->branch_id));
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($q) => $q->where('reporting_to', $teamVal));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_date', [
                $request->start_date,
                $request->end_date,
            ]);
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

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->with(['employee', 'creator'])->latest('start_date')->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_pips'     => $summaryBase->count(),
            'status_counts'  => $summaryBase->groupBy('status')->map->count()->toArray(),
        ];

        $rows->through(function ($row) {
            return [
                'id'                  => $row->id,
                'employee_id'         => $row->employee_id,
                'employee_code'       => optional($row->employee)->employee_code,
                'employee_name'       => optional($row->employee)->name,
                'reason'              => $row->reason,
                'improvement_targets' => $row->improvement_targets,
                'start_date'          => $row->start_date ? $row->start_date->format('Y-m-d') : null,
                'end_date'            => $row->end_date ? $row->end_date->format('Y-m-d') : null,
                'status'              => $row->status,
                'review_result'       => $row->review_result,
                'created_by'          => optional($row->creator)->name,
                'created_at'          => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'PIP report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the PIP report as a CSV stream.
     */
    public function pipReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $teamIds = $this->getTeamEmployeeIds('pip_report_viewAny');

        $query = EmployeePip::query()
            ->where('partner_id', $partnerId)
            ->whereIn('employee_id', $teamIds);

        if ($request->filled('branch_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('branch_id', $request->branch_id));
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($q) => $q->where('reporting_to', $teamVal));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_date', [
                $request->start_date,
                $request->end_date,
            ]);
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

        $data = $query->with(['employee', 'creator'])->latest('start_date')->get();

        $columns = [
            'Emp ID', 'Employee Name', 'Reason / Targets', 'Start Date', 'End Date', 'Status', 'Review Outcome',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    $row->reason,
                    $row->start_date ? $row->start_date->format('Y-m-d') : '',
                    $row->end_date ? $row->end_date->format('Y-m-d') : '',
                    ucwords(str_replace('_', ' ', $row->status)),
                    $row->review_result ?? '-',
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pip_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Recruitment report. Mirrors the workspace HRMS Recruitment Report screen
     * (Recruitment where partner_id = partner). Supports department/stage/
     * status/date-range/search filtering with a status summary.
     */
    public function recruitmentReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = Recruitment::query()->where('partner_id', $partnerId);

        if ($request->filled('branch_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('branch_id', $request->branch_id));
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($q) => $q->where('reporting_to', $teamVal));
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

        if ($request->filled('offer_status')) {
            $query->where('offer_status', $request->offer_status);
        }

        if ($request->filled('joining_status')) {
            $query->where('joining_status', $request->joining_status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
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

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->with(['department', 'creator'])->latest()->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_candidates' => $summaryBase->count(),
            'total_vacancies'  => $summaryBase->sum('vacancies_count'),
            'status_counts'    => $summaryBase->groupBy('status')->map->count()->toArray(),
            'offer_counts'     => $summaryBase->groupBy('offer_status')->map->count()->toArray(),
            'joining_counts'   => $summaryBase->groupBy('joining_status')->map->count()->toArray(),
        ];

        $rows->through(function ($row) {
            return [
                'id'                => $row->id,
                'job_title'         => $row->job_title,
                'vacancies_count'   => (int) $row->vacancies_count,
                'candidate_name'    => $row->candidate_name,
                'candidate_email'   => $row->candidate_email,
                'candidate_phone'   => $row->candidate_phone,
                'department'        => optional($row->department)->name,
                'interview_stage'   => $row->interview_stage,
                'status'            => $row->status,
                'offer_status'      => $row->offer_status,
                'joining_status'    => $row->joining_status,
                'cost_per_hire'     => (float) $row->cost_per_hire,
                'interview_date'    => $row->interview_date ? $row->interview_date->format('Y-m-d') : null,
                'created_by'        => optional($row->creator)->name,
                'created_at'        => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Recruitment report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the recruitment report as a CSV stream.
     */
    public function recruitmentReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = Recruitment::query()->where('partner_id', $partnerId);

        if ($request->filled('branch_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('branch_id', $request->branch_id));
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($q) => $q->where('reporting_to', $teamVal));
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

        if ($request->filled('offer_status')) {
            $query->where('offer_status', $request->offer_status);
        }

        if ($request->filled('joining_status')) {
            $query->where('joining_status', $request->joining_status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
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

        $data = $query->with(['department', 'creator'])->latest()->get();

        $columns = [
            'Position Title', 'Vacancies', 'Candidate Name', 'Candidate Email', 'Candidate Phone',
            'Department', 'Interview Stage', 'Selection Status', 'Offer Status',
            'Joining Status', 'Cost Per Hire', 'Interview Date',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->job_title,
                    $row->vacancies_count,
                    $row->candidate_name,
                    $row->candidate_email ?? '-',
                    $row->candidate_phone ?? '-',
                    optional($row->department)->name ?? '-',
                    ucwords(str_replace('_', ' ', $row->interview_stage)),
                    ucwords(str_replace('_', ' ', $row->status)),
                    ucwords(str_replace('_', ' ', $row->offer_status)),
                    ucwords(str_replace('_', ' ', $row->joining_status)),
                    $row->cost_per_hire,
                    $row->interview_date ? $row->interview_date->format('Y-m-d') : '-',
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="recruitment_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Probation & Confirmation report. Mirrors the workspace HRMS Probation
     * Report screen (EmployeeProbation where partner_id = partner). Supports
     * employee/status/date-range(search)/search filtering with a status summary.
     */
    public function probationReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = EmployeeProbation::query()->where('partner_id', $partnerId);

        if ($request->filled('branch_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('branch_id', $request->branch_id));
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($q) => $q->where('reporting_to', $teamVal));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('confirmation_due_date', [
                $request->start_date,
                $request->end_date,
            ]);
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

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->with(['employee', 'creator'])->latest('confirmation_due_date')->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_records' => $summaryBase->count(),
            'status_counts' => $summaryBase->groupBy('status')->map->count()->toArray(),
            'is_extended'   => $summaryBase->where('is_extended', true)->count(),
        ];

        $rows->through(function ($row) {
            return [
                'id'                    => $row->id,
                'employee_id'           => $row->employee_id,
                'employee_code'         => optional($row->employee)->employee_code,
                'employee_name'         => optional($row->employee)->name,
                'start_date'            => $row->start_date ? $row->start_date->format('Y-m-d') : null,
                'confirmation_due_date' => $row->confirmation_due_date ? $row->confirmation_due_date->format('Y-m-d') : null,
                'extended_due_date'     => $row->extended_due_date ? $row->extended_due_date->format('Y-m-d') : null,
                'extension_reason'      => $row->extension_reason,
                'is_extended'           => (bool) $row->is_extended,
                'status'                => $row->status,
                'confirmation_date'     => $row->confirmation_date ? $row->confirmation_date->format('Y-m-d') : null,
                'asset_allocation'      => $row->asset_allocation,
                'evaluation_notes'      => $row->evaluation_notes,
                'created_by'            => optional($row->creator)->name,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Probation report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the probation report as a CSV stream.
     */
    public function probationReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = EmployeeProbation::query()->where('partner_id', $partnerId);

        if ($request->filled('branch_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('branch_id', $request->branch_id));
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($q) => $q->where('reporting_to', $teamVal));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('confirmation_due_date', [
                $request->start_date,
                $request->end_date,
            ]);
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

        $data = $query->with(['employee', 'creator'])->latest('confirmation_due_date')->get();

        $columns = [
            'Emp ID', 'Employee Name', 'Start Date', 'Confirmation Due Date',
            'Extended Due Date', 'Extension Reason', 'Status',
            'Confirmation Date', 'Asset Allocation',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    $row->start_date ? $row->start_date->format('Y-m-d') : '-',
                    $row->confirmation_due_date ? $row->confirmation_due_date->format('Y-m-d') : '-',
                    $row->extended_due_date ? $row->extended_due_date->format('Y-m-d') : '-',
                    $row->extension_reason ?? '-',
                    ucwords(str_replace('_', ' ', $row->status)),
                    $row->confirmation_date ? $row->confirmation_date->format('Y-m-d') : '-',
                    $row->asset_allocation ?? '-',
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="probation_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Resignation & Exit report. Mirrors the workspace HRMS Resignation/Exit
     * Report screen (EmployeeExit where partner_id = partner). Supports
     * employee/status/clearance/fnf/date-range(search) filtering with a summary.
     */
    public function exitReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = EmployeeExit::query()->where('partner_id', $partnerId);

        if ($request->filled('branch_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id)
                  ->orWhereHas('employee', fn ($eq) => $eq->where('branch_id', $request->branch_id));
            });
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($eq) => $eq->where('reporting_to', $teamVal));
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

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('resignation_date', [
                $request->start_date,
                $request->end_date,
            ]);
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

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->with(['employee', 'creator'])->latest('resignation_date')->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_records'    => $summaryBase->count(),
            'status_counts'    => $summaryBase->groupBy('status')->map->count()->toArray(),
            'clearance_counts' => $summaryBase->groupBy('clearance_status')->map->count()->toArray(),
            'fnf_counts'       => $summaryBase->groupBy('fnf_status')->map->count()->toArray(),
            'total_fnf_amount' => round($summaryBase->sum('fnf_amount'), 2),
        ];

        $rows->through(function ($row) {
            return [
                'id'                  => $row->id,
                'employee_id'         => $row->employee_id,
                'employee_code'       => optional($row->employee)->employee_code,
                'employee_name'       => optional($row->employee)->name,
                'resignation_date'    => $row->resignation_date ? $row->resignation_date->format('Y-m-d') : null,
                'exit_date'           => $row->exit_date ? $row->exit_date->format('Y-m-d') : null,
                'last_working_date'   => $row->last_working_date ? $row->last_working_date->format('Y-m-d') : null,
                'exit_type'           => $row->exit_type,
                'exit_reason'         => $row->exit_reason,
                'notice_period_days'   => $row->notice_period_days,
                'clearance_status'    => $row->clearance_status,
                'fnf_status'          => $row->fnf_status,
                'fnf_amount'          => (float) $row->fnf_amount,
                'fnf_settlement_date' => $row->fnf_settlement_date ? $row->fnf_settlement_date->format('Y-m-d') : null,
                'status'              => $row->status,
                'remarks'             => $row->remarks,
                'created_by'          => optional($row->creator)->name,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Resignation & exit report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the resignation & exit report as a CSV stream.
     */
    public function exitReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = EmployeeExit::query()->where('partner_id', $partnerId);

        if ($request->filled('branch_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id)
                  ->orWhereHas('employee', fn ($eq) => $eq->where('branch_id', $request->branch_id));
            });
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($eq) => $eq->where('reporting_to', $teamVal));
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

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('resignation_date', [
                $request->start_date,
                $request->end_date,
            ]);
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

        $data = $query->with(['employee', 'creator'])->latest('resignation_date')->get();

        $columns = [
            'Emp ID', 'Employee Name', 'Resignation Date', 'Notice Period (Days)',
            'Last Working Date', 'Exit Reason', 'Clearance Status', 'F&F Status',
            'F&F Amount', 'Settlement Date', 'Exit Status',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    $row->resignation_date ? $row->resignation_date->format('Y-m-d') : '-',
                    $row->notice_period_days,
                    $row->last_working_date ? $row->last_working_date->format('Y-m-d') : '-',
                    $row->exit_reason ?? '-',
                    ucwords(str_replace('_', ' ', $row->clearance_status)),
                    ucwords(str_replace('_', ' ', $row->fnf_status)),
                    $row->fnf_amount,
                    $row->fnf_settlement_date ? $row->fnf_settlement_date->format('Y-m-d') : '-',
                    ucwords(str_replace('_', ' ', $row->status)),
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="resignation_exit_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Documents & KYC report. Mirrors the workspace HRMS Documents Report
     * screen (EmployeeDocument where partner_id = partner). Supports
     * employee/category/status/expiring/date-range(search) filtering with a summary.
     */
    public function documentReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $expiring = (bool) $request->input('expiring', false);

        $query = EmployeeDocument::query()->where('partner_id', $partnerId);

        if ($request->filled('branch_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('branch_id', $request->branch_id));
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($q) => $q->where('reporting_to', $teamVal));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('document_category')) {
            $query->where('document_category', $request->document_category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($expiring) {
            $query->whereNotNull('expiry_date')
                  ->where('expiry_date', '<=', Carbon::now()->addDays(30));
        }

        if ($request->filled('start_date') && $request->filled('end_date') && !$expiring) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->with(['employee', 'creator'])->latest()->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_records'  => $summaryBase->count(),
            'status_counts'  => $summaryBase->groupBy('status')->map->count()->toArray(),
            'category_counts' => $summaryBase->groupBy('document_category')->map->count()->toArray(),
            'expiring_soon'  => $summaryBase->whereNotNull('expiry_date')
                ->filter(fn ($r) => $r->expiry_date->lte(Carbon::now()->addDays(30)))
                ->count(),
        ];

        $rows->through(function ($row) {
            return [
                'id'               => $row->id,
                'employee_id'      => $row->employee_id,
                'employee_code'    => optional($row->employee)->employee_code,
                'employee_name'    => optional($row->employee)->name,
                'document_category' => $row->document_category,
                'document_type'    => $row->document_type,
                'title'            => $row->title,
                'document_number'  => $row->document_number,
                'issue_date'       => $row->issue_date ? $row->issue_date->format('Y-m-d') : null,
                'expiry_date'      => $row->expiry_date ? $row->expiry_date->format('Y-m-d') : null,
                'status'           => $row->status,
                'created_by'       => optional($row->creator)->name,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Documents & KYC report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the documents & KYC report as a CSV stream.
     */
    public function documentReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $expiring = (bool) $request->input('expiring', false);

        $query = EmployeeDocument::query()->where('partner_id', $partnerId);

        if ($request->filled('branch_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('branch_id', $request->branch_id));
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($q) => $q->where('reporting_to', $teamVal));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('document_category')) {
            $query->where('document_category', $request->document_category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($expiring) {
            $query->whereNotNull('expiry_date')
                  ->where('expiry_date', '<=', Carbon::now()->addDays(30));
        }

        if ($request->filled('start_date') && $request->filled('end_date') && !$expiring) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        $data = $query->with(['employee', 'creator'])->latest()->get();

        $columns = [
            'Emp ID', 'Employee Name', 'Document Category', 'Document Type',
            'Document Title', 'Document Number', 'Issue Date', 'Expiry Date', 'Verification Status',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    ucwords(str_replace('_', ' ', $row->document_category)),
                    ucwords(str_replace('_', ' ', $row->document_type)),
                    $row->title,
                    $row->document_number ?? '-',
                    $row->issue_date ? $row->issue_date->format('Y-m-d') : '-',
                    $row->expiry_date ? $row->expiry_date->format('Y-m-d') : 'No Expiry',
                    ucwords(str_replace('_', ' ', $row->status)),
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employee_documents_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Grievance & Discipline report. Mirrors the workspace HRMS Grievance
     * Report screen (EmployeeGrievance where partner_id = partner). Supports
     * employee/record_type/status/date-range(search) filtering with a summary.
     */
    public function grievanceReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = EmployeeGrievance::query()->where('partner_id', $partnerId);

        if ($request->filled('branch_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('branch_id', $request->branch_id));
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($q) => $q->where('reporting_to', $teamVal));
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

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
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

        $perPage = (int) $request->input('per_page', 20);
        $rows = $query->with(['employee', 'creator'])->latest()->paginate($perPage);

        $summaryBase = (clone $query)->get();
        $summary = [
            'total_records' => $summaryBase->count(),
            'status_counts' => $summaryBase->groupBy('status')->map->count()->toArray(),
            'type_counts'   => $summaryBase->groupBy('record_type')->map->count()->toArray(),
        ];

        $rows->through(function ($row) {
            return [
                'id'               => $row->id,
                'employee_id'      => $row->employee_id,
                'employee_code'    => optional($row->employee)->employee_code,
                'employee_name'    => optional($row->employee)->name,
                'record_type'      => $row->record_type,
                'title'            => $row->title,
                'incident_date'    => $row->incident_date ? $row->incident_date->format('Y-m-d') : null,
                'description'      => $row->description,
                'action_taken'     => $row->action_taken,
                'resolution_notes' => $row->resolution_notes,
                'status'           => $row->status,
                'created_by'       => optional($row->creator)->name,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Grievance & discipline report fetched successfully.',
            'data'    => $rows,
            'summary' => $summary,
        ]);
    }

    /**
     * Export the grievance & discipline report as a CSV stream.
     */
    public function grievanceReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = EmployeeGrievance::query()->where('partner_id', $partnerId);

        if ($request->filled('branch_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('branch_id', $request->branch_id));
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($q) => $q->where('reporting_to', $teamVal));
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

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
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

        $data = $query->with(['employee', 'creator'])->latest()->get();

        $columns = [
            'Emp ID', 'Employee Name', 'Record Type', 'Case Title', 'Incident Date',
            'Description', 'Action Taken / Warning', 'Resolution Outcome', 'Status',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    ucwords(str_replace('_', ' ', $row->record_type)),
                    $row->title,
                    $row->incident_date ? $row->incident_date->format('Y-m-d') : '-',
                    $row->description ?? '-',
                    $row->action_taken ?? '-',
                    $row->resolution_notes ?? '-',
                    ucwords(str_replace('_', ' ', $row->status)),
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="grievance_discipline_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Attrition report. Mirrors the workspace HRMS Attrition Report screen.
     * Returns aggregated attrition analytics (rate, voluntary/involuntary,
     * exit-reason & department breakdowns, monthly trends) plus a paginated
     * list of the underlying EmployeeExit records, filterable by year/month/
     * branch/department/exit_type/exit_reason/search.
     */
    public function attritionReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $selectedYear = (int) ($request->input('year', Carbon::now()->year));

        $query = EmployeeExit::with(['employee', 'branch', 'department'])
            ->where('partner_id', $partnerId)
            ->whereYear('exit_date', $selectedYear);

        if ($request->filled('month') && $request->month !== 'all') {
            $query->whereMonth('exit_date', (int) $request->month);
        }

        if ($request->filled('branch_id') && $request->branch_id !== '') {
            $query->where(function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id)
                  ->orWhereHas('employee', fn ($eq) => $eq->where('branch_id', $request->branch_id));
            });
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($eq) => $eq->where('reporting_to', $teamVal));
        }

        if ($request->filled('department_id') && $request->department_id !== '') {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('exit_type') && $request->exit_type !== 'all') {
            $query->where('exit_type', $request->exit_type);
        }

        if ($request->filled('exit_reason') && $request->exit_reason !== 'all') {
            $query->where('exit_reason', $request->exit_reason);
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->whereHas('employee', function ($q) use ($s) {
                $q->where('name', 'like', $s)->orWhere('email', 'like', $s);
            });
        }

        $allFilteredExits = (clone $query)->get();

        $totalExits = $allFilteredExits->count();
        $voluntaryExits = $allFilteredExits->where('exit_type', 'voluntary')->count();
        $involuntaryExits = $allFilteredExits->where('exit_type', 'involuntary')->count();

        $totalStaff = User::where('parent_id', $partnerId)->where('role', 'employee')->count();
        $avgHeadcount = max(1, $totalStaff + ($totalExits / 2));
        $attritionRate = round(($totalExits / $avgHeadcount) * 100, 2);

        $exitReasonsBreakdown = $allFilteredExits
            ->groupBy('exit_reason')
            ->map(function ($group, $reason) use ($totalExits) {
                return [
                    'reason'     => $reason,
                    'count'      => $group->count(),
                    'percentage' => $totalExits > 0 ? round(($group->count() / $totalExits) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->toArray();

        $departments = Department::where('partner_id', $partnerId)->get();
        $deptBreakdown = [];
        foreach ($departments as $dept) {
            $deptExits = $allFilteredExits->where('department_id', $dept->id)->count();
            if ($deptExits > 0) {
                $deptBreakdown[] = [
                    'department' => $dept->name,
                    'count'      => $deptExits,
                ];
            }
        }

        $monthlyTrends = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyTrends[] = [
                'month' => Carbon::create($selectedYear, $m, 1)->format('M'),
                'exits' => EmployeeExit::where('partner_id', $partnerId)
                    ->whereYear('exit_date', $selectedYear)
                    ->whereMonth('exit_date', $m)
                    ->count(),
            ];
        }

        $perPage = (int) $request->input('per_page', 10);
        $paginatedExits = $query->orderByDesc('exit_date')->paginate($perPage);

        $paginatedExits->through(function ($row) {
            return [
                'id'                => $row->id,
                'employee_id'       => $row->employee_id,
                'employee_code'     => optional($row->employee)->employee_code,
                'employee_name'     => optional($row->employee)->name,
                'branch'            => optional($row->branch)->name,
                'department'        => optional($row->department)->name,
                'exit_type'         => $row->exit_type,
                'exit_reason'       => $row->exit_reason,
                'resignation_date'  => $row->resignation_date ? $row->resignation_date->format('Y-m-d') : null,
                'exit_date'         => $row->exit_date ? $row->exit_date->format('Y-m-d') : null,
                'last_working_date' => $row->last_working_date ? $row->last_working_date->format('Y-m-d') : null,
                'clearance_status'  => $row->clearance_status,
                'fnf_status'        => $row->fnf_status,
                'status'            => $row->status,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Attrition report fetched successfully.',
            'data'    => $paginatedExits,
            'summary' => [
                'total_exits'      => $totalExits,
                'voluntary_exits'  => $voluntaryExits,
                'involuntary_exits' => $involuntaryExits,
                'total_staff'      => $totalStaff,
                'avg_headcount'    => round($avgHeadcount, 2),
                'attrition_rate'   => $attritionRate,
            ],
            'exit_reasons_breakdown' => $exitReasonsBreakdown,
            'department_breakdown'   => $deptBreakdown,
            'monthly_trends'         => $monthlyTrends,
        ]);
    }

    /**
     * Export the attrition report (underlying exit records) as a CSV stream.
     */
    public function attritionReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $selectedYear = (int) ($request->input('year', Carbon::now()->year));

        $query = EmployeeExit::with(['employee', 'branch', 'department'])
            ->where('partner_id', $partnerId)
            ->whereYear('exit_date', $selectedYear);

        if ($request->filled('month') && $request->month !== 'all') {
            $query->whereMonth('exit_date', (int) $request->month);
        }

        if ($request->filled('branch_id') && $request->branch_id !== '') {
            $query->where(function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id)
                  ->orWhereHas('employee', fn ($eq) => $eq->where('branch_id', $request->branch_id));
            });
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($eq) => $eq->where('reporting_to', $teamVal));
        }

        if ($request->filled('department_id') && $request->department_id !== '') {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('exit_type') && $request->exit_type !== 'all') {
            $query->where('exit_type', $request->exit_type);
        }

        if ($request->filled('exit_reason') && $request->exit_reason !== 'all') {
            $query->where('exit_reason', $request->exit_reason);
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->whereHas('employee', function ($q) use ($s) {
                $q->where('name', 'like', $s)->orWhere('email', 'like', $s);
            });
        }

        $data = $query->orderByDesc('exit_date')->get();

        $columns = [
            'Emp ID', 'Employee Name', 'Branch', 'Department', 'Exit Type',
            'Exit Reason', 'Resignation Date', 'Exit Date', 'Last Working Date',
            'Clearance Status', 'F&F Status', 'Status',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    optional($row->branch)->name ?? '-',
                    optional($row->department)->name ?? '-',
                    ucwords(str_replace('_', ' ', $row->exit_type)),
                    $row->exit_reason ?? '-',
                    $row->resignation_date ? $row->resignation_date->format('Y-m-d') : '-',
                    $row->exit_date ? $row->exit_date->format('Y-m-d') : '-',
                    $row->last_working_date ? $row->last_working_date->format('Y-m-d') : '-',
                    ucwords(str_replace('_', ' ', $row->clearance_status)),
                    ucwords(str_replace('_', ' ', $row->fnf_status)),
                    ucwords(str_replace('_', ' ', $row->status)),
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attrition_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Exit Reasons report. Mirrors the workspace HRMS Exit Reasons Report
     * screen: employee exit records with branch/team/exit type/search filters,
     * KPI totals (total/voluntary/involuntary/unique reasons) and an exit
     * reason breakdown with counts + percentages.
     */
    public function exitReasonsReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = EmployeeExit::where('partner_id', $partnerId)->with(['employee', 'branch', 'department', 'creator']);

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('exit_type', $request->type);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($q) => $q->where('reporting_to', $teamVal));
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('exit_reason', 'like', $s)
                  ->orWhereHas('employee', fn ($eq) => $eq->where('name', 'like', $s));
            });
        }

        $allExits = (clone $query)->get();

        $totalExits = $allExits->count();
        $voluntaryCount = $allExits->where('exit_type', 'voluntary')->count();
        $involuntaryCount = $allExits->where('exit_type', 'involuntary')->count();

        $exitReasonsBreakdown = $allExits->groupBy('exit_reason')->map(function ($group) use ($totalExits) {
            return [
                'reason'     => $group->first()->exit_reason ?? 'Not Specified',
                'count'      => $group->count(),
                'percentage' => $totalExits > 0 ? round(($group->count() / $totalExits) * 100, 1) : 0,
            ];
        })->sortByDesc('count')->values()->toArray();

        $perPage = (int) $request->input('per_page', 10);
        $rows = $query->orderByDesc('exit_date')->paginate($perPage);

        $rows->through(function ($row) {
            return [
                'id'            => $row->id,
                'employee_id'   => $row->employee_id,
                'employee_name' => optional($row->employee)->name ?? 'Unassigned',
                'employee_code' => optional($row->employee)->employee_code,
                'branch_id'     => $row->branch_id,
                'branch_name'   => optional($row->branch)->name,
                'department'    => optional($row->department)->name,
                'exit_type'     => $row->exit_type,
                'exit_reason'   => $row->exit_reason,
                'exit_date'     => $row->exit_date ? $row->exit_date->format('Y-m-d') : null,
                'status'        => $row->status,
                'created_by'    => optional($row->creator)->name,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Exit reasons report fetched successfully.',
            'data'    => $rows,
            'summary' => [
                'total_exits'            => $totalExits,
                'voluntary_count'        => $voluntaryCount,
                'involuntary_count'      => $involuntaryCount,
                'unique_reasons'         => count($exitReasonsBreakdown),
                'exit_reasons_breakdown' => $exitReasonsBreakdown,
            ],
        ]);
    }

    /**
     * Export the exit reasons report as a CSV stream (mirrors the report table).
     */
    public function exitReasonsReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = EmployeeExit::where('partner_id', $partnerId)->with(['employee', 'branch', 'department']);

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('exit_type', $request->type);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($q) => $q->where('reporting_to', $teamVal));
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('exit_reason', 'like', $s)
                  ->orWhereHas('employee', fn ($eq) => $eq->where('name', 'like', $s));
            });
        }

        $data = $query->orderByDesc('exit_date')->get();

        $columns = ['Employee', 'Employee Code', 'Branch', 'Department', 'Exit Reason', 'Type', 'Exit Date', 'Status'];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    optional($row->employee)->name ?? 'Unassigned',
                    optional($row->employee)->employee_code ?? '',
                    optional($row->branch)->name ?? '-',
                    optional($row->department)->name ?? '-',
                    $row->exit_reason ?: '-',
                    ucwords(str_replace('_', ' ', $row->exit_type)),
                    $row->exit_date ? $row->exit_date->format('Y-m-d') : '-',
                    ucfirst($row->status),
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="exit_reasons_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Employee Cost report. Mirrors the workspace HRMS Employee Cost Report
     * screen: aggregates employee CTC / salary / overtime / incentive costs
     * for a given year, broken down by department, branch and month, plus an
     * employee-wise paginated cost breakdown. Filterable by year/branch/
     * department/search.
     */
    public function employeeCostReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $selectedYear = (int) ($request->input('year', Carbon::now()->year));

        $employeesQuery = User::with(['department', 'branch'])
            ->where(function ($q) use ($partnerId) {
                $q->where('parent_id', $partnerId)->orWhere('id', $partnerId);
            })
            ->whereIn('role', ['employee', 'manager']);

        if ($request->filled('branch_id') && $request->branch_id !== '') {
            $employeesQuery->where('branch_id', $request->branch_id);
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $employeesQuery->where('reporting_to', $teamVal);
        }

        if ($request->filled('department_id') && $request->department_id !== '') {
            $employeesQuery->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $employeesQuery->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                  ->orWhere('email', 'like', $s)
                  ->orWhere('employee_code', 'like', $s);
            });
        }

        $allFilteredEmployees = (clone $employeesQuery)->get();
        $filteredEmployeeIds = $allFilteredEmployees->pluck('id')->toArray();

        $salaryStructures = EmployeeSalaryStructure::whereIn('employee_id', $filteredEmployeeIds)
            ->get()
            ->keyBy('employee_id');

        $payrolls = EmployeePayroll::whereIn('employee_id', $filteredEmployeeIds)
            ->where('year', $selectedYear)
            ->get();

        $totalSalaryCost = (float) $payrolls->sum('net_pay');
        $totalOvertimeCost = (float) $payrolls->sum('bonuses');
        $totalIncentiveCost = (float) $payrolls->sum('commissions');
        $totalEmployeeCost = $totalSalaryCost + $totalOvertimeCost + $totalIncentiveCost;

        $totalEmployeeCount = count($filteredEmployeeIds);
        $totalAnnualCTC = $allFilteredEmployees->sum(function ($emp) use ($salaryStructures) {
            $st = $salaryStructures[$emp->id] ?? null;
            return $st ? (float) $st->annual_ctc : (float) (($emp->base_salary ?? 0) * 12);
        });
        $averageCTC = $totalEmployeeCount > 0 ? ($totalAnnualCTC / $totalEmployeeCount) : 0;

        $departments = Department::where('partner_id', $partnerId)->get();
        $deptCostBreakdown = [];
        foreach ($departments as $dept) {
            $deptEmpIds = $allFilteredEmployees->where('department_id', $dept->id)->pluck('id');
            $deptSalary = (float) $payrolls->whereIn('employee_id', $deptEmpIds)->sum('net_pay');
            $deptOT = (float) $payrolls->whereIn('employee_id', $deptEmpIds)->sum('bonuses');
            $deptInc = (float) $payrolls->whereIn('employee_id', $deptEmpIds)->sum('commissions');
            $deptTotal = $deptSalary + $deptOT + $deptInc;

            if ($deptEmpIds->count() > 0 || $deptTotal > 0) {
                $deptCostBreakdown[] = [
                    'department_id'  => $dept->id,
                    'department'     => $dept->name,
                    'employee_count' => $deptEmpIds->count(),
                    'salary_cost'    => $deptSalary,
                    'ot_cost'        => $deptOT,
                    'incentive_cost' => $deptInc,
                    'total_cost'     => $deptTotal,
                ];
            }
        }

        $branches = HrmsBranch::where('partner_id', $partnerId)->get();
        $branchCostBreakdown = [];
        foreach ($branches as $branch) {
            $branchEmpIds = $allFilteredEmployees->where('branch_id', $branch->id)->pluck('id');
            $branchSalary = (float) $payrolls->whereIn('employee_id', $branchEmpIds)->sum('net_pay');
            $branchOT = (float) $payrolls->whereIn('employee_id', $branchEmpIds)->sum('bonuses');
            $branchInc = (float) $payrolls->whereIn('employee_id', $branchEmpIds)->sum('commissions');
            $branchTotal = $branchSalary + $branchOT + $branchInc;

            if ($branchEmpIds->count() > 0 || $branchTotal > 0) {
                $branchCostBreakdown[] = [
                    'branch_id'     => $branch->id,
                    'branch'        => $branch->name,
                    'employee_count' => $branchEmpIds->count(),
                    'salary_cost'    => $branchSalary,
                    'ot_cost'        => $branchOT,
                    'incentive_cost' => $branchInc,
                    'total_cost'     => $branchTotal,
                ];
            }
        }

        $monthlyTrends = [];
        for ($m = 1; $m <= 12; $m++) {
            $mStr = sprintf('%02d', $m);
            $mPayrolls = $payrolls->filter(function ($p) use ($m, $mStr) {
                return (int) $p->month === $m || (string) $p->month === $mStr;
            });
            $monthlyTrends[] = [
                'month'          => Carbon::create($selectedYear, $m, 1)->format('M'),
                'salary_cost'    => (float) $mPayrolls->sum('net_pay'),
                'ot_cost'        => (float) $mPayrolls->sum('bonuses'),
                'incentive_cost' => (float) $mPayrolls->sum('commissions'),
                'total_cost'     => (float) ($mPayrolls->sum('net_pay') + $mPayrolls->sum('bonuses') + $mPayrolls->sum('commissions')),
            ];
        }

        $perPage = (int) $request->input('per_page', 10);
        $paginatedEmployees = $employeesQuery->orderBy('name')->paginate($perPage);

        $paginatedEmployees->through(function ($emp) use ($payrolls, $salaryStructures) {
            $empPayrolls = $payrolls->where('employee_id', $emp->id);
            $st = $salaryStructures[$emp->id] ?? null;
            $monthlySalary = $st ? (float) $st->monthly_ctc : (float) ($emp->base_salary ?? 0);
            $annualCTC = $st ? (float) $st->annual_ctc : ($monthlySalary * 12);
            $otCost = (float) $empPayrolls->sum('bonuses');
            $incentiveCost = (float) $empPayrolls->sum('commissions');
            $salaryCost = (float) $empPayrolls->sum('net_pay');
            $totalCost = $salaryCost + $otCost + $incentiveCost;

            return [
                'employee_id'    => $emp->id,
                'employee_code'  => $emp->employee_code,
                'employee_name'  => $emp->name,
                'department'     => optional($emp->department)->name,
                'branch'         => optional($emp->branch)->name,
                'monthly_salary' => round($monthlySalary, 2),
                'annual_ctc'     => round($annualCTC, 2),
                'salary_cost'    => round($salaryCost, 2),
                'ot_cost'        => round($otCost, 2),
                'incentive_cost' => round($incentiveCost, 2),
                'total_cost'     => round($totalCost > 0 ? $totalCost : $annualCTC, 2),
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Employee cost report fetched successfully.',
            'data'    => $paginatedEmployees,
            'summary' => [
                'total_employee_cost'  => round($totalEmployeeCost, 2),
                'total_salary_cost'    => round($totalSalaryCost, 2),
                'total_overtime_cost'  => round($totalOvertimeCost, 2),
                'total_incentive_cost' => round($totalIncentiveCost, 2),
                'total_annual_ctc'     => round($totalAnnualCTC, 2),
                'average_ctc'          => round($averageCTC, 2),
                'total_employee_count' => $totalEmployeeCount,
            ],
            'department_breakdown' => $deptCostBreakdown,
            'branch_breakdown'     => $branchCostBreakdown,
            'monthly_trends'       => $monthlyTrends,
        ]);
    }

    /**
     * Export the employee cost report (employee-wise breakdown) as a CSV stream.
     */
    public function employeeCostReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $selectedYear = (int) ($request->input('year', Carbon::now()->year));

        $employeesQuery = User::with(['department', 'branch'])
            ->where(function ($q) use ($partnerId) {
                $q->where('parent_id', $partnerId)->orWhere('id', $partnerId);
            })
            ->whereIn('role', ['employee', 'manager']);

        if ($request->filled('branch_id') && $request->branch_id !== '') {
            $employeesQuery->where('branch_id', $request->branch_id);
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $employeesQuery->where('reporting_to', $teamVal);
        }

        if ($request->filled('department_id') && $request->department_id !== '') {
            $employeesQuery->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $employeesQuery->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                  ->orWhere('email', 'like', $s)
                  ->orWhere('employee_code', 'like', $s);
            });
        }

        $allFilteredEmployees = $employeesQuery->get();
        $filteredEmployeeIds = $allFilteredEmployees->pluck('id')->toArray();

        $salaryStructures = EmployeeSalaryStructure::whereIn('employee_id', $filteredEmployeeIds)
            ->get()
            ->keyBy('employee_id');

        $payrolls = EmployeePayroll::whereIn('employee_id', $filteredEmployeeIds)
            ->where('year', $selectedYear)
            ->get();

        $columns = [
            'Emp ID', 'Employee Name', 'Department', 'Branch', 'Monthly Salary',
            'Annual CTC', 'Salary Cost', 'OT Cost', 'Incentive Cost', 'Total Cost',
        ];

        $callback = function () use ($allFilteredEmployees, $payrolls, $salaryStructures, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($allFilteredEmployees as $emp) {
                $empPayrolls = $payrolls->where('employee_id', $emp->id);
                $st = $salaryStructures[$emp->id] ?? null;
                $monthlySalary = $st ? (float) $st->monthly_ctc : (float) ($emp->base_salary ?? 0);
                $annualCTC = $st ? (float) $st->annual_ctc : ($monthlySalary * 12);
                $otCost = (float) $empPayrolls->sum('bonuses');
                $incentiveCost = (float) $empPayrolls->sum('commissions');
                $salaryCost = (float) $empPayrolls->sum('net_pay');
                $totalCost = $salaryCost + $otCost + $incentiveCost;

                fputcsv($file, [
                    $emp->employee_code ?? substr($emp->id, 0, 8),
                    $emp->name,
                    optional($emp->department)->name ?? '-',
                    optional($emp->branch)->name ?? '-',
                    round($monthlySalary, 2),
                    round($annualCTC, 2),
                    round($salaryCost, 2),
                    round($otCost, 2),
                    round($incentiveCost, 2),
                    round($totalCost > 0 ? $totalCost : $annualCTC, 2),
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employee_cost_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Training report. Mirrors the workspace HRMS Training Report screen.
     * Reports on employee training assignments (EmployeeTraining) under the
     * partner's training programs: KPI summary, monthly trends, department
     * breakdown, and a paginated assignment list. Filterable by program/
     * status/branch/department/result/search.
     */
    public function trainingReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = EmployeeTraining::with(['training', 'employee', 'department', 'branch'])
            ->whereHas('training', function ($q) use ($partnerId) {
                $q->where('partner_id', $partnerId);
            });

        if ($request->filled('training_id') && $request->training_id !== '') {
            $query->where('training_id', $request->training_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id') && $request->branch_id !== '') {
            $query->where(function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id)
                  ->orWhereHas('employee', fn ($eq) => $eq->where('branch_id', $request->branch_id));
            });
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($eq) => $eq->where('reporting_to', $teamVal));
        }

        if ($request->filled('department_id') && $request->department_id !== '') {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('result') && $request->result !== 'all') {
            $query->where('result', $request->result);
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->whereHas('employee', function ($eq) use ($s) {
                    $eq->where('name', 'like', $s)->orWhere('employee_code', 'like', $s);
                })->orWhereHas('training', function ($tq) use ($s) {
                    $tq->where('title', 'like', $s)->orWhere('trainer', 'like', $s);
                });
            });
        }

        $allFilteredAssignments = (clone $query)->get();

        $totalAssigned = $allFilteredAssignments->count();
        $totalCompleted = $allFilteredAssignments->where('status', 'completed')->count();
        $totalPending = $allFilteredAssignments->whereIn('status', ['assigned', 'in_progress'])->count();
        $avgAttendance = $totalAssigned > 0 ? round($allFilteredAssignments->avg('attendance_percentage'), 1) : 0;
        $completedRows = $allFilteredAssignments->where('status', 'completed');
        $avgAssessmentScore = $completedRows->count() > 0 ? round($completedRows->avg('assessment_score'), 1) : 0;

        $currentYear = (int) ($request->input('year', Carbon::now()->year));
        $monthlyTrends = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyAssigned = (clone $query)->whereYear('assigned_at', $currentYear)->whereMonth('assigned_at', $m)->count();
            $monthlyCompleted = (clone $query)->where('status', 'completed')->whereYear('assigned_at', $currentYear)->whereMonth('assigned_at', $m)->count();
            $monthlyTrends[] = [
                'month'     => Carbon::create($currentYear, $m, 1)->format('M'),
                'assigned'  => $monthlyAssigned,
                'completed' => $monthlyCompleted,
            ];
        }

        $departments = Department::where('partner_id', $partnerId)->orderBy('name')->get();
        $deptBreakdown = [];
        foreach ($departments as $dept) {
            $deptCompleted = $allFilteredAssignments->where('department_id', $dept->id)->where('status', 'completed')->count();
            if ($deptCompleted > 0) {
                $deptBreakdown[] = [
                    'department' => $dept->name,
                    'completed'  => $deptCompleted,
                ];
            }
        }

        $perPage = (int) $request->input('per_page', 10);
        $paginatedAssignments = $query->orderByDesc('created_at')->paginate($perPage);

        $paginatedAssignments->through(function ($row) {
            return [
                'id'                 => $row->id,
                'training_id'        => $row->training_id,
                'training_title'     => optional($row->training)->title,
                'trainer'            => optional($row->training)->trainer,
                'employee_id'        => $row->employee_id,
                'employee_code'      => optional($row->employee)->employee_code,
                'employee_name'      => optional($row->employee)->name,
                'department'         => optional($row->department)->name,
                'branch'             => optional($row->branch)->name,
                'status'             => $row->status,
                'result'             => $row->result,
                'attendance_percentage' => $row->attendance_percentage,
                'assessment_score'   => $row->assessment_score,
                'assigned_at'        => $row->assigned_at ? $row->assigned_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Training report fetched successfully.',
            'data'    => $paginatedAssignments,
            'summary' => [
                'total_assigned'     => $totalAssigned,
                'total_completed'    => $totalCompleted,
                'total_pending'      => $totalPending,
                'avg_attendance'     => $avgAttendance,
                'avg_assessment_score' => $avgAssessmentScore,
            ],
            'monthly_trends'  => $monthlyTrends,
            'department_breakdown' => $deptBreakdown,
        ]);
    }

    /**
     * Export the training report (assignment list) as a CSV stream.
     */
    public function trainingReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = EmployeeTraining::with(['training', 'employee', 'department', 'branch'])
            ->whereHas('training', function ($q) use ($partnerId) {
                $q->where('partner_id', $partnerId);
            });

        if ($request->filled('training_id') && $request->training_id !== '') {
            $query->where('training_id', $request->training_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id') && $request->branch_id !== '') {
            $query->where(function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id)
                  ->orWhereHas('employee', fn ($eq) => $eq->where('branch_id', $request->branch_id));
            });
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->whereHas('employee', fn ($eq) => $eq->where('reporting_to', $teamVal));
        }

        if ($request->filled('department_id') && $request->department_id !== '') {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('result') && $request->result !== 'all') {
            $query->where('result', $request->result);
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->whereHas('employee', function ($eq) use ($s) {
                    $eq->where('name', 'like', $s)->orWhere('employee_code', 'like', $s);
                })->orWhereHas('training', function ($tq) use ($s) {
                    $tq->where('title', 'like', $s)->orWhere('trainer', 'like', $s);
                });
            });
        }

        $data = $query->orderByDesc('created_at')->get();

        $columns = [
            'Emp ID', 'Employee Name', 'Training Program', 'Trainer', 'Department',
            'Branch', 'Status', 'Result', 'Attendance %', 'Assessment Score', 'Assigned At',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    optional($row->training)->title ?? '-',
                    optional($row->training)->trainer ?? '-',
                    optional($row->department)->name ?? '-',
                    optional($row->branch)->name ?? '-',
                    $row->status,
                    $row->result ?? '-',
                    $row->attendance_percentage ?? 0,
                    $row->assessment_score ?? 0,
                    $row->assigned_at ? $row->assigned_at->format('Y-m-d H:i:s') : '-',
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="training_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Asset report. Mirrors the workspace HRMS Asset Report screen: company
     * asset inventory with status distribution and category breakdown.
     * Filterable by category/status/branch/department/search.
     */
    public function assetReport(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = Asset::with(['branch', 'department', 'creator'])
            ->where('partner_id', $partnerId);

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id') && $request->branch_id !== '') {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('department_id') && $request->department_id !== '') {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                  ->orWhere('asset_code', 'like', $s)
                  ->orWhere('serial_number', 'like', $s)
                  ->orWhere('imei_number', 'like', $s);
            });
        }

        $allFilteredAssets = (clone $query)->get();

        $summary = [
            'total_assets'  => $allFilteredAssets->count(),
            'available'     => $allFilteredAssets->where('status', 'available')->count(),
            'issued'        => $allFilteredAssets->where('status', 'issued')->count(),
            'damaged'       => $allFilteredAssets->where('status', 'damaged')->count(),
            'total_cost'    => round($allFilteredAssets->sum('purchase_cost'), 2),
        ];

        $categoriesList = ['laptop', 'mobile', 'sim', 'id_card', 'other'];
        $categoryBreakdown = [];
        foreach ($categoriesList as $cat) {
            $catAssets = $allFilteredAssets->where('category', $cat);
            $categoryBreakdown[] = [
                'category'  => $cat,
                'label'     => strtoupper(str_replace('_', ' ', $cat)),
                'total'     => $catAssets->count(),
                'available' => $catAssets->where('status', 'available')->count(),
                'issued'    => $catAssets->where('status', 'issued')->count(),
                'damaged'   => $catAssets->where('status', 'damaged')->count(),
            ];
        }

        $perPage = (int) $request->input('per_page', 10);
        $paginatedAssets = $query->orderByDesc('id')->paginate($perPage);

        $paginatedAssets->through(function ($row) {
            return [
                'id'            => $row->id,
                'asset_code'    => $row->asset_code,
                'category'      => $row->category,
                'name'          => $row->name,
                'brand'         => $row->brand,
                'model'         => $row->model,
                'serial_number' => $row->serial_number,
                'imei_number'   => $row->imei_number,
                'purchase_date' => $row->purchase_date ? $row->purchase_date->format('Y-m-d') : null,
                'purchase_cost' => (float) $row->purchase_cost,
                'condition'     => $row->condition,
                'status'        => $row->status,
                'branch'        => optional($row->branch)->name,
                'department'    => optional($row->department)->name,
                'created_by'    => optional($row->creator)->name,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Asset report fetched successfully.',
            'data'    => $paginatedAssets,
            'summary' => $summary,
            'category_breakdown' => $categoryBreakdown,
        ]);
    }

    /**
     * Export the asset report as a CSV stream.
     */
    public function assetReportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $query = Asset::with(['branch', 'department', 'creator'])
            ->where('partner_id', $partnerId);

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id') && $request->branch_id !== '') {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('department_id') && $request->department_id !== '') {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                  ->orWhere('asset_code', 'like', $s)
                  ->orWhere('serial_number', 'like', $s)
                  ->orWhere('imei_number', 'like', $s);
            });
        }

        $data = $query->orderByDesc('id')->get();

        $columns = [
            'Asset Code', 'Category', 'Name', 'Brand', 'Model', 'Serial Number',
            'IMEI', 'Purchase Date', 'Purchase Cost', 'Condition', 'Status',
            'Branch', 'Department',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->asset_code,
                    ucwords(str_replace('_', ' ', $row->category)),
                    $row->name,
                    $row->brand ?? '-',
                    $row->model ?? '-',
                    $row->serial_number ?? '-',
                    $row->imei_number ?? '-',
                    $row->purchase_date ? $row->purchase_date->format('Y-m-d') : '-',
                    $row->purchase_cost,
                    $row->condition ?? '-',
                    ucwords(str_replace('_', ' ', $row->status)),
                    optional($row->branch)->name ?? '-',
                    optional($row->department)->name ?? '-',
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="asset_report.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream($callback, 200, $headers);
    }

    private function getPartnerId()
    {
        $user = auth('hrms_api')->user();
        return $user->role === 'employee' ? $user->parent_id : $user->id;
    }

    /**
     * Match a role by its display name, accounting for the partner-id
     * prefix/suffix that is stored in the roles table
     * (e.g. "12_manager" / "manager_12"). The incoming $roleName has no prefix.
     */
    private function roleNameWhere($query, string $roleName, $partnerId)
    {
        return $query->where('name', $partnerId . '_' . $roleName)
            ->orWhere('name', $roleName . '_' . $partnerId)
            ->orWhere('name', $roleName);
    }

    private function getTeamEmployeeIds($viewAnyPermission = null)
    {
        $user = auth('hrms_api')->user();

        if ($user->role === 'partner' || ($viewAnyPermission && $user->canAccess($viewAnyPermission))) {
            return User::where('parent_id', $this->getPartnerId())
                ->where('role', 'employee')
                ->pluck('id')
                ->toArray();
        }

        if ($viewAnyPermission) {
            $module = str_replace('_viewAny', '', $viewAnyPermission);

            if ($user->canAccess($module . '_viewTeam') || $user->canAccess($module . '_viewteam')) {
                return $user->getTeamIds();
            }

            if ($user->canAccess($module . '_viewOwn') || $user->canAccess($module . '_viewown')) {
                return [$user->id];
            }
        }

        return $user->getTeamIds();
    }
}
