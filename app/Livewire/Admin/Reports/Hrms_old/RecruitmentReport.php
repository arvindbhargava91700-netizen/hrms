<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use App\Models\Recruitment;
use App\Models\Department;
use Carbon\Carbon;
use Livewire\WithPagination;

class RecruitmentReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';
    public $partnerFilter = '';


    public $startDate;
    public $endDate;
    public $departmentId = '';
    public $stageFilter = '';
    public $statusFilter = '';
    public $search = '';

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updated()
    {
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $query = Recruitment::query();
        if ($this->partnerFilter) { $query->where('partner_id', $this->partnerFilter); }


        if ($this->departmentId) {
            $query->where('department_id', $this->departmentId);
        }

        if ($this->stageFilter) {
            $query->where('interview_stage', $this->stageFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('candidate_name', 'like', "%{$search}%")
                  ->orWhere('candidate_email', 'like', "%{$search}%")
                  ->orWhere('candidate_phone', 'like', "%{$search}%")
                  ->orWhere('job_title', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with(['department', 'creator'])->latest()->paginate(20);
    }

    public function getDepartmentsProperty()
    {
        return Department::query()->orderBy('name')->get();
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['department', 'creator'])->latest()->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=recruitment_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Position Title', 'Vacancies', 'Candidate Name', 'Candidate Email', 'Candidate Phone', 'Department', 'Interview Stage', 'Selection Status', 'Offer Status', 'Joining Status', 'Cost Per Hire', 'Interview Date'];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->job_title,
                    $row->vacancies_count,
                    $row->candidate_name,
                    $row->candidate_email ?? '-',
                    $row->candidate_phone ?? '-',
                    optional($row->department)->name ?? '-',
                    ucwords(str_replace('_', ' ', $row->interview_stage)),
                    ucwords(str_replace('_', ' ', $row->status)),
                    ucwords(str_replace('_', ' ', $row->offer_status)),
                    ucwords(str_replace('_', ' ', $row->joining_status)),
                    $row->cost_per_hire,
                    $row->interview_date ? $row->interview_date->format('Y-m-d') : '-'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.admin.reports.hrms.recruitment-report', [
            'reportData'  => $this->reportData,
            'departments' => $this->departments,
        ])->layout('layouts.app', [
            'panelName'    => 'Admin Panel',
            'pageTitle'    => 'Recruitment Report',
            'pageSubtitle' => 'Candidate Applications, Hiring Stages & Selection Outcomes Report',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
