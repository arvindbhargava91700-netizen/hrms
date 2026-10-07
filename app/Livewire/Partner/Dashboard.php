<?php

namespace App\Livewire\Partner;

use App\Models\User;
use App\Models\Department;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeTask;
use App\Models\EmployeeLeave;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Dashboard extends Component 
{
    public function render()
    {
        $user = auth()->user();

        $stats = [];

        // Total Employees
        $stats['totalEmployees'] = User::where('role', 'employee')->count();
        
        // Departments & Branches
        $stats['totalDepartments'] = Department::count();
        $stats['totalBranches'] = \App\Models\HrmsBranch::count();
        
        // Today's Attendance
        $stats['attendanceToday'] = EmployeeAttendance::whereDate('date', today())
            ->whereNotNull('check_in')
            ->count();

        // On Leave Today
        $stats['onLeaveToday'] = EmployeeLeave::where('status', 'approved')
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->count();

        // Absent Today
        $stats['absentToday'] = max(0, $stats['totalEmployees'] - $stats['attendanceToday'] - $stats['onLeaveToday']);
            
        // Pending Leaves
        $stats['pendingLeaves'] = EmployeeLeave::where('status', 'pending')->count();

        // Tasks in Progress / Pending
        $stats['pendingTasks'] = EmployeeTask::whereIn('status', ['pending', 'in_progress'])->count();

        // Completed Tasks (This Month)
        $stats['completedTasksMonth'] = EmployeeTask::where('status', 'completed')
            ->whereMonth('created_at', today()->month)
            ->whereYear('created_at', today()->year)
            ->count();

        // Daily Work Reports (Today)
        $stats['dwrToday'] = \App\Models\DailyWorkReport::whereDate('report_date', today())->count();

        // Recruitment & Jobs
        $stats['activeJobPosts'] = \App\Models\JobPost::where('status', 'active')->count();
        $stats['totalCandidates'] = \App\Models\JobApplication::count();

        // Expenses & Advance Payments
        $stats['monthlyExpenses'] = \App\Models\Expense::whereMonth('date', today()->month)
            ->whereYear('date', today()->year)
            ->sum('amount');
        $stats['pendingAdvances'] = \App\Models\AdvancePayment::where('status', 'pending')->count();

        // Assets
        $stats['allocatedAssets'] = \App\Models\Asset::where('status', 'allocated')->count();
        $stats['totalAssets'] = \App\Models\Asset::count();

        // Active CRM Leads
        $stats['activeLeads'] = \App\Models\Lead::whereNotIn('status', ['lost', 'converted', 'closed', 'Lost', 'Converted', 'Closed'])->count();

        // ── Charts Data ────────────────────────────────────────────────
        
        // 1. Weekly Attendance Chart
        $attendanceLabels = [];
        $attendanceData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $attendanceLabels[] = $date->format('D');
            $attendanceData[] = EmployeeAttendance::whereDate('date', $date->format('Y-m-d'))
                ->whereNotNull('check_in')
                ->count();
        }
        $chartData['attendanceLabels'] = $attendanceLabels;
        $chartData['attendanceData'] = $attendanceData;

        // 2. Task Status Distribution
        $chartData['taskStatusData'] = [
            EmployeeTask::where('status', 'completed')->count(),
            EmployeeTask::where('status', 'in_progress')->count(),
            EmployeeTask::where('status', 'pending')->count(),
        ];

        // 3. Recent Activities (Tasks & Leaves)
        $recentTasks = EmployeeTask::with('employee')
            ->latest()
            ->limit(5)
            ->get();
            
        $recentLeaves = EmployeeLeave::with('employee')
            ->latest()
            ->limit(5)
            ->get();

        return view('livewire.partner.dashboard', compact('stats', 'chartData', 'recentTasks', 'recentLeaves'))
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'HRMS Overview',
                'pageSubtitle' => 'Premium Dashboard for ' . $user->name,
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
