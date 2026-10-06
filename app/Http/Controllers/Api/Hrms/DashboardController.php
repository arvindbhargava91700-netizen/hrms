<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeTask;
use App\Models\EmployeeLeave;
use App\Models\Notice;
use App\Models\User;
use App\Models\Department;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\LeadOrder;
use App\Models\EmployeeSalaryStructure;
use App\Services\CommissionService;
use App\Models\Holiday;
use App\Models\PartnerSetting;
use App\Models\PipelineStage;
use App\Models\DepartmentBranchHead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        // Partner is either the user themselves (if they are a partner) or their parent
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        
        $stats = [];
        $activeNotices = collect();
        $deptStats = [];

        // ── Department Performance ──────────────────────────────────────────
        $departments = collect();
        if ($user->canAccess('department_viewany')) {
            $departments = Department::where('partner_id', $partnerId)->with(['branchHeads.head', 'employees'])->get();
        } elseif ($user->canAccess('department_viewteam')) {
            $departments = Department::where('partner_id', $partnerId)
                ->where(function ($q) use ($user) {
                    $q->whereHas('branchHeads', function($q2) use ($user) {
                        $q2->where('head_id', $user->id);
                    })->orWhere('id', $user->department_id);
                })->with(['branchHeads.head', 'employees'])->get();
        }

        foreach ($departments as $dept) {
            $empIds    = $dept->employees->pluck('id');
            $staffCount = $empIds->count();

            $attendanceToday = EmployeeAttendance::whereIn('employee_id', $empIds)
                ->whereDate('date', today())
                ->whereNotNull('check_in')
                ->count();

            $pendingLeaves = EmployeeLeave::whereIn('employee_id', $empIds)
                ->where('status', 'pending')
                ->count();

            $pendingTasks = EmployeeTask::whereIn('employee_id', $empIds)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();

            $deptStats[] = [
                'id'              => $dept->id,
                'name'            => $dept->name,
                'head'            => $dept->branchHeads->first() && $dept->branchHeads->first()->head ? $dept->branchHeads->first()->head->name : 'N/A',
                'staffCount'      => $staffCount,
                'attendanceToday' => $attendanceToday,
                'pendingLeaves'   => $pendingLeaves,
                'pendingTasks'    => $pendingTasks,
            ];
        }
        $stats['departmentPerformance'] = $deptStats;

        // ── My Personal Stats ───────────────────────────────────────────────
        $stats['myAttendanceThisMonth'] = EmployeeAttendance::where('employee_id', $user->id)
            ->whereMonth('date', today()->month)
            ->whereNotNull('check_in')
            ->count();

        // Weekly attendance for chart
        $weeklyAttendance = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i)->format('Y-m-d');
            $att  = EmployeeAttendance::where('employee_id', $user->id)->whereDate('date', $date)->first();
            $hours = 0;
            if ($att && $att->check_in && $att->check_out) {
                $hours = Carbon::parse($att->check_in)->diffInHours(Carbon::parse($att->check_out));
            }
            $weeklyAttendance->push([
                'date'  => today()->subDays($i)->format('D'),
                'hours' => $hours,
            ]);
        }
        $stats['weeklyAttendance'] = $weeklyAttendance;

        $stats['myPendingLeaves'] = EmployeeLeave::where('employee_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $todayDate = today()->format('Y-m-d');
        $myAttendanceToday = EmployeeAttendance::where('employee_id', $user->id)
            ->whereDate('date', $todayDate)
            ->first();
        $stats['myAttendanceToday'] = $myAttendanceToday;

        $leave = EmployeeLeave::where('employee_id', $user->id)
            ->whereDate('start_date', '<=', $todayDate)
            ->whereDate('end_date', '>=', $todayDate)
            ->where('status', 'approved')
            ->first();
        $stats['myLeaveToday'] = $leave;

        // --- NEW LOGIC (Shift, Leaves, Holidays) ---
        $stats['myShift'] = $user->shift;
        $stats['isLate'] = false;
        $stats['lateMinutes'] = 0;
        if ($stats['myShift'] && $myAttendanceToday && $myAttendanceToday->check_in) {
            $shiftStartTime = Carbon::parse($myAttendanceToday->date->format('Y-m-d') . ' ' . $stats['myShift']->start_time);
            $checkInTime = Carbon::parse($myAttendanceToday->check_in);
            if ($checkInTime->gt($shiftStartTime)) {
                $lateMinutes = $shiftStartTime->diffInMinutes($checkInTime, false);
                $graceTime = $stats['myShift']->late_tolerance_minutes ?? 0;
                if ($lateMinutes > $graceTime) {
                    $stats['isLate'] = true;
                    $stats['lateMinutes'] = max(0, $lateMinutes);
                }
            }
        }

        $teamIds = $user->getTeamIds();
        if ($user->canAccess('leave_viewany')) {
            $stats['upcomingLeaves'] = EmployeeLeave::with('employee')->whereHas('employee', fn($q) => $q->where('parent_id', $partnerId))->whereDate('start_date', '>=', clone today())->where('status', 'approved')->orderBy('start_date')->limit(5)->get();
            $stats['leaveLabel'] = 'Company Upcoming Leaves';
        } elseif ($user->canAccess('leave_viewteam') || $user->canAccess('attendance_viewteam')) {
            $stats['upcomingLeaves'] = EmployeeLeave::with('employee')->whereIn('employee_id', $teamIds)->whereDate('start_date', '>=', clone today())->where('status', 'approved')->orderBy('start_date')->limit(5)->get();
            $stats['leaveLabel'] = 'Team Upcoming Leaves';
        } else {
            $stats['upcomingLeaves'] = EmployeeLeave::where('employee_id', $user->id)->whereDate('start_date', '>', clone today())->where('status', 'approved')->orderBy('start_date')->limit(5)->get();
            $stats['leaveLabel'] = 'My Upcoming Leaves';
        }

        $stats['upcomingHolidays'] = Holiday::where('partner_id', $partnerId)->whereDate('date', '>=', clone today())->orderBy('date')->limit(5)->get();

        // Banner Status Flags
        $stats['isHoliday'] = false;
        $stats['holidayName'] = '';
        $stats['isWeekOff'] = false;
        $stats['isFullLeave'] = false;
        $stats['isHalfLeave'] = false;
        $stats['isAbsent'] = false;

        $holiday = Holiday::where('partner_id', $partnerId)
            ->whereDate('date', $todayDate)
            ->first();
        if ($holiday) {
            $stats['isHoliday'] = true;
            $stats['holidayName'] = $holiday->name;
        }

        if ($leave) {
            if ($leave->type === 'half_day') {
                $stats['isHalfLeave'] = true;
            } else {
                $stats['isFullLeave'] = true;
            }
        }

        $shiftWeekOff = ($user->shift_id && $user->shift && $user->shift->week_off_days) ? json_decode($user->shift->week_off_days, true) : null;
        $globalWeekOff = PartnerSetting::where('partner_id', $partnerId)->where('key', 'week_off_days')->value('value');
        $globalWeekOff = $globalWeekOff ? json_decode($globalWeekOff, true) : ['Sunday'];
        $weekOffDays = $shiftWeekOff ?? $globalWeekOff;
        $stats['weekOffDays'] = $weekOffDays;
        $todayDayName = today()->format('l');

        if (is_array($weekOffDays) && in_array($todayDayName, $weekOffDays)) {
            $stats['isWeekOff'] = true;
        }

        if ($myAttendanceToday && $myAttendanceToday->status === 'absent') {
            $stats['isAbsent'] = true;
        }

        // ── Tasks ───────────────────────────────────────────────────────────
        if ($user->canAccess('task_viewany')) {
            $stats['myPendingTasks'] = EmployeeTask::whereHas('employee', fn ($q) => $q->where('parent_id', $partnerId))->whereIn('status', ['pending', 'in_progress'])->count();
            $stats['taskLabel']      = 'Total Tasks';
        } elseif ($user->canAccess('task_viewteam')) {
            $stats['myPendingTasks'] = EmployeeTask::whereIn('employee_id', $teamIds)->whereIn('status', ['pending', 'in_progress'])->count();
            $stats['taskLabel']      = 'Team Tasks';
        } else {
            $stats['myPendingTasks'] = EmployeeTask::where('employee_id', $user->id)->whereIn('status', ['pending', 'in_progress'])->count();
            $stats['taskLabel']      = 'My Tasks';
        }

        // ── Expenses ────────────────────────────────────────────────────────
        if ($user->canAccess('expense_viewany')) {
            $stats['myExpensesAmount'] = Expense::whereHas('employee', fn ($q) => $q->where('parent_id', $partnerId))->whereMonth('date', today()->month)->sum('amount');
            $stats['expenseLabel']     = 'Total Expenses (Month)';
        } elseif ($user->canAccess('expense_viewteam')) {
            $stats['myExpensesAmount'] = Expense::whereIn('employee_id', $teamIds)->whereMonth('date', today()->month)->sum('amount');
            $stats['expenseLabel']     = 'Team Expenses (Month)';
        } else {
            $stats['myExpensesAmount'] = Expense::where('employee_id', $user->id)->whereMonth('date', today()->month)->sum('amount');
            $stats['expenseLabel']     = 'My Expenses (Month)';
        }

        // ── Notices ─────────────────────────────────────────────────────────
        $activeNotices = Notice::where(function ($q) use ($partnerId) {
            $q->whereNull('user_id')
              ->orWhereHas('user', function ($q2) use ($partnerId) {
                  $q2->where('id', $partnerId)->orWhere('parent_id', $partnerId);
              });
        })->where(function ($q) use ($user) {
            // Global notices are visible to everyone (within the partner)
            $q->where('type', 'global')
              // The creator of the notice can always see it
              ->orWhere('user_id', $user->id)
              // Targeted employee notices
              ->orWhere(function ($subQ) use ($user) {
                  $subQ->whereIn('type', ['personal', 'employee'])
                       ->where(function($pQ) use ($user) {
                           $pQ->whereJsonContains('user_ids', (string)$user->id)
                              ->orWhereJsonContains('user_ids', (int)$user->id);
                       });
              });
                
            // Targeted branch notices
            if ($user->branch_id) {
                $q->orWhere(function($subQ) use ($user) {
                    $subQ->where('type', 'branch')
                         ->where(function($pQ) use ($user) {
                             $pQ->whereJsonContains('branch_ids', (string)$user->branch_id)
                                ->orWhereJsonContains('branch_ids', (int)$user->branch_id);
                         });
                });
            }
            
            // Targeted department notices
            if ($user->department_id) {
                $q->orWhere(function($subQ) use ($user) {
                    $subQ->where('type', 'department')
                         ->where(function($pQ) use ($user) {
                             $pQ->whereJsonContains('department_ids', (string)$user->department_id)
                                ->orWhereJsonContains('department_ids', (int)$user->department_id);
                         });
                });
            }
        })->where(function ($q) {
            $q->whereNull('start_date')
                ->orWhere(function ($subQ) {
                    $subQ->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today());
                });
        })->latest()->get();
        $stats['recentNotices'] = $activeNotices;

        // ── Recent Tasks ────────────────────────────────────────────────────
        $stats['recentTasks'] = EmployeeTask::where('employee_id', $user->id)->latest()->limit(10)->get();

        // ── HR / Team Stats (manager-level) ─────────────────────────────────
        if ($user->canAccess('staff_viewany') || $user->canAccess('attendance_viewany')) {
            $stats['hrmsStaff']          = User::where('parent_id', $partnerId)->where('role', 'employee')->count();
            $stats['hrmsDepts']          = Department::where('partner_id', $partnerId)->count();
            $stats['hrmsAttendanceToday'] = EmployeeAttendance::whereHas('employee', fn ($q) => $q->where('parent_id', $partnerId))
                ->whereDate('date', today())->count();
            $stats['hrmsPendingLeaves']  = EmployeeLeave::whereHas('employee', fn ($q) => $q->where('parent_id', $partnerId))->where('status', 'pending')->count();
            $stats['hrmsPendingTasks']   = EmployeeTask::whereHas('employee', fn ($q) => $q->where('parent_id', $partnerId))->whereIn('status', ['pending', 'in_progress'])->count();

            $stats['recentTasks'] = EmployeeTask::with('employee')
                ->whereHas('employee', fn ($q) => $q->where('parent_id', $partnerId))
                ->latest()->limit(10)->get();

        } elseif ($user->canAccess('attendance_viewteam') || $user->canAccess('staff_viewteam') || $user->canAccess('task_viewteam')) {
            // Manager with team view
            $stats['hrmsStaff']          = count($teamIds);
            $stats['hrmsAttendanceToday'] = EmployeeAttendance::whereIn('employee_id', $teamIds)->whereDate('date', today())->count();
            $stats['hrmsPendingLeaves']  = EmployeeLeave::whereIn('employee_id', $teamIds)->where('status', 'pending')->count();
            $stats['hrmsPendingTasks']   = EmployeeTask::whereIn('employee_id', $teamIds)->whereIn('status', ['pending', 'in_progress'])->count();
            $stats['teamLabel']          = 'My Team';

            $stats['recentTasks'] = EmployeeTask::with('employee')
                ->whereIn('employee_id', $teamIds)
                ->latest()->limit(10)->get();
        }

        // ── Team Commission/Earnings Summary (for managers) ──────────────────
        $hasViewAny = $user->canAccess('staff_viewany') || $user->canAccess('attendance_viewany') || $user->isPartner() || $user->canAccess('performance_viewany');
        $hasViewTeam = $user->canAccess('staff_viewBranch') || $user->canAccess('staff_viewteam') || $user->canAccess('attendance_viewBranch') || $user->canAccess('attendance_viewteam') || $user->canAccess('performance_viewBranch') || $user->canAccess('performance_viewteam');

        if ($hasViewAny || ($hasViewTeam && count($teamIds) > 0)) {
            $commissionSvc      = new CommissionService();
            $teamTotalCommission = 0;
            $teamTotalBusiness   = 0;
            $teamTotalTarget     = 0;
            
            if ($hasViewAny) {
                $teamMembers = User::where('parent_id', $partnerId)->where('role', 'employee')->get();
            } else {
                $teamMembers = User::whereIn('id', $teamIds)->get();
            }
            
            $achieversList       = [];

            foreach ($teamMembers as $member) {
                $mData = $commissionSvc->calculateEmployeeCommission($member, now()->month, now()->year);
                $teamTotalCommission += $mData['total_commission'] ?? 0;
                $teamTotalBusiness   += $mData['new_business'] ?? 0;
                $teamTotalTarget     += $mData['target_required'] ?? 0;
                
                $business = $mData['new_business'] ?? 0;
                $target = $mData['target_required'] ?? 0;
                $pct = $target > 0 ? ($business / $target) * 100 : ($business > 0 ? 100 : 0);

                if ($business > 0) {
                    $achieversList[] = [
                        'user' => clone $member, // Clone to avoid side effects
                        'business' => $business,
                        'target' => $target,
                        'pct' => $pct,
                    ];
                }
            }
            
            // Sort by percentage descending, then by business descending
            usort($achieversList, function($a, $b) {
                if ($a['pct'] == $b['pct']) {
                    return $b['business'] <=> $a['business'];
                }
                return $b['pct'] <=> $a['pct'];
            });

            $stats['teamTotalCommission'] = $teamTotalCommission;
            $stats['teamTotalBusiness']   = $teamTotalBusiness;
            $stats['teamTotalTarget']     = $teamTotalTarget;
            $stats['topAchievers']        = collect(array_slice($achieversList, 0, 3));
        } else {
            $stats['topAchievers'] = collect();
        }

        // ── CRM & Sales Stats ───────────────────────────────────────────────
        if ($user->canAccess('lead_viewany') || $user->canAccess('lead_viewown') || $user->isPartner() || $user->canAccess('lead_viewteam')) {
            if ($user->canAccess('lead_viewany')) {
                $stats['totalLeads']        = Lead::where('partner_id', $partnerId)->count();
                $stats['wonLeads']          = Lead::where('partner_id', $partnerId)->where('status', 'won')->count();
                $stats['totalOrders']       = LeadOrder::where('partner_id', $partnerId)->count();
                $stats['totalPipelineValue'] = LeadOrder::where('partner_id', $partnerId)->sum('total_amount');
            } elseif ($user->canAccess('lead_viewteam')) {
                $stats['totalLeads']        = Lead::whereIn('assigned_to', $teamIds)->count();
                $stats['wonLeads']          = Lead::whereIn('assigned_to', $teamIds)->where('status', 'won')->count();
                $stats['totalOrders']       = LeadOrder::whereHas('lead', fn ($q) => $q->whereIn('assigned_to', $teamIds))->count();
                $stats['totalPipelineValue'] = LeadOrder::whereHas('lead', fn ($q) => $q->whereIn('assigned_to', $teamIds))->sum('total_amount');
            } else {
                $stats['totalLeads']        = Lead::where('assigned_to', $user->id)->count();
                $stats['wonLeads']          = Lead::where('assigned_to', $user->id)->where('status', 'won')->count();
                $stats['totalOrders']       = LeadOrder::whereHas('lead', fn ($q) => $q->where('assigned_to', $user->id))->count();
                $stats['totalPipelineValue'] = LeadOrder::whereHas('lead', fn ($q) => $q->where('assigned_to', $user->id))->sum('total_amount');
            }

            // My personal target (commission only — no salary)
            $salaryStructure = EmployeeSalaryStructure::where('employee_id', $user->id)->first();
            
            // Merchant target logic
            $stats['target'] = $salaryStructure ? (int) $salaryStructure->merchant_target : 0;
            $stats['won_leads'] = $stats['wonLeads'] ?? 0;
            $stats['percentage'] = $stats['target'] > 0 ? min(100, ($stats['won_leads'] / $stats['target']) * 100) : 0;

            if ($salaryStructure && $salaryStructure->monthly_target > 0) {
                $commissionSvc = new CommissionService();
                $commData = $commissionSvc->calculateEmployeeCommission($user, now()->month, now()->year);

                $businessAchieved = $commData['new_business'] ?? 0;
                $monthlyTarget    = (float) $salaryStructure->monthly_target;
                $targetPct        = $monthlyTarget > 0 ? min(100, ($businessAchieved / $monthlyTarget) * 100) : 0;

                $stats['myTarget'] = [
                    'monthly_target'    => $monthlyTarget,
                    'business_achieved' => $businessAchieved,
                    'recovery_business' => $commData['recovery_business'] ?? 0,
                    'target_pct'        => $targetPct,
                    'target_achieved'   => $businessAchieved >= $monthlyTarget,
                    // Commission only — NO salary
                    'est_commission'    => ($commData['base_commission'] ?? 0) + ($commData['recovery_commission'] ?? 0),
                    'commission_pct'    => $salaryStructure->commission_percent ?? 0,
                    'recovery_pct'      => $salaryStructure->recovery_percent ?? 0,
                ];
            } else {
                $stats['myTarget'] = null;
            }

            // Recent Leads & Orders
            if ($user->canAccess('lead_viewany')) {
                $stats['recentLeads']  = Lead::where('partner_id', $partnerId)->latest()->limit(5)->get();
                $stats['recentOrders'] = LeadOrder::where('partner_id', $partnerId)->with('lead')->latest()->limit(5)->get();
            } elseif ($user->canAccess('lead_viewteam')) {
                $stats['recentLeads']  = Lead::whereIn('assigned_to', $teamIds)->latest()->limit(5)->get();
                $stats['recentOrders'] = LeadOrder::whereHas('lead', fn ($q) => $q->whereIn('assigned_to', $teamIds))->with('lead')->latest()->limit(5)->get();
            } else {
                $stats['recentLeads']  = Lead::where('assigned_to', $user->id)->latest()->limit(5)->get();
                $stats['recentOrders'] = LeadOrder::whereHas('lead', fn ($q) => $q->where('assigned_to', $user->id))->with('lead')->latest()->limit(5)->get();
            }
        }

        // ── Pipeline Approval Queue ─────────────────────────────────────────
        $stats['pipelineQueues'] = [];
        if ($user->canAccess('leadorder_approve') || $user->isPartner()) {
            $pipelineStages = PipelineStage::where('partner_id', $partnerId)->orderBy('order_index')->get();
            $myStageIds     = [];

            if ($user->isPartner()) {
                $myStageIds = $pipelineStages->pluck('id')->toArray();
            } else {
                $departmentId = $user->department_id;
                $headedDepts  = DepartmentBranchHead::where('head_id', $user->id)->pluck('department_id')->toArray();
                $myDepts      = array_merge([$departmentId], $headedDepts);
                $myStageIds   = $pipelineStages->whereIn('department_id', $myDepts)->pluck('id')->toArray();
            }

            $icons  = ['bi-person-check', 'bi-currency-dollar', 'bi-file-earmark-text', 'bi-bank', 'bi-gear'];
            $colors = ['primary', 'success', 'info', 'warning', 'secondary'];

            foreach ($pipelineStages as $index => $stage) {
                if (in_array($stage->id, $myStageIds)) {
                    $stats['pipelineQueues']['stage_' . $stage->id] = [
                        'label' => 'Pending ' . $stage->name,
                        'count' => LeadOrder::where('partner_id', $partnerId)->where('current_stage_id', $stage->id)->where('approval_status', 'pending')->count(),
                        'icon'  => $icons[$index % count($icons)],
                        'color' => $colors[$index % count($colors)],
                    ];
                }
            }

            if ($user->hasRole($partnerId . '_Recovery') || $user->isPartner()) {
                $stats['pipelineQueues']['recovery'] = [
                    'label' => 'Pending Recovery',
                    'count' => LeadOrder::where('partner_id', $partnerId)->where('remaining_balance', '>', 0)->where('approval_status', 'completed')->count(),
                    'icon'  => 'bi-shield-check',
                    'color' => 'danger',
                ];
            }
        }

        // ── Analytics & Charts (Task & Performance) ──────────────────────────
        $chartData = [];
        $taskBaseQuery = EmployeeTask::query();
        if ($user->canAccess('task_viewany') || $user->isPartner()) {
            $taskBaseQuery->whereHas('employee', fn ($q) => $q->where('parent_id', $partnerId));
        } elseif ($user->canAccess('task_viewteam')) {
            $taskBaseQuery->whereIn('employee_id', $teamIds);
        } else {
            $taskBaseQuery->where('employee_id', $user->id);
        }

        $chartData['taskStatus'] = [
            $taskBaseQuery->clone()->where('status', 'completed')->count(),
            $taskBaseQuery->clone()->where('status', 'pending')->count(),
            $taskBaseQuery->clone()->where('status', 'in_progress')->count()
        ];

        // Monthly Task Completion (Last 12 Months)
        $monthlyTasks = $taskBaseQuery->clone()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthlyLabels = [];
        $monthlyCounts = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthKey = now()->subMonths($i)->format('Y-m');
            $monthlyLabels[] = now()->subMonths($i)->format('M Y');
            $monthlyCounts[] = $monthlyTasks[$monthKey]->count ?? 0;
        }
        $chartData['monthlyLabels'] = $monthlyLabels;
        $chartData['monthlyTasks'] = $monthlyCounts;

        // Top Employees by Task Completion
        $chartData['topEmployeesLabels'] = [];
        $chartData['topEmployeesTasks'] = [];
        
        if ($user->canAccess('task_viewany') || $user->canAccess('task_viewteam') || $user->isPartner()) {
            $topEmpStats = $taskBaseQuery->clone()
                ->where('status', 'completed')
                ->select('employee_id', DB::raw('COUNT(*) as total_completed'))
                ->groupBy('employee_id')
                ->orderByDesc('total_completed')
                ->limit(5)
                ->get();
                
            if ($topEmpStats->isNotEmpty()) {
                $empIdsToFetch = $topEmpStats->pluck('employee_id')->toArray();
                $emps = User::whereIn('id', $empIdsToFetch)->get()->keyBy('id');
                
                foreach ($topEmpStats as $stat) {
                    if (isset($emps[$stat->employee_id])) {
                        $chartData['topEmployeesLabels'][] = $emps[$stat->employee_id]->name;
                        $chartData['topEmployeesTasks'][] = $stat->total_completed;
                    }
                }
            }
        }
        
        $stats['chartData'] = $chartData;

        return response()->json([
            'status' => 'success',
            'data'   => $stats,
        ]);
    }
}

