<?php

namespace App\Livewire\Partner\Hrms\Leaves;

use Livewire\Component;
use App\Models\EmployeeLeave;
use App\Models\LeaveCategory;
use App\Livewire\Partner\Hrms\HasPartnerId;
use Carbon\Carbon;

class LeaveBalance extends Component
{
    use HasPartnerId;

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('leave_viewOwn'), 403);
    }

    public function render()
    {
        $currentYear = date('Y');
        $partnerId = $this->getPartnerId();
        
        $categories = LeaveCategory::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('status', true)->get();
        
        $userLeaves = EmployeeLeave::where('employee_id', auth()->id())
            ->whereYear('start_date', $currentYear)
            ->get();
            
        $categoryBalances = [];
        $totalDaysTaken = 0;

        foreach ($categories as $cat) {
            $catLeaves = $userLeaves->filter(function ($leave) use ($cat) {
                return $leave->leave_category_id == $cat->id || strtolower($leave->type) == strtolower($cat->name);
            });

            $used = 0;
            foreach ($catLeaves->where('status', 'approved') as $leave) {
                $days = Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
                $used += $days;
                $totalDaysTaken += $days;
            }

            $pending = 0;
            foreach ($catLeaves->where('status', 'pending') as $leave) {
                $days = Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
                $pending += $days;
            }

            $rejected = 0;
            foreach ($catLeaves->where('status', 'rejected') as $leave) {
                $days = Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
                $rejected += $days;
            }

            $categoryBalances[] = [
                'id' => $cat->id,
                'name' => $cat->name,
                'total' => $cat->days,
                'used' => $used,
                'pending' => $pending,
                'rejected' => $rejected,
                'lop' => max(0, $used - $cat->days),
                'remaining' => max(0, $cat->days - $used),
            ];
        }

        return view('livewire.partner.hrms.leaves.leave-balance', [
            'currentYear' => $currentYear,
            'totalDaysTaken' => $totalDaysTaken,
            'categoryBalances' => $categoryBalances,
            'pendingRequests' => $userLeaves->where('status', 'pending')->count()
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'My Leave Balance',
            'pageSubtitle' => 'View your leave statistics for the year',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
