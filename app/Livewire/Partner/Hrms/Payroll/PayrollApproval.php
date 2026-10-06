<?php

namespace App\Livewire\Partner\Hrms\Payroll;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmployeePayroll;

class PayrollApproval extends Component
{
    use WithPagination, \App\Livewire\Partner\Hrms\HasPartnerId;
    
    protected $paginationTheme = 'bootstrap';

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
    }
    public function approvePayroll($payrollId)
    {
        $payroll = EmployeePayroll::findOrFail($payrollId);
        
        // Ensure this payroll belongs to an employee of this partner
        // Ensure this payroll belongs to an employee of this partner/manager
        if (!in_array($payroll->employee_id, $this->getTeamEmployeeIds('payroll_viewAny'))) {
            abort(403);
        }

        $payroll->update([
            'status' => 'paid'
        ]);
        
        session()->flash('message', "Payroll marked as Paid for {$payroll->employee->name}.");
    }

    public function render()
    {
        $pendingPayrolls = EmployeePayroll::whereIn('employee_id', $this->getTeamEmployeeIds('payroll_viewAny'))
            ->where('status', 'pending')
            ->with('employee')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(15);

        return view('livewire.partner.hrms.payroll.payroll-approval', [
            'pendingPayrolls' => $pendingPayrolls
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Payroll Approval',
            'pageSubtitle' => 'Finalize payrolls and mark them as Paid',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
