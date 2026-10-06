<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DepartmentBranchHead;
use App\Models\HrmsBranch;
use App\Models\User;
use App\Models\WorkShift;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OrganizationController extends Controller
{
    /**
     * Get Roles
     */
    public function getRoles(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        $partnerId = $user->isPartner()
            ? $user->id
            : $user->parent_id;

        $prefix = $partnerId.'_';

        $query = Role::query()
            ->where('name', 'like', $partnerId.'\_%');

        /*
        |--------------------------------------------------------------------------
        | Search Role Name
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(
                'name',
                'like',
                '%'.$prefix.$search.'%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Get Roles
        |--------------------------------------------------------------------------
        */

        $roles = $query
            ->withCount('permissions')
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Remove Partner ID Prefix
        |--------------------------------------------------------------------------
        */

        $roles->transform(function ($role) use ($prefix) {

            if (str_starts_with($role->name, $prefix)) {

                $role->name = substr(
                    $role->name,
                    strlen($prefix)
                );
            }

            return $role;
        });

        return response()->json([
            'status' => 'success',
            'data' => $roles,
        ]);
    }

    /**
     * Get Role Details
     */
    public function getRole($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $role = Role::with('permissions')
            ->where('id', $id)
            ->where('name', 'like', '%'.$partnerId.'%')
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $role,
        ]);
    }

    /**
     * Get All Permissions
     *
     * Returns the same HRMS permission groups used by the web Roles screen,
     * grouped by module with the correct keys, titles and permission names,
     * so web login and mobile API behave identically.
     */
    public function getPermissions(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $modules = $this->hrmPermissionModules();

        // Roles are explicitly created with the 'web' guard in createRole()
        // so permissions must also strictly use 'web' guard.
        $guard = 'web';
        
        $permissionNames = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissionNames[] = strtolower($module).'_'.strtolower($action);
            }
        }

        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        // Flag each permission with a status: 1 if the role has it, 0 otherwise.
        // When role_id is supplied, use that role; otherwise fall back to the
        // authenticated user's own role(s) so the mobile app gets correct
        // permission statuses without needing to know the role_id.
        $rolePermissionNames = [];
        if ($request->filled('role_id') || $request->filled('role_name')) {
            $roleQuery = Role::query()
                ->where('name', 'like', '%'.$partnerId.'%');

            if ($request->filled('role_id')) {
                $roleQuery->where('id', $request->role_id);
            } else {
                $roleQuery->where('name', $partnerId.'_'.$request->role_name);
            }

            $role = $roleQuery->first();

            if (! $role) {
                // Don't silently return all statuses as 0: the role either
                // belongs to another partner or doesn't exist.
                return response()->json([
                    'status' => 'error',
                    'message' => 'Role not found for your organization.',
                ], 404);
            }

            $rolePermissionNames = $role->permissions()->pluck('name')->toArray();
        } else {
            // Default: use the authenticated user's role permissions
            // Fetch directly from relationship to avoid Spatie cached stale data
            $rolePermissionNames = $user->roles()->with('permissions')->get()
                ->pluck('permissions')
                ->flatten()
                ->pluck('name')
                ->toArray();
                
            $directPerms = $user->permissions()->pluck('name')->toArray();
            $rolePermissionNames = array_unique(array_merge($rolePermissionNames, $directPerms));
        }

        $permissions = Permission::whereIn('name', $permissionNames)
            ->where('guard_name', $guard)
            ->get()
            ->keyBy('name');

        $groups = [];

        foreach ($modules as $module => $actions) {
            $moduleKey = strtolower($module);
            $groupPermissions = [];

            foreach ($actions as $action) {
                $permName = $moduleKey.'_'.strtolower($action);
                $permission = $permissions->get($permName);

                if (! $permission) {
                    continue;
                }

                $groupPermissions[] = [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'action' => $action, // Useful for displaying labels in the app
                    'guard_name' => $permission->guard_name,
                    'status' => in_array($permission->name, $rolePermissionNames) ? 1 : 0,
                    'is_assigned' => in_array($permission->name, $rolePermissionNames), // Boolean representation
                ];
            }

            $groups[] = [
                'module' => $moduleKey,
                'title' => $this->moduleTitle($moduleKey),
                'permissions' => $groupPermissions,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $groups,
        ]);
    }

    /**
     * HRMS permission modules & actions, mirroring the web Roles screen
     * (app/Livewire/Partner/Hrms/Roles.php). Permissions are stored as
     * "module_action" (e.g. leadorder_viewany).
     */
    private function hrmPermissionModules(): array
    {
        return [
            'Leave' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'approved', 'rejected'],
            'Attendance' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete', 'manualOverride'],
            'Payroll' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete'],
            'Department' => ['viewAny', 'viewBranch', 'create', 'update', 'delete'],
            'Branch' => ['viewAny', 'viewBranch', 'create', 'update', 'delete'],
            'Shift' => ['viewAny', 'viewBranch', 'viewOwn', 'create', 'update', 'delete'],
            'Staff' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete'],
            'Role' => ['viewAny', 'viewBranch', 'viewTeam', 'create', 'update', 'delete'],
            'Salary' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'update'],
            'Performance' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn'],
            'Holiday' => ['viewAny', 'create', 'update', 'delete'],
            'Lead' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete'],
            'LeadOrder' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'approve', 'reject'],
            'Recovery' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'update'],
            'RecoveryHistory' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'export'],
            'Target' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete'],
            'CustomerVisit' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete'],
            'Task' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update'],
            'Expense' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update'],
            'Notice' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete'],
            'Pip' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete'],
            'Recruitment' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete'],
            'JobPost' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete'],
            'AppliedJobPost' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn',  'update_status'],
            'Probation' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete'],
            'ResignationExit' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete'],
            'ExitReason' => ['viewAny', 'viewBranch', 'viewOwn', 'create', 'update', 'delete'],
            'EmployeeCost' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'export'],
            'Training' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete', 'assign', 'attendance', 'assessment', 'complete'],
            'Asset' => ['viewAny', 'viewBranch', 'viewTeam', 'viewOwn', 'create', 'update', 'delete', 'issue', 'return', 'damage'],
            'HrmsSetting' => ['manage'],
        ];
    }

    /**
     * Map a permission module key to a display title.
     */
    private function moduleTitle(string $module): string
    {
        $titles = [
            'leave' => 'Leave',
            'attendance' => 'Attendance',
            'payroll' => 'Payroll',
            'department' => 'Department',
            'branch' => 'Branch',
            'shift' => 'Work Shift',
            'staff' => 'Staff',
            'role' => 'Role',
            'salary' => 'Salary',
            'performance' => 'Performance',
            'holiday' => 'Holiday',
            'lead' => 'Leads',
            'leadorder' => 'Lead Orders',
            'recovery' => 'Recovery',
            'recoveryhistory' => 'Recovery History',
            'target' => 'Targets',
            'customervisit' => 'Customer Visit',
            'task' => 'Tasks',
            'expense' => 'Expenses',
            'notice' => 'Notices',
            'pip' => 'PIP',
            'recruitment' => 'Recruitment',
            'jobpost' => 'Job Posts',
            'appliedjobpost' => 'Applied Job Posts',
            'probation' => 'Probation',
            'resignationexit' => 'Resignation & Exit',
            'exitreason' => 'Exit Reason',
            'employeecost' => 'Employee Cost',
            'training' => 'Training',
            'asset' => 'Assets',
            'hrmssetting' => 'HRMS Settings',
        ];

        return $titles[$module] ?? ucfirst(str_replace('_', ' ', $module));
    }

    public function createRole(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (! $user->isPartner() && ! $user->canAccess('role_create')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:50',
            'permissions' => 'array',
        ]);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $fullRoleName = $partnerId.'_'.$request->name;
        $role = Role::create(['name' => $fullRoleName, 'guard_name' => 'web']);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json(['status' => 'success', 'data' => $role], 201);
    }

    public function updateRole(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (! $user->isPartner() && ! $user->canAccess('role_update')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:50',
            'permissions' => 'array',
        ]);

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $role = Role::where('id', $id)->where('name', 'like', '%'.$partnerId.'%')->firstOrFail();

        $fullRoleName = $partnerId.'_'.$request->name;
        $role->update(['name' => $fullRoleName]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json(['status' => 'success', 'data' => $role]);
    }

    public function deleteRole($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (! $user->isPartner() && ! $user->canAccess('role_delete')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $role = Role::where('id', $id)->where('name', 'like', '%'.$partnerId.'%')->firstOrFail();
        $role->delete();

        return response()->json(['status' => 'success', 'message' => 'Role deleted successfully.']);
    }

    /**
     * Get Departments
     */
    public function getDepartments(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $query = Department::where('partner_id', $partnerId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $departments = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $departments,
        ]);
    }

    /**
     * Get Branches
     */
    public function getBranches(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $query = HrmsBranch::where('partner_id', $partnerId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $branches = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $branches,
        ]);
    }

    /**
     * Get Work Shifts
     */
    public function getWorkShifts(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $query = WorkShift::where('partner_id', $partnerId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $shifts = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $shifts,
        ]);
    }

    // CREATE Endpoints

    public function createDepartment(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (! $user->isPartner() && ! $user->canAccess('department_create')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:departments,id',
            'branch_heads' => 'nullable|array',
            'branch_heads.*' => 'nullable|exists:users,id',
        ]);
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $department = Department::create([
            'name' => $request->name,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'partner_id' => $partnerId,
        ]);

        if ($request->has('branch_heads') && is_array($request->branch_heads)) {
            foreach ($request->branch_heads as $branch_id => $head_id) {
                if ($head_id) {
                    DepartmentBranchHead::create([
                        'department_id' => $department->id,
                        'branch_id' => $branch_id,
                        'head_id' => $head_id,
                    ]);
                }
            }
        }

        return response()->json(['status' => 'success', 'data' => $department], 201);
    }

    public function updateDepartment(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (! $user->isPartner() && ! $user->canAccess('department_update')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:departments,id',
            'branch_heads' => 'nullable|array',
            'branch_heads.*' => 'nullable|exists:users,id',
        ]);
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $department = Department::where('id', $id)->where('partner_id', $partnerId)->firstOrFail();
        $department->update([
            'name' => $request->name,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
        ]);

        if ($request->has('branch_heads') && is_array($request->branch_heads)) {
            DepartmentBranchHead::where('department_id', $department->id)->delete();
            foreach ($request->branch_heads as $branch_id => $head_id) {
                if ($head_id) {
                    DepartmentBranchHead::create([
                        'department_id' => $department->id,
                        'branch_id' => $branch_id,
                        'head_id' => $head_id,
                    ]);
                }
            }
        }

        return response()->json(['status' => 'success', 'data' => $department]);
    }

    public function deleteDepartment($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (! $user->isPartner() && ! $user->canAccess('department_delete')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $department = Department::where('id', $id)->where('partner_id', $partnerId)->firstOrFail();
        $department->delete();

        return response()->json(['status' => 'success', 'message' => 'Department deleted successfully.']);
    }

    public function createBranch(Request $request): JsonResponse
    {

        $user = auth('hrms_api')->user();
        if (! $user->isPartner() && ! $user->canAccess('branch_create')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'radius' => 'nullable|integer|min:1',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:active,inactive',
        ]);
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $branch = HrmsBranch::create([
            'partner_id' => $partnerId,
            'name' => $request->name,
            'address' => $request->address,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'radius' => $request->radius ?? 100,
            'manager_id' => $request->manager_id,
            'status' => $request->status ?? 'active',
        ]);

        return response()->json(['status' => 'success', 'data' => $branch], 201);
    }

    public function updateBranch(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (! $user->isPartner() && ! $user->canAccess('branch_update')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'radius' => 'nullable|integer|min:1',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:active,inactive',
        ]);
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $branch = HrmsBranch::where('id', $id)->where('partner_id', $partnerId)->firstOrFail();
        $branch->update([
            'name' => $request->name,
            'address' => $request->address,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'radius' => $request->radius ?? 100,
            'manager_id' => $request->manager_id,
            'status' => $request->status ?? 'active',
        ]);

        return response()->json(['status' => 'success', 'data' => $branch]);
    }

    public function deleteBranch($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (! $user->isPartner() && ! $user->canAccess('branch_delete')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $branch = HrmsBranch::where('id', $id)->where('partner_id', $partnerId)->firstOrFail();
        $branch->delete();

        return response()->json(['status' => 'success', 'message' => 'Branch deleted successfully.']);
    }

    public function createWorkShift(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        if (! $user->isPartner() && ! $user->canAccess('shift_create')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',

            'start_time' => 'required|date_format:H:i',

            'end_time' => 'required|date_format:H:i',

            'branch_id' => 'nullable|exists:branches,id',

            'auto_mark_attendance' => 'nullable|boolean',

            'auto_mark_status' => 'required|in:absent,present,half_day',

            'late_tolerance_minutes' => 'required|integer|min:0',

            'week_off_days' => 'nullable|array',

            'week_off_days.*' => 'string',
        ]);

        $partnerId = $user->isPartner()
            ? $user->id
            : $user->parent_id;

        /*
        |--------------------------------------------------------------------------
        | Calculate Minutes Dynamically
        |--------------------------------------------------------------------------
        */

        $calculatedMins = $this->calculateShiftMinutes(
            $request->start_time,
            $request->end_time
        );

        /*
        |--------------------------------------------------------------------------
        | Check Overlapping Shift
        |--------------------------------------------------------------------------
        */

        $existingShift = WorkShift::where('partner_id', $partnerId)
            ->when(
                $request->filled('branch_id'),
                fn ($query) => $query->where(
                    'branch_id',
                    $request->branch_id
                ),
                fn ($query) => $query->whereNull('branch_id')
            )
            ->where('start_time', '<', $request->end_time)
            ->where('end_time', '>', $request->start_time)
            ->first();

        if ($existingShift) {
            return response()->json([
                'status' => 'error',
                'message' => 'Another shift already exists for this time.',
                'data' => [
                    'existing_shift_id' => $existingShift->id,
                    'existing_shift_name' => $existingShift->name,
                    'existing_start_time' => $existingShift->start_time,
                    'existing_end_time' => $existingShift->end_time,
                ],
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Create Shift
        |--------------------------------------------------------------------------
        */

        $shift = WorkShift::create([
            'partner_id' => $partnerId,

            'branch_id' => $request->branch_id ?: null,

            'name' => $request->name,

            'start_time' => $request->start_time,

            'end_time' => $request->end_time,

            'auto_mark_attendance' => $request->boolean('auto_mark_attendance'),

            'auto_mark_status' => $request->auto_mark_status,

            'late_tolerance_minutes' => $request->late_tolerance_minutes,

            // Dynamic values
            'min_present_mins' => $calculatedMins['min_present_mins'],

            'min_half_day_mins' => $calculatedMins['min_half_day_mins'],

            'auto_absent_mark_mins' => $calculatedMins['auto_absent_mark_mins'],

            'week_off_days' => $request->filled('week_off_days')
                    ? json_encode($request->week_off_days)
                    : null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Work shift created successfully.',
            'data' => $shift,
        ], 201);
    }

    public function updateWorkShift(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();

        if (! $user->isPartner() && ! $user->canAccess('shift_update')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',

            'start_time' => 'required|date_format:H:i',

            'end_time' => 'required|date_format:H:i',

            'branch_id' => 'nullable|exists:branches,id',

            'auto_mark_attendance' => 'nullable|boolean',

            'auto_mark_status' => 'required|in:absent,present,half_day',

            'late_tolerance_minutes' => 'required|integer|min:0',

            'week_off_days' => 'nullable|array',

            'week_off_days.*' => 'string',
        ]);

        $partnerId = $user->isPartner()
            ? $user->id
            : $user->parent_id;

        /*
        |--------------------------------------------------------------------------
        | Get Existing Shift
        |--------------------------------------------------------------------------
        */

        $shift = WorkShift::where('id', $id)
            ->where('partner_id', $partnerId)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Calculate Minutes Dynamically
        |--------------------------------------------------------------------------
        */

        $calculatedMins = $this->calculateShiftMinutes(
            $request->start_time,
            $request->end_time
        );

        /*
        |--------------------------------------------------------------------------
        | Check Overlapping Shift
        |--------------------------------------------------------------------------
        | Exclude current shift
        */

        $existingShift = WorkShift::where('partner_id', $partnerId)
            ->where('id', '!=', $id)
            ->when(
                $request->filled('branch_id'),
                fn ($query) => $query->where(
                    'branch_id',
                    $request->branch_id
                ),
                fn ($query) => $query->whereNull('branch_id')
            )
            ->where('start_time', '<', $request->end_time)
            ->where('end_time', '>', $request->start_time)
            ->first();

        if ($existingShift) {
            return response()->json([
                'status' => 'error',
                'message' => 'Another shift already exists for this time.',
                'data' => [
                    'existing_shift_id' => $existingShift->id,
                    'existing_shift_name' => $existingShift->name,
                    'existing_start_time' => $existingShift->start_time,
                    'existing_end_time' => $existingShift->end_time,
                ],
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Update Shift
        |--------------------------------------------------------------------------
        */

        $shift->update([
            'branch_id' => $request->branch_id ?: null,

            'name' => $request->name,

            'start_time' => $request->start_time,

            'end_time' => $request->end_time,

            'auto_mark_attendance' => $request->boolean('auto_mark_attendance'),

            'auto_mark_status' => $request->auto_mark_status,

            'late_tolerance_minutes' => $request->late_tolerance_minutes,

            // Dynamic values
            'min_present_mins' => $calculatedMins['min_present_mins'],

            'min_half_day_mins' => $calculatedMins['min_half_day_mins'],

            'auto_absent_mark_mins' => $calculatedMins['auto_absent_mark_mins'],

            'week_off_days' => $request->filled('week_off_days')
                    ? json_encode($request->week_off_days)
                    : null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Work shift updated successfully.',
            'data' => $shift->fresh(),
        ]);
    }

    public function deleteWorkShift($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        if (! $user->isPartner() && ! $user->canAccess('shift_delete')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $shift = WorkShift::where('id', $id)->where('partner_id', $partnerId)->firstOrFail();
        $shift->delete();

        return response()->json(['status' => 'success', 'message' => 'Work Shift deleted successfully.']);
    }

    private function calculateShiftMinutes(string $startTime, string $endTime): array
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);

        // Support overnight shifts
        // if ($end->lt($start)) {
        //     $end->addDay();
        // }

        $totalMins = $start->diffInMinutes($end);

        return [
            'min_present_mins' => $totalMins,
            'min_half_day_mins' => (int) round($totalMins / 2),
            'auto_absent_mark_mins' => $totalMins + 120,
        ];
    }

    /**
     * Department listing with parent, branch heads and employee counts.
     */
    public function departmentListing(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $departments = Department::where('partner_id', $partnerId)
            ->with(['parent', 'branchHeads.head', 'branchHeads.branch'])
            ->withCount('employees')
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $departments,
        ]);
    }

    /**
     * Employee listing (same scope as the partner team employee ids).
     */
    public function employeesListing(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        if ($user->isPartner()) {
            $employeeIds = User::where('parent_id', $partnerId)
                ->where('role', 'employee')
                ->pluck('id')
                ->toArray();
        } else {
            $employeeIds = $user->getTeamIds();
        }

        $employees = User::whereIn('id', $employeeIds)->get();

        return response()->json([
            'status' => 'success',
            'data' => $employees,
        ]);
    }

    /**
     * Branch listing.
     */
    public function branchesListing(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $branches = HrmsBranch::where('partner_id', $partnerId)->get();

        return response()->json([
            'status' => 'success',
            'data' => $branches,
        ]);
    }
}
