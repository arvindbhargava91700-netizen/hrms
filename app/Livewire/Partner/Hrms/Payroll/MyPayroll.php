<?php

namespace App\Livewire\Partner\Hrms\Payroll;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmployeePayroll;

class MyPayroll extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('payroll_viewOwn'), 403);
    }

    public function render()
    {
        $payrolls = EmployeePayroll::where('employee_id', auth()->id())
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(12);

        return view('livewire.partner.hrms.payroll.my-payroll', [
            'payrolls' => $payrolls
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'My Payslips',
            'pageSubtitle' => 'View and download your monthly salary slips',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
