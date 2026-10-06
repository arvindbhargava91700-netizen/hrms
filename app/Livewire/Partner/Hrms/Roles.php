<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Roles extends Component
{
    use HasPartnerId;

    public $permissionGroups = [];

    public $roleName;

    public $selectedPermissions = [];

    public $editingRoleId = null;

    public $isRoleModalOpen = false;

    public $isViewModalOpen = false;

    public $viewingRole = null;

    public function selectViewPermission($module, $action)
    {
        $viewActions = ['viewAny', 'viewBranch', 'viewTeam'];

        // Current module ki sabhi view permissions
        $viewPermissions = collect($viewActions)
            ->map(fn ($viewAction) => strtolower($module).'_'.strtolower($viewAction))
            ->toArray();

        // Selected permission
        $selectedPermission = strtolower($module).'_'.strtolower($action);

        // Pehle current module ki saari view permissions remove karo
        $this->selectedPermissions = array_values(
            array_diff($this->selectedPermissions, $viewPermissions)
        );

        // Agar same permission select nahi thi to select karo
        $this->selectedPermissions[] = $selectedPermission;

        // Duplicate remove
        $this->selectedPermissions = array_values(
            array_unique($this->selectedPermissions)
        );
    }

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('role_viewAny'), 403);
        $this->permissionGroups = [
            // 'Listing' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            // 'Customer' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            // 'Visit' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            // 'Booking' => ['viewAny', 'viewOwn', 'create', 'update', 'delete', 'status_update'],
            // 'Subscription' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            // 'Invoice' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            // 'Wallet' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            // 'Payment' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            // 'Package' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            // 'Review' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],
            // 'PlatformPlan' => ['viewAny', 'viewOwn', 'create', 'update', 'delete'],

            // HRMS Modules
            'Leave' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'approved', 'rejected'],
            'Attendance' => ['viewAny', 'viewBranch', 'viewTeam',  'viewOwn', 'create', 'update', 'delete', 'manualOverride'],
            'Payroll' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn',  'create', 'update', 'delete'],
            'Department' => ['viewAny', 'viewBranch', 'create', 'update', 'delete'],
            'Branch' => ['viewAny', 'viewBranch', 'create', 'update', 'delete'],
            'Shift' => ['viewAny', 'viewBranch', 'viewOwn', 'create', 'update', 'delete'],
            'Staff' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete'],
            'Role' => ['viewAny', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'Salary' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'update'],

            'Performance' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn'],

            'Holiday' => ['viewAny', 'create', 'update', 'delete'],
            'Lead' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete'],
            'LeadOrder' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn',  'create',  'approve', 'reject'],
            'Recovery' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn',  'update'],
            'RecoveryHistory' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'export'],
            'Target' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn',  'create', 'update', 'delete'],
            'CustomerVisit' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete'],

            'Task' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn',  'create', 'update'],
            'Expense' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update'],
            'Notice' => ['viewAny', 'viewBranch',  'viewTeam', 'viewOwn', 'create', 'update', 'delete'],

            'Pip' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn',  'create', 'update', 'delete'],
            'Recruitment' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete'],
            'JobPost' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete'],
            'AppliedJobPost' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn',  'update_status'],
            'Probation' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn',  'create', 'update', 'delete'],
            'ResignationExit' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn',  'create', 'update', 'delete'],

            'ExitReason' => ['viewAny', 'viewBranch', 'viewOwn', 'create', 'update', 'delete'],
            'EmployeeCost' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn',  'export'],
            'Training' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn',  'create', 'update', 'delete', 'assign', 'attendance', 'assessment', 'complete'],
            'Asset' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn',  'create', 'update', 'delete', 'issue', 'return', 'damage'],

            'HrmsSetting' => ['manage'],
        ];

        foreach ($this->permissionGroups as $module => $actions) {
            foreach ($actions as $action) {
                $permName = strtolower($module).'_'.strtolower($action);
                Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            }
        }
    }

    public function createRole()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('role_create'), 403);
        $this->reset(['roleName', 'selectedPermissions', 'editingRoleId']);
        $this->isRoleModalOpen = true;
    }

    public function editRole($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('role_update'), 403);
        $role = Role::findOrFail($id);
        $this->editingRoleId = $role->id;
        // Strip out the partner ID prefix or suffix
        $this->roleName = str_replace([$this->getPartnerId().'_', '_'.$this->getPartnerId()], '', $role->name);
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->isRoleModalOpen = true;
    }

    public function viewRole($id)
    {
        $this->viewingRole = Role::with('permissions')->findOrFail($id);
        $this->isViewModalOpen = true;
    }

    public function toggleModule($module)
    {
        $actions = $this->permissionGroups[$module] ?? [];
        $modulePerms = collect($actions)->map(fn ($a) => strtolower($module).'_'.strtolower($a))->toArray();

        $currentModulePerms = array_intersect($modulePerms, $this->selectedPermissions);

        if (count($currentModulePerms) === count($modulePerms)) {
            // Uncheck all
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $modulePerms));
        } else {
            // Check all
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $modulePerms)));
        }
    }

    public function saveRole()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess($this->editingRoleId ? 'role_update' : 'role_create'), 403);
        $this->validate([
            'roleName' => 'required|string|max:50',
            'selectedPermissions' => 'array',
        ]);

        $fullRoleName = $this->getPartnerId().'_'.$this->roleName;
        // In actual implementation, we might use a dedicated field for display_name, but for simplicity we use name

        $role = Role::updateOrCreate(
            ['id' => $this->editingRoleId],
            ['name' => $fullRoleName, 'guard_name' => 'web']
        );

        $role->syncPermissions($this->selectedPermissions);

        $this->isRoleModalOpen = false;
        session()->flash('success', 'Role saved successfully.');
    }

    public function deleteRole($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('role_delete'), 403);
        Role::findOrFail($id)->delete();
    }

    public function render()
    {
        $permissions = Permission::all();

        $query = Role::with('permissions')->withCount('users')->latest();

        if (auth()->user()->isSuperAdmin()) {
            // super_admin sees all roles across all partners
              $query->where('name', '!=', "super_admin");
        } else {
            $query->where('name', 'like', '%'.$this->getPartnerId().'%');
        }

        $roles = $query->get();

        return view('livewire.partner.hrms.roles', compact('roles', 'permissions'))
            ->layout('layouts.app', [
                'panelName' => 'HRMS Module',
                'pageTitle' => 'Roles & Permissions',
                'pageSubtitle' => 'Manage custom roles for your staff',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
