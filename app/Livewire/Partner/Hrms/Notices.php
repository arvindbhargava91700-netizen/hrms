<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use App\Models\Notice;
use App\Models\User;
use App\Models\HrmsBranch;
use App\Models\Department;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Auth;

use App\Livewire\Partner\Hrms\Traits\HasHrmsFilters;

class Notices extends Component
{
    use HasPartnerId, HasHrmsFilters;

    public $notices = [];
    public $employees = [];
    public $branches = [];
    public $departments = [];
    
    public $isModalOpen = false;
    public $editingId = null;
    
    public $title, $content, $type = 'global', $user_id, $start_date, $end_date, $action_link, $action_text;
    public $user_ids = [];
    public $branch_ids = [];
    public $department_ids = [];
    public $is_unlimited = true;
    public $employeeSearch = '';

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() || 
            auth()->user()->canAccess('notice_viewAny') || 
            auth()->user()->canAccess('notice_viewBranch') || 
            auth()->user()->canAccess('notice_viewTeam') || 
            auth()->user()->canAccess('notice_viewOwn'), 
            403
        );
        $this->loadData();
    }

    public function loadData()
    {
        $allowedIds = $this->getFilteredEmployeeIds('notice_viewAny');
        $this->employees = User::whereIn('id', $this->getTeamEmployeeIds('notice_viewAny'))->get();
        
        // Ensure Partner ID is available, and load branches/departments
        $this->branches = HrmsBranch::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->get();
        $this->departments = Department::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->get();
        
        $query = Notice::query();
        
        if (property_exists($this, 'filterEmployeeId') && $this->filterEmployeeId) {
            if ($allowedIds->contains($this->filterEmployeeId)) {
                $employee = User::find($this->filterEmployeeId);
                $query->where(function($q) use ($employee) {
                    $q->where('type', 'global')
                      ->orWhere('user_id', $employee->id)
                      ->orWhereJsonContains('user_ids', (string)$employee->id)
                      ->orWhereJsonContains('user_ids', (int)$employee->id);
                      
                    if ($employee && $employee->branch_id) {
                        $q->orWhereJsonContains('branch_ids', (string)$employee->branch_id)
                          ->orWhereJsonContains('branch_ids', (int)$employee->branch_id);
                    }
                    if ($employee && $employee->department_id) {
                        $q->orWhereJsonContains('department_ids', (string)$employee->department_id)
                          ->orWhereJsonContains('department_ids', (int)$employee->department_id);
                    }
                });
            } else {
                $query->where('id', -1); // Force empty if they can't access this employee
            }
        } else {
            $query->where(function($q) use ($allowedIds) {
                $q->where('type', 'global')
                  ->orWhereIn('user_id', $allowedIds);
                
                foreach ($allowedIds as $id) {
                    $q->orWhereJsonContains('user_ids', (string)$id)
                      ->orWhereJsonContains('user_ids', (int)$id);
                }
                
                if ($this->filterBranchId) {
                    $q->orWhereJsonContains('branch_ids', (string)$this->filterBranchId)
                      ->orWhereJsonContains('branch_ids', (int)$this->filterBranchId);
                } else {
                    $q->orWhereNotNull('branch_ids');
                }

                if ($this->filterDepartmentId) {
                    $q->orWhereJsonContains('department_ids', (string)$this->filterDepartmentId)
                      ->orWhereJsonContains('department_ids', (int)$this->filterDepartmentId);
                } else {
                    $q->orWhereNotNull('department_ids');
                }
            });
        }
        
        // Scope everything to the partner to avoid leaking other partner's notices
        // Allow system-wide notices (user_id is null)
        if (!auth()->user()->isSuperAdmin()) {
            $query->where(function($q) {
                $q->whereNull('user_id')
                  ->orWhereHas('user', function($q2) {
                      $q2->where('id', $this->getPartnerId())
                         ->orWhere('parent_id', $this->getPartnerId());
                  });
            });
        }
        
        $this->notices = $query->orderBy('created_at', 'desc')->get();
    }

    public function getFilteredEmployeesProperty()
    {
        $search = trim(strtolower($this->employeeSearch));
        if (empty($search)) {
            return $this->employees;
        }

        return collect($this->employees)->filter(function ($emp) use ($search) {
            $name = is_array($emp) ? ($emp['name'] ?? '') : ($emp->name ?? '');
            $email = is_array($emp) ? ($emp['email'] ?? '') : ($emp->email ?? '');
            $code = is_array($emp) ? ($emp['employee_code'] ?? '') : ($emp->employee_code ?? '');

            return str_contains(strtolower($name), $search) ||
                   str_contains(strtolower($email), $search) ||
                   str_contains(strtolower($code), $search);
        })->values();
    }

    public function selectAllFilteredEmployees()
    {
        $filteredIds = $this->filteredEmployees->map(function($emp) {
            return is_array($emp) ? $emp['id'] : $emp->id;
        })->toArray();

        $this->user_ids = array_values(array_unique(array_merge($this->user_ids, $filteredIds)));
    }

    public function deselectAllEmployees()
    {
        $this->user_ids = [];
    }

    public function toggleEmployee($id)
    {
        if (in_array($id, $this->user_ids)) {
            $this->user_ids = array_values(array_diff($this->user_ids, [$id]));
        } else {
            $this->user_ids[] = $id;
        }
    }

    // viewScope no longer needed

    public function createNotice()
    {
        abort_unless(auth()->user()->canAccess('notice_create'), 403);
        $this->reset(['editingId', 'title', 'content', 'type', 'user_id', 'user_ids', 'branch_ids', 'department_ids', 'start_date', 'end_date', 'action_link', 'action_text', 'employeeSearch']);
        $this->type = 'global';
        $this->user_ids = [];
        $this->branch_ids = [];
        $this->department_ids = [];
        $this->is_unlimited = true;
        $this->isModalOpen = true;
    }

    public function editNotice($id)
    {
        abort_unless(auth()->user()->canAccess('notice_update'), 403);
        
        $query = Notice::where('id', $id);
        if (!auth()->user()->isSuperAdmin()) {
            $query->where(function($q) {
                $q->whereNull('user_id')
                  ->orWhereHas('user', function($q2) {
                      $q2->where('id', $this->getPartnerId())
                         ->orWhere('parent_id', $this->getPartnerId());
                  });
            });
        }
        $notice = $query->firstOrFail();

        $this->editingId = $notice->id;
        $this->title = $notice->title;
        $this->content = $notice->content;
        $this->type = $notice->type;
        $this->user_id = $notice->user_id;
        $this->user_ids = $notice->user_ids ?: ($notice->user_id ? [$notice->user_id] : []);
        $this->branch_ids = $notice->branch_ids ?: [];
        $this->department_ids = $notice->department_ids ?: [];
        $this->action_link = $notice->action_link;
        $this->action_text = $notice->action_text;
        $this->employeeSearch = '';
        
        if ($notice->start_date || $notice->end_date) {
            $this->is_unlimited = false;
            $this->start_date = $notice->start_date;
            $this->end_date = $notice->end_date;
        } else {
            $this->is_unlimited = true;
            $this->start_date = null;
            $this->end_date = null;
        }
        
        $this->isModalOpen = true;
    }

    public function setQuickActionText($text)
    {
        $this->action_text = $text;
    }

    public function saveNotice()
    {
        abort_unless(auth()->user()->canAccess($this->editingId ? 'notice_update' : 'notice_create'), 403);
        
        $validator = \Illuminate\Support\Facades\Validator::make([
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'user_ids' => $this->user_ids,
            'branch_ids' => $this->branch_ids,
            'department_ids' => $this->department_ids,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'action_link' => $this->action_link,
            'action_text' => $this->action_text,
            'is_unlimited' => $this->is_unlimited,
        ], [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:global,personal,branch,department,employee',
            'user_ids' => 'required_if:type,personal,employee|nullable|array',
            'branch_ids' => 'required_if:type,branch|nullable|array',
            'department_ids' => 'required_if:type,department|nullable|array',
            'start_date' => 'nullable|required_if:is_unlimited,false|date',
            'end_date' => 'nullable|required_if:is_unlimited,false|date|after_or_equal:start_date',
            'action_link' => 'nullable|url|max:2000',
            'action_text' => 'nullable|string|max:100',
        ], [
            'user_ids.required_if' => 'Please select at least one employee.',
            'branch_ids.required_if' => 'Please select at least one branch.',
            'department_ids.required_if' => 'Please select at least one department.',
        ]);

        if ($validator->fails()) {
            \Log::error('Notice Validation Failed', $validator->errors()->toArray());
            $this->setErrorBag($validator->errors());
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        try {

            $selectedUserIds = in_array($this->type, ['personal', 'employee']) ? array_values(array_unique($this->user_ids)) : null;
            $selectedBranchIds = $this->type === 'branch' ? array_values(array_unique($this->branch_ids)) : null;
            $selectedDepartmentIds = $this->type === 'department' ? array_values(array_unique($this->department_ids)) : null;
            $primaryUserId = $selectedUserIds && count($selectedUserIds) > 0 ? $selectedUserIds[0] : null;
            if (!$primaryUserId) {
                $primaryUserId = auth()->id();
            }

            $notice = Notice::updateOrCreate(
                ['id' => $this->editingId],
                [
                    'title' => $this->title,
                    'content' => $this->content,
                    'type' => $this->type === 'personal' ? 'employee' : $this->type,
                    'user_id' => $primaryUserId,
                    'user_ids' => $selectedUserIds,
                    'branch_ids' => $selectedBranchIds,
                    'department_ids' => $selectedDepartmentIds,
                    'start_date' => $this->is_unlimited ? null : $this->start_date,
                    'end_date' => $this->is_unlimited ? null : $this->end_date,
                    'action_link' => $this->action_link,
                    'action_text' => $this->action_text ?: 'Join Now',
                ]
            );

            $this->sendPushNotifications($notice);

            $this->isModalOpen = false;
            session()->flash('success', 'Notice saved successfully.');
            $this->loadData();
        } catch (\Throwable $th) {
            \Log::error('Notice Save Error: ' . $th->getMessage(), ['exception' => $th]);
            session()->flash('error', 'Failed to save notice: ' . $th->getMessage());
        }
    }

    protected function sendPushNotifications(Notice $notice)
    {
        $partnerId = $this->getPartnerId();
        
        $activeSubscription = \App\Models\PartnerSubscription::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
            ->where('status', 'active')
            ->where('expires_at', '>=', now())
            ->with('package')
            ->first();

        $hasAppNotification = $activeSubscription && $activeSubscription->package && $activeSubscription->package->app_notification;

        if (!$hasAppNotification) {
            return;
        }

        $query = User::where(function($q) use ($partnerId) {
            $q->where('id', $partnerId)->orWhere('parent_id', $partnerId);
        })->whereNotNull('fcm_token');

        if ($notice->type === 'employee' || $notice->type === 'personal') {
            $query->where(function($q) use ($notice) {
                $q->whereIn('id', $notice->user_ids ?? [])
                  ->orWhere('id', $notice->user_id);
            });
        } elseif ($notice->type === 'branch') {
            $query->where(function($q) use ($notice) {
                $q->whereIn('branch_id', $notice->branch_ids ?? [])
                  ->orWhere('id', $notice->user_id);
            });
        } elseif ($notice->type === 'department') {
            $query->where(function($q) use ($notice) {
                $q->whereIn('department_id', $notice->department_ids ?? [])
                  ->orWhere('id', $notice->user_id);
            });
        }

        $usersToNotify = $query->get();
        $firebaseService = app(FirebaseNotificationService::class);

        foreach ($usersToNotify as $user) {
            if ($user->fcm_token) {
                $firebaseService->sendNotification(
                    $user->fcm_token,
                    "New Notice: " . $notice->title,
                    str($notice->content)->limit(100),
                    ['type' => 'notice', 'notice_id' => (string)$notice->id],
                    null,
                    true // log to database
                );
            }
        }
    }

    public function deleteNotice($id)
    {
        abort_unless(auth()->user()->canAccess('notice_delete'), 403);
        
        $query = Notice::where('id', $id);
        if (!auth()->user()->isSuperAdmin()) {
            $query->where(function($q) {
                $q->whereNull('user_id')
                  ->orWhereHas('user', function($q2) {
                      $q2->where('id', $this->getPartnerId())
                         ->orWhere('parent_id', $this->getPartnerId());
                  });
            });
        }
        $notice = $query->firstOrFail();
        
        $notice->delete();
        
        session()->flash('success', 'Notice deleted successfully.');
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.partner.hrms.notices')
            ->layout('layouts.app', [
                'panelName'    => 'HRMS Module',
                'pageTitle'    => 'Notice Board',
                'pageSubtitle' => 'Manage global and personal notices',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
