<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\EmployeeGrievance;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class EmployeeGrievances extends Component
{
    use WithPagination;
    use WithFileUploads;
    use HasPartnerId;
    use Traits\HasHrmsFilters;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $search = '';
    public $typeFilter = '';
    public $statusFilter = '';

    // Form Fields
    public $grievanceId = null;
    public $isEditMode = false;
    public $employee_id = '';
    public $record_type = 'complaint';
    public $title = '';
    public $description = '';
    public $incident_date = '';
    public $action_taken = '';
    public $resolution_notes = '';
    public $status = 'open';
    public $new_file;
    public $existing_file_path = '';

    // Modals
    public $isFormModalOpen = false;
    public $isViewModalOpen = false;
    public $viewGrievance = null;

    protected function rules()
    {
        return [
            'employee_id'      => 'required|exists:users,id',
            'record_type'      => 'required|in:complaint,warning,show_cause,disciplinary_action',
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'incident_date'    => 'nullable|date',
            'action_taken'     => 'nullable|string',
            'resolution_notes' => 'nullable|string',
            'status'           => 'required|in:open,under_investigation,resolved,closed',
            'new_file'         => 'nullable|file|max:10240', // 10MB
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
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
            auth()->user()->canAccess('grievance_viewAny') ||
            auth()->user()->canAccess('grievance_viewBranch') ||
            auth()->user()->canAccess('grievance_viewTeam') ||
            auth()->user()->canAccess('grievance_viewOwn'),
            403
        );
    }

    public function render()
    {
        $user = auth()->user();
        $query = EmployeeGrievance::query()->with(['employee', 'creator']);

        $allowedIds = $this->getFilteredEmployeeIds('grievance_viewAny');
        $query->where(function ($q) use ($allowedIds) {
            $q->whereIn('employee_id', $allowedIds)
              ->orWhereIn('created_by', $allowedIds);
        });

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        if ($this->typeFilter) {
            $query->where('record_type', $this->typeFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $grievances = $query->orderBy('created_at', 'desc')->paginate(10);

        // Stat Card Counts
        $baseQuery = EmployeeGrievance::where(function ($q) use ($allowedIds) {
                $q->whereIn('employee_id', $allowedIds)
                  ->orWhereIn('created_by', $allowedIds);
            });

        $totalCases         = (clone $baseQuery)->count();
        $complaintsCount    = (clone $baseQuery)->where('record_type', 'complaint')->count();
        $investigatingCount = (clone $baseQuery)->where('status', 'under_investigation')->count();
        $resolvedCount      = (clone $baseQuery)->where('status', 'resolved')->count();

        // Employees List
        $employees = User::where('role', 'employee')->orderBy('name')->get();  

        return view('livewire.partner.hrms.employee-grievances', [
            'grievances'         => $grievances,
            'employees'          => $employees,
            'totalCases'         => $totalCases,
            'complaintsCount'    => $complaintsCount,
            'investigatingCount' => $investigatingCount,
            'resolvedCount'      => $resolvedCount,
            'filterBranches'     => $this->getFilterBranches(),
            'filterDepartments'  => $this->getFilterDepartments(),
            'filterEmployees'    => $this->getFilterEmployees('grievance_viewAny'),
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'Grievance & Disciplinary Management',
            'pageSubtitle' => 'Track complaints, official warnings, show-cause notices, disciplinary actions, and resolutions',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }

    public function createGrievance()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('grievance_create'), 403);
        $this->resetValidation();
        $this->reset([
            'grievanceId', 'isEditMode', 'employee_id', 'record_type', 'title', 
            'description', 'action_taken', 'resolution_notes', 'new_file', 
            'existing_file_path'
        ]);
        $this->record_type = 'complaint';
        $this->status = 'open';
        $this->incident_date = date('Y-m-d');
        $this->isFormModalOpen = true;
    }

    public function editGrievance($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('grievance_update'), 403);
        $grv = EmployeeGrievance::findOrFail($id);

        $this->resetValidation();
        $this->grievanceId = $grv->id;
        $this->isEditMode = true;
        $this->employee_id = (string) $grv->employee_id;
        $this->record_type = (string) $grv->record_type;
        $this->title = $grv->title;
        $this->description = $grv->description;
        $this->incident_date = $grv->incident_date ? $grv->incident_date->format('Y-m-d') : '';
        $this->action_taken = $grv->action_taken;
        $this->resolution_notes = $grv->resolution_notes;
        $this->status = (string) $grv->status;
        $this->existing_file_path = $grv->file_path;
        $this->new_file = null;

        $this->isFormModalOpen = true;
    }

    public function viewGrievanceDetails($id)
    {
        $this->viewGrievance = EmployeeGrievance::with(['employee', 'creator', 'partner'])->findOrFail($id);
        $this->isViewModalOpen = true;
    }

    public function saveGrievance()
    {
        $user = auth()->user();
        abort_unless($user->isPartner() || $user->canAccess($this->isEditMode ? 'grievance_update' : 'grievance_create'), 403);

        $this->validate();

        $filePath = $this->existing_file_path;

        if ($this->new_file) {
            $filePath = $this->new_file->store('grievances', 'public');
        }

        if ($this->isEditMode && $this->grievanceId) {
            $grv = EmployeeGrievance::findOrFail($this->grievanceId);
            $grv->update([
                'employee_id'      => $this->employee_id,
                'record_type'      => $this->record_type,
                'title'            => $this->title,
                'description'      => $this->description,
                'incident_date'    => $this->incident_date ?: null,
                'action_taken'     => $this->action_taken,
                'resolution_notes' => $this->resolution_notes,
                'status'           => $this->status,
                'file_path'        => $filePath,
            ]);
            session()->flash('success', 'Grievance / Disciplinary record updated successfully.');
        } else {
            EmployeeGrievance::create([
                'partner_id'       => null,
                'created_by'       => auth()->id(),
                'employee_id'      => $this->employee_id,
                'record_type'      => $this->record_type,
                'title'            => $this->title,
                'description'      => $this->description,
                'incident_date'    => $this->incident_date ?: null,
                'action_taken'     => $this->action_taken,
                'resolution_notes' => $this->resolution_notes,
                'status'           => $this->status,
                'file_path'        => $filePath,
            ]);
            session()->flash('success', 'Grievance / Disciplinary record created successfully.');
        }

        $this->closeModals();
    }

    public function deleteGrievance($id)
    {
        $user = auth()->user();
        abort_unless($user->isPartner() || $user->canAccess('grievance_delete'), 403);

        $grv = EmployeeGrievance::findOrFail($id);
        if ($grv->file_path) {
            Storage::disk('public')->delete($grv->file_path);
        }
        $grv->delete();

        session()->flash('success', 'Grievance / Disciplinary record deleted successfully.');
    }

    public function closeModals()
    {
        $this->isFormModalOpen = false;
        $this->isViewModalOpen = false;
        $this->reset([
            'grievanceId', 'isEditMode', 'employee_id', 'record_type', 'title', 
            'description', 'incident_date', 'action_taken', 'resolution_notes', 
            'status', 'new_file', 'existing_file_path', 'viewGrievance'
        ]);
    }
}
