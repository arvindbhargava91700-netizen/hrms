<?php

namespace App\Livewire\Partner\Hrms\CustomerVisits;

use App\Models\CustomerVisit;
use App\Models\Lead;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    
    // Create Visit Properties
    public $newVisitLeadId = '';
    public $newVisitDate = '';
    public $newVisitTime = '';
    public $newVisitLocation = '';
    public $newVisitPurpose = '';
    public $newVisitNotes = '';

    public $editVisitId = null;
    public $isEditMode = false;
    public $viewVisit = null;

    protected $rules = [
        'newVisitLeadId' => 'required',
        'newVisitDate' => 'required|date',
        'newVisitTime' => 'required',
        'newVisitLocation' => 'required|string|max:255',
        'newVisitPurpose' => 'nullable|string|max:500',
    ];

    public function render()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('customervisit_viewAny') || auth()->user()->canAccess('customervisit_viewOwn') || auth()->user()->canAccess('customervisit_viewBranch') || auth()->user()->canAccess('customervisit_viewTeam'), 403);
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        
        $query = CustomerVisit::with('lead')
            ->where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } });

        if (auth()->user()->role === 'employee' && !auth()->user()->canAccess('customervisit_viewAny')) {
            if (auth()->user()->canAccess('customervisit_viewBranch') || auth()->user()->canAccess('customervisit_viewTeam')) {
                $query->whereIn('employee_id', auth()->user()->getTeamIds());
            } else {
                $query->where('employee_id', auth()->id());
            }
        }

        if ($this->search) {
            $query->whereHas('lead', function($q) {
                $q->where('customer_name', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_mobile', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $visits = $query->latest('visit_date')->paginate(10);
        $leads = Lead::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->get();

        return view('livewire.partner.hrms.customer-visits.index', [
            'visits' => $visits,
            'availableLeads' => $leads
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'Customer Visits',
            'pageSubtitle' => 'Manage your customer and lead visits',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }

    public function createVisit()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('customervisit_create'), 403);
        $this->isEditMode = false;
        $this->reset(['newVisitLeadId', 'newVisitDate', 'newVisitTime', 'newVisitLocation', 'newVisitPurpose', 'newVisitNotes', 'editVisitId']);
        $this->resetValidation();
        $this->dispatch('show-visit-modal');
    }

    public function showVisit($id)
    {
        $this->viewVisit = CustomerVisit::with('lead')->findOrFail($id);
        $this->dispatch('show-view-visit-modal');
    }

    public function editVisit($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('customervisit_update'), 403);
        $this->isEditMode = true;
        $this->editVisitId = $id;
        $visit = CustomerVisit::findOrFail($id);
        
        $this->newVisitLeadId = $visit->lead_id;
        $this->newVisitDate = \Carbon\Carbon::parse($visit->visit_date)->format('Y-m-d');
        $this->newVisitTime = \Carbon\Carbon::parse($visit->visit_date)->format('H:i');
        $this->newVisitLocation = $visit->location;
        $this->newVisitPurpose = $visit->purpose;
        $this->newVisitNotes = $visit->notes;
        
        $this->resetValidation();
        $this->dispatch('show-visit-modal');
    }

    public function saveVisit()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess($this->isEditMode ? 'customervisit_update' : 'customervisit_create'), 403);
        $this->validate();

        $partnerId = auth()->user()->isPartner() ? auth()->id() : null;

        if ($this->isEditMode) {
            $visit = CustomerVisit::findOrFail($this->editVisitId);
            $visit->update([
                'lead_id' => $this->newVisitLeadId,
                'visit_date' => $this->newVisitDate . ' ' . $this->newVisitTime,
                'location' => $this->newVisitLocation,
                'purpose' => $this->newVisitPurpose,
                'notes' => $this->newVisitNotes,
            ]);
            session()->flash('success', 'Visit updated successfully.');
        } else {
            CustomerVisit::create([
                'partner_id' => $partnerId,
                'employee_id' => auth()->id(),
                'lead_id' => $this->newVisitLeadId,
                'visit_date' => $this->newVisitDate . ' ' . $this->newVisitTime,
                'location' => $this->newVisitLocation,
                'purpose' => $this->newVisitPurpose,
                'notes' => $this->newVisitNotes,
                'status' => 'scheduled',
            ]);
            session()->flash('success', 'Visit scheduled successfully.');
        }

        $this->reset(['newVisitLeadId', 'newVisitDate', 'newVisitTime', 'newVisitLocation', 'newVisitPurpose', 'newVisitNotes', 'isEditMode', 'editVisitId']);
        $this->dispatch('close-visit-modal');
    }

    public function deleteVisit($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('customervisit_delete'), 403);
        CustomerVisit::findOrFail($id)->delete();
        session()->flash('success', 'Visit deleted successfully.');
    }
}
