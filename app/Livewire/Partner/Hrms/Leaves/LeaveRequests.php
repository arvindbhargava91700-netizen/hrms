<?php

namespace App\Livewire\Partner\Hrms\Leaves;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmployeeLeave;

class LeaveRequests extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('leave_viewOwn'), 403);
    }

    public function render()
    {
        $requests = EmployeeLeave::where('employee_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.partner.hrms.leaves.leave-requests', [
            'requests' => $requests
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'My Leave Requests',
            'pageSubtitle' => 'View the status of your leave applications',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
