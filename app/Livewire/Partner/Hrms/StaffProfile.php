<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use App\Models\User;
use App\Models\Lead;
use App\Models\EmployeeTask;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;

class StaffProfile extends Component
{
    use HasPartnerId, Traits\HasHrmsFilters;
    
    public $employeeId;
    public $employee;
    public $leads = [];
    public $tasks = [];
    public $expenses = [];
    public $orders = [];
    public $visits = [];
    public $metrics = [];
    public $salaryStructure;
    public $leaveBalances = [];
    public $leaveRequests = [];

    public function mount($id)
    {
        $this->employeeId = $id;
        $partnerId = $this->getPartnerId();
        
        $this->employee = User::with(['department', 'manager', 'branch', 'shift', 'roles'])
            ->where('id', $id)
            ->firstOrFail();
            
        // Check permissions
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('staff_viewAny') ||
            auth()->user()->canAccess('staff_viewBranch') ||
            auth()->user()->canAccess('staff_viewTeam') ||
            auth()->user()->canAccess('staff_viewOwn'),
            403
        );
        
        // Scope Check (If not partner, they must have access to THIS specific employee via HRMS filters)
        if (!auth()->user()->isPartner() && !in_array($id, $this->getTeamEmployeeIds('staff_viewAny'))) {
            abort(403, 'Unauthorized access to this profile.');
        }
        
        // Fetch related records
        $this->leads = Lead::where('assigned_to', $id)->latest()->take(10)->get();
        $this->tasks = EmployeeTask::where('employee_id', $id)->latest()->take(10)->get();
        $this->expenses = Expense::with('categoryRelation')->where('employee_id', $id)->latest()->take(10)->get();
        
        $this->orders = \App\Models\LeadOrder::with('lead')->where('employee_id', $id)->latest()->take(10)->get();
        $this->visits = \App\Models\CustomerVisit::with('lead')->where('employee_id', $id)->latest()->take(10)->get();
        
        $commissionService = new \App\Services\CommissionService();
        $this->metrics = $commissionService->calculateEmployeeCommission($this->employee, date('m'), date('Y'));
        
        // Salary Structure
        $this->salaryStructure = \App\Models\EmployeeSalaryStructure::where('employee_id', $id)->first();
        
        // Leaves
        $this->leaveRequests = \App\Models\EmployeeLeave::with('leaveCategory')->where('employee_id', $id)->latest()->take(10)->get();
        
        // Calculate leave balances
        $categories = \App\Models\LeaveCategory::where('status', 1)->get();
        
        $currentYear = date('Y');
        foreach ($categories as $category) {
            $used = \App\Models\EmployeeLeave::where('employee_id', $id)
                ->where('leave_category_id', $category->id)
                ->where('status', 'approved')
                ->whereYear('start_date', $currentYear)
                ->get()
                ->sum(function($leave) {
                    $start = \Carbon\Carbon::parse($leave->start_date);
                    $end = \Carbon\Carbon::parse($leave->end_date);
                    return $start->diffInDays($end) + 1;
                });
                
            $this->leaveBalances[] = [
                'category' => $category->name,
                'total' => $category->days,
                'used' => $used,
                'remaining' => $category->days - $used
            ];
        }
    }

    public function render()
    {
        return view('livewire.partner.hrms.staff-profile')
            ->layout('layouts.app', [
                'panelName'    => 'HRMS Module',
                'pageTitle'    => 'Staff Profile',
                'pageSubtitle' => 'View employee details and activities',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
