<?php

namespace App\Livewire\Employee\Hrms;

use Livewire\Component;
use App\Models\DailyWorkReport as ReportModel;
use App\Models\DailyWorkItem;
use App\Models\EmployeeTask;
use Carbon\Carbon;

class DailyWorkReport extends Component
{
    public $reportDate;
    public $report;
    public $summary = '';
    
    // Work Item Form
    public $title;
    public $details;
    public $category = 'Other';
    public $status = 'Completed';
    public $priority = 'Medium';
    public $timeSpentHours = 0;
    public $timeSpentMinutes = 0;
    public $linkedTaskId = null;
    
    public $availableTasks = [];
    public $categories = ['Development', 'Meeting', 'Design', 'Testing', 'Docs', 'Support', 'Training', 'Sales', 'Other'];
    public $statuses = ['Completed', 'In Progress', 'Pending', 'Blocked'];
    public $priorities = ['Low', 'Medium', 'High'];

    protected $rules = [
        'title' => 'required|string|max:255',
        'details' => 'nullable|string',
        'category' => 'required|string',
        'status' => 'required|string',
        'priority' => 'required|string',
        'timeSpentHours' => 'required|integer|min:0',
        'timeSpentMinutes' => 'required|integer|min:0|max:59',
        'linkedTaskId' => 'nullable|exists:employee_tasks,id',
    ];

    public function mount()
    {
        $this->reportDate = Carbon::today()->format('Y-m-d');
        $this->loadReport();
        $this->loadAvailableTasks();
    }

    public function updatedReportDate()
    {
        $this->loadReport();
    }

    public function loadAvailableTasks()
    {
        $this->availableTasks = EmployeeTask::where('employee_id', auth()->id())
            ->whereIn('status', ['pending', 'in_progress'])
            ->get();
    }

    public function loadReport()
    {
        $this->report = ReportModel::firstOrCreate(
            [
                'employee_id' => auth()->id(),
                'report_date' => $this->reportDate,
            ],
            [
                'partner_id' => auth()->user()->parent_id ?? auth()->id(),
                'manager_id' => auth()->user()->reporting_to,
                'status' => 'draft',
                'summary' => '',
            ]
        );
        $this->summary = $this->report->summary;
    }

    public function saveSummary()
    {
        $this->report->update(['summary' => $this->summary]);
        session()->flash('message', 'Summary saved.');
    }

    public function addItem()
    {
        $this->validate();

        DailyWorkItem::create([
            'daily_work_report_id' => $this->report->id,
            'task_id' => $this->linkedTaskId ?: null,
            'title' => $this->title,
            'details' => $this->details,
            'category' => $this->category,
            'status' => $this->status,
            'priority' => $this->priority,
            'time_spent_hours' => $this->timeSpentHours,
            'time_spent_minutes' => $this->timeSpentMinutes,
        ]);

        $this->resetItemForm();
        $this->loadReport();
        session()->flash('item_message', 'Work item added successfully.');
    }

    public function deleteItem($id)
    {
        DailyWorkItem::where('id', $id)->where('daily_work_report_id', $this->report->id)->delete();
        $this->loadReport();
    }

    private function resetItemForm()
    {
        $this->title = '';
        $this->details = '';
        $this->category = 'Other';
        $this->status = 'Completed';
        $this->priority = 'Medium';
        $this->timeSpentHours = 0;
        $this->timeSpentMinutes = 0;
        $this->linkedTaskId = null;
        $this->resetErrorBag();
    }

    public function submitReport()
    {
        if ($this->report->items()->count() === 0) {
            session()->flash('error', 'Please add at least one work item before submitting.');
            return;
        }

        $this->report->update([
            'summary' => $this->summary,
            'status' => 'submitted',
        ]);
        
        session()->flash('message', 'Report submitted successfully.');
    }

    public function render()
    {
        return view('livewire.employee.hrms.daily-work-report', [
            'items' => $this->report->items()->latest()->get()
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'My Daily Work Report',
            'pageSubtitle' => 'Home > Daily Report',
            'sidebarLinks' => view('partials.sidebar-employee'),
        ]);
    }
}
