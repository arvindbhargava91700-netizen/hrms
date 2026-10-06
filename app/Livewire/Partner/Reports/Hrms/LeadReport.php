<?php

namespace App\Livewire\Partner\Reports\Hrms;

use Livewire\Component;
use App\Models\Lead;
use App\Models\User;
use App\Models\HrmsBranch;
use Carbon\Carbon;
use Livewire\WithPagination;

class LeadReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $startDate;
    public $endDate;
    public $branchId = '';
    public $teamId = '';
    public $employeeId = '';
    public $statusFilter = '';

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedBranchId()
    {
        $this->teamId = '';
        $this->employeeId = '';
        $this->resetPage();
    }

    public function updatedTeamId()
    {
        $this->employeeId = '';
        $this->resetPage();
    }

    public function updatedEmployeeId()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedStartDate()
    {
        $this->resetPage();
    }

    public function updatedEndDate()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->branchId = '';
        $this->teamId = '';
        $this->employeeId = '';
        $this->statusFilter = '';
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        
        $query = Lead::where('partner_id', $partnerId);

        if ($this->branchId) {
            $query->whereHas('assignedTo', function($q) {
                $q->where('branch_id', $this->branchId);
            });
        }

        if ($this->teamId) {
            $query->whereHas('assignedTo', function($q) {
                $q->where('reporting_to', $this->teamId);
            });
        }
        
        if ($this->employeeId) {
            $query->where('assigned_to', $this->employeeId);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with(['assignedTo.branch', 'assignedTo.reportingTo'])->latest()->paginate(20);
    }

    public function getBranchesProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        return HrmsBranch::where('partner_id', $partnerId)->where('status', 'active')->orderBy('name')->get();
    }

    public function getTeamsProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        
        $reportingQuery = User::where('parent_id', $partnerId)
            ->where('role', 'employee')
            ->whereNotNull('reporting_to');
            
        if ($this->branchId) {
            $reportingQuery->where('branch_id', $this->branchId);
        }
        
        $leadIds = $reportingQuery->pluck('reporting_to')->unique();

        if ($leadIds->isNotEmpty()) {
            return User::whereIn('id', $leadIds)->orderBy('name')->get();
        }

        return User::where('parent_id', $partnerId)
            ->where('role', 'employee')
            ->whereHas('reportees')
            ->orderBy('name')
            ->get();
    }

    public function getEmployeesProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $query = User::where('parent_id', $partnerId)->where('role', 'employee');

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        if ($this->teamId) {
            $query->where('reporting_to', $this->teamId);
        }

        return $query->orderBy('name')->get();
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['assignedTo.branch', 'assignedTo.reportingTo'])->latest()->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=lead_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Lead Name', 'Mobile', 'Emp ID', 'Assigned To', 'Branch', 'Team / Reporting Manager', 'Status', 'Created At'];
        
        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->customer_name ?? '-',
                    $row->customer_mobile ?? '-',
                    optional($row->assignedTo)->employee_code ?? (optional($row->assignedTo)->id ? substr(optional($row->assignedTo)->id, 0, 8) : ''),
                    optional($row->assignedTo)->name ?? 'Unassigned',
                    optional(optional($row->assignedTo)->branch)->name ?: ($row->assignedTo ? 'Main Branch' : '-'),
                    optional(optional($row->assignedTo)->reportingTo)->name ?: ($row->assignedTo ? 'Direct / None' : '-'),
                    $row->status,
                    $row->created_at->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.partner.reports.hrms.lead-report', [
            'reportData' => $this->reportData,
            'branches'   => $this->branches,
            'teams'      => $this->teams,
            'employees'  => $this->employees,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Panel',
            'pageTitle'    => 'Lead Report',
            'pageSubtitle' => 'View lead performance',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
