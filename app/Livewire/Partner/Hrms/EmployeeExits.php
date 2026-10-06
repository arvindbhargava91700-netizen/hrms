<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmployeeExit;
use App\Models\User;
use Carbon\Carbon;

class EmployeeExits extends Component
{
    use WithPagination;
    use HasPartnerId;
    use Traits\HasHrmsFilters;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $search = '';
    public $statusFilter = '';

    // Form Fields
    public $exitId = null;
    public $isEditMode = false;
    public $employee_id = '';
    public $resignation_date = '';
    public $notice_period_days = 30;
    public $last_working_date = '';
    public $exit_reason = '';
    public $clearance_status = 'pending';
    public $fnf_status = 'pending';
    public $fnf_amount = 0.00;
    public $fnf_settlement_date = '';
    public $status = 'resigned';
    public $exit_interview_notes = '';
    public $remarks = '';

    // Modals
    public $isFormModalOpen = false;
    public $isViewModalOpen = false;
    public $viewExit = null;

    protected function rules()
    {
        return [
            'employee_id'          => 'required|exists:users,id',
            'resignation_date'     => 'required|date',
            'notice_period_days'   => 'required|integer|min:0',
            'last_working_date'    => 'nullable|date|after_or_equal:resignation_date',
            'exit_reason'          => 'required|string|min:5',
            'clearance_status'     => 'required|in:pending,in_progress,partially_cleared,fully_cleared',
            'fnf_status'           => 'required|in:pending,in_progress,settled,on_hold',
            'fnf_amount'           => 'required|numeric|min:0',
            'fnf_settlement_date'  => 'nullable|date',
            'status'               => 'required|in:resigned,serving_notice,cleared,exited,cancelled',
            'exit_interview_notes' => 'nullable|string',
            'remarks'              => 'nullable|string',
        ];
    }

    public function updatedResignationDate($val)
    {
        $this->calculateLastWorkingDate();
    }

    public function updatedNoticePeriodDays($val)
    {
        $this->calculateLastWorkingDate();
    }

    private function calculateLastWorkingDate()
    {
        if ($this->resignation_date && is_numeric($this->notice_period_days)) {
            $this->last_working_date = Carbon::parse($this->resignation_date)->addDays((int)$this->notice_period_days)->format('Y-m-d');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('resignationexit_viewAny') ||
            auth()->user()->canAccess('resignationexit_viewBranch') ||
            auth()->user()->canAccess('resignationexit_viewTeam') ||
            auth()->user()->canAccess('resignationexit_viewOwn'),
            403
        );
    }

    public function render()
    {
        $user = auth()->user();
        $query = EmployeeExit::query()->with(['employee', 'creator']);


        
        if ($user->isPartner() || $user->canAccess('resignationexit_viewAny')) {
            if ($this->filterBranchId || $this->filterDepartmentId || $this->filterEmployeeSearch) {
                $allowedIds = $this->getFilteredEmployeeIds('resignationexit_viewAny');
                $query->where(function ($q) use ($allowedIds) {
                    $q->whereIn('employee_id', $allowedIds)
                      ->orWhereIn('created_by', $allowedIds);
                });
            }
        } elseif ($user->canAccess('resignationexit_viewBranch') || $user->canAccess('resignationexit_viewbranch')) {
            $allowedIds = $this->getFilteredEmployeeIds('resignationexit_viewAny');
            $query->where(function ($q) use ($allowedIds) {
                $q->whereIn('employee_id', $allowedIds)
                  ->orWhereIn('created_by', $allowedIds);
            });
        } elseif ($user->canAccess('resignationexit_viewTeam') || $user->canAccess('resignationexit_viewteam')) {
            $teamIds = array_unique(array_merge($user->getTeamIds(), [$user->id]));
            $query->where(function ($q) use ($teamIds) {
                $q->whereIn('employee_id', $teamIds)
                  ->orWhereIn('created_by', $teamIds);
            });
        } else {
            // View Own
            $query->where(function ($q) use ($user) {
                $q->where('employee_id', $user->id)
                  ->orWhere('created_by', $user->id);
            });
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee', function ($eq) use ($search) {
                    $eq->where('name', 'like', "%{$search}%")
                       ->orWhere('employee_code', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('exit_reason', 'like', "%{$search}%")
                ->orWhere('exit_interview_notes', 'like', "%{$search}%")
                ->orWhere('remarks', 'like', "%{$search}%");
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $exits = $query->orderBy('created_at', 'desc')->paginate(10);

        // Metrics Summary Calculation
        $baseMetricsQuery = EmployeeExit::query();
        $totalResignations  = (clone $baseMetricsQuery)->whereNotIn('status', ['cancelled'])->count();
        $totalServingNotice = (clone $baseMetricsQuery)->whereIn('status', ['resigned', 'serving_notice'])->count();
        $totalPendingFnF    = (clone $baseMetricsQuery)->whereIn('fnf_status', ['pending', 'in_progress'])->count();
        $totalExitedSettled = (clone $baseMetricsQuery)->where(function ($q) {
            $q->where('status', 'exited')->orWhere('fnf_status', 'settled');
        })->count();

        // Show all employees (active + inactive) for resignation assignment
        $employees = User::where('role', 'employee')->orderBy('name')->get();

        return view('livewire.partner.hrms.employee-exits', [
            'exits'               => $exits,
            'employees'           => $employees,
            'totalResignations'   => $totalResignations,
            'totalServingNotice'  => $totalServingNotice,
            'totalPendingFnF'     => $totalPendingFnF,
            'totalExitedSettled'  => $totalExitedSettled,
            'filterBranches'      => $this->getFilterBranches(),
            'filterDepartments'   => $this->getFilterDepartments(),
            'filterEmployees'     => $this->getFilterEmployees('resignationexit_viewAny'),
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'Resignation & Exit Management',
            'pageSubtitle' => 'Track employee resignations, notice periods, last working dates, exit reasons, clearances, and F&F settlements',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }

    public function createExit()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('resignationexit_create'), 403);
        $this->resetValidation();
        $this->reset(['exitId', 'isEditMode', 'employee_id', 'resignation_date', 'last_working_date', 'exit_reason', 'fnf_settlement_date', 'exit_interview_notes', 'remarks']);
        $this->notice_period_days = 30;
        $this->resignation_date = date('Y-m-d');
        $this->last_working_date = date('Y-m-d', strtotime('+30 days'));
        $this->clearance_status = 'pending';
        $this->fnf_status = 'pending';
        $this->fnf_amount = 0.00;
        $this->status = 'serving_notice';
        $this->isFormModalOpen = true;
    }

    public function editExit($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('resignationexit_update'), 403);
        $ex = EmployeeExit::findOrFail($id);

        $this->resetValidation();
        $this->exitId = $ex->id;
        $this->isEditMode = true;
        $this->employee_id = (string) $ex->employee_id;
        $this->resignation_date = $ex->resignation_date ? $ex->resignation_date->format('Y-m-d') : '';
        $this->notice_period_days = $ex->notice_period_days;
        $this->last_working_date = $ex->last_working_date ? $ex->last_working_date->format('Y-m-d') : '';
        $this->exit_reason = $ex->exit_reason;
        $this->clearance_status = (string) $ex->clearance_status;
        $this->fnf_status = (string) $ex->fnf_status;
        $this->fnf_amount = $ex->fnf_amount;
        $this->fnf_settlement_date = $ex->fnf_settlement_date ? $ex->fnf_settlement_date->format('Y-m-d') : '';
        $this->status = (string) $ex->status;
        $this->exit_interview_notes = $ex->exit_interview_notes;
        $this->remarks = $ex->remarks;

        $this->isFormModalOpen = true;
    }

    public function viewExitDetails($id)
    {
        $this->viewExit = EmployeeExit::with(['employee', 'creator', 'partner'])->findOrFail($id);
        $this->isViewModalOpen = true;
    }

    public function saveExit()
    {
        $user = auth()->user();
        abort_unless($user->isPartner() || auth()->user()->canAccess($this->isEditMode ? 'resignationexit_update' : 'resignationexit_create'), 403);

        $this->validate();

        // Auto-set F&F settlement date if F&F status changed to settled
        if ($this->fnf_status === 'settled' && empty($this->fnf_settlement_date)) {
            $this->fnf_settlement_date = date('Y-m-d');
        }

        $selectedEmp = User::find($this->employee_id);
        $lwd = $this->last_working_date ?: $this->resignation_date;
        $isPast = $lwd ? Carbon::parse($lwd)->startOfDay()->lessThanOrEqualTo(Carbon::today()->startOfDay()) : false;
        $empStatus = ($this->status === 'exited' || $isPast) ? 'exited' : 'serving_notice';
        $userStatus = ($this->status === 'exited' || $isPast) ? 'inactive' : 'active';

        if ($this->isEditMode && $this->exitId) {
            $ex = EmployeeExit::findOrFail($this->exitId);
            $ex->update([
                'employee_id'          => $this->employee_id,
                'branch_id'            => $selectedEmp?->branch_id,
                'department_id'        => $selectedEmp?->department_id,
                'designation_id'       => $selectedEmp?->designation_id,
                'resignation_date'     => $this->resignation_date,
                'notice_period_days'   => $this->notice_period_days,
                'last_working_date'    => $this->last_working_date ?: null,
                'exit_reason'          => $this->exit_reason,
                'clearance_status'     => $this->clearance_status,
                'fnf_status'           => $this->fnf_status,
                'fnf_amount'           => $this->fnf_amount,
                'fnf_settlement_date'  => $this->fnf_settlement_date ?: null,
                'status'               => $this->status,
                'exit_interview_notes' => $this->exit_interview_notes,
                'remarks'              => $this->remarks,
                'updated_by'           => auth()->id(),
            ]);

            if ($selectedEmp) {
                $selectedEmp->update([
                    'resignation_date'  => $this->resignation_date ?: $selectedEmp->resignation_date,
                    'termination_date'  => $this->last_working_date ?: $selectedEmp->termination_date,
                    'employment_status' => $empStatus,
                    'status'            => $userStatus,
                ]);
            }

            session()->flash('success', 'Resignation and exit record updated successfully.');
        } else {
            EmployeeExit::create([
                'partner_id'          => $this->requirePartnerId(),
                'employee_id'         => $this->employee_id,
                'branch_id'           => $selectedEmp?->branch_id,
                'department_id'       => $selectedEmp?->department_id,
                'designation_id'      => $selectedEmp?->designation_id,
                'created_by'          => auth()->id(),
                'resignation_date'     => $this->resignation_date,
                'notice_period_days'   => $this->notice_period_days,
                'last_working_date'    => $this->last_working_date ?: null,
                'exit_reason'          => $this->exit_reason,
                'clearance_status'     => $this->clearance_status,
                'fnf_status'           => $this->fnf_status,
                'fnf_amount'           => $this->fnf_amount,
                'fnf_settlement_date'  => $this->fnf_settlement_date ?: null,
                'status'               => $this->status,
                'exit_interview_notes' => $this->exit_interview_notes,
                'remarks'              => $this->remarks,
            ]);

            if ($selectedEmp) {
                $selectedEmp->update([
                    'resignation_date'  => $this->resignation_date ?: $selectedEmp->resignation_date,
                    'termination_date'  => $this->last_working_date ?: $selectedEmp->termination_date,
                    'employment_status' => $empStatus,
                    'status'            => $userStatus,
                ]);
            }

            session()->flash('success', 'Resignation and exit record created successfully.');
        }

        $this->closeModals();
    }

    public function deleteExit($id)
    {
        $user = auth()->user();
        abort_unless($user->isPartner() || $user->canAccess('resignationexit_delete'), 403);

        $ex = EmployeeExit::findOrFail($id);
        $empId = $ex->employee_id;
        $ex->delete();

        // Restore employee status if no other active exit exists
        $hasOtherExit = EmployeeExit::where('employee_id', $empId)->whereNotIn('status', ['cancelled'])->exists();
        if (!$hasOtherExit) {
            $emp = User::find($empId);
            if ($emp) {
                $emp->update([
                    'employment_status' => 'active',
                    'status'            => 'active',
                    'termination_date'  => null,
                    'resignation_date'  => null,
                ]);
            }
        }

        session()->flash('success', 'Resignation and exit record deleted successfully.');
    }

    public function closeModals()
    {
        $this->isFormModalOpen = false;
        $this->isViewModalOpen = false;
        $this->reset(['exitId', 'isEditMode', 'employee_id', 'resignation_date', 'last_working_date', 'exit_reason', 'fnf_settlement_date', 'exit_interview_notes', 'remarks', 'viewExit']);
    }
}
