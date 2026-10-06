<?php

namespace App\Livewire\Partner\Reports\Hrms;

use Livewire\Component;
use App\Models\Notice;
use App\Models\User;
use App\Models\Department;
use App\Models\HrmsBranch;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Livewire\WithPagination;

class NoticeReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $search = '';
    public $branchId = '';
    public $teamId = '';
    public $employeeId = '';
    public $departmentId = '';
    public $type = '';
    public $startDate = '';
    public $endDate = '';

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

    public function updatedType()
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
        $this->search = '';
        $this->branchId = '';
        $this->teamId = '';
        $this->employeeId = '';
        $this->departmentId = '';
        $this->type = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        
        $query = Notice::query()->where('user_id', $partnerId);

        if ($this->branchId) {
            $branchUserIds = User::where('parent_id', $partnerId)
                ->where('branch_id', $this->branchId)
                ->pluck('id')
                ->toArray();

            $branchId = $this->branchId;
            $query->where(function ($q) use ($branchId, $branchUserIds) {
                $q->whereJsonContains('branch_ids', (int) $branchId)
                  ->orWhereJsonContains('branch_ids', (string) $branchId);

                if (!empty($branchUserIds)) {
                    $q->orWhere(function ($sub) use ($branchUserIds) {
                        foreach ($branchUserIds as $uId) {
                            $sub->orWhereJsonContains('user_ids', (int) $uId)
                                ->orWhereJsonContains('user_ids', (string) $uId);
                        }
                    });
                }
            });
        }

        if ($this->teamId) {
            $teamUserIds = User::where('parent_id', $partnerId)
                ->where('reporting_to', $this->teamId)
                ->pluck('id')
                ->push((int) $this->teamId)
                ->toArray();

            $query->where(function ($q) use ($teamUserIds) {
                foreach ($teamUserIds as $uId) {
                    $q->orWhereJsonContains('user_ids', (int) $uId)
                      ->orWhereJsonContains('user_ids', (string) $uId);
                }
            });
        }

        if ($this->employeeId) {
            $empId = $this->employeeId;
            $query->where(function ($q) use ($empId) {
                $q->whereJsonContains('user_ids', (int) $empId)
                  ->orWhereJsonContains('user_ids', (string) $empId);
            });
        }

        if ($this->search) {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('content', 'like', "%{$s}%");
            });
        }

        if ($this->departmentId) {
            $deptId = $this->departmentId;
            $query->where(function ($q) use ($deptId) {
                $q->whereJsonContains('department_ids', (int) $deptId)
                  ->orWhereJsonContains('department_ids', (string) $deptId);
            });
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('start_date', [
                $this->startDate . ' 00:00:00',
                $this->endDate . ' 23:59:59',
            ]);
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->latest()->paginate(20);
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

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->latest()->get();
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=notice_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['ID', 'Title', 'Type', 'Start Date', 'End Date', 'Created At'];
        
        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->id,
                    $row->title,
                    ucwords(str_replace('_', ' ', $row->type)),
                    $row->start_date ? Carbon::parse($row->start_date)->format('Y-m-d H:i') : '-',
                    $row->end_date ? Carbon::parse($row->end_date)->format('Y-m-d H:i') : '-',
                    $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '-',
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.partner.reports.hrms.notice-report', [
            'reportData'  => $this->reportData,
            'branches'    => $this->branches,
            'teams'       => $this->teams,
            'employees'   => $this->employees,
            'departments' => $this->departments,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Panel',
            'pageTitle'    => 'Notice Report',
            'pageSubtitle' => 'View and export notice announcements across branches and teams',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
