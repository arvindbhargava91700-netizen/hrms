<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DailyWorkReport;
use Carbon\Carbon;

class DailyWorkReports extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $dateFilter = '';
    
    public $viewingReportId = null;

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
    }

    public function viewReport($id)
    {
        $this->viewingReportId = $id;
    }

    public function closeReport()
    {
        $this->viewingReportId = null;
    }

    public function getPartnerId()
    {
        return auth()->user()->parent_id ?? auth()->id();
    }

    public function approveReport($id)
    {
        $report = DailyWorkReport::findOrFail($id);
        
        $user = auth()->user();
        if ($user->role === 'employee') {
            // Manager approval
            $report->update([
                'status' => 'manager_approved',
                'manager_id' => $user->id,
            ]);
        } else {
            // Partner approval
            $report->update([
                'status' => 'partner_approved',
            ]);
        }

        session()->flash('message', 'Report approved successfully.');
        $this->closeReport();
    }

    public function rejectReport($id)
    {
        $report = DailyWorkReport::findOrFail($id);
        $report->update([
            'status' => 'rejected',
        ]);
        
        session()->flash('error', 'Report rejected.');
        $this->closeReport();
    }

    public function render()
    {
        $query = DailyWorkReport::with(['employee', 'items'])
            ->where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } });

        $user = auth()->user();

        // If the user is an employee, they are acting as a Manager, so they only see reports from users reporting to them
        if ($user->role === 'employee') {
            $query->whereHas('employee', function ($q) use ($user) {
                $q->where('reporting_to', $user->id);
            })->whereIn('status', ['submitted', 'manager_approved', 'partner_approved', 'rejected']);
        } else {
            // Partner sees all submitted or approved reports, plus they can approve reports straight from 'submitted' if there is no manager.
            $query->whereIn('status', ['submitted', 'manager_approved', 'partner_approved', 'rejected']);
        }

        if (!empty($this->search)) {
            $query->whereHas('employee', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('employee_code', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->dateFilter)) {
            $query->whereDate('report_date', $this->dateFilter);
        }

        $reports = $query->orderBy('report_date', 'desc')->paginate(10);

        $viewingReport = null;
        if ($this->viewingReportId) {
            $viewingReport = DailyWorkReport::with(['employee', 'items.task', 'manager'])->find($this->viewingReportId);
        }

        $layout = $user->role === 'employee' ? 'layouts.app' : 'layouts.app';
        $sidebar = $user->role === 'employee' ? 'partials.sidebar-employee' : 'partials.sidebar-partner';

        return view('livewire.partner.hrms.daily-work-reports', [
            'reports' => $reports,
            'viewingReport' => $viewingReport
        ])->layout($layout, [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'Daily Work Reports',
            'pageSubtitle' => 'Manage and review daily work reports',
            'sidebarLinks' => view($sidebar),
        ]);
    }
}
