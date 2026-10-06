<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Models\JobApplication;
use App\Models\JobTemplate;
use App\Models\JobPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WalletTransaction;
use App\Models\TransactionHistory;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\SystemSetting;
use App\Services\TpiPaymentService;
use Illuminate\Support\Facades\Cache;

class JobPostController extends Controller
{
    private function getPartnerId()
    {
        $user = auth()->user();
        return $user->isPartner() ? $user->id : $user->parent_id;
    }

    public function templates()
    {
        $templates = JobTemplate::where('is_active', true)->get()->map(function ($t) {
            return $this->formatTemplate($t);
        });

        return response()->json([
            'success' => true,
            'data'    => $templates,
        ]);
    }

    /**
     * Get a single template with all its dynamic field definitions.
     */
    public function templateDetail($id)
    {
        $template = JobTemplate::where('is_active', true)->findOrFail($id);
        return response()->json([
            'success' => true,
            'data'    => $this->formatTemplate($template),
        ]);
    }

    /**
     * Format a JobTemplate for the API response.
     * Returns a structured payload including dynamic form fields.
     */
    protected function formatTemplate(JobTemplate $template): array
    {
        // Parse screening questions into mobile-ready dynamic field definitions
        $rawQuestions = $template->default_screening_questions ?? [];
        if (is_string($rawQuestions)) {
            $rawQuestions = json_decode($rawQuestions, true) ?? [];
        }

        $dynamicFields = array_values(array_map(function ($q) {
            $options = [];
            if (!empty($q['options'])) {
                $options = array_values(array_filter(array_map('trim', explode(',', $q['options']))));
            }
            return [
                'id'                 => $q['id'] ?? null,
                'question'           => $q['question'] ?? '',
                'type'               => $q['type'] ?? 'text',    // text|textarea|radio|select|checkbox|file
                'options'            => $options,
                'required'           => (bool)($q['required'] ?? false),
                'conditional_parent' => $q['conditional_parent'] ?? null,
                'conditional_value'  => $q['conditional_value'] ?? null,
            ];
        }, $rawQuestions));

        return [
            'id'                    => $template->id,
            'title'                 => $template->title,
            'category'              => $template->category,
            'overview'              => $template->overview,
            'description'           => $template->description,

            // Basic defaults
            'job_type'              => $template->job_type,
            'salary_type'           => $template->salary_type,
            'default_salary_min'    => $template->default_salary_min,
            'default_salary_max'    => $template->default_salary_max,
            'distance'              => $template->distance,
            'job_city'              => $template->job_city,
            'skills'                => is_array($template->skills) ? $template->skills : [],

            // Candidate requirements defaults
            'minimum_education'     => $template->minimum_education,
            'english_level'         => $template->english_level,
            'min_experience_years'  => $template->min_experience_years,
            'max_experience_years'  => $template->max_experience_years,
            'gender_preference'     => $template->gender_preference,
            'joining_fee_required'  => (bool)$template->joining_fee_required,
            'additional_perks'      => is_array($template->additional_perks) ? $template->additional_perks : [],

            // Interview defaults
            'interview_information' => is_array($template->interview_information) ? $template->interview_information : [],

            // Dynamic form fields (screening questions as field definitions)
            'dynamic_fields'        => $dynamicFields,
            'dynamic_fields_count'  => count($dynamicFields),
        ];
    }

    public function plans()
    {
        return response()->json([
            'success' => true,
            'data'    => JobPlan::where('is_active', true)->get()
        ]);
    }

    public function draft(Request $request)
    {
        $user = auth()->user();
        if (!$user->isPartner() && !$user->canAccess('jobpost_create')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $partnerId = $this->getPartnerId();
        
        $jobData = [
            'partner_id' => $partnerId,
            'created_by' => $user->id,
            'status' => 'draft',
            'vacancies_count' => 1,
            'referral_budget' => 0,
            'wallet_deducted' => 0,
            'online_payable' => 0,
        ];

        if ($request->filled('template_id')) {
            $template = JobTemplate::where('is_active', true)->findOrFail($request->template_id);
            
            $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $template->title), 0, 3));
            if (strlen($prefix) < 3) $prefix = str_pad($prefix, 3, 'X');
            
            $desc = $template->description ?? '';
            if ($template->overview) {
                $desc = "Overview:\n" . $template->overview . "\n\n" . $desc;
            }
            if (!empty($template->responsibilities) && is_array($template->responsibilities)) {
                $desc .= "\n\nResponsibilities:\n" . implode("\n", $template->responsibilities);
            }

            $jobData = array_merge($jobData, [
                'template_id'          => $template->id,
                'job_title'            => $template->title,
                'job_code'             => 'JOB-' . $prefix . '-' . rand(1000, 9999),
                'job_description'      => $desc,
                'employment_type'      => $template->job_type ?? 'Full-time',
                'salary'               => ($template->default_salary_min && $template->default_salary_max) ? $template->default_salary_min . '-' . $template->default_salary_max : null,
                'min_salary'           => $template->default_salary_min,
                'max_salary'           => $template->default_salary_max,
                'salary_min'           => $template->default_salary_min,
                'salary_max'           => $template->default_salary_max,
                'distance'             => $template->distance,
                'job_city'             => $template->job_city,
                'skills'               => is_array($template->skills) ? implode(', ', $template->skills) : $template->skills,
                'screening_questions'  => $template->default_screening_questions,
                'job_type'             => $template->job_type,
                'minimum_education'    => $template->minimum_education,
                'english_level'        => $template->english_level,
                'min_experience_years' => $template->min_experience_years,
                'max_experience_years' => $template->max_experience_years,
                'gender_preference'    => $template->gender_preference,
                'interview_information'=> is_string($template->interview_information) ? json_decode($template->interview_information, true) : $template->interview_information,
                'additional_perks'     => is_string($template->additional_perks) ? json_decode($template->additional_perks, true) : $template->additional_perks,
                'joining_fee_required' => $template->joining_fee_required,
                'salary_type'          => $template->salary_type,
            ]);
        } else {
            $jobData['job_title'] = 'Draft Job';
            $jobData['job_code'] = 'JOB-DRF-' . rand(1000, 9999);
            $jobData['employment_type'] = 'Full-time';
        }

        $job = JobPost::create($jobData);
        return response()->json(['success' => true, 'message' => 'Draft created successfully', 'data' => $job], 201);
    }

    public function publish(Request $request, $id)
    {
        $job = JobPost::where('partner_id', $this->getPartnerId())->where('status', 'draft')->findOrFail($id);
        
        $request->validate([
            'job_plan_id' => 'required|exists:job_plans,id',
        ]);

        $plan = JobPlan::where('is_active', true)->findOrFail($request->job_plan_id);
        
        $activeJobsCount = JobPost::where('partner_id', $this->getPartnerId())
                                  ->where('job_plan_id', $plan->id)
                                  ->where('status', 'active')
                                  ->count();

        $partnerUser = User::findOrFail($this->getPartnerId());
        
        // Calculate plan cost based on package limits (concurrent active jobs)
        if (empty($plan->job_limit)) {
            // Unlimited jobs: Pay only once
            $planCost = ($activeJobsCount > 0) ? 0 : $plan->price;
        } else {
            // Limited jobs: Pay whenever active jobs hit a multiple of the limit
            $planCost = ($activeJobsCount % $plan->job_limit === 0) ? $plan->price : 0;
        }

        $totalCost = $planCost + ($job->referral_budget ?? 0);
        $walletDeducted = $partnerUser->isPartner() ? min($partnerUser->wallet_balance, $totalCost) : 0;
        $onlinePayable = $totalCost - $walletDeducted;

        DB::beginTransaction();
        try {
            if ($onlinePayable == 0) {
                if ($walletDeducted > 0) {
                    $partnerUser->decrement('wallet_balance', $walletDeducted);
                    WalletTransaction::create([
                        'user_id' => $partnerUser->id,
                        'amount' => $walletDeducted,
                        'type' => 'debit',
                        'description' => 'Published Job: ' . $job->job_title,
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
                return response()->json([
                    'success' => true, 
                    'message' => 'Job published successfully', 
                    'data' => $job,
                    'requires_payment' => false,
                    'online_payable' => 0
                ]);
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
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment gateway is not configured.'
                    ], 400);
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
                    return response()->json([
                        'success' => true,
                        'message' => 'Payment required to publish job post.',
                        'payment_link' => $resp['paymentLink'],
                        'transaction_id' => $transactionId,
                        'payment_id' => $resp['paymentId'],
                        'requires_payment' => true,
                        'online_payable' => $onlinePayable
                    ], 201);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate payment link.'
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function history(Request $request)
    {
        $query = JobPost::withCount('applications')->where('partner_id', $this->getPartnerId());
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $jobs = $query->latest()->paginate($request->per_page ?? 10);
        return response()->json(['success' => true, 'data' => $jobs]);
    }

    public function billingHistory(Request $request)
    {
        $query = TransactionHistory::where('user_id', $this->getPartnerId())
            ->where('type', 'job_post');

        if ($request->filled('branch_id')) {
            $query->whereHas('jobPost', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }
        
        if ($request->filled('status') && $request->status !== 'All') {
            $status = strtolower($request->status);
            if ($status === 'success') {
                $status = 'completed';
            }
            $query->where('status', $status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 10);
        return response()->json(['success' => true, 'data' => $transactions]);
    }

    public function closeJob($id)
    {
        $job = JobPost::where('partner_id', $this->getPartnerId())->findOrFail($id);
        $job->update(['status' => 'closed']);
        return response()->json(['success' => true, 'message' => 'Job closed successfully', 'data' => $job]);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = JobPost::with(['department', 'creator'])->where('partner_id', $this->getPartnerId());
        
        if (!$user->isPartner() && !$user->canAccess('jobpost_viewAny')) {
            if ($user->canAccess('jobpost_viewBranch')) {
                $query->where('branch_id', $user->branch_id);
            } elseif ($user->canAccess('jobpost_viewTeam')) {
                $query->where('department_id', $user->department_id);
            } elseif ($user->canAccess('jobpost_viewOwn')) {
                $query->where('created_by', $user->id);
            } else {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }
        
        $jobPosts = $query->latest()->paginate($request->per_page ?? 10);
        return response()->json(['success' => true, 'data' => $jobPosts]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isPartner() && !$user->canAccess('jobpost_create')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'job_title'            => 'required|string|max:255',
            'department_id'        => 'nullable|exists:departments,id',
            'branch_id'            => 'nullable|exists:branches,id',
            'employment_type'      => 'required|string',
            'work_location_type'   => 'nullable|string',   // frontend label, maps to is_work_from_home
            'is_work_from_home'    => 'nullable|boolean',
            'job_city'             => 'required|string',
            'distance'             => 'required|string',
            'experience'           => 'nullable|string',
            'salary'               => 'nullable|string',
            'vacancies_count'      => 'required|integer|min:1',
            'referral_budget'      => 'required|numeric|min:0',
            'application_deadline' => 'nullable|date',
            'job_description'      => 'required|string',
            'skills'               => 'nullable|string',
            'banner'               => 'nullable|image|max:2048',
            'job_type'             => 'nullable|string',
            'salary_type'          => 'required|string',
            'salary_min'           => 'required|numeric|min:0',
            'salary_max'           => 'required|numeric|min:0',
            'additional_perks'     => 'nullable|array',
            'joining_fee_required' => 'required|boolean',
            'minimum_education'    => 'required|string',
            'english_level'        => 'required|string',
            'experience_type'      => 'nullable|string',   // frontend label, used for min/max logic
            'min_experience_years' => 'nullable|numeric',
            'max_experience_years' => 'nullable|numeric',
            'gender_preference'    => 'required|string',
            'min_age'              => 'nullable|numeric',
            'max_age'              => 'nullable|numeric',
            'interview_information'=> 'nullable|array',
            'template_id'          => 'nullable|exists:job_templates,id',
        ]);

        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $validated['job_title']), 0, 3));
        if (strlen($prefix) < 3) {
            $prefix = str_pad($prefix, 3, 'X');
        }
        $jobCode = 'JOB-' . $prefix . '-' . rand(1000, 9999);
        
        $bannerPath = null;
        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('job_banners', 'public');
        }

        $partnerId = $this->getPartnerId();
        $partnerUser = User::findOrFail($partnerId);

        DB::beginTransaction();
        try {
            $walletBalance = $partnerUser->wallet_balance ?? 0;
            $walletDeducted = min($walletBalance, $validated['referral_budget']);
            $onlinePayable = $validated['referral_budget'] - $walletDeducted;

            $screeningQuestions = $request->input('screening_questions');
            if (empty($screeningQuestions) && !empty($validated['template_id'])) {
                $template = JobTemplate::find($validated['template_id']);
                if ($template) {
                    $screeningQuestions = $template->default_screening_questions ?? [];
                }
            }

            $jobData = [
                'partner_id'           => $partnerId,
                'created_by'           => $user->id,
                'job_title'            => $validated['job_title'],
                'job_code'             => $jobCode,
                'department_id'        => $validated['department_id'] ?? null,
                'branch_id'            => $validated['branch_id'] ?? null,
                'employment_type'      => $validated['employment_type'],
                'experience'           => $validated['experience'] ?? null,
                'salary'               => $validated['salary'] ?? null,
                'vacancies_count'      => $validated['vacancies_count'],
                'referral_budget'      => $validated['referral_budget'],
                'wallet_deducted'      => $walletDeducted,
                'online_payable'       => $onlinePayable,
                'application_deadline' => $validated['application_deadline'] ?? null,
                'job_description'      => $validated['job_description'],
                'skills'               => $validated['skills'] ?? null,
                'banner'               => $bannerPath,
                'status'               => 'active',
                'job_type'             => $validated['job_type'] ?? $validated['employment_type'],
                'is_work_from_home'    => ($validated['work_location_type'] ?? null) === 'Work From Home'
                                            ? true
                                            : ($validated['is_work_from_home'] ?? false),
                'job_city'             => $validated['job_city'],
                'city'                 => $validated['job_city'],
                'distance'             => $validated['distance'],
                'salary_type'          => $validated['salary_type'],
                'salary_min'           => $validated['salary_min'],
                'salary_max'           => $validated['salary_max'],
                'min_salary'           => $validated['salary_min'],
                'max_salary'           => $validated['salary_max'],
                'additional_perks'     => $validated['additional_perks'] ?? [],
                'joining_fee_required' => $validated['joining_fee_required'],
                'minimum_education'    => $validated['minimum_education'],
                'english_level'        => $validated['english_level'],
                'min_experience_years' => $validated['min_experience_years'] ?? null,
                'max_experience_years' => $validated['max_experience_years'] ?? null,
                'gender_preference'    => $validated['gender_preference'],
                'min_age'              => $validated['min_age'] ?? null,
                'max_age'              => $validated['max_age'] ?? null,
                'interview_information'=> $validated['interview_information'] ?? [],
                'template_id'          => $validated['template_id'] ?? null,
                'screening_questions'  => $screeningQuestions,
            ];

            if ($onlinePayable == 0) {
                $job = JobPost::create($jobData);

                if ($walletDeducted > 0) {
                    $partnerUser->decrement('wallet_balance', $walletDeducted);

                    WalletTransaction::create([
                        'user_id' => $partnerId,
                        'amount' => $walletDeducted,
                        'type' => 'debit',
                        'description' => 'Job Post Referral Budget for: ' . $validated['job_title'],
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
                            'description' => 'Received Job Post Referral Budget from: ' . $partnerUser->name,
                            'reference_type' => 'job_post',
                            'reference_id' => $job->id,
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Job Post created successfully',
                    'data' => $job,
                    'requires_payment' => false,
                    'online_payable' => 0
                ], 201);
            } else {
                DB::commit();

                $transactionId = 'JOB-'.time().'-'.rand(1000, 9999);
                Cache::put('temp_job_post_'.$transactionId, $jobData, now()->addHours(2));

                $merchantId = $partnerUser->getResolvedTpiMerchantId();

                if (! $merchantId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment gateway is not configured.'
                    ], 400);
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
                    'productInfo' => 'Job Post Referral Budget',
                    'requestFlow' => 'CUSTOM_CHECKOUT',
                ]);

                if (isset($resp['paymentId'])) {
                    Cache::put('temp_job_post_pid_'.$resp['paymentId'], $transactionId, now()->addHours(2));
                }

                if (isset($resp['paymentLink'])) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Payment required to activate job post.',
                        'payment_link' => $resp['paymentLink'],
                        'transaction_id' => $transactionId,
                        'payment_id' => $resp['paymentId'] ?? null,
                        'requires_payment' => true,
                        'online_payable' => $onlinePayable
                    ], 201);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate payment link.'
                ], 500);
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $user = auth()->user();
        $jobPost = JobPost::with(['department', 'branch', 'creator'])->where('partner_id', $this->getPartnerId())->findOrFail($id);

        if (!$user->isPartner() && !$user->canAccess('jobpost_viewAny')) {
            if ($user->canAccess('jobpost_viewBranch') && $jobPost->branch_id != $user->branch_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            } elseif ($user->canAccess('jobpost_viewTeam') && $jobPost->department_id != $user->department_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            } elseif ($user->canAccess('jobpost_viewOwn') && $jobPost->created_by != $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        return response()->json(['success' => true, 'data' => $jobPost]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->isPartner() && !$user->canAccess('jobpost_update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $job = JobPost::where('partner_id', $this->getPartnerId())->findOrFail($id);

        $validated = $request->validate([
            'job_title'            => 'required|string|max:255',
            'department_id'        => 'nullable|exists:departments,id',
            'branch_id'            => 'nullable|exists:branches,id',
            'employment_type'      => 'required|string',
            'work_location_type'   => 'nullable|string',   // frontend label
            'is_work_from_home'    => 'nullable|boolean',
            'job_city'             => 'required|string',
            'distance'             => 'required|string',
            'experience'           => 'nullable|string',
            'salary'               => 'nullable|string',
            'vacancies_count'      => 'required|integer|min:1',
            'application_deadline' => 'nullable|date',
            'job_description'      => 'required|string',
            'skills'               => 'nullable|string',
            'status'               => 'nullable|in:draft,active,closed',
            'banner'               => 'nullable|image|max:2048',
            'job_type'             => 'nullable|string',
            'salary_type'          => 'required|string',
            'salary_min'           => 'required|numeric|min:0',
            'salary_max'           => 'required|numeric|min:0',
            'additional_perks'     => 'nullable|array',
            'joining_fee_required' => 'required|boolean',
            'minimum_education'    => 'required|string',
            'english_level'        => 'required|string',
            'experience_type'      => 'nullable|string',   // frontend label
            'min_experience_years' => 'nullable|numeric',
            'max_experience_years' => 'nullable|numeric',
            'gender_preference'    => 'required|string',
            'min_age'              => 'nullable|numeric',
            'max_age'              => 'nullable|numeric',
            'interview_information'=> 'nullable|array',
        ]);

        $bannerPath = $job->banner;
        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('job_banners', 'public');
        }

        $job->update([
            'job_title'            => $validated['job_title'],
            'department_id'        => $validated['department_id'] ?? null,
            'branch_id'            => $validated['branch_id'] ?? null,
            'employment_type'      => $validated['employment_type'],
            'experience'           => $validated['experience'] ?? null,
            'salary'               => $validated['salary'] ?? null,
            'vacancies_count'      => $validated['vacancies_count'],
            'application_deadline' => $validated['application_deadline'] ?? null,
            'job_description'      => $validated['job_description'],
            'skills'               => $validated['skills'] ?? null,
            'status'               => $validated['status'] ?? $job->status,
            'banner'               => $bannerPath,
            'job_type'             => $validated['job_type'] ?? $validated['employment_type'],
            'is_work_from_home'    => ($validated['work_location_type'] ?? null) === 'Work From Home'
                                        ? true
                                        : ($validated['is_work_from_home'] ?? false),
            'job_city'             => $validated['job_city'],
            'city'                 => $validated['job_city'],
            'distance'             => $validated['distance'],
            'salary_type'          => $validated['salary_type'],
            'salary_min'           => $validated['salary_min'],
            'salary_max'           => $validated['salary_max'],
            'min_salary'           => $validated['salary_min'],
            'max_salary'           => $validated['salary_max'],
            'additional_perks'     => $validated['additional_perks'] ?? [],
            'joining_fee_required' => $validated['joining_fee_required'],
            'minimum_education'    => $validated['minimum_education'],
            'english_level'        => $validated['english_level'],
            'min_experience_years' => $validated['min_experience_years'] ?? null,
            'max_experience_years' => $validated['max_experience_years'] ?? null,
            'gender_preference'    => $validated['gender_preference'],
            'min_age'              => $validated['min_age'] ?? null,
            'max_age'              => $validated['max_age'] ?? null,
            'interview_information'=> $validated['interview_information'] ?? [],
            'screening_questions'  => $request->has('screening_questions') ? $request->input('screening_questions') : $job->screening_questions,
        ]);

        return response()->json(['success' => true, 'message' => 'Job Post updated successfully', 'data' => $job]);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->isPartner() && !$user->canAccess('jobpost_delete')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $job = JobPost::where('partner_id', $this->getPartnerId())->findOrFail($id);
        $job->delete();

        return response()->json(['success' => true, 'message' => 'Job Post deleted successfully']);
    }

    public function applications(Request $request)
    {
        $user = auth()->user();
        
        $query = JobApplication::with(['jobPost.department', 'jobPost.branch', 'applicant', 'referral.user'])
            ->whereHas('jobPost', function ($q) {
                $q->where('partner_id', $this->getPartnerId());
            });
            
        if (!$user->isPartner() && !$user->canAccess('appliedjobpost_viewAny') && !$user->canAccess('jobpost_viewAny')) {
            if ($user->canAccess('appliedjobpost_viewBranch') || $user->canAccess('jobpost_viewBranch')) {
                $query->whereHas('jobPost', function ($q) use ($user) {
                    $q->where('branch_id', $user->branch_id);
                });
            } elseif ($user->canAccess('appliedjobpost_viewTeam') || $user->canAccess('jobpost_viewTeam')) {
                $query->whereHas('jobPost', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            } elseif ($user->canAccess('appliedjobpost_viewOwn') || $user->canAccess('jobpost_viewOwn')) {
                $query->whereHas('jobPost', function ($q) use ($user) {
                    $q->where('created_by', $user->id);
                });
            } else {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        if ($request->has('post_id')) {
            $query->where('post_id', $request->post_id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $applications = $query->latest()->paginate($request->per_page ?? 10);
        return response()->json(['success' => true, 'data' => $applications]);
    }

    public function updateApplicationStatus(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->isPartner() && !$user->canAccess('appliedjobpost_update_status') && !$user->canAccess('jobpost_update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|string|in:' . implode(',', JobApplication::statuses()),
        ]);

        $application = JobApplication::with('jobPost')->findOrFail($id);

        if ($application->jobPost->partner_id != $this->getPartnerId()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $previousStatus = $application->status;
        $application->update(['status' => $request->status]);

        $this->creditReferralReward($application, $previousStatus);

        if (in_array($request->status, [JobApplication::STATUS_SHORTLISTED, JobApplication::STATUS_COMPLETED])) {
            $jobPost = $application->jobPost;
            if ($jobPost && $jobPost->vacancies_count > 0) {
                $successfulCount = JobApplication::where('post_id', $jobPost->id)
                    ->whereIn('status', [JobApplication::STATUS_SHORTLISTED, JobApplication::STATUS_COMPLETED])
                    ->count();

                if ($successfulCount >= $jobPost->vacancies_count && $jobPost->status !== 'closed') {
                    $jobPost->update(['status' => 'closed']);
                }
            }
        }

        return response()->json([
            'success' => true, 
            'message' => 'Application status updated to ' . $request->status, 
            'data' => $application->fresh()
        ]);
    }

    private function creditReferralReward(JobApplication $application, string $previousStatus): void
    {
        if (! in_array($application->status, [JobApplication::STATUS_SHORTLISTED, JobApplication::STATUS_COMPLETED])) {
            return;
        }

        if ($previousStatus === $application->status) {
            return;
        }

        $jobPost = $application->jobPost;

        if (! $jobPost) {
            return;
        }

        $amount = (float) $jobPost->referral_amount;

        if ($amount <= 0) {
            return;
        }

        // Limit: total successful (hired/completed) applicants must not exceed vacancies_count
        $successfulCount = JobApplication::where('post_id', $jobPost->id)
            ->whereIn('status', [JobApplication::STATUS_SHORTLISTED, JobApplication::STATUS_COMPLETED])
            ->count();

        if ($successfulCount > $jobPost->vacancies_count) {
            return;
        }

        $referrer = $application->referral?->user;

        if (! $referrer) {
            return;
        }

        $alreadyCredited = WalletTransaction::where('reference_type', 'job_application')
            ->where('reference_id', $application->id)
            ->exists();

        if ($alreadyCredited) {
            return;
        }

        $referrer->increment('wallet_balance', $amount);

        WalletTransaction::create([
            'user_id' => $referrer->id,
            'amount' => $amount,
            'type' => 'credit',
            'description' => 'Referral reward for '.$jobPost->job_title,
            'reference_type' => 'job_application',
            'reference_id' => $application->id,
        ]);

        TransactionHistory::create([
            'transaction_id' => Str::uuid()->toString(),
            'user_id' => $referrer->id,
            'type' => 'credit',
            'reference_id' => $application->id,
            'total_amount' => $amount,
            'platform_fee' => 0,
            'net_amount' => $amount,
            'status' => 'completed',
            'description' => 'Referral reward for '.$jobPost->job_title,
        ]);
    }
}
