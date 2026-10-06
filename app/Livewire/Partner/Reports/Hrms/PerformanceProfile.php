<?php

namespace App\Livewire\Partner\Reports\Hrms;

use Livewire\Component;
use App\Models\User;
use App\Models\EmployeeTask;
use App\Models\EmployeeAttendance;
use App\Models\LeadOrder;
use App\Models\Lead;
use App\Models\EmployeeSalaryStructure;
use App\Models\PartnerSetting;
use Carbon\Carbon;

class PerformanceProfile extends Component
{
    public $employeeId;
    public $year;
    
    public function mount($id)
    {
        $this->employeeId = $id;
        $this->year = date('Y');
        
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('performance_viewAny') ||
            auth()->user()->canAccess('performance_viewTeam') ||
            auth()->user()->canAccess('performance_viewOwn'),
            403
        );
    }
    
    public function getPerformanceDataProperty()
    {
        $employee = User::with(['department', 'designation'])->findOrFail($this->employeeId);
        
        $year = $this->year;
        
        $monthlyData = [];
        $totalYtdScore = 0;
        $monthsWithData = 0;
        
        // Fetch Salary Structure
        $salaryStructure = EmployeeSalaryStructure::where('employee_id', $this->employeeId)->first();
        $merchantTarget = (float)($salaryStructure->merchant_target ?? 0);
        $monthlyTarget = (float)($salaryStructure->monthly_target ?? 0);

        // Fetch Partner Performance Weights
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $globalAttendanceWeight = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_attendance_weight')->value('value') ?? 25);
        $globalTasksWeight = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_tasks_weight')->value('value') ?? 25);
        $globalMerchantWeight = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_merchant_target_weight')->value('value') ?? 25);
        $globalMonthlyWeight = (int) (PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_monthly_target_weight')->value('value') ?? 25);

        $rawOverrides = PartnerSetting::where('partner_id', $partnerId)->where('key', 'perf_staff_overrides')->value('value');
        $staffOverrides = !empty($rawOverrides) ? json_decode($rawOverrides, true) : [];

        // Check if this specific employee has custom weights
        $perfAttendanceWeight = isset($staffOverrides[$this->employeeId]['attendance']) ? (int)$staffOverrides[$this->employeeId]['attendance'] : $globalAttendanceWeight;
        $perfTasksWeight = isset($staffOverrides[$this->employeeId]['tasks']) ? (int)$staffOverrides[$this->employeeId]['tasks'] : $globalTasksWeight;
        $perfMerchantWeight = isset($staffOverrides[$this->employeeId]['merchant']) ? (int)$staffOverrides[$this->employeeId]['merchant'] : $globalMerchantWeight;
        $perfMonthlyWeight = isset($staffOverrides[$this->employeeId]['monthly']) ? (int)$staffOverrides[$this->employeeId]['monthly'] : $globalMonthlyWeight;

        for ($month = 1; $month <= 12; $month++) {
            $period = Carbon::create($year, $month, 1);
            $start = $period->copy()->startOfMonth();
            $end = $period->copy()->endOfMonth();
            
            // Only calculate up to the current month if in the current year
            if ($year == date('Y') && $month > date('n')) {
                $monthlyData[] = [
                    'month_name' => $period->format('M'),
                    'attendance_score' => '-',
                    'attendance_days' => '-',
                    'task_score' => '-',
                    'task_completed' => '-',
                    'merchant_score' => '-',
                    'merchant_details' => '-',
                    'monthly_score' => '-',
                    'monthly_details' => '-',
                    'rate_score' => '-',
                    'grade' => '-'
                ];
                continue;
            }

            // Tasks
            $taskData = EmployeeTask::where('employee_id', $this->employeeId)
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw("COUNT(*) as total, SUM(status = 'completed') as completed")
                ->first();
            $totalTasks = (int)($taskData->total ?? 0);
            $completedTasks = (int)($taskData->completed ?? 0);
            $taskScore = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * $perfTasksWeight) : 0;
            
            // Attendance
            $attendanceData = EmployeeAttendance::where('employee_id', $this->employeeId)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->whereNotIn('status', ['absent', 'leave'])
                ->selectRaw("COUNT(*) as days, SUM(CASE WHEN status = 'late' OR late_minutes > 0 THEN 1 ELSE 0 END) as late_marks")
                ->first();
            $attendanceDays = (int)($attendanceData->days ?? 0);
            $daysInMonth = $period->daysInMonth;
            $attendanceScore = min($perfAttendanceWeight, round(($attendanceDays / max(1, $daysInMonth)) * $perfAttendanceWeight));
            
            // Merchants
            $merchantAchieved = Lead::where('assigned_to', $this->employeeId)
                ->where('status', 'won')
                ->whereBetween('created_at', [$start, $end])
                ->count();
            $merchantPercentage = $merchantTarget > 0 ? min(100, ($merchantAchieved / $merchantTarget) * 100) : 0;
            $merchantScore = round(($merchantPercentage / 100) * $perfMerchantWeight);
            
            // Monthly Business
            $monthlyAchieved = LeadOrder::where('employee_id', $this->employeeId)
                ->whereBetween('created_at', [$start, $end])
                ->where(function ($query) {
                    $query->where('target_credited', true)->orWhere('approval_status', 'completed');
                })
                ->sum('paid_amount');
            $monthlyPercentage = $monthlyTarget > 0 ? min(100, ($monthlyAchieved / $monthlyTarget) * 100) : 0;
            $monthlyTargetScore = round(($monthlyPercentage / 100) * $perfMonthlyWeight);
            
            $rateScore = $attendanceScore + $taskScore + $merchantScore + $monthlyTargetScore;
            
            $grade = $rateScore >= 90 ? 'A+' : ($rateScore >= 80 ? 'A' : ($rateScore >= 70 ? 'B+' : ($rateScore >= 60 ? 'B' : 'C')));
            
            $monthlyData[] = [
                'month_name' => $period->format('M'),
                'attendance_score' => $attendanceScore,
                'attendance_days' => $attendanceDays . '/' . $daysInMonth,
                'task_score' => $taskScore,
                'task_completed' => $completedTasks . '/' . $totalTasks,
                'merchant_score' => $merchantScore,
                'merchant_details' => number_format($merchantAchieved) . '/' . number_format($merchantTarget),
                'monthly_score' => $monthlyTargetScore,
                'monthly_details' => '₹' . number_format($monthlyAchieved) . ' / ₹' . number_format($monthlyTarget),
                'rate_score' => $rateScore,
                'grade' => $grade
            ];
            
            $totalYtdScore += $rateScore;
            $monthsWithData++;
        }
        
        $avgScore = $monthsWithData > 0 ? round($totalYtdScore / $monthsWithData) : 0;
        $overallGrade = $avgScore >= 90 ? 'A+' : ($avgScore >= 80 ? 'A' : ($avgScore >= 70 ? 'B+' : ($avgScore >= 60 ? 'B' : 'C')));

        // YTD Stats
        $startOfYear = Carbon::create($year, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($year, 12, 31)->endOfDay();
        
        $ytdAttendance = EmployeeAttendance::where('employee_id', $this->employeeId)
            ->whereBetween('date', [$startOfYear->toDateString(), $endOfYear->toDateString()])
            ->selectRaw("SUM(status NOT IN ('absent', 'leave')) as present, SUM(CASE WHEN status = 'late' OR late_minutes > 0 THEN 1 ELSE 0 END) as late_marks, SUM(status = 'leave') as leaves")
            ->first();
            
        $ytdTasks = EmployeeTask::where('employee_id', $this->employeeId)
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->selectRaw("COUNT(*) as total, SUM(status = 'completed') as completed")
            ->first();
            
        return [
            'employee' => $employee,
            'monthly' => $monthlyData,
            'avgScore' => $avgScore,
            'overallGrade' => $overallGrade,
            'weights' => [
                'attendance' => $perfAttendanceWeight,
                'tasks' => $perfTasksWeight,
                'merchant' => $perfMerchantWeight,
                'monthly' => $perfMonthlyWeight,
            ],
            'isCustom' => isset($staffOverrides[$this->employeeId]),
            'stats' => [
                'total_present' => (int)($ytdAttendance->present ?? 0),
                'late_marks' => (int)($ytdAttendance->late_marks ?? 0),
                'tasks_completed' => (int)($ytdTasks->completed ?? 0),
                'total_tasks' => (int)($ytdTasks->total ?? 0),
                'leaves_taken' => (int)($ytdAttendance->leaves ?? 0),
            ]
        ];
    }

    public function render()
    {
        return view('livewire.partner.reports.hrms.performance-profile', $this->performanceData)
            ->layout('layouts.app', [
                'panelName' => 'HRMS Panel',
                'pageTitle' => 'Performance Profile',
                'pageSubtitle' => 'Employee performance history',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
