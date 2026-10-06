<?php

namespace App\Livewire\Partner\Hrms\Leaves;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmployeeLeave;
use App\Models\User;

use App\Livewire\Partner\Hrms\Traits\HasHrmsFilters;

class LeaveApprovals extends Component
{
    use WithPagination, \App\Livewire\Partner\Hrms\HasPartnerId, HasHrmsFilters;
    
    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('leave_viewAny') || auth()->user()->canAccess('leave_viewBranch') || auth()->user()->canAccess('leave_viewteam'), 403);
    }
    public function updateStatus($leaveId, $status)
    {
        if (!in_array($status, ['approved', 'rejected'])) {
            return;
        }

        $leave = EmployeeLeave::findOrFail($leaveId);
        
        // Ensure this leave belongs to an employee of this partner/manager
        if (!in_array($leave->employee_id, $this->getTeamEmployeeIds())) {
            abort(403);
        }

        $leave->update(['status' => $status]);
        
        if ($status === 'approved') {
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
        
        session()->flash('message', "Leave request {$status} successfully.");
    }

    public function render()
    {
        // Get all leaves for employees under this partner/manager
        $query = EmployeeLeave::with('employee');
        $query = $this->applyHrmsFilters($query, 'employee_id', 'leave_viewAny');
        $query->whereHas('employee', function ($q) {
            $q->where('status', 'active');
        });
        
        $requests = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.partner.hrms.leaves.leave-approvals', [
            'requests' => $requests
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Leave Approvals',
            'pageSubtitle' => 'Manage employee leave requests',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
