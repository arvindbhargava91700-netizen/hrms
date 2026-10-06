<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmployeePip;
use App\Models\User;

class EmployeePips extends Component
{
    use WithPagination, HasPartnerId;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $search = '';
    public $statusFilter = '';

    // Form fields
    public $pipId = null;
    public $isEditMode = false;
    public $employee_id = '';
    public $reason = '';
    public $start_date = '';
    public $end_date = '';
    public $improvement_targets = '';
    public $review_result = '';
    public $status = 'active';
    public $remarks = '';

    // Modals
    public $isFormModalOpen = false;
    public $isViewModalOpen = false;
    public $viewPip = null;

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('pip_viewAny') ||
            auth()->user()->canAccess('pip_viewBranch') ||
            auth()->user()->canAccess('pip_viewTeam') ||
            auth()->user()->canAccess('pip_viewOwn'),
            403
        );
    }

    protected function rules()
    {
        return [
            'employee_id'         => 'required|exists:users,id',
            'reason'              => 'required|string|min:5',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'improvement_targets' => 'nullable|string',
            'review_result'       => 'nullable|string',
            'status'              => 'required|in:active,under_review,completed_passed,completed_failed,cancelled',
            'remarks'             => 'nullable|string',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = EmployeePip::query()->with(['employee', 'creator']);

        // Permission-based employee filtering
        $allowedIds = $this->getTeamEmployeeIds('pip_viewAny');
        $query->where(function ($q) use ($allowedIds) {
            $q->whereIn('employee_id', $allowedIds)
              ->orWhereIn('created_by', $allowedIds);
        });

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee', function ($eq) use ($search) {
                    $eq->where('name', 'like', "%{$search}%")
                       ->orWhere('employee_code', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('reason', 'like', "%{$search}%")
                ->orWhere('improvement_targets', 'like', "%{$search}%");
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $pips = $query->orderBy('created_at', 'desc')->paginate(10);

        // Team members available for assignment (all staff)
        $employees = User::where('role', 'employee')->orderBy('name')->get();

        return view('livewire.partner.hrms.employee-pips', [
            'pips'              => $pips,
            'employees'         => $employees,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'PIP (Performance Improvement Plan)',
            'pageSubtitle' => 'Manage employee performance targets, reasons, and review outcomes',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }

    public function createPip()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('pip_create'), 403);
        $this->resetValidation();
        $this->reset(['pipId', 'isEditMode', 'employee_id', 'reason', 'start_date', 'end_date', 'improvement_targets', 'review_result', 'remarks']);
        $this->status = 'active';
        $this->start_date = date('Y-m-d');
        $this->end_date = date('Y-m-d', strtotime('+30 days'));
        $this->isFormModalOpen = true;
    }

    public function editPip($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('pip_update'), 403);
        $pip = EmployeePip::findOrFail($id);
        
        $this->resetValidation();
        $this->pipId = $pip->id;
        $this->isEditMode = true;
        $this->employee_id = (string) $pip->employee_id;
        $this->reason = $pip->reason;
        $this->start_date = $pip->start_date ? $pip->start_date->format('Y-m-d') : '';
        $this->end_date = $pip->end_date ? $pip->end_date->format('Y-m-d') : '';
        $this->improvement_targets = $pip->improvement_targets;
        $this->review_result = $pip->review_result;
        $this->status = (string) $pip->status;
        $this->remarks = $pip->remarks;

        $this->isFormModalOpen = true;
    }

    public function viewPipDetails($id)
    {
        $this->viewPip = EmployeePip::with(['employee', 'creator', 'partner'])->findOrFail($id);
        $this->isViewModalOpen = true;
    }

    public function savePip()
    {
        $user = auth()->user();
        abort_unless($user->isPartner() || $user->canAccess($this->isEditMode ? 'pip_update' : 'pip_create'), 403);

        $this->validate();

        if ($this->isEditMode && $this->pipId) {
            $pip = EmployeePip::findOrFail($this->pipId);
            $pip->update([
                'employee_id'         => $this->employee_id,
                'reason'              => $this->reason,
                'start_date'          => $this->start_date,
                'end_date'            => $this->end_date,
                'improvement_targets' => $this->improvement_targets,
                'review_result'       => $this->review_result,
                'status'              => $this->status,
                'remarks'             => $this->remarks,
            ]);
            session()->flash('success', 'PIP record updated successfully.');
        } else {
            EmployeePip::create([
                'employee_id'         => $this->employee_id,
                'created_by'          => auth()->id(),
                'reason'              => $this->reason,
                'start_date'          => $this->start_date,
                'end_date'            => $this->end_date,
                'improvement_targets' => $this->improvement_targets,
                'review_result'       => $this->review_result,
                'status'              => $this->status,
                'remarks'             => $this->remarks,
            ]);
            session()->flash('success', 'PIP record created successfully.');
        }

        $this->closeModals();
    }

    public function deletePip($id)
    {
        $user = auth()->user();
        abort_unless($user->isPartner() || $user->canAccess('pip_delete'), 403);

        $pip = EmployeePip::findOrFail($id);
        $pip->delete();

        session()->flash('success', 'PIP record deleted successfully.');
    }

    public function closeModals()
    {
        $this->isFormModalOpen = false;
        $this->isViewModalOpen = false;
        $this->reset(['pipId', 'isEditMode', 'employee_id', 'reason', 'start_date', 'end_date', 'improvement_targets', 'review_result', 'remarks', 'viewPip']);
    }
}
