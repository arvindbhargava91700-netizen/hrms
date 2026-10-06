<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use App\Models\AdvancePayment;
use Illuminate\Support\Facades\Auth;

class AdvancePayments extends Component
{
    use HasPartnerId;

    public $advances = [];
    public $amount, $reason, $deduction_month;
    public $isRequestModalOpen = false;
    
    // For Approval
    public $isApprovalModalOpen = false;
    public $approvingAdvanceId = null;
    public $approvalStatus = 'approved';
    public $approvalRemarks = '';

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
        $this->loadData();
    }

    public function loadData()
    {
        $user = auth()->user();
        if (!$user->isPartner() && !$user->canAccess('payroll_viewAny') && !$user->canAccess('payroll_viewBranch') && !$user->canAccess('payroll_viewTeam')) {
            // Employee sees their own requests
            $this->advances = AdvancePayment::where('employee_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Partner/Manager sees requests for their allowed team/branch
            $allowedIds = $this->getTeamEmployeeIds('payroll_viewAny');
            $this->advances = AdvancePayment::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })
                ->whereIn('employee_id', $allowedIds)
                ->with('employee')
                ->orderBy('created_at', 'desc')
                ->get();
        }
    }

    public function createRequest()
    {
        abort_unless(auth()->user()->role === 'employee', 403);
        $this->reset(['amount', 'reason', 'deduction_month']);
        $this->isRequestModalOpen = true;
    }

    public function saveRequest()
    {
        abort_unless(auth()->user()->role === 'employee', 403);
        $this->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string',
            'deduction_month' => 'required|date_format:Y-m',
        ]);

        $parts = explode('-', $this->deduction_month);
        
        AdvancePayment::create([
            'partner_id' => $this->requirePartnerId(),
            'employee_id' => auth()->id(),
            'amount' => $this->amount,
            'reason' => $this->reason,
            'deduction_year' => $parts[0],
            'deduction_month' => $parts[1],
            'status' => 'pending',
        ]);

        $this->isRequestModalOpen = false;
        session()->flash('success', 'Advance payment request submitted successfully.');
        $this->loadData();
    }

    public function openApprovalModal($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('payroll_manage'), 403);
        $this->approvingAdvanceId = $id;
        $this->reset(['approvalStatus', 'approvalRemarks']);
        $this->approvalStatus = 'approved';
        $this->isApprovalModalOpen = true;
    }

    public function processApproval()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('payroll_manage'), 403);
        $this->validate([
            'approvalStatus' => 'required|in:approved,rejected',
            'approvalRemarks' => 'nullable|string',
        ]);

        $advance = AdvancePayment::findOrFail($this->approvingAdvanceId);
        $advance->update([
            'status' => $this->approvalStatus,
            'approved_by' => auth()->id(),
            'remarks' => $this->approvalRemarks,
        ]);

        $this->isApprovalModalOpen = false;
        session()->flash('success', 'Advance payment request processed successfully.');
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.partner.hrms.advance-payments')
            ->layout('layouts.app', [
                'panelName'    => 'HRMS Module',
                'pageTitle'    => 'Advance Payments',
                'pageSubtitle' => 'Manage salary advance requests',
                'sidebarLinks' => auth()->user()->role === 'employee' ? view('partials.sidebar-employee') : view('partials.sidebar-partner'),
            ]);
    }
}
