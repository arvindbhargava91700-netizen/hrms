<?php

namespace App\Models;

use App\Services\PackageService;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name', 'email', 'mobile', 'role', 'status', 'password','device_id',
        'email_verified_at', 'mobile_verified_at', 'fcm_token', 'wallet_balance', 'profile_image',
        'parent_id', 'basic_salary', 'department_id', 'reporting_to', 'employee_code',
        'branch_id', 'shift_id', 'joining_date', 'resignation_date', 'termination_date', 'employment_status', 'designation_id', 'working_mode',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $appends = ['profile_image_url'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function scopeActiveForHrms($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(fn ($m) => $m->id = $m->id ?: (string) Str::uuid());
    }

    // ── Relationships ─────────────────────────────────────────
    public function listings()
    {
        return $this->hasMany(Listing::class, 'partner_id');
    }

    public function kycDocument()
    {
        return $this->hasOne(KycDocument::class, 'partner_id');
    }

    public function customerKyc()
    {
        return $this->hasOne(CustomerKyc::class, 'customer_id');
    }

    public function tpiMerchant()
    {
        return $this->hasOne(TpiMerchant::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'customer_id');
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Subscription::class, 'customer_id', 'subscription_id');
    }

    public function visitBookings()
    {
        return $this->hasMany(VisitBooking::class, 'partner_id');
    }

    public function customerVisits()
    {
        return $this->hasMany(VisitBooking::class, 'customer_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class, 'user_id');
    }

    public function partnerSubscriptions()
    {
        return $this->hasMany(PartnerSubscription::class, 'partner_id');
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class, 'partner_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'partner_categories', 'user_id', 'category_id');
    }

    // HRMS Relationships
    public function candidateProfile()
    {
        return $this->hasOne(CandidateProfile::class, 'user_id');
    }
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function branch()
    {
        return $this->belongsTo(HrmsBranch::class, 'branch_id');
    }

    public function reportingTo()
    {
        return $this->belongsTo(User::class, 'reporting_to');
    }

    public function reportees()
    {
        return $this->hasMany(User::class, 'reporting_to');
    }

    public function shift()
    {
        return $this->belongsTo(WorkShift::class, 'shift_id');
    }

    public function attendances()
    {
        return $this->hasMany(EmployeeAttendance::class, 'employee_id');
    }

    public function leaves()
    {
        return $this->hasMany(EmployeeLeave::class, 'employee_id');
    }

    public function payrolls()
    {
        return $this->hasMany(EmployeePayroll::class, 'employee_id');
    }

    // ── Helpers ───────────────────────────────────────────────
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin()
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isPartner()
    {
        return $this->role === 'partner';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function exits()
    {
        return $this->hasMany(EmployeeExit::class, 'employee_id');
    }

    public function getLastWorkingDate(): ?Carbon
    {
        if (! empty($this->termination_date)) {
            return Carbon::parse($this->termination_date);
        }

        $exit = $this->exits()
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($exit && $exit->last_working_date) {
            return Carbon::parse($exit->last_working_date);
        }

        if (! empty($this->resignation_date)) {
            return Carbon::parse($this->resignation_date);
        }

        return null;
    }

    public function hasExitedOnOrBefore($date = null): bool
    {
        $checkDate = $date ? Carbon::parse($date)->startOfDay() : Carbon::today()->startOfDay();
        $lwd = $this->getLastWorkingDate();

        if ($lwd && $checkDate->greaterThan($lwd->startOfDay())) {
            return true;
        }

        if (in_array($this->employment_status, ['exited', 'terminated'])) {
            if (! $lwd || $checkDate->greaterThanOrEqualTo($lwd->startOfDay())) {
                return true;
            }
        }

        return false;
    }

    public function scopeActiveForDate($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date)->format('Y-m-d') : Carbon::today()->format('Y-m-d');

        return $query->where(function ($q) use ($checkDate) {
            $q->whereNull('termination_date')->orWhere('termination_date', '>=', $checkDate);
        })->whereDoesntHave('exits', function ($eq) use ($checkDate) {
            $eq->whereNotNull('last_working_date')
                ->where('last_working_date', '<', $checkDate)
                ->whereNotIn('status', ['cancelled']);
        });
    }

    public function scopeAvailableForHrmsAssignment($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date)->format('Y-m-d') : Carbon::today()->format('Y-m-d');

        return $query
            ->where(function ($q) use ($checkDate) {
                $q->whereNull('resignation_date')
                    ->orWhere('resignation_date', '>', $checkDate);
            })
            ->where(function ($q) use ($checkDate) {
                $q->whereNull('termination_date')
                    ->orWhere('termination_date', '>', $checkDate);
            });
    }

    /**
     * Check if this partner/employee can access a specific module by slug.
     * Admins always have access. Resolves to the parent partner's package for employees.
     */
    public function canAccessModule(string $moduleSlug): bool
    {
        // Admins and Super Admins have unrestricted access to modules (or check permissions if needed, but keeping it simple for now)
        if ($this->isAdmin()) {
            return true;
        }

        $partnerId = $this->isPartner() ? $this->id : $this->parent_id;
        if (! $partnerId) {
            return false;
        }

        return PackageService::hasModule($partnerId, $moduleSlug);
    }

    public function hasApprovedKyc(): bool
    {
        if ($this->role === 'employee') {
            return User::find($this->parent_id)?->hasApprovedKyc() ?? false;
        }

        return $this->kycDocument?->status === 'approved';
    }

    public function canAccess(string $permission): bool
    {
        if ($this->isPartner() || $this->isAdmin()) {
            return true;
        }

        try {
            // Permissions are stored lowercase in the database
            // Explicitly use the 'web' guard since all role permissions are seeded under 'web'
            return $this->hasPermissionTo(strtolower($permission), 'web');
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    public function hasApprovedCustomerKyc(): bool
    {
        return $this->customerKyc?->status === 'approved';
    }

    public function getActiveTpiMerchantId(): ?string
    {
        return $this->tpiMerchant?->tpi_merchant_id;
    }

    public function getResolvedTpiMerchantId(): ?string
    {
        $merchantId = $this->getActiveTpiMerchantId();
        if (!$merchantId) {
            $admin = self::whereIn('role', ['super_admin'])->first();
            $merchantId = $admin ? $admin->getActiveTpiMerchantId() : null;
        }
        return $merchantId;
    }

    public function activeSubscriptions()
    {
        return $this->subscriptions()->where('status', 'active');
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        if (empty($this->attributes['profile_image'])) {
            return null;
        }
        $val = $this->attributes['profile_image'];
        if (str_starts_with($val, 'data:image')) {
            return $val;
        }
        if (str_contains($val, '/storage/')) {
            $parts = explode('/storage/', $val);
            return asset('storage/' . ltrim(end($parts), '/'));
        }

        return str_starts_with($val, 'http') ? $val : asset('storage/' . ltrim($val, '/'));
    }

    public function getAvatarUrlAttribute(): string
    {
        if (! empty($this->attributes['profile_image'])) {
            return $this->profile_image_url;
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=2563EB&color=fff';
    }

    public function getDashboardRouteAttribute(): string
    {
        return route('partner.dashboard');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'reporting_to');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'reporting_to');
    }

    public function getTeamIds(): array
    {
        $teamIds = [];

        // 1. Direct subordinates
        $subordinates = $this->subordinates()->pluck('id')->toArray();
        $teamIds = array_merge($teamIds, $subordinates);

        // 2. Departments where this user is the head
        $deptHeads = DepartmentBranchHead::where('head_id', $this->id)->get();

        foreach ($deptHeads as $deptHead) {
            // Find all child departments for this department
            $allDeptIds = [$deptHead->department_id];
            $queue = [$deptHead->department_id];

            while (count($queue) > 0) {
                $current = array_shift($queue);
                $children = Department::where('parent_id', $current)->pluck('id')->toArray();
                if (count($children) > 0) {
                    $allDeptIds = array_merge($allDeptIds, $children);
                    $queue = array_merge($queue, $children);
                }
            }

            // Get employees in these departments, strictly filtered by branch if the head is assigned to a specific branch
            $query = User::whereIn('department_id', $allDeptIds);
            if ($deptHead->branch_id) {
                $query->where('branch_id', $deptHead->branch_id);
            }
            $deptEmployees = $query->pluck('id')->toArray();
            $teamIds = array_merge($teamIds, $deptEmployees);
        }

        // 3. Branches where this user is the branch manager
        $managedBranches = HrmsBranch::where('manager_id', $this->id)->pluck('id')->toArray();
        if (count($managedBranches) > 0) {
            $branchEmployees = User::whereIn('branch_id', $managedBranches)->pluck('id')->toArray();
            $teamIds = array_merge($teamIds, $branchEmployees);
        }

        $teamIds = array_unique($teamIds);

        if (! in_array($this->id, $teamIds)) {
            $teamIds[] = $this->id;
        }

        return $teamIds;
    }

    public function assignBranchManagerRole($partner_id): void
    {
        $roleName = $partner_id ? $partner_id.'_Manager' : 'manager';

        $role = Role::firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        // Permissions covering every branch-management module
        $prefixes = [
            'shift_',        // Work Shifts
            'staff_',        // Staff
            'lead_',         // Lead Management + Product Category + Product (products reuse lead_*)
            'task_',         // Tasks
            'expense_',      // Expenses
            'notice_',       // Notices
            'hrmssetting_',  // HRMS Settings
            'attendance_',   // Attendance
            'leave_',        // Leaves
            'payroll_',      // Payroll + Commission (commission reuses payroll_*)
            'department_',   // Departments
            'branch_',       // Branches
            'attrition_',    // Attrition Analytics
            'exitreason_',   // Exit Reasons
            'employeecost_', // Employee Cost
            'training_',     // Training Management
            'asset_',        // Asset Management
        ];

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->where(function ($query) use ($prefixes) {
                foreach ($prefixes as $prefix) {
                    $query->orWhere('name', 'like', $prefix.'%');
                }
            })
            ->get();

        if ($permissions->isNotEmpty()) {
            $role->givePermissionTo($permissions);
        }

        // Purane roles hatao aur sirf Manager role rakho
        $this->syncRoles([$role->name]);
    }
}
