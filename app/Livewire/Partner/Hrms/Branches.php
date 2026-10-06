<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\HrmsBranch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Branches extends Component
{
    use HasPartnerId, WithFileUploads;

    public $branches = [];
    public $name, $address, $lat, $lng, $radius = 100, $manager_id, $status = 'active';
    
    // Company Profile Fields
    public $company_logo, $existing_company_logo, $gst_number, $founded_year, $website, $company_size, $company_type, $industry, $about_company, $linkedin_url, $facebook_url, $instagram_url;
    
    public $branchId = null;
    public $isOpen = false;
    public $managers = [];

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() || 
            auth()->user()->canAccess('branch_viewAny') ||
            auth()->user()->canAccess('branch_viewBranch') ||
            auth()->user()->canAccess('branch_viewOwn'), 
            403
        );
        $this->loadBranches();
        
        // Load potential managers (employees)
        $this->managers = User::whereIn('id', $this->getTeamEmployeeIds())->get();
    }

    public function loadBranches()
    {
        $query = HrmsBranch::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { 
          //  $q->where('partner_id', $this->getPartnerId()); 
            } });
        
        if (!auth()->user()->isPartner() && !auth()->user()->canAccess('branch_viewAny')) {
            if (auth()->user()->canAccess('branch_viewBranch')) {
                $query->where('id', auth()->user()->branch_id);
            } elseif (auth()->user()->canAccess('branch_viewOwn')) {
                $query->where('manager_id', auth()->id());
            } else {
                $query->where('id', -1);
            }
        }
        
        $this->branches = $query->latest()->get();
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetInputFields();
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->address = '';
        $this->lat = '';
        $this->lng = '';
        $this->radius = 100;
        $this->manager_id = '';
        $this->status = 'active';
        $this->branchId = null;

        // Reset Company Profile fields
        $this->company_logo = null;
        $this->existing_company_logo = null;
        $this->gst_number = '';
        $this->founded_year = '';
        $this->website = '';
        $this->company_size = '';
        $this->company_type = '';
        $this->industry = '';
        $this->about_company = '';
        $this->linkedin_url = '';
        $this->facebook_url = '';
        $this->instagram_url = '';
    }

    public function store_old()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('branch_manage'), 403);

        $this->validate([
            'name' => 'required|string|max:255',
            'radius' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        HrmsBranch::updateOrCreate(['id' => $this->branchId], [
     
            'name' => $this->name,
            'address' => $this->address,
            'lat' => $this->lat ?: null,
            'lng' => $this->lng ?: null,
            'radius' => $this->radius,
            'manager_id' => $this->manager_id ?: null,
            'status' => $this->status,
        ]);

        session()->flash('message', $this->branchId ? 'Branch Updated Successfully.' : 'Branch Created Successfully.');
        $this->closeModal();
        $this->loadBranches();
    }

        public function store()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('branch_manage'), 403);

        $this->validate([
            'name' => 'required|string|max:255',
            'radius' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive',
            'company_logo' => 'nullable|image|max:2048',
            'founded_year' => 'nullable|integer|min:1800|max:' . (date('Y') + 1),
            'company_size' => 'nullable|string|max:255',
        ]);
        
        $logoPath = $this->existing_company_logo;
        if ($this->company_logo) {
            $logoPath = $this->company_logo->store('company_logos', 'public');
        }

        $data = [
            'name' => $this->name,
            'address' => $this->address,
            'lat' => $this->lat ?: null,
            'lng' => $this->lng ?: null,
            'radius' => $this->radius,
            'manager_id' => $this->manager_id ?: null,
            'status' => $this->status,
            'company_logo' => $logoPath,
            'gst_number' => $this->gst_number ?: null,
            'founded_year' => $this->founded_year ?: null,
            'website' => $this->website ?: null,
            'company_size' => $this->company_size ?: null,
            'company_type' => $this->company_type ?: null,
            'industry' => $this->industry ?: null,
            'about_company' => $this->about_company ?: null,
            'linkedin_url' => $this->linkedin_url ?: null,
            'facebook_url' => $this->facebook_url ?: null,
            'instagram_url' => $this->instagram_url ?: null,
        ];

        if ($this->branchId) {
            // Update existing branch
            $branch = HrmsBranch::findOrFail($this->branchId);
            $branch->update($data);
        } else {
            // Create new branch
            $data['partner_id'] = null;
            $branch = HrmsBranch::create($data);
        }

        // As soon as a branch is created with a manager, set them up as a
        // branch manager (create the manager role + grant permissions).
        if (!$this->branchId && $branch->manager_id) {
            $manager = \App\Models\User::find($branch->manager_id);
            if ($manager) {
                $manager->assignBranchManagerRole($this->getPartnerId());
            }
        }

        session()->flash('message', $this->branchId ? 'Branch Updated Successfully.' : 'Branch Created Successfully.');
        $this->closeModal();
        $this->loadBranches();
    }

    public $viewBranch = null;
    public $isViewOpen = false;

    public function view($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('branch_viewAny'), 403);
        
        $this->viewBranch = HrmsBranch::with(['manager', 'employees'])->findOrFail($id);
        $this->isViewOpen = true;
    }

    public function closeViewModal()
    {
        $this->isViewOpen = false;
        $this->viewBranch = null;
    }

    public function edit($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('branch_manage'), 403);
        
        $branch = HrmsBranch::findOrFail($id);
        
        $this->branchId = $id;
        $this->name = $branch->name;
        $this->address = $branch->address;
        $this->lat = $branch->lat;
        $this->lng = $branch->lng;
        $this->radius = $branch->radius;
        $this->manager_id = $branch->manager_id;
        $this->status = $branch->status;

        $this->existing_company_logo = $branch->company_logo;
        $this->gst_number = $branch->gst_number;
        $this->founded_year = $branch->founded_year;
        $this->website = $branch->website;
        $this->company_size = $branch->company_size;
        $this->company_type = $branch->company_type;
        $this->industry = $branch->industry;
        $this->about_company = $branch->about_company;
        $this->linkedin_url = $branch->linkedin_url;
        $this->facebook_url = $branch->facebook_url;
        $this->instagram_url = $branch->instagram_url;

        $this->openModal();
    }

    public function delete($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('branch_manage'), 403);
        
        HrmsBranch::where(function ($q) { 
            if (!auth()->user()->isSuperAdmin()) { 
               // $q->where('partner_id', $this->getPartnerId()); 
            } 
        })->findOrFail($id)->delete();
        
        session()->flash('message', 'Branch Deleted Successfully.');
        $this->loadBranches();
    }

    public function render()
    {
        return view('livewire.partner.hrms.branches')
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Branches',
                'pageSubtitle' => 'Manage your business locations and geofencing',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
