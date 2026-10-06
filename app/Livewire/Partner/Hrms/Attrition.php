<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmployeeExit;
use App\Models\ExitReason;
use App\Models\User;
use App\Models\HrmsBranch;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Attrition extends Component
{
    use WithPagination, HasPartnerId;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $filterYear;
    public $filterMonth = 'all';
    public $filterBranchId = '';
    public $filterDepartmentId = '';
    public $filterExitType = 'all';
    public $filterExitReason = 'all';
    public $search = '';
    public $perPage = 10;

    // Modal Form Properties
    public $showModal = false;
    public $editingId = null;
    public $formEmployeeId = '';
    public $formExitDate = '';
    public $formLastWorkingDate = '';
    public $formExitType = 'voluntary';
    public $formExitReason = 'Resignation';
    public $formNoticePeriod = 0;
    public $formRemarks = '';
    public $formStatus = 'completed';

    // Selected Employee Preview Snapshot
    public $selectedEmployeeBranch = null;
    public $selectedEmployeeDepartment = null;
    public $selectedEmployeeDesignation = null;

    protected $listeners = ['refreshAttrition' => '$refresh'];

    public function mount()
    {
        $this->filterYear = date('Y');
        $this->formExitDate = date('Y-m-d');
        $this->formLastWorkingDate = date('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterYear() { $this->resetPage(); }
    public function updatingFilterMonth() { $this->resetPage(); }
    public function updatingFilterBranchId() { $this->resetPage(); }
    public function updatingFilterDepartmentId() { $this->resetPage(); }
    public function updatingFilterExitType() { $this->resetPage(); }
    public function updatingFilterExitReason() { $this->resetPage(); }

    public $assignedAssets = [];

    public function updatedFormEmployeeId($value)
    {
        if (!empty($value)) {
            $employee = User::find($value);
            if ($employee) {
                $this->selectedEmployeeBranch = $employee->branch?->name ?? 'N/A';
                $this->selectedEmployeeDepartment = $employee->department?->name ?? 'N/A';
                $this->selectedEmployeeDesignation = $employee->designation?->name ?? 'N/A';
                // $this->assignedAssets = \App\Models\Asset::where('current_employee_id', $employee->id)->where('status', 'issued')->get();
                return;
            }
        }
        $this->selectedEmployeeBranch = null;
        $this->selectedEmployeeDepartment = null;
        $this->selectedEmployeeDesignation = null;
        $this->assignedAssets = [];
    }

    public function returnAssetFromExit($assetId)
    {
        $asset = \App\Models\Asset::find($assetId);
        if ($asset) {
            $asset->update([
                'status' => 'available',
            ]);
            $this->assignedAssets = \App\Models\Asset::where('status', 'issued')->get();
            session()->flash('success', "Asset [{$asset->asset_code}] status updated to available.");
        }
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->formEmployeeId = '';
        $this->formExitDate = date('Y-m-d');
        $this->formLastWorkingDate = date('Y-m-d');
        $this->formExitType = 'voluntary';
        $this->formExitReason = 'Resignation';
        $this->formNoticePeriod = 0;
        $this->formRemarks = '';
        $this->formStatus = 'completed';
        $this->selectedEmployeeBranch = null;
        $this->selectedEmployeeDepartment = null;
        $this->selectedEmployeeDesignation = null;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $exit = EmployeeExit::findOrFail($id);
        
        $this->editingId = $exit->id;
        $this->formEmployeeId = $exit->employee_id;
        $this->formExitDate = $exit->exit_date?->format('Y-m-d') ?? date('Y-m-d');
        $this->formLastWorkingDate = $exit->last_working_date?->format('Y-m-d') ?? date('Y-m-d');
        $this->formExitType = $exit->exit_type;
        $this->formExitReason = $exit->exit_reason;
        $this->formNoticePeriod = $exit->notice_period_days;
        $this->formRemarks = $exit->remarks;
        $this->formStatus = $exit->status;

        $employee = User::find($exit->employee_id);
        if ($employee) {
            $this->selectedEmployeeBranch = $exit->branch?->name ?? $employee->branch?->name ?? 'N/A';
            $this->selectedEmployeeDepartment = $exit->department?->name ?? $employee->department?->name ?? 'N/A';
            $this->selectedEmployeeDesignation = $exit->designation?->name ?? $employee->designation?->name ?? 'N/A';
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function saveExit()
    {
        $this->validate([
            'formEmployeeId' => 'required|exists:users,id',
            'formExitDate' => 'required|date',
            'formLastWorkingDate' => 'nullable|date',
            'formExitType' => 'required|in:voluntary,involuntary',
            'formExitReason' => 'required|string|max:255',
            'formNoticePeriod' => 'nullable|numeric|min:0',
            'formRemarks' => 'nullable|string|max:1000',
        ]);

        $employee = User::findOrFail($this->formEmployeeId);
        $partnerId = $this->getPartnerId();

        $data = [
            'employee_id' => $employee->id,
            'partner_id' => $this->requirePartnerId(),
            'branch_id' => $employee->branch_id,
            'department_id' => $employee->department_id,
            'designation_id' => $employee->designation_id,
            'exit_date' => $this->formExitDate,
            'last_working_date' => $this->formLastWorkingDate ?: $this->formExitDate,
            'exit_type' => $this->formExitType,
            'exit_reason' => $this->formExitReason,
            'notice_period_days' => $this->formNoticePeriod ?: 0,
            'remarks' => $this->formRemarks,
            'status' => $this->formStatus,
            'updated_by' => Auth::id(),
        ];

        if ($this->editingId) {
            $exit = EmployeeExit::findOrFail($this->editingId);
            $exit->update($data);
        } else {
            $data['created_by'] = Auth::id();
            $exit = EmployeeExit::create($data);
        }

        // Save exit reason dynamically to DB if not existing
        if (!empty($this->formExitReason)) {
            ExitReason::firstOrCreate([
                'name' => trim($this->formExitReason),
                'partner_id' => $this->requirePartnerId(),
            ], [
                'type' => $this->formExitType,
                'is_active' => true,
            ]);
        }

        // Update User status & employment details
        $empStatus = ($this->formExitType === 'voluntary') ? 'resigned' : 'terminated';
        $updatePayload = [
            'employment_status' => $empStatus,
            'status' => 'inactive',
        ];
        if ($this->formExitType === 'voluntary') {
            $updatePayload['resignation_date'] = $this->formExitDate;
        } else {
            $updatePayload['termination_date'] = $this->formExitDate;
        }
        $employee->update($updatePayload);

        $this->showModal = false;
        session()->flash('success', 'Employee exit record saved successfully and employee status updated.');
    }

    public function deleteExit($id)
    {
        $exit = EmployeeExit::findOrFail($id);
        
        // Revert employee status if needed
        $employee = User::find($exit->employee_id);
        if ($employee && $employee->employment_status !== 'active') {
            $employee->update([
                'employment_status' => 'active',
                'status' => 'active',
            ]);
        }

        $exit->delete();
        session()->flash('success', 'Employee exit record deleted and employee status restored to active.');
    }

    public function render()
    {
        $partnerId = $this->getPartnerId();

        // Active & All Employees under this partner
        $employeesQuery = User::whereIn('role', ['employee', 'manager']);

        $activeEmployeesList = (clone $employeesQuery)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Branches, Departments, Designations for Filters
        $branches = HrmsBranch::get();
        $departments = Department::get();
        $designations = Designation::get();

        // Base Exits Query
        $exitsQuery = EmployeeExit::with(['employee', 'branch', 'department', 'designation']);

        if (!empty($this->filterYear)) {
            $exitsQuery->whereYear('exit_date', $this->filterYear);
        }

        if ($this->filterMonth !== 'all' && !empty($this->filterMonth)) {
            $exitsQuery->whereMonth('exit_date', $this->filterMonth);
        }

        if (!empty($this->filterBranchId)) {
            $exitsQuery->where('branch_id', $this->filterBranchId);
        }

        if (!empty($this->filterDepartmentId)) {
            $exitsQuery->where('department_id', $this->filterDepartmentId);
        }

        if ($this->filterExitType !== 'all' && !empty($this->filterExitType)) {
            $exitsQuery->where('exit_type', $this->filterExitType);
        }

        if ($this->filterExitReason !== 'all' && !empty($this->filterExitReason)) {
            $exitsQuery->where('exit_reason', $this->filterExitReason);
        }

        if (!empty($this->search)) {
            $search = $this->search;
            $exitsQuery->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        // Clone for Analytics
        $analyticsQuery = clone $exitsQuery;
        $totalExits = (clone $analyticsQuery)->count();
        $voluntaryExits = (clone $analyticsQuery)->where('exit_type', 'voluntary')->count();
        $involuntaryExits = (clone $analyticsQuery)->where('exit_type', 'involuntary')->count();

        // Paginated Exits List
        $exits = (clone $exitsQuery)->latest('exit_date')->paginate($this->perPage);

        // Headcount & Attrition Rate Calculation
        $year = $this->filterYear ?: date('Y');
        $startDate = ($this->filterMonth !== 'all' && !empty($this->filterMonth))
            ? Carbon::createFromDate($year, $this->filterMonth, 1)->startOfMonth()
            : Carbon::createFromDate($year, 1, 1)->startOfYear();

        $endDate = ($this->filterMonth !== 'all' && !empty($this->filterMonth))
            ? Carbon::createFromDate($year, $this->filterMonth, 1)->endOfMonth()
            : Carbon::createFromDate($year, 12, 31)->endOfYear();

        // Opening Headcount: Joined before/on start date, and either not exited or exited after start date
        $openingHeadcount = (clone $employeesQuery)
            ->where(function ($q) use ($startDate) {
                $q->whereNull('joining_date')
                  ->orWhere('joining_date', '<=', $startDate->format('Y-m-d'));
            })
            ->where(function ($q) use ($startDate) {
                $q->whereNull('resignation_date')
                  ->whereNull('termination_date')
                  ->orWhere('resignation_date', '>=', $startDate->format('Y-m-d'))
                  ->orWhere('termination_date', '>=', $startDate->format('Y-m-d'));
            })
            ->count();

        // New Joiners in period
        $newJoiners = (clone $employeesQuery)
            ->whereBetween('joining_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->count();

        // Exits in period
        $periodExits = (clone $analyticsQuery)->count();

        $closingHeadcount = max(0, $openingHeadcount + $newJoiners - $periodExits);
        $averageHeadcount = ($openingHeadcount + $closingHeadcount) / 2;
        $attritionRate = $averageHeadcount > 0 ? round(($periodExits / $averageHeadcount) * 100, 2) : 0;

        // Current Active Employees
        $currentActiveEmployees = (clone $employeesQuery)->where('status', 'active')->count();

        // Monthly Exits Breakdown
        $monthlyExits = [];
        for ($m = 1; $m <= 12; $m++) {
            $mStart = Carbon::createFromDate($year, $m, 1)->startOfMonth();
            $mEnd = Carbon::createFromDate($year, $m, 1)->endOfMonth();

            $mExits = EmployeeExit::
                whereBetween('exit_date', [$mStart->format('Y-m-d'), $mEnd->format('Y-m-d')])
                ->when(!empty($this->filterBranchId), fn($q) => $q->where('branch_id', $this->filterBranchId))
                ->when(!empty($this->filterDepartmentId), fn($q) => $q->where('department_id', $this->filterDepartmentId))
                ->count();

            $monthlyExits[] = [
                'month' => $mStart->format('M'),
                'exits' => $mExits
            ];
        }

        // Department-wise Attrition Breakdown
        $deptAttrition = EmployeeExit::selectRaw('department_id, count(*) as exit_count')
            ->whereYear('exit_date', $year)
            ->when(!empty($this->filterBranchId), fn($q) => $q->where('branch_id', $this->filterBranchId))
            ->groupBy('department_id')
            ->with('department')
            ->get()
            ->map(function ($item) use ($partnerId) {
                $deptName = $item->department?->name ?? 'Unassigned';
                $deptEmpCount = User::where(function ($q) use ($partnerId) {
                        $q->where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('parent_id', $partnerId); } })->orWhere('id', $partnerId);
                    })
                    ->where('department_id', $item->department_id)
                    ->count();
                $rate = $deptEmpCount > 0 ? round(($item->exit_count / $deptEmpCount) * 100, 1) : 0;

                return [
                    'department' => $deptName,
                    'exits' => $item->exit_count,
                    'employees' => $deptEmpCount,
                    'rate' => $rate
                ];
            });

        // Branch-wise Attrition Breakdown
        $branchAttrition = EmployeeExit::selectRaw('branch_id, count(*) as exit_count')
            ->whereYear('exit_date', $year)
            ->groupBy('branch_id')
            ->with('branch')
            ->get()
            ->map(function ($item) use ($partnerId) {
                $branchName = $item->branch?->name ?? 'Main Branch';
                $branchEmpCount = User::where(function ($q) use ($partnerId) {
                        $q->where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('parent_id', $partnerId); } })->orWhere('id', $partnerId);
                    })
                    ->where('branch_id', $item->branch_id)
                    ->count();
                $rate = $branchEmpCount > 0 ? round(($item->exit_count / $branchEmpCount) * 100, 1) : 0;

                return [
                    'branch' => $branchName,
                    'exits' => $item->exit_count,
                    'employees' => $branchEmpCount,
                    'rate' => $rate
                ];
            });

        // Reason Breakdown
        $reasonBreakdown = EmployeeExit::selectRaw('exit_reason, count(*) as count')
            ->whereYear('exit_date', $year)
            ->groupBy('exit_reason')
            ->get();

        // Dynamic Exit Reasons
        $dynamicExitReasons = ExitReason::where('is_active', true)
        ->where('type', $this->formExitType)
        ->orderBy('name')
        ->get();

        $allExitReasonsForFilter = ExitReason::where('is_active', true)
        ->orderBy('name')
        ->get();

        return view('livewire.partner.hrms.attrition', [
            'exits' => $exits,
            'activeEmployeesList' => $activeEmployeesList,
            'branches' => $branches,
            'departments' => $departments,
            'designations' => $designations,
            'currentActiveEmployees' => $currentActiveEmployees,
            'totalExits' => $totalExits,
            'voluntaryExits' => $voluntaryExits,
            'involuntaryExits' => $involuntaryExits,
            'attritionRate' => $attritionRate,
            'monthlyExits' => $monthlyExits,
            'deptAttrition' => $deptAttrition,
            'branchAttrition' => $branchAttrition,
            'reasonBreakdown' => $reasonBreakdown,
            'dynamicExitReasons' => $dynamicExitReasons,
            'allExitReasonsForFilter' => $allExitReasonsForFilter,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'Attrition Analytics',
            'pageSubtitle' => 'Track employee exits, voluntary vs involuntary turnover, and branch metrics',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }

    public function exportCsv()
    {
        $partnerId = $this->getPartnerId();
        $exits = EmployeeExit::with(['employee', 'branch', 'department', 'designation'])
            ->when(!empty($this->filterYear), fn($q) => $q->whereYear('exit_date', $this->filterYear))
            ->when($this->filterMonth !== 'all' && !empty($this->filterMonth), fn($q) => $q->whereMonth('exit_date', $this->filterMonth))
            ->when(!empty($this->filterBranchId), fn($q) => $q->where('branch_id', $this->filterBranchId))
            ->when(!empty($this->filterDepartmentId), fn($q) => $q->where('department_id', $this->filterDepartmentId))
            ->when($this->filterExitType !== 'all' && !empty($this->filterExitType), fn($q) => $q->where('exit_type', $this->filterExitType))
            ->orderBy('exit_date', 'desc')
            ->get();

        $filename = "attrition_report_" . date('Y_m_d_H_i') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($exits) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Employee Name', 'Employee Code', 'Email', 'Mobile', 'Branch', 'Department', 'Designation', 'Exit Date', 'Last Working Date', 'Exit Type', 'Exit Reason', 'Notice Period (Days)', 'Remarks', 'Status']);

            foreach ($exits as $exit) {
                fputcsv($file, [
                    $exit->employee?->name ?? 'N/A',
                    $exit->employee?->employee_code ?? 'N/A',
                    $exit->employee?->email ?? 'N/A',
                    $exit->employee?->mobile ?? 'N/A',
                    $exit->branch?->name ?? 'N/A',
                    $exit->department?->name ?? 'N/A',
                    $exit->designation?->name ?? 'N/A',
                    $exit->exit_date?->format('Y-m-d') ?? '',
                    $exit->last_working_date?->format('Y-m-d') ?? '',
                    ucfirst($exit->exit_type),
                    $exit->exit_reason,
                    $exit->notice_period_days,
                    $exit->remarks,
                    ucfirst($exit->status)
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
