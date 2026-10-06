<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Recruitment as RecruitmentModel;
use App\Models\Department;

class Recruitment extends Component
{
    use WithPagination, HasPartnerId;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $search = '';
    public $statusFilter = '';
    public $stageFilter = '';
    public $filterBranchId = '';
    public $filterDepartmentId = '';

    // Form Fields
    public $recruitmentId = null;
    public $isEditMode = false;
    public $selected_candidate_id = '';
    public $employee_id = null;
    public $job_title = '';
    public $vacancies_count = 1;
    public $candidate_name = '';
    public $candidate_email = '';
    public $candidate_phone = '';
    public $branch_id = '';
    public $department_id = '';
    public $interview_stage = 'applied';
    public $status = 'under_review';
    public $offer_status = 'pending';
    public $joining_status = 'pending';
    public $cost_per_hire = 0.00;
    public $interview_date = '';
    public $remarks = '';

    // Computed departments based on branch
    public $filteredDepartments = [];

    public function updatedSelectedCandidateId($val)
    {
        if ($val && $val !== 'custom') {
            $user = \App\Models\User::find($val);
            if ($user) {
                $this->employee_id = $user->id;
                $this->candidate_name = $user->name;
                $this->candidate_email = $user->email ?? '';
                $this->candidate_phone = $user->mobile ?? $user->phone ?? '';
            }
        } else {
            $this->employee_id = null;
        }
    }

    // Modals
    public $isFormModalOpen = false;
    public $isViewModalOpen = false;
    public $viewRecruitment = null;

    protected function rules()
    {
        return [
            'job_title'       => 'required|string|max:255',
            'vacancies_count' => 'required|integer|min:1',
            'candidate_name'  => 'required|string|max:255',
            'candidate_email' => 'nullable|email|max:255',
            'candidate_phone' => 'nullable|string|max:50',
            'department_id'   => 'nullable|exists:departments,id',
            'interview_stage' => 'required|in:applied,screening,technical_round,hr_round,final_round',
            'status'          => 'required|in:under_review,selected,rejected,on_hold',
            'offer_status'    => 'required|in:pending,offered,offer_accepted,offer_declined',
            'joining_status'  => 'required|in:pending,joined,not_joined',
            'cost_per_hire'   => 'required|numeric|min:0',
            'interview_date'  => 'nullable|date',
            'remarks'         => 'nullable|string',
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

    public function updatingStageFilter()
    {
        $this->resetPage();
    }

    public function updatingFilterBranchId()
    {
        $this->resetPage();
    }

    public function updatingFilterDepartmentId()
    {
        $this->resetPage();
    }

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('recruitment_viewAny') ||
            auth()->user()->canAccess('recruitment_viewBranch') ||
            auth()->user()->canAccess('recruitment_viewTeam') ||
            auth()->user()->canAccess('recruitment_viewOwn'),
            403
        );
        
        // Show all departments by default
        $this->filteredDepartments = \App\Models\Department::orderBy('name')->get();
    }

    public function render()
    {
        $query = RecruitmentModel::query()->with(['department', 'creator', 'employee']);

        $allowedIds = $this->getTeamEmployeeIds('recruitment_viewAny');
        $query->where(function ($q) use ($allowedIds) {
            $q->whereIn('created_by', $allowedIds)
              ->orWhereIn('employee_id', $allowedIds);
        });

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('candidate_name', 'like', "%{$search}%")
                  ->orWhere('candidate_email', 'like', "%{$search}%")
                  ->orWhere('candidate_phone', 'like', "%{$search}%")
                  ->orWhere('job_title', 'like', "%{$search}%");
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->stageFilter) {
            $query->where('interview_stage', $this->stageFilter);
        }

        // Filter by Department
        if ($this->filterDepartmentId) {
            $query->where('department_id', $this->filterDepartmentId);
        }

        // Filter by Branch (through department -> branchHeads)
        if ($this->filterBranchId) {
            $query->whereHas('department', function ($q) {
                $q->whereHas('branchHeads', function ($q2) {
                    $q2->where('branch_id', $this->filterBranchId);
                });
            });
        }

        $recruitments = $query->orderBy('created_at', 'desc')->paginate(10);
        $branches = \App\Models\HrmsBranch::orderBy('name')->get();
        $allDepartments = Department::orderBy('name')->get();
        $candidates = \App\Models\User::where('role', 'employee')->orderBy('name')->get();

        \Log::info('filteredDepartments in render: ' . count($this->filteredDepartments));

        return view('livewire.partner.hrms.recruitment', [
            'recruitments'       => $recruitments,
            'branches'           => $branches,
            'filteredDepartments' => $this->filteredDepartments,
            'allDepartments'     => $allDepartments,
            'candidates'         => $candidates,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'Recruitment & Hiring Management',
            'pageSubtitle' => 'Track open vacancies, candidate applications, interview stages, selection outcomes, and cost per hire',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }

    public function createRecruitment()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('recruitment_create'), 403);
        $this->resetValidation();
        $this->reset(['recruitmentId', 'isEditMode', 'selected_candidate_id', 'employee_id', 'job_title', 'candidate_name', 'candidate_email', 'candidate_phone', 'branch_id', 'department_id', 'interview_date', 'remarks']);
        $this->vacancies_count = 1;
        $this->interview_stage = 'applied';
        $this->status = 'under_review';
        $this->offer_status = 'pending';
        $this->joining_status = 'pending';
        $this->cost_per_hire = 0.00;
        $this->interview_date = date('Y-m-d');
        $this->filteredDepartments = \App\Models\Department::orderBy('name')->get();
        $this->isFormModalOpen = true;
    }

    public function editRecruitment($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('recruitment_update'), 403);
        $rec = RecruitmentModel::findOrFail($id);
        
        $this->resetValidation();
        $this->recruitmentId = $rec->id;
        $this->isEditMode = true;
        $this->employee_id = $rec->employee_id ? (string) $rec->employee_id : null;
        $this->selected_candidate_id = $rec->employee_id ? (string) $rec->employee_id : '';
        $this->job_title = $rec->job_title;
        $this->vacancies_count = $rec->vacancies_count;
        $this->candidate_name = $rec->candidate_name;
        $this->candidate_email = $rec->candidate_email;
        $this->candidate_phone = $rec->candidate_phone;
        $this->branch_id = $rec->department && $rec->department->branchHeads->isNotEmpty() 
            ? (string) $rec->department->branchHeads->first()->branch_id 
            : '';
        $this->department_id = $rec->department_id ? (string) $rec->department_id : '';
        $this->filteredDepartments = \App\Models\Department::orderBy('name')->get();
        $this->interview_stage = (string) $rec->interview_stage;
        $this->status = (string) $rec->status;
        $this->offer_status = (string) $rec->offer_status;
        $this->joining_status = (string) $rec->joining_status;
        $this->cost_per_hire = $rec->cost_per_hire;
        $this->interview_date = $rec->interview_date ? $rec->interview_date->format('Y-m-d') : '';
        $this->remarks = $rec->remarks;

        $this->isFormModalOpen = true;
    }

    public function viewRecruitmentDetails($id)
    {
        $this->viewRecruitment = RecruitmentModel::with(['department', 'creator', 'partner', 'employee'])->findOrFail($id);
        $this->isViewModalOpen = true;
    }

    public function saveRecruitment()
    {
        $user = auth()->user();
        abort_unless($user->isPartner() || $user->canAccess($this->isEditMode ? 'recruitment_update' : 'recruitment_create'), 403);

        $this->validate();

        if ($this->isEditMode && $this->recruitmentId) {
            $rec = RecruitmentModel::findOrFail($this->recruitmentId);
            $rec->update([
                'employee_id'     => $this->employee_id ?: null,
                'job_title'       => $this->job_title,
                'vacancies_count' => $this->vacancies_count,
                'candidate_name'  => $this->candidate_name,
                'candidate_email' => $this->candidate_email,
                'candidate_phone' => $this->candidate_phone,
                'department_id'   => $this->department_id ?: null,
                'interview_stage' => $this->interview_stage,
                'status'          => $this->status,
                'offer_status'    => $this->offer_status,
                'joining_status'  => $this->joining_status,
                'cost_per_hire'   => $this->cost_per_hire,
                'interview_date'  => $this->interview_date ?: null,
                'remarks'         => $this->remarks,
            ]);
            session()->flash('success', 'Recruitment record updated successfully.');
        } else {
            RecruitmentModel::create([
                'created_by'      => auth()->id(),
                'employee_id'     => $this->employee_id ?: null,
                'job_title'       => $this->job_title,
                'vacancies_count' => $this->vacancies_count,
                'candidate_name'  => $this->candidate_name,
                'candidate_email' => $this->candidate_email,
                'candidate_phone' => $this->candidate_phone,
                'department_id'   => $this->department_id ?: null,
                'interview_stage' => $this->interview_stage,
                'status'          => $this->status,
                'offer_status'    => $this->offer_status,
                'joining_status'  => $this->joining_status,
                'cost_per_hire'   => $this->cost_per_hire,
                'interview_date'  => $this->interview_date ?: null,
                'remarks'         => $this->remarks,
            ]);
            session()->flash('success', 'Recruitment record created successfully.');
        }

        $this->closeModals();
    }

    public function deleteRecruitment($id)
    {
        $user = auth()->user();
        abort_unless($user->isPartner() || $user->canAccess('recruitment_delete'), 403);

        $rec = RecruitmentModel::findOrFail($id);
        $rec->delete();

        session()->flash('success', 'Recruitment record deleted successfully.');
    }

    public function closeModals()
    {
        $this->isFormModalOpen = false;
        $this->isViewModalOpen = false;
        $this->reset(['recruitmentId', 'isEditMode', 'selected_candidate_id', 'employee_id', 'job_title', 'vacancies_count', 'candidate_name', 'candidate_email', 'candidate_phone', 'department_id', 'interview_date', 'remarks', 'viewRecruitment']);
    }
}
