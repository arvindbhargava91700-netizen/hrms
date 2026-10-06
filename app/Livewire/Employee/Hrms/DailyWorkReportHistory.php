<?php

namespace App\Livewire\Employee\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DailyWorkReport;

class DailyWorkReportHistory extends Component
{
    use WithPagination;

    public $statusFilter = '';
    public $dateFilter = '';
    
    public $viewingReportId = null;

    protected $paginationTheme = 'bootstrap';

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

    public function render()
    {
        $query = DailyWorkReport::with(['items', 'manager'])
            ->where('employee_id', auth()->id());

        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        if (!empty($this->dateFilter)) {
            $query->whereDate('report_date', $this->dateFilter);
        }

        $reports = $query->orderBy('report_date', 'desc')->paginate(10);

        $viewingReport = null;
        if ($this->viewingReportId) {
            $viewingReport = DailyWorkReport::with(['items.task', 'manager'])->where('employee_id', auth()->id())->find($this->viewingReportId);
        }

        return view('livewire.employee.hrms.daily-work-report-history', [
            'reports' => $reports,
            'viewingReport' => $viewingReport
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'My Daily Report History',
            'pageSubtitle' => 'View your past submitted work reports',
            'sidebarLinks' => view('partials.sidebar-employee'),
        ]);
    }
}
