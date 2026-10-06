<?php

namespace App\Livewire\Partner\Reports\Hrms;

use Livewire\Component;
use App\Models\LeadOrder;
use App\Models\User;
use App\Models\Department;
use App\Models\HrmsBranch;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Livewire\WithPagination;

class RecoveryReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $search = '';
    public $branchId = '';
    public $teamId = '';
    public $employeeId = '';
    public $departmentId = '';
    public $roleName = '';
    public $paymentStatus = '';

    public function updatingSearch()
    {
        $this->resetPage();
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

    public function updatedDepartmentId()
    {
        $this->resetPage();
    }

    public function updatedRoleName()
    {
        $this->resetPage();
    }

    public function updatedPaymentStatus()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->branchId = '';
        $this->teamId = '';
        $this->employeeId = '';
        $this->departmentId = '';
        $this->roleName = '';
        $this->paymentStatus = '';
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        
        $query = LeadOrder::query()
            ->where('partner_id', $partnerId)
            ->where('remaining_balance', '>', 0);

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

        if ($this->search) {
            $search = $this->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('lead', function($sub) use ($search) {
                    $sub->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_mobile', 'like', "%{$search}%");
                })->orWhereHas('employee', function($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('lead.assignedTo', function($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        if ($this->departmentId) {
            $query->where(function($q) {
                $q->whereHas('employee', function($sub) {
                    $sub->where('department_id', $this->departmentId);
                })->orWhereHas('lead.assignedTo', function($sub) {
                    $sub->where('department_id', $this->departmentId);
                });
            });
        }

        if ($this->roleName) {
            $query->where(function($q) {
                $q->whereHas('employee.roles', function($sub) {
                    $sub->where('name', $this->roleName);
                })->orWhereHas('lead.assignedTo.roles', function($sub) {
                    $sub->where('name', $this->roleName);
                });
            });
        }

        if ($this->paymentStatus) {
            $query->where('payment_status', $this->paymentStatus);
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()
            ->with(['lead.assignedTo.branch', 'lead.assignedTo.reportingTo', 'employee.branch', 'employee.reportingTo'])
            ->latest()
            ->paginate(20);
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

    public function getDepartmentsProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        return Department::where('partner_id', $partnerId)->orderBy('name')->get();
    }

    public function getRolesProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        return Role::where('name', 'like', '%' . $partnerId . '%')->get();
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['lead.assignedTo.branch', 'lead.assignedTo.reportingTo', 'employee.branch', 'employee.reportingTo'])->latest()->get();
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=recovery_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Order ID', 'Lead Name', 'Mobile', 'Emp ID', 'Assigned To / Employee', 'Branch', 'Team / Reporting Manager', 'Total Amount', 'Paid Amount', 'Remaining Balance', 'Payment Status'];
        
        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($data as $row) {
                $emp = $row->employee ?? optional($row->lead)->assignedTo;
                fputcsv($file, [
                    $row->id,
                    optional($row->lead)->customer_name ?? '-',
                    optional($row->lead)->customer_mobile ?? '-',
                    optional($emp)->employee_code ?? (optional($emp)->id ? substr(optional($emp)->id, 0, 8) : ''),
                    optional($emp)->name ?? 'Unassigned',
                    optional(optional($emp)->branch)->name ?: ($emp ? 'Main Branch' : '-'),
                    optional(optional($emp)->reportingTo)->name ?: ($emp ? 'Direct / None' : '-'),
                    $row->total_amount,
                    $row->paid_amount,
                    $row->remaining_balance,
                    $row->payment_status,
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.partner.reports.hrms.recovery-report', [
            'reportData'  => $this->reportData,
            'branches'    => $this->branches,
            'teams'       => $this->teams,
            'employees'   => $this->employees,
            'departments' => $this->departments,
            'roles'       => $this->roles,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Panel',
            'pageTitle'    => 'Recovery Report',
            'pageSubtitle' => 'Track outstanding payments, branch, and team recovery performance',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
