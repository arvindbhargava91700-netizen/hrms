<?php

namespace App\Livewire\Admin\Reports\Hrms;

use App\Models\Department;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeSalaryStructure;
use App\Models\EmployeeTask;
use App\Models\HrmsBranch;
use App\Models\Lead;
use App\Models\LeadOrder;
use App\Models\PartnerSetting;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class PerformanceReport extends Component
{
    public string $search = '';

    public string $filterMonth;

    public string $filterDept = '';

    public string $branchId = '';

    public string $teamId = '';

    public string $sortBy = 'score_desc';

    public function mount(): void
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('performance_viewAny') || auth()->user()->canAccess('performance_viewTeam') || auth()->user()->canAccess('performance_viewOwn'), 403);
        $this->filterMonth = now()->format('Y-m');
    }

    public function updatedBranchId(): void
    {
        $this->teamId = '';
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->branchId = '';
        $this->teamId = '';
        $this->filterDept = '';
        $this->filterMonth = now()->format('Y-m');
        $this->sortBy = 'score_desc';
    }

    public function getReportDataProperty()
    {
        $period = Carbon::createFromFormat('Y-m', $this->filterMonth);
        $members = $this->visibleMembers();
        $memberIds = $members->pluck('id');
        $start = $period->copy()->startOfMonth();
        $end = $period->copy()->endOfMonth();

        $tasks = EmployeeTask::whereIn('employee_id', $memberIds)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("employee_id, COUNT(*) as total, SUM(status = 'completed') as completed")
            ->groupBy('employee_id')->get()->keyBy('employee_id');
        $attendance = EmployeeAttendance::whereIn('employee_id', $memberIds)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNotIn('status', ['absent', 'leave'])
            ->selectRaw("employee_id, COUNT(*) as days, SUM(CASE WHEN status = 'late' OR late_minutes > 0 THEN 1 ELSE 0 END) as late_marks")
            ->groupBy('employee_id')->get()->keyBy('employee_id');
        $merchants = Lead::whereIn('assigned_to', $memberIds)
            ->where('status', 'won')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('assigned_to, COUNT(*) as total')
            ->groupBy('assigned_to')->get()->keyBy('assigned_to');
        $targets = EmployeeSalaryStructure::whereIn('employee_id', $memberIds)
            ->get(['employee_id', 'merchant_target', 'monthly_target'])
            ->keyBy('employee_id');
        $monthlyAchieved = LeadOrder::whereIn('employee_id', $memberIds)
            ->whereBetween('created_at', [$start, $end])
            ->where(function ($query) {
                $query->where('target_credited', true)->orWhere('approval_status', 'completed');
            })
            ->selectRaw('employee_id, SUM(paid_amount) as achieved')
            ->groupBy('employee_id')->get()->keyBy('employee_id');

        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $perfAttendanceWeight = (int) (PartnerSetting::when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))->where('key', 'perf_attendance_weight')->value('value') ?? 25);
        $perfTasksWeight = (int) (PartnerSetting::when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))->where('key', 'perf_tasks_weight')->value('value') ?? 25);
        $perfMerchantWeight = (int) (PartnerSetting::when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))->where('key', 'perf_merchant_target_weight')->value('value') ?? 25);
        $perfMonthlyWeight = (int) (PartnerSetting::when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))->where('key', 'perf_monthly_target_weight')->value('value') ?? 25);

        $rawOverrides = PartnerSetting::when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))->where('key', 'perf_staff_overrides')->value('value');
        $staffOverrides = ! empty($rawOverrides) ? json_decode($rawOverrides, true) : [];

        $daysInMonth = $period->daysInMonth;
        $rows = $members->map(function (User $member) use ($tasks, $attendance, $merchants, $targets, $monthlyAchieved, $daysInMonth, $perfAttendanceWeight, $perfTasksWeight, $perfMerchantWeight, $perfMonthlyWeight, $staffOverrides) {
            $task = $tasks->get($member->id);
            $merchant = $merchants->get($member->id);
            $attendanceDays = (int) ($attendance->get($member->id)->days ?? 0);
            $lateMarks = (int) ($attendance->get($member->id)->late_marks ?? 0);
            $totalTasks = (int) ($task->total ?? 0);
            $completedTasks = (int) ($task->completed ?? 0);
            $attendancePercentage = $daysInMonth > 0 ? ($attendanceDays / $daysInMonth) * 100 : 0;
            $taskPercentage = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
            $merchantTarget = (float) ($targets->get($member->id)->merchant_target ?? 0);
            $monthlyTarget = (float) ($targets->get($member->id)->monthly_target ?? 0);
            $monthlyBusiness = (float) ($monthlyAchieved->get($member->id)->achieved ?? 0);
            $merchantAchieved = (int) ($merchant->total ?? 0);
            $merchantPercentage = $merchantTarget > 0 ? min(100, ($merchantAchieved / $merchantTarget) * 100) : 0;
            $monthlyPercentage = $monthlyTarget > 0 ? min(100, ($monthlyBusiness / $monthlyTarget) * 100) : 0;

            // Use custom staff override if set, otherwise fallback to company global default
            $attW = isset($staffOverrides[$member->id]['attendance']) ? (int) $staffOverrides[$member->id]['attendance'] : $perfAttendanceWeight;
            $taskW = isset($staffOverrides[$member->id]['tasks']) ? (int) $staffOverrides[$member->id]['tasks'] : $perfTasksWeight;
            $merchW = isset($staffOverrides[$member->id]['merchant']) ? (int) $staffOverrides[$member->id]['merchant'] : $perfMerchantWeight;
            $monthW = isset($staffOverrides[$member->id]['monthly']) ? (int) $staffOverrides[$member->id]['monthly'] : $perfMonthlyWeight;

            $attendanceScore = min($attW, round(($attendanceDays / max(1, $daysInMonth)) * $attW));
            $taskScore = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * $taskW) : 0;
            $merchantScore = round(($merchantPercentage / 100) * $merchW);
            $monthlyTargetScore = round(($monthlyPercentage / 100) * $monthW);
            $totalScore = $attendanceScore + $taskScore + $merchantScore + $monthlyTargetScore;

            return [
                'employee_id' => $member->id,
                'user' => $member,
                'name' => $member->name,
                'avatar_url' => $member->avatar_url ?? null,
                'employee_code' => $member->employee_code,
                'department' => $member->department?->name ?? 'N/A',
                'branch' => $member->branch?->name ?? 'Main Branch',
                'reporting_to' => $member->reportingTo?->name ?? 'Direct / None',
                'tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'task_percentage' => $taskPercentage,
                'attendance' => $attendanceDays,
                'attendance_percentage' => $attendancePercentage,
                'late_marks' => $lateMarks,
                'attendance_score' => $attendanceScore,
                'task_score' => $taskScore,
                'merchant_percentage' => $merchantPercentage,
                'merchant_score' => $merchantScore,
                'monthly_percentage' => $monthlyPercentage,
                'monthly_target_score' => $monthlyTargetScore,
                'total_score' => $totalScore,
                'grade' => $totalScore >= 90 ? 'A+' : ($totalScore >= 80 ? 'A' : ($totalScore >= 70 ? 'B+' : ($totalScore >= 60 ? 'B' : 'C'))),
                'days_in_month' => $daysInMonth,
                'merchants' => $merchantAchieved,
                'merchant_target' => $merchantTarget,
                'monthly_target' => $monthlyTarget,
                'monthly_achieved' => $monthlyBusiness,
                'att_weight' => $attW,
                'task_weight' => $taskW,
                'merch_weight' => $merchW,
                'month_weight' => $monthW,
                'is_custom' => isset($staffOverrides[$member->id]),
            ];
        });

        return match ($this->sortBy) {
            'score_asc' => $rows->sortBy('total_score')->values(),
            'name_asc' => $rows->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            default => $rows->sortByDesc('total_score')->values(),
        };
    }

    public function getBranchesProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        return HrmsBranch::when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))->where('status', 'active')->orderBy('name')->get();
    }

    public function getTeamsProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $reportingQuery = User::when($partnerId, fn ($q) => $q->where('parent_id', $partnerId))
            ->where('role', 'employee')
            ->whereNotNull('reporting_to');

        if ($this->branchId) {
            $reportingQuery->where('branch_id', $this->branchId);
        }

        $leadIds = $reportingQuery->pluck('reporting_to')->unique();

        if ($leadIds->isNotEmpty()) {
            return User::whereIn('id', $leadIds)->orderBy('name')->get();
        }

        return User::when($partnerId, fn ($q) => $q->where('parent_id', $partnerId))
            ->where('role', 'employee')
            ->whereHas('reportees')
            ->orderBy('name')
            ->get();
    }

    private function visibleMembers()
    {
        $user = auth()->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        if ($user->isPartner() || $user->canAccess('performance_viewAny')) {
            $query = User::when($partnerId, fn ($q) => $q->where('parent_id', $partnerId))->where('role', 'employee');
        } elseif ($user->canAccess('performance_viewBranch')) {
            $query = User::when($partnerId, fn ($q) => $q->where('parent_id', $partnerId))->where('role', 'employee')
                ->where('branch_id', $user->branch_id);
        } elseif ($user->canAccess('performance_viewTeam')) {
            $query = User::whereIn('id', $user->getTeamIds())->where('role', 'employee');
        } else {
            $query = User::where('id', $user->id);
        }

        if ($this->search !== '') {
            $search = '%'.$this->search.'%';
            $query->where(fn ($q) => $q->where('name', 'like', $search)
                ->orWhere('employee_code', 'like', $search)
                ->orWhere('email', 'like', $search));
        }

        if ($this->branchId !== '') {
            $query->where('branch_id', $this->branchId);
        }

        if ($this->teamId !== '') {
            $query->where('reporting_to', $this->teamId);
        }

        if ($this->filterDept !== '') {
            $query->where('department_id', $this->filterDept);
        }

        return $query->with(['department', 'branch', 'reportingTo'])->get();
    }

    public function render()
    {
        $rows = $this->reportData;

        return view('livewire.admin.reports.hrms.performance-report', [
            'rows' => $rows,
            'branches' => $this->branches,
            'teams' => $this->teams,
            'departments' => Department::where('partner_id', auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id)->get(),
            'totals' => [
                'tasks' => $rows->sum('tasks'),
                'attendance' => $rows->sum('attendance'),
                'merchants' => $rows->sum('merchants'),
                'merchant_target' => $rows->sum('merchant_target'),
            ],
        ])->layout('layouts.app', [
            'panelName' => 'HRMS Panel',
            'pageTitle' => 'Performance',
            'pageSubtitle' => 'Monthly employees performance overview',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
