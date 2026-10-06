<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class AdminStaff extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $search = '';
    public $staffId, $name, $email, $password, $mobile;
    public $selectedPermissions = [];
    public $availablePermissions = [];
    
    public $isEditMode = false;
    public $showModal = false;

    protected $listeners = ['refreshStaff' => '$refresh'];

    public function mount()
    {
        // Load only admin permissions
        $this->availablePermissions = Permission::where('name', 'like', 'admin_%')->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function createStaff()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function editStaff($id)
    {
        $this->resetInputFields();
        $this->isEditMode = true;
        
        $staff = User::findOrFail($id);
        $this->staffId = $staff->id;
        $this->name = $staff->name;
        $this->email = $staff->email;
        $this->mobile = $staff->mobile;
        
        // Load direct permissions assigned to this user
        $this->selectedPermissions = $staff->permissions()->pluck('name')->toArray();
        
        $this->showModal = true;
    }

    public function saveStaff()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->staffId,
            'mobile' => 'nullable|string|max:20|unique:users,mobile,' . $this->staffId,
        ];

        if (!$this->isEditMode) {
            $rules['password'] = 'required|min:6';
        } else {
            $rules['password'] = 'nullable|min:6';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->isEditMode) {
            $staff = User::findOrFail($this->staffId);
            $staff->update($data);
        } else {
            $data['role'] = 'admin';
            $data['status'] = 'active';
            $staff = User::create($data);
            $staff->assignRole('admin');
        }

        // Sync permissions directly to the user (bypassing role permissions so each admin can have custom access)
        $staff->syncPermissions($this->selectedPermissions);

        $this->showModal = false;
        session()->flash('success', $this->isEditMode ? 'Admin updated successfully' : 'Admin created successfully');
    }

    public function deleteStaff($id)
    {
        $staff = User::findOrFail($id);
        if ($staff->id === auth()->id()) {
            session()->flash('error', 'You cannot delete yourself.');
            return;
        }
        $staff->delete();
        session()->flash('success', 'Admin deleted successfully');
    }

    public function toggleStatus($id)
    {
        $staff = User::findOrFail($id);
        if ($staff->id === auth()->id()) {
            session()->flash('error', 'You cannot deactivate yourself.');
            return;
        }
        $staff->status = $staff->status === 'active' ? 'inactive' : 'active';
        $staff->save();
        session()->flash('success', 'Status updated successfully');
    }

    private function resetInputFields()
    {
        $this->staffId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->mobile = '';
        $this->selectedPermissions = [];
    }

    public function render()
    {
        $staffs = User::where('role', 'admin')
            ->where(function($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.admin-staff', compact('staffs'))
            ->layout('layouts.app', [
                'title' => 'Manage Admin Staff',
                'panelName' => 'Admin Panel',
                'sidebarLinks' => view('partials.sidebar-admin')->render(),
                'pageTitle' => 'Admin Staff',
                'pageSubtitle' => 'Manage sub-admins and their permissions'
            ]);
    }
}
