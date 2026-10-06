<?php

namespace App\Livewire\Partner\Hrms\Commission;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\CommissionPayout;
use Carbon\Carbon;

class CommissionHistory extends Component
{
    use WithPagination;
    use \App\Livewire\Partner\Hrms\HasPartnerId;
    
    protected $paginationTheme = 'bootstrap';
    
    public $month;
    public $year;
    public $employeeId = '';

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() || 
            auth()->user()->canAccess('payroll_viewAny') ||
            auth()->user()->canAccess('payroll_viewBranch') ||
            auth()->user()->canAccess('payroll_viewTeam') ||
            auth()->user()->canAccess('payroll_viewOwn'),
            403
        );
        $this->month = date('m');
        $this->year = date('Y');
    }
    
    public function updated($propertyName)
    {
        if (in_array($propertyName, ['month', 'year', 'employeeId'])) {
            $this->resetPage();
        }
    }

    public function markAsPaid($id)
    {
        $teamIds = $this->getTeamEmployeeIds('payroll_viewAny');
        $payout = CommissionPayout::whereIn('employee_id', $teamIds)->find($id);

        if ($payout) {
            $payout->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            session()->flash('success', "Commission payout for {$payout->employee->name} marked as Paid.");
        }
    }

    public function render()
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[str_pad($m, 2, '0', STR_PAD_LEFT)] = Carbon::create()->month($m)->format('F');
        }
        $years = range(2020, date('Y') + 1);
        
        $teamIds = $this->getTeamEmployeeIds('payroll_viewAny');
        $employees = User::whereIn('id', $teamIds)->orderBy('name')->get();

        $query = CommissionPayout::with(['employee', 'employee.designation'])
            ->whereIn('employee_id', $teamIds);
            
        if ($this->month) {
            $query->where('month', str_pad($this->month, 2, '0', STR_PAD_LEFT));
        }
        
        if ($this->year) {
            $query->where('year', $this->year);
        }
        
        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }
        
        $payouts = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('livewire.partner.hrms.commission.commission-history', [
            'payouts' => $payouts,
            'months' => $months,
            'years' => $years,
            'employees' => $employees,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Commission History',
            'pageSubtitle' => 'View processed commission records for your team',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
