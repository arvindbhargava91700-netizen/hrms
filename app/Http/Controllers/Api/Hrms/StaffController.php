<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    /**
     * Get list of staff
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        
        $query = User::where('parent_id', $partnerId)
            ->with(['roles', 'department', 'manager', 'branch', 'shift']);

        if (!$user->isPartner() && !$user->canAccess('staff_viewAny')) {
            $teamIds = $user->getTeamIds();
            $query->whereIn('id', $teamIds);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('employment_status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('team_id') || $request->filled('reporting_to')) {
            $teamVal = $request->input('team_id', $request->input('reporting_to'));
            $query->where('reporting_to', $teamVal);
        }

        if ($request->filled('role_id')) {
            $roleId = $request->role_id;
            $query->whereHas('roles', function($q) use ($roleId) {
                $q->where('id', $roleId);
            });
        }

        if ($request->filled('shift_id')) {
            $query->where('shift_id', $request->shift_id);
        }

        $staff = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Staff fetched successfully.',
            'data' => $staff
        ]);
    }

    /**
     * Get single staff profile
     */
    public function show($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        
        $staff = User::with(['roles', 'department', 'manager', 'branch', 'shift'])->findOrFail($id);

        $userPartnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $staffPartnerId = $staff->isPartner() ? $staff->id : $staff->parent_id;

        if ($staffPartnerId !== $userPartnerId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        if ($staff->id !== $user->id && !$user->isPartner() && !$user->canAccess('staff_viewAny')) {
            $teamIds = $user->getTeamIds();
            if (!in_array($staff->id, $teamIds)) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Staff profile fetched successfully.',
            'data' => $staff
        ]);
    }

    /**
     * Create new staff
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        
        if (!$user->isPartner() && !$user->canAccess('staff_create')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|string|unique:users,mobile',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'employee_code' => 'nullable|string|max:50|unique:users,employee_code',
            'basic_salary' => 'nullable|numeric|min:0',
            'department_id' => 'nullable|exists:departments,id',
            'reporting_to' => 'nullable|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id',
            'shift_id' => 'nullable|exists:work_shifts,id',
            'joining_date' => 'nullable|date',
            'resignation_date' => 'nullable|date',
            'termination_date' => 'nullable|date',
            'employment_status' => 'nullable|string|in:active,resigned,terminated,on_leave',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        DB::beginTransaction();
        try {
            $staff = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'mobile' => $validated['mobile'],
                'employee_code' => $validated['employee_code'] ?? null,
                'password' => Hash::make($validated['password']),
                'parent_id' => $partnerId,
                'role' => 'employee',
                'basic_salary' => $validated['basic_salary'] ?? 0,
                'department_id' => $validated['department_id'] ?? null,
                'reporting_to' => $validated['reporting_to'] ?? null,
                'branch_id' => $validated['branch_id'] ?? null,
                'shift_id' => $validated['shift_id'] ?? null,
                'joining_date' => $validated['joining_date'] ?? null,
                'resignation_date' => $validated['resignation_date'] ?? null,
                'termination_date' => $validated['termination_date'] ?? null,
                'employment_status' => $validated['employment_status'] ?? 'active',
                'status' => $validated['status'] ?? 'active',
            ]);

            $role = Role::findById($validated['role_id'], 'web');
            if ($role) {
                $staff->assignRole($role);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Staff created successfully.',
                'data' => $staff
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create staff: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update existing staff
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        
        if (!$user->isPartner() && !$user->canAccess('staff_update')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $staff = User::findOrFail($id);
        
        $userPartnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $staffPartnerId = $staff->isPartner() ? $staff->id : $staff->parent_id;

        if ($staffPartnerId !== $userPartnerId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($staff->id)],
            'mobile' => ['sometimes', 'required', 'string', Rule::unique('users')->ignore($staff->id)],
            'employee_code' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($staff->id)],
            'password' => 'nullable|string|min:6',
            'role_id' => 'sometimes|required|exists:roles,id',
            'basic_salary' => 'nullable|numeric|min:0',
            'department_id' => 'nullable|exists:departments,id',
            'reporting_to' => 'nullable|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id',
            'shift_id' => 'nullable|exists:work_shifts,id',
            'joining_date' => 'nullable|date',
            'resignation_date' => 'nullable|date',
            'termination_date' => 'nullable|date',
            'employment_status' => 'nullable|string|in:active,resigned,terminated,on_leave',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        DB::beginTransaction();
        try {
            $updateData = $request->only([
                'name', 'email', 'mobile', 'employee_code', 'basic_salary',
                'department_id', 'reporting_to', 'branch_id', 'shift_id',
                'joining_date', 'resignation_date', 'termination_date', 'employment_status', 'status'
            ]);

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $staff->update($updateData);

            if ($request->has('role_id')) {
                $role = Role::findById($validated['role_id'], 'web');
                if ($role) {
                    $staff->syncRoles([$role]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Staff updated successfully.',
                'data' => $staff
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update staff: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete staff
     */
    public function destroy($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        
        if (!$user->isPartner() && !$user->canAccess('staff_delete')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $staff = User::findOrFail($id);
        
        $userPartnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $staffPartnerId = $staff->isPartner() ? $staff->id : $staff->parent_id;

        if ($staffPartnerId !== $userPartnerId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }
        
        if ($staff->id === $user->id) {
            return response()->json(['status' => 'error', 'message' => 'You cannot delete yourself.'], 400);
        }

        $staff->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Staff deleted successfully.'
        ]);
    }
}
