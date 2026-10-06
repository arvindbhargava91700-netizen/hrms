<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use App\Models\User;
use App\Models\EmployeeLeave;
use App\Models\LeaveCategory;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Leaves extends Component
{
    use HasPartnerId;
    public $leaves = [];
    public $staff = [];
    public $categories = [];
    
    public $isModalOpen = false;
    public $employeeId;
    public $startDate;
    public $endDate;
    public $leaveCategoryId;
    public $reason = '';
    
    public $viewMode = 'all';

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('leave_viewAny') ||
            auth()->user()->canAccess('leave_viewBranch') ||
            auth()->user()->canAccess('leave_viewTeam') ||
            auth()->user()->canAccess('leave_viewOwn'),
            403
        );
        $this->startDate = date('Y-m-d');
        $this->endDate = date('Y-m-d');
        $this->viewMode = request()->query('view', 'all');
        $this->loadData();
    }

    public function loadData()
    {
        $partnerId = $this->getPartnerId();
        $user = auth()->user();

        $this->categories = LeaveCategory::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('status', true)->get();

        if ($this->viewMode === 'all' && !$user->canAccess('leave_viewAny') && !$user->canAccess('leave_viewBranch') && !$user->canAccess('leave_viewTeam')) {
            $this->viewMode = 'my';
        }
        
        if ($this->viewMode === 'my' && !$user->canAccess('leave_viewOwn')) {
            $this->viewMode = 'all';
        }

        if ($this->viewMode === 'all' && ($user->canAccess('leave_viewAny') || $user->canAccess('leave_viewBranch') || $user->canAccess('leave_viewTeam'))) {
            $this->staff = User::whereIn('id', $this->getTeamEmployeeIds('leave_viewAny'))->get();
            $employeeIds = $this->staff->pluck('id')->toArray();
            
            $this->leaves = EmployeeLeave::whereIn('employee_id', $employeeIds)
                ->with(['employee', 'leaveCategory'])
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($this->viewMode === 'my' && $user->canAccess('leave_viewOwn')) {
            $this->staff = collect([$user]);
            $this->leaves = EmployeeLeave::where('employee_id', $user->id)
                ->with(['employee', 'leaveCategory'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $this->staff = collect([]);
            $this->leaves = collect([]);
        }
    }

    public function createLeave()
    {
        if (!auth()->user()->canAccess('leave_create')) {
            abort(403);
        }
        $this->reset(['employeeId', 'reason', 'leaveCategoryId']);
        if (!auth()->user()->canAccess('leave_viewAny')) {
            $this->employeeId = auth()->id();
        }
        $this->startDate = date('Y-m-d');
        $this->endDate = date('Y-m-d');
        if ($this->categories->isNotEmpty()) {
            $this->leaveCategoryId = $this->categories->first()->id;
        }
        $this->isModalOpen = true;
    }

    public function saveLeave()
    {
        abort_unless(auth()->user()->canAccess('leave_create'), 403);
        $this->validate([
            'employeeId' => 'required|exists:users,id',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'leaveCategoryId' => 'required|exists:leave_categories,id',
            'reason' => 'nullable|string|max:1000',
        ]);

        $category = LeaveCategory::findOrFail($this->leaveCategoryId);
        $requestedDays = Carbon::parse($this->startDate)->diffInDays(Carbon::parse($this->endDate)) + 1;

        // Check balance for this employee
        $usedDays = EmployeeLeave::where('employee_id', $this->employeeId)
            ->where('leave_category_id', $category->id)
            ->whereIn('status', ['approved', 'pending'])
            ->whereYear('start_date', date('Y'))
            ->get()
            ->sum(function ($leave) {
                return Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
            });

        $remainingBalance = max(0, $category->days - $usedDays);

        if (!$category->is_unlimited && $requestedDays > $remainingBalance) {
            $this->addError('leaveCategoryId', "Insufficient leave balance for {$category->name}. Available: {$remainingBalance} day(s), Requested: {$requestedDays} day(s).");
            return;
        }

        EmployeeLeave::create([
            'employee_id' => $this->employeeId,
            'leave_category_id' => $category->id,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'type' => $category->name,
            'status' => 'pending',
            'reason' => $this->reason,
        ]);

        $this->isModalOpen = false;
        session()->flash('success', 'Leave request created successfully.');
        $this->loadData();
    }
    
    public $isApprovalModalOpen = false;
    public $approvingLeaveId = null;
    public $approvalStatus = 'approved';
    public $approvalRemarks = '';

    public function openApprovalModal($leaveId)
    {
        if (!auth()->user()->canAccess('leave_status_update')) {
            abort(403);
        }
        $this->approvingLeaveId = $leaveId;
        $this->reset(['approvalStatus', 'approvalRemarks']);
        $this->approvalStatus = 'approved';
        $this->isApprovalModalOpen = true;
    }

    public function processApproval()
    {
        if (!auth()->user()->canAccess('leave_status_update')) {
            abort(403);
        }
        
        $this->validate([
            'approvalStatus' => 'required|in:approved,rejected',
            'approvalRemarks' => 'nullable|string|max:1000',
        ]);

        EmployeeLeave::findOrFail($this->approvingLeaveId)->update([
            'status' => $this->approvalStatus,
            'remarks' => $this->approvalRemarks,
        ]);
        
        $this->isApprovalModalOpen = false;
        $this->loadData();
        session()->flash('success', 'Leave status updated.');
    }

    
    public function deleteLeave($leaveId)
    {
        if (!auth()->user()->canAccess('leave_delete')) {
            abort(403);
        }
        EmployeeLeave::findOrFail($leaveId)->delete();
        $this->loadData();
        session()->flash('success', 'Leave deleted successfully.');
    }

    public function render()
    {
        return view('livewire.partner.hrms.leaves')
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Leave Management',
                'pageSubtitle' => 'Manage employee leave requests',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
