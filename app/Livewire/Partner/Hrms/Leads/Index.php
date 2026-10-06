<?php

namespace App\Livewire\Partner\Hrms\Leads;

use App\Models\Lead;
use App\Models\User;

use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use \App\Livewire\Partner\Hrms\HasPartnerId;
    use \App\Livewire\Partner\Hrms\Traits\HasHrmsFilters;

    public $search = '';
    public $status = '';

    public $newCustomerName = '';
    public $newCustomerMobile = '';
    public $newCustomerEmail = '';
    public $newCompanyName = '';
    public $newNotes = '';
    public $assignedTo = '';

    public $editLeadId = null;
    public $isEditMode = false;
    public $editStatus = 'new';
    
    public $viewLead = null;

    protected $rules = [
        'newCustomerName' => 'required|string|max:255',
        'newCustomerMobile' => 'nullable|string|max:50',
        'newCustomerEmail' => 'nullable|email|max:255',
        'newCompanyName' => 'nullable|string|max:255',
        'newNotes' => 'nullable|string',
        'assignedTo' => 'nullable',
        'editStatus' => 'nullable|string|in:new,first_call,interested,meeting_scheduled,customer_visit,quotation,negotiation,won,lost',
    ];

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('lead_viewAny') ||
            auth()->user()->canAccess('lead_viewBranch') ||
            auth()->user()->canAccess('lead_viewTeam') ||
            auth()->user()->canAccess('lead_viewOwn'),
            403
        );
    }

    public function render()
    {
        $query = Lead::query();

        $query = $this->applyHrmsFilters($query, 'assigned_to', 'lead_viewAny');
        $query->whereHas('assignedTo', function ($employeeQuery) {
            $employeeQuery->where(function ($q) {
                $q->whereNull('resignation_date')
                    ->orWhereColumn('users.resignation_date', '>', 'leads.created_at');
            })->where(function ($q) {
                $q->whereNull('termination_date')
                    ->orWhereColumn('users.termination_date', '>', 'leads.created_at');
            });
        });

        if ($this->search) {
            $query->where(function($q) {
                $q->where('customer_name', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_mobile', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('company_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $leads = $query->with('orders')->latest()->paginate(10);

        // Fetch team members for assignment — strictly based on view permission
        $user = auth()->user();
        if ($user->isPartner() || $user->canAccess('lead_viewAny')) {
            // AnyView — can assign to anyone in the organisation
            $teamMembers = \App\Models\User::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('parent_id', $this->getPartnerId()); } })
                ->availableForHrmsAssignment()
                ->latest()->get();
        } elseif ($user->canAccess('lead_viewBranch') || $user->canAccess('lead_viewTeam')) {
            // TeamView / BranchView
            $teamMembers = \App\Models\User::whereIn('id', $this->getTeamEmployeeIds('lead_viewAny'))
                ->availableForHrmsAssignment()
                ->latest()->get();
        } else {
            // OurView only (even if they also have create/update) — assign to self only
            $teamMembers = \App\Models\User::where('id', $user->id)
                ->availableForHrmsAssignment()
                ->get();
        }

        return view('livewire.partner.hrms.leads.index', [
            'leads' => $leads,
            'teamMembers' => $teamMembers,
            'filterBranches' => $this->getFilterBranches(),
            'filterDepartments' => $this->getFilterDepartments(),
            'filterEmployees' => $this->getFilterEmployees('lead_viewAny'),
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'Leads Management',
            'pageSubtitle' => 'Manage your potential customers and inquiries',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }

    public function createLead()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('lead_create'), 403);
        $this->isEditMode = false;
        $this->editStatus = 'new';
        $this->reset(['newCustomerName', 'newCustomerMobile', 'newCustomerEmail', 'newCompanyName', 'newNotes', 'assignedTo', 'editLeadId']);
        // Default to self assignment if not explicitly changed
        $this->assignedTo = auth()->id();
        $this->resetValidation();
        $this->dispatch('show-lead-modal');
    }

    public function showLead($id)
    {
        $this->viewLead = Lead::with(['orders', 'visits', 'assignedTo'])->findOrFail($id);
        $this->dispatch('show-view-lead-modal');
    }

    public function editLead($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('lead_update'), 403);
        $lead = Lead::findOrFail($id);
        
        // Verify access to this specific lead
        $user = auth()->user();
        if (!$user->isPartner() && !$user->canAccess('lead_viewAny')) {
            if ($user->canAccess('lead_viewBranch') || $user->canAccess('lead_viewTeam')) {
                abort_unless(in_array($lead->assigned_to, $user->getTeamIds()), 403);
            } else {
                abort_unless($lead->assigned_to === $user->id, 403);
            }
        }
        
        $this->isEditMode = true;
        $this->editLeadId = $id;
        $this->newCustomerName = $lead->customer_name;
        $this->newCustomerMobile = $lead->customer_mobile;
        $this->newCustomerEmail = $lead->email;
        $this->newCompanyName = $lead->company_name;
        $this->newNotes = $lead->notes;
        $this->assignedTo = $lead->assigned_to;
        $this->editStatus = $lead->status;
        
        $this->resetValidation();
        $this->dispatch('show-lead-modal');
    }

    public function saveLead()
    {
        if ($this->isEditMode) {
            abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('lead_update'), 403);
            // Verify access to the lead being edited
            $editLead = Lead::findOrFail($this->editLeadId);
            $user = auth()->user();
            if (!$user->isPartner() && !$user->canAccess('lead_viewAny')) {
                if ($user->canAccess('lead_viewBranch') || $user->canAccess('lead_viewTeam')) {
                    abort_unless(in_array($editLead->assigned_to, $user->getTeamIds()), 403);
                } else {
                    abort_unless($editLead->assigned_to === $user->id, 403);
                }
            }
        } else {
            abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('lead_create'), 403);
        }

        $this->validate();

        abort_unless(User::whereKey($this->assignedTo ?: auth()->id())->availableForHrmsAssignment()->exists(), 403);

        if ($this->isEditMode) {
            $lead = Lead::findOrFail($this->editLeadId);
            $oldStatus = $lead->status;
            
            $lead->update([
                'customer_name' => $this->newCustomerName,
                'customer_mobile' => $this->newCustomerMobile,
                'email' => $this->newCustomerEmail,
                'company_name' => $this->newCompanyName,
                'status' => $this->editStatus,
                'notes' => $this->newNotes,
                'assigned_to' => $this->assignedTo ?: auth()->id(),
            ]);
            
            // Lead status updated to 'won' — target tracking can be handled separately
            
            session()->flash('success', 'Lead updated successfully.');
        } else {
            Lead::create([
                'partner_id' => null,
                'assigned_to' => $this->assignedTo ?: auth()->id(),
                'customer_name' => $this->newCustomerName,
                'customer_mobile' => $this->newCustomerMobile,
                'email' => $this->newCustomerEmail,
                'company_name' => $this->newCompanyName,
                'notes' => $this->newNotes,
                'status' => $this->editStatus ?: 'new',
            ]);
            session()->flash('success', 'Lead created successfully.');
        }

        $this->reset(['newCustomerName', 'newCustomerMobile', 'newCustomerEmail', 'newCompanyName', 'newNotes', 'assignedTo', 'editLeadId', 'isEditMode', 'editStatus']);
        $this->dispatch('close-lead-modal');
    }

    public function deleteLead($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('lead_delete'), 403);
        $lead = Lead::findOrFail($id);
        
        // Verify access
        $user = auth()->user();
        if (!$user->isPartner() && !$user->canAccess('lead_viewAny')) {
            if ($user->canAccess('lead_viewBranch') || $user->canAccess('lead_viewTeam')) {
                abort_unless(in_array($lead->assigned_to, $user->getTeamIds()), 403);
            } else {
                abort_unless($lead->assigned_to === $user->id, 403);
            }
        }
        
        $lead->delete();
        session()->flash('success', 'Lead deleted successfully.');
    }
}
