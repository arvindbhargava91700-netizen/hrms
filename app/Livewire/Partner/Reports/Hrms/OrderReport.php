<?php

namespace App\Livewire\Partner\Reports\Hrms;

use Livewire\Component;
use App\Models\LeadOrder;
use App\Models\User;
use App\Models\HrmsBranch;
use Carbon\Carbon;
use Livewire\WithPagination;

class OrderReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $branchId = '';
    public $teamId = '';
    public $employeeId = '';
    public $startDate = '';
    public $endDate = '';
    public $status = '';

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

    public function updatedStatus()
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
        $this->status = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        
        $query = LeadOrder::where('partner_id', $partnerId);

        if ($this->branchId) {
            $query->where(function($q) {
                $q->whereHas('employee', function($sub) {
                    $sub->where('branch_id', $this->branchId);
                })->orWhereHas('lead.assignedTo', function($sub) {
                    $sub->where('branch_id', $this->branchId);
                });
            });
        }

        if ($this->teamId) {
            $query->where(function($q) {
                $q->whereHas('employee', function($sub) {
                    $sub->where('reporting_to', $this->teamId);
                })->orWhereHas('lead.assignedTo', function($sub) {
                    $sub->where('reporting_to', $this->teamId);
                });
            });
        }
        
        if ($this->employeeId) {
            $query->where(function($q) {
                $q->where('employee_id', $this->employeeId)
                  ->orWhereHas('lead', function($sub) {
                      $sub->where('assigned_to', $this->employeeId);
                  });
            });
        }
        
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        }
        
        if ($this->status) {
            $query->where('approval_status', $this->status);
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with(['lead', 'lead.assignedTo.branch', 'lead.assignedTo.reportingTo', 'employee.branch', 'employee.reportingTo', 'currentStage'])->latest()->paginate(20);
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
        $data = $this->getBaseQuery()->with(['lead', 'lead.assignedTo.branch', 'lead.assignedTo.reportingTo', 'employee.branch', 'employee.reportingTo', 'currentStage'])->latest()->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=order_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Lead Name', 'Mobile', 'Emp ID', 'Assigned To / Employee', 'Branch', 'Team / Reporting Manager', 'Current Stage', 'Total Amount', 'Discount', 'Final Amount', 'Paid Amount', 'Remaining Balance', 'Payment Status', 'Approval Status', 'Created At'];
        
        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                $emp = $row->employee ?? optional($row->lead)->assignedTo;
                fputcsv($file, [
                    optional($row->lead)->customer_name ?? '-',
                    optional($row->lead)->customer_mobile ?? '-',
                    optional($emp)->employee_code ?? (optional($emp)->id ? substr(optional($emp)->id, 0, 8) : ''),
                    optional($emp)->name ?? 'Unassigned',
                    optional(optional($emp)->branch)->name ?: ($emp ? 'Main Branch' : '-'),
                    optional(optional($emp)->reportingTo)->name ?: ($emp ? 'Direct / None' : '-'),
                    optional($row->currentStage)->name ?? '-',
                    $row->total_amount,
                    $row->discount,
                    $row->final_amount,
                    $row->paid_amount,
                    $row->remaining_balance,
                    $row->payment_status,
                    $row->approval_status,
                    $row->created_at->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.partner.reports.hrms.order-report', [
            'reportData' => $this->reportData,
            'branches'   => $this->branches,
            'teams'      => $this->teams,
            'employees'  => $this->employees
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Panel',
            'pageTitle'    => 'Order Report',
            'pageSubtitle' => 'View lead order performance',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
