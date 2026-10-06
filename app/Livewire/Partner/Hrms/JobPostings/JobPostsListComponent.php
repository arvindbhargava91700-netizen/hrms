<?php

namespace App\Livewire\Partner\Hrms\JobPostings;

use App\Livewire\Partner\Hrms\HasPartnerId;
use App\Models\Department;
use App\Models\HrmsBranch;
use App\Models\JobPost;
use App\Models\JobTemplate;
use App\Models\JobPlan;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\TpiPaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class JobPostsListComponent extends Component
{
    use HasPartnerId, WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = '';

    // Wizard State: 0 = List, 1 = Template, 2 = Job Details, 3 = Candidate Requirements, 4 = Interview Info, 5 = Plan/Payment
    public $step = 0; 
    public $jobPostId = null;
    public $isEditMode = false;
    
    // Core Fields
    public $template_id = null;
    public $status = 'draft';
    public $job_plan_id = null;
    public $job_code = '';
    public $department_id = '';
    public $branch_id = '';
    public $vacancies_count = 1;
    public $referral_budget = 0;
    
    // Step 2: Job Details
    public $job_title = '';
    public $employment_type = 'Full Time'; // Full Time, Part Time, Both
    public $is_night_shift = false;
    public $work_location_type = 'Work From Office'; // Work From Office, Work From Home, Field Job
    public $job_city = '';
    public $office_address = '';
    public $working_area = '';
    public $salary_type = 'Fixed Only'; // Fixed Only, Fixed + Incentive, Incentive Only
    public $salary_min = '';
    public $salary_max = '';
    public $average_incentive = '';
    public $additional_perks = []; // Array of perks
    public $joining_fee_required = false;

    // Step 3: Candidate Requirements
    public $minimum_education = '';
    public $english_level = '';
    public $experience_type = 'Any'; // Any, Experienced Only, Fresher Only
    public $min_experience_years = '';
    public $max_experience_years = '';
    public $gender_preference = '';
    public $min_age = '';
    public $max_age = '';
    public $skills = '';
    public $job_description = '';

    // Step 4: Interviewer Information
    public $is_walk_in = false;
    public $contact_preference = '';

    public $isViewModalOpen = false;
    public $viewJobPost = null;
    public $walletBalance = 0;
    
    public $banner;
    public $existing_banner;

    protected function rules()
    {
        if ($this->step === 2) {
            return [
                'job_title' => 'required|string|max:255',
                'employment_type' => 'required|string',
                'salary_min' => 'required|numeric',
                'salary_max' => 'required|numeric',
            ];
        } elseif ($this->step === 3) {
            return [
                'minimum_education' => 'required|string',
                'english_level' => 'required|string',
                'job_description' => 'required|string',
            ];
        }
        return [];
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('jobpost_viewAny') ||
            auth()->user()->canAccess('jobpost_viewOwn') ||
            auth()->user()->canAccess('jobpost_viewTeam') ||
            auth()->user()->canAccess('jobpost_viewBranch'),
            403
        );
        $this->walletBalance = auth()->user()->wallet_balance ?? 0;
    }

    public function render()
    {
        if ($this->step === 0) {
            $query = JobPost::query()->with(['department', 'creator'])->where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } });
            $user = auth()->user();
            if (!$user->isPartner() && !$user->canAccess('jobpost_viewAny')) {
                if ($user->canAccess('jobpost_viewBranch')) $query->where('branch_id', $user->branch_id);
                elseif ($user->canAccess('jobpost_viewTeam')) $query->where('department_id', $user->department_id);
                elseif ($user->canAccess('jobpost_viewOwn')) $query->where('created_by', $user->id);
            }
            if ($this->search) {
                $search = $this->search;
                $query->where(function ($q) use ($search) {
                    $q->where('job_title', 'like', "%{$search}%")->orWhere('job_code', 'like', "%{$search}%");
                });
            }
            if ($this->statusFilter) {
                $query->where('status', $this->statusFilter);
            }
            if ($this->branch_id) {
                $query->where('branch_id', $this->branch_id);
            }
            $jobPosts = $query->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $jobPosts = [];
        }

        return view('livewire.partner.hrms.job-postings.job-posts-list-component', [
            'jobPosts' => $jobPosts,
            'departments' => Department::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->orderBy('name')->get(),
            'branches' => HrmsBranch::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->orderBy('name')->get(),
            'templates' => $this->step === 1 ? JobTemplate::where('is_active', true)->get() : [],
            'plans' => $this->step === 5 ? JobPlan::where('is_active', true)->get() : [],
        ])->layout('layouts.app', [
            'panelName' => 'HRMS Module',
            'pageTitle' => 'Job Postings & Referrals',
            'pageSubtitle' => 'Manage job vacancies and referral incentives',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }

    public function createJobPost()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('jobpost_create'), 403);
        return redirect()->route('partner.hrms.job-posts.create');
    }

    public function selectTemplate($templateId = null)
    {
        if ($templateId) {
            $template = JobTemplate::find($templateId);
            if ($template) {
                $this->template_id = $template->id;
                $this->job_title = $template->title;
                $this->job_description = $template->description;
                $this->skills = is_array($template->skills) ? implode(', ', $template->skills) : $template->skills;
                $this->employment_type = $template->job_type ?? 'Full Time';
                $this->salary_min = $template->default_salary_min;
                $this->salary_max = $template->default_salary_max;
            }
        }
        $this->step = 2; // Go to Job Details form
    }

    public function nextStep()
    {
        $this->validate();
        
        if ($this->step === 4) {
            $this->submitDetails();
            $this->step = 5; // Go to Plan selection
        } else {
            $this->step++;
        }
    }
    
    public function submitDetails()
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $this->job_title), 0, 3));
        if (strlen($prefix) < 3) $prefix = str_pad($prefix, 3, 'X');
        if (empty($this->job_code)) $this->job_code = 'JOB-' . $prefix . '-' . rand(1000, 9999);

        $bannerPath = $this->existing_banner;
        if ($this->banner) {
            $bannerPath = $this->banner->store('job_banners', 'public');
        }

        $jobData = [
            'job_title' => $this->job_title,
            'job_code' => $this->job_code,
            'department_id' => $this->department_id ?: null,
            'branch_id' => $this->branch_id ?: null,
            'employment_type' => $this->employment_type,
            'job_type' => $this->is_night_shift ? 'Night Shift' : 'Day Shift',
            'is_work_from_home' => $this->work_location_type === 'Work From Home',
            'work_location_type' => $this->work_location_type,
            'job_city' => $this->job_city,
            'office_address' => $this->office_address,
            'working_area' => $this->working_area,
            'salary_type' => $this->salary_type,
            'salary_min' => $this->salary_min,
            'salary_max' => $this->salary_max,
            'average_incentive' => $this->average_incentive,
            'additional_perks' => $this->additional_perks,
            'joining_fee_required' => $this->joining_fee_required,

            'minimum_education' => $this->minimum_education,
            'english_level' => $this->english_level,
            'min_experience_years' => $this->experience_type === 'Experienced Only' ? $this->min_experience_years : null,
            'max_experience_years' => $this->experience_type === 'Experienced Only' ? $this->max_experience_years : null,
            'gender_preference' => $this->gender_preference,
            'min_age' => $this->min_age,
            'max_age' => $this->max_age,
            
            'interview_information' => [
                'is_walk_in' => $this->is_walk_in,
                'contact_preference' => $this->contact_preference,
            ],

            'vacancies_count' => $this->vacancies_count,
            'referral_budget' => $this->referral_budget,
            'job_description' => $this->job_description,
            'skills' => $this->skills,
            'banner' => $bannerPath,
        ];

        if ($this->jobPostId) {
            JobPost::where('id', $this->jobPostId)->where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->update($jobData);
        } else {
            $jobData['partner_id'] = $this->requirePartnerId();
            $jobData['created_by'] = auth()->id();
            $jobData['status'] = 'draft';
            $jobData['template_id'] = $this->template_id;
            
            $job = JobPost::create($jobData);
            $this->jobPostId = $job->id;
        }
    }

    public function selectPlan($planId)
    {
        $plan = JobPlan::findOrFail($planId);
        $this->job_plan_id = $plan->id;
    }

    public function publishJob()
    {
        if (!$this->job_plan_id) {
            session()->flash('error', 'Please select a Job Plan.');
            return;
        }

        $plan = JobPlan::findOrFail($this->job_plan_id);
        
        if ($plan->job_limit) {
            $activeJobs = JobPost::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })
                                 ->where('job_plan_id', $plan->id)
                                 ->where('status', 'active')
                                 ->count();
            if ($activeJobs >= $plan->job_limit) {
                session()->flash('error', 'Job limit reached for this plan.');
                return;
            }
        }

        $partnerUser = User::findOrFail($this->getPartnerId());
        
        // Calculate Total Cost = Plan Price + Referral Budget
        $totalCost = $plan->price + $this->referral_budget;
        $walletDeducted = min($partnerUser->wallet_balance, $totalCost);
        $onlinePayable = $totalCost - $walletDeducted;

        DB::beginTransaction();
        try {
            $job = JobPost::findOrFail($this->jobPostId);

            if ($onlinePayable == 0) {
                if ($walletDeducted > 0) {
                    $partnerUser->decrement('wallet_balance', $walletDeducted);
                    WalletTransaction::create([
                        'user_id' => $partnerUser->id,
                        'amount' => $walletDeducted,
                        'type' => 'debit',
                        'description' => 'Published Job: ' . $this->job_title,
                        'reference_type' => 'job_post',
                        'reference_id' => $job->id,
                    ]);
                    
                    $admin = User::where('role', 'super_admin')->first();
                    if ($admin) {
                        $admin->increment('wallet_balance', $walletDeducted);
                        WalletTransaction::create([
                            'user_id' => $admin->id,
                            'amount' => $walletDeducted,
                            'type' => 'credit',
                            'description' => 'Received Job Post Payment from: ' . $partnerUser->name,
                            'reference_type' => 'job_post',
                            'reference_id' => $job->id,
                        ]);
                    }
                }

                $job->update([
                    'status' => 'active',
                    'job_plan_id' => $plan->id,
                    'wallet_deducted' => $walletDeducted,
                    'online_payable' => 0,
                    'published_at' => now(),
                    'expires_at' => now()->addDays($plan->validity_days),
                ]);

                DB::commit();
                session()->flash('success', 'Job published successfully!');
                $this->step = 0;
            } else {
                DB::commit();
                
                // Process Online Payment
                $transactionId = 'JOB-'.time().'-'.rand(1000, 9999);
                Cache::put('temp_job_post_'.$transactionId, [
                    'job_id' => $job->id,
                    'plan_id' => $plan->id,
                    'wallet_deducted' => $walletDeducted,
                    'online_payable' => $onlinePayable,
                ], now()->addHours(2));
                
                $merchantId = $partnerUser->getResolvedTpiMerchantId();
                if (!$merchantId) {
                    session()->flash('error', 'Payment gateway is not configured.');
                    return;
                }
                
                $tpiService = app(TpiPaymentService::class);
                $resp = $tpiService->createPayment([
                    'merchantId' => $merchantId,
                    'orderId' => $transactionId,
                    'amount' => (float) $onlinePayable,
                    'customerName' => $partnerUser->name,
                    'email' => $partnerUser->email,
                    'phone' => $partnerUser->mobile ?? '9999999999',
                    'surl' => route('partner.hrms.job-post.payment.callback', ['tx_id' => $transactionId]),
                    'furl' => route('partner.hrms.job-post.payment.callback', ['tx_id' => $transactionId]),
                    'productInfo' => 'Job Publish & Referral Budget',
                    'requestFlow' => 'CUSTOM_CHECKOUT',
                ]);
                
                if (isset($resp['paymentLink']) && isset($resp['paymentId'])) {
                    $jobData = Cache::get('temp_job_post_'.$transactionId);
                    if ($jobData) {
                        $jobData['paymentId'] = $resp['paymentId'];
                        Cache::put('temp_job_post_'.$transactionId, $jobData, now()->addHours(2));
                        Cache::put('temp_job_post_pid_'.$resp['paymentId'], $transactionId, now()->addHours(2));
                    }
                    return redirect()->away($resp['paymentLink']);
                }
                session()->flash('error', 'Failed to generate payment link.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function editJobPost($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('jobpost_update'), 403);
        $jobPost = \App\Models\JobPost::findOrFail($id);
        return redirect()->route('partner.hrms.job-posts.edit', $jobPost->job_code);
    }

    public function deleteJobPost($id)
    {
        $user = auth()->user();
        abort_unless($user->isPartner() || $user->canAccess('jobpost_delete'), 403);
        JobPost::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->findOrFail($id)->delete();
        session()->flash('success', 'Job Post deleted successfully.');
    }

    public function closeJobPost($id)
    {
        $user = auth()->user();
        abort_unless($user->isPartner() || $user->canAccess('jobpost_update'), 403);
        $job = JobPost::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->findOrFail($id);
        $job->update(['status' => 'closed']);
        session()->flash('success', 'Job Post closed successfully.');
    }

    public function viewJobPostDetails($id)
    {
        $this->viewJobPost = JobPost::with(['department', 'branch', 'creator', 'partner'])->findOrFail($id);
        $this->isViewModalOpen = true;
    }

    public function goBack()
    {
        if ($this->step > 0) $this->step--;
    }

    public function cancelWizard()
    {
        $this->step = 0;
    }

    public function closeModals()
    {
        $this->isViewModalOpen = false;
        $this->reset(['viewJobPost']);
    }
}
