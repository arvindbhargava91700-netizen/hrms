<?php

namespace App\Livewire\Partner\Hrms\JobPostings;

use App\Livewire\Partner\Hrms\HasPartnerId;
use App\Models\Department;
use App\Models\HrmsBranch;
use App\Models\JobPost;
use App\Models\JobTemplate;
use App\Models\JobPlan;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class JobPostFormComponent extends Component
{
    use HasPartnerId, WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = '';

    public $step = 0; 
    public $jobPostId = null;
    public $isEditMode = false;
    
    public $template_id = null;
    public $status = 'draft';
    public $job_plan_id = null;
    public $job_code = '';
    public $department_id = '';
    public $branch_id = '';
    public $vacancies_count = 1;
    public $referral_budget = 0;
    
    public $job_title = '';
    public $employment_type = 'Full Time';
    public $is_night_shift = false;
    public $work_location_type = 'Work From Office';
    public $job_city = '';
    public $office_address = '';
    public $office_address_details = '';
    public $office_lat = '';
    public $office_lng = '';
    public $office_city = '';
    public $accept_pan_india_candidates = false;
    public $working_area = '';
    public $distance = ''; 
    public $salary_type = 'Fixed Only';
    public $salary_min = '';
    public $salary_max = '';
    public $average_incentive = '';
    public $additional_perks = [];
    public $joining_fee_required = false;

    public $minimum_education = '';
    public $english_level = '';
    public $experience_type = 'Any';
    public $min_experience_years = '';
    public $max_experience_years = '';
    public $gender_preference = '';
    public $min_age = '';
    public $max_age = '';
    public $job_description = '';
    
    // Additional Requirements dynamic data
    public $selected_requirements = [];
    public $selected_skills = [];
    public $selected_assets = [];
    public $selected_languages = [];
    public $selected_degrees = [];

    public $is_walk_in = false;
    public $contact_preference = '';
    
    public $screening_questions = [];
    public $screening_answers = []; // Partner answers for the dynamic fields

    public $isViewModalOpen = false;
    public $viewJobPost = null;
    public $walletBalance = 0;
    
    public $jobTitleSuggestions = [];
    
    public $banner;
    public $existing_banner;

    public function rules()
    {
        if ($this->step === 2) {
            return [
                'job_title' => 'required|string|max:255',
                'branch_id' => 'required',
                'employment_type' => 'required|string',
                'work_location_type' => 'required|string',
                'job_city' => 'required_if:work_location_type,Work From Home|nullable|string',
                'vacancies_count' => 'required|integer|min:1',
                'referral_budget' => 'required|numeric|min:0',
                
                'is_night_shift' => 'required|boolean',
                'salary_type' => 'required|string',
                'salary_min' => 'required_if:salary_type,Fixed Only|required_if:salary_type,Fixed + Incentive|nullable|numeric|min:0',
                'salary_max' => 'required_if:salary_type,Fixed Only|required_if:salary_type,Fixed + Incentive|nullable|numeric|min:0|gte:salary_min',
                'joining_fee_required' => 'required|boolean',
                'average_incentive' => 'required_if:salary_type,Incentive Only|required_if:salary_type,Fixed + Incentive|nullable|numeric|min:0',
            ];
        } elseif ($this->step === 3) {
            return [
                'distance' => 'required_if:work_location_type,Work From Office|required_if:work_location_type,Field Job|nullable|string',
                'minimum_education' => 'required|string',
                'english_level' => 'required|string',
                'job_description' => 'required|string',
                'experience_type' => 'required|string',
                'min_experience_years' => 'required_if:experience_type,Experienced Only',
                'max_experience_years' => 'required_if:experience_type,Experienced Only',
                'gender_preference' => 'required|string',
                'min_age' => 'nullable|integer|min:16',
                'max_age' => 'nullable|integer|min:16|gte:min_age',
            ];
        }
        return [];
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }

    public function updatedJobTitle($value)
    {
        if (strlen($value) >= 1) {
            $suggestions = \App\Models\JobTemplate::where('title', 'like', '%' . $value . '%')
                ->where('is_active', true)
                ->limit(8)
                ->get(['title', 'category']);
                
            $partnerId = $this->jobPostPartnerId ?? $this->getPartnerId();
            $departments = \App\Models\Department::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->get()->keyBy('name');

            $results = [];
            foreach ($suggestions as $suggestion) {
                $deptId = null;
                if ($suggestion->category && isset($departments[$suggestion->category])) {
                    $deptId = $departments[$suggestion->category]->id;
                }
                $results[] = [
                    'title' => $suggestion->title,
                    'department_id' => $deptId
                ];
            }
            $this->jobTitleSuggestions = $results;
        } else {
            $this->jobTitleSuggestions = [];
        }
    }
    
    public function selectJobTitle($title, $deptId = null)
    {
        $this->job_title = $title;
        if ($deptId) {
            $this->department_id = $deptId;
        }
        $this->jobTitleSuggestions = [];
    }
    
    public function updatedMinimumEducation($value)
    {
        if (in_array($value, ['Diploma', 'ITI', 'Graduate', 'Post Graduate'])) {
            if (!in_array('degrees', $this->selected_requirements)) {
                $reqs = $this->selected_requirements;
                $reqs[] = 'degrees';
                $this->selected_requirements = $reqs;
            }
        } else {
            if (in_array('degrees', $this->selected_requirements)) {
                $reqs = $this->selected_requirements;
                $key = array_search('degrees', $reqs);
                unset($reqs[$key]);
                $this->selected_requirements = array_values($reqs);
            }
            $this->selected_degrees = [];
        }
    }

    public function mount($id = null)
    {
        abort_unless(
            auth()->user()->role === 'super_admin' ||
            auth()->user()->role === 'admin' ||
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('jobpost_viewAny') ||
            auth()->user()->canAccess('jobpost_viewOwn') ||
            auth()->user()->canAccess('jobpost_viewTeam') ||
            auth()->user()->canAccess('jobpost_viewBranch'),
            403
        );
        $this->walletBalance = auth()->user()->wallet_balance ?? 0;
        
        if ($id) {
            $this->editJobPost($id);
        } else {
            $this->createJobPost();
        }
    }

    public function render()
    {
        if ($this->step === 0) {
            $query = JobPost::query()->with(['department', 'creator']);
            if (!auth()->user()->isSuperAdmin()) {
                $query->where('partner_id', $this->getPartnerId());
            }
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
            $jobPosts = $query->orderBy('created_at', 'desc')->paginate(10);
        } else {
            $jobPosts = [];
        }

        return view('livewire.partner.hrms.job-postings.job-post-form-component', [
            'jobPosts' => $jobPosts,
            'departments' => Department::where('partner_id', $this->jobPostPartnerId ?? $this->getPartnerId())->orderBy('name')->get(),
            'branches' => HrmsBranch::where('partner_id', $this->jobPostPartnerId ?? $this->getPartnerId())->orderBy('name')->get(),
            'templates' => $this->step === 1 ? JobTemplate::where('is_active', true)->get() : [],
            'plans' => $this->step === 5 ? JobPlan::where('is_active', true)->get() : [],
            'jobSettings' => in_array($this->step, [2, 3]) ? \App\Models\JobSetting::all()->groupBy('type') : [],
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
        $this->resetValidation();
        $this->reset([
            'jobPostId', 'isEditMode', 'job_title', 'department_id', 'branch_id', 
            'job_description', 'banner', 'existing_banner', 'template_id', 'job_plan_id',
            'salary_min', 'salary_max', 'additional_perks', 'minimum_education', 'english_level',
            'min_experience_years', 'max_experience_years', 'min_age', 'max_age', 'gender_preference',
            'selected_requirements', 'selected_skills', 'selected_assets', 'selected_languages', 'selected_degrees'
        ]);
        
        $this->job_code = '';
        $this->employment_type = 'Full Time';
        $this->work_location_type = 'Work From Office';
        $this->office_address = '';
        $this->office_address_details = '';
        $this->working_area = '';
        $this->salary_type = 'Fixed Only';
        $this->average_incentive = '';
        $this->experience_type = 'Any';
        $this->is_night_shift = false;
        $this->joining_fee_required = false;
        $this->is_walk_in = false;
        $this->contact_preference = 'All candidates';
        $this->additional_perks = [];
        $this->distance = '';
        $this->screening_answers = [];

        $this->vacancies_count = 1;
        $this->referral_budget = 0;
        $this->status = 'draft';
        
        if (!auth()->user()->isPartner() && auth()->user()->branch_id) {
            $this->branch_id = auth()->user()->branch_id;
        }

        $this->walletBalance = auth()->user()->isPartner() ? (auth()->user()->wallet_balance ?? 0) : 0;
        
        $this->step = 1; 
    }

    public function selectTemplate($templateId = null)
    {
        if ($templateId) {
            $template = JobTemplate::find($templateId);
            if ($template) {
                $this->template_id = $template->id;
                $this->job_title = $template->title;
                $this->job_description = $template->description;
                $skillsData = $template->skills;
                $this->selected_skills = is_string($skillsData) ? json_decode($skillsData, true) ?? [] : (is_array($skillsData) ? $skillsData : []);
                if(!empty($this->selected_skills)) $this->selected_requirements[] = 'skills';
                $this->employment_type = $template->job_type ?? 'Full Time';
                $this->salary_min = $template->default_salary_min;
                $this->salary_max = $template->default_salary_max;
                $this->distance = $template->distance;
                $this->minimum_education = $template->minimum_education ?: '10th Or Below 10th';
                $this->english_level = $template->english_level ?: 'No English';
                $this->min_experience_years = $template->min_experience_years;
                $this->max_experience_years = $template->max_experience_years;
                $this->gender_preference = $template->gender_preference;
                $this->salary_type = $template->salary_type ?? 'Fixed Only';
                $this->joining_fee_required = (bool)$template->joining_fee_required;
                
                $perks = $template->additional_perks;
                $this->additional_perks = is_string($perks) ? json_decode($perks, true) : (is_array($perks) ? $perks : []);
                
                $questions = $template->default_screening_questions;
                $this->screening_questions = is_string($questions) ? json_decode($questions, true) : (is_array($questions) ? $questions : []);
                
                // pre-populate answers array
                foreach($this->screening_questions as $q) {
                    $this->screening_answers[$q['id'] ?? $q['question']] = '';
                }

                $interview = $template->interview_information;
                $interviewArr = is_string($interview) ? json_decode($interview, true) : (is_array($interview) ? $interview : []);
                $this->is_walk_in = (bool)($interviewArr['is_walk_in'] ?? false);
                $this->contact_preference = $interviewArr['contact_preference'] ?? 'All candidates';
            }
        }
        $this->step = 2; 
    }

    public function nextStep()
    {
        $rules = $this->rules();
        if (!empty($rules)) {
            $this->validate($rules);
        }
        
        if ($this->step === 4) {
            $this->submitDetails();
            if ($this->isEditMode && $this->status === 'active') {
                session()->flash('success', 'Job updated successfully!');
                if (in_array(auth()->user()->role, ['super_admin', 'admin'])) {
                    return redirect()->route('admin.job-posts');
                }
                return redirect()->route('partner.hrms.job-posts');
            }
            $this->step = 5;
        } else {
            $this->step++;
        }
    }


    public function submitDetails()
    {
        $prefix = strtoupper(substr(preg_replace("/[^a-zA-Z0-9]/", '', $this->job_title), 0, 3));
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
            'job_city' => $this->job_city ?: null,
            'city' => $this->job_city ?: null,
            'office_address' => $this->office_address ?: null,
            'office_address_details' => $this->office_address_details ?: null,
            'office_lat' => $this->office_lat ?: null,
            'office_lng' => $this->office_lng ?: null,
            'office_city' => $this->office_city ?: null,
            'accept_pan_india_candidates' => $this->accept_pan_india_candidates,
            'working_area' => $this->working_area ?: null,
            'distance' => $this->distance ?: null,
            'salary_type' => $this->salary_type,
            'salary_min' => $this->salary_min,
            'salary_max' => $this->salary_max,
            'min_salary' => $this->salary_min,
            'max_salary' => $this->salary_max,
            'average_incentive' => $this->average_incentive ?: null,
            'additional_perks' => $this->additional_perks ?: [],
            'joining_fee_required' => $this->joining_fee_required,

            'minimum_education' => $this->minimum_education,
            'english_level' => $this->english_level,
            'min_experience_years' => $this->experience_type === 'Experienced Only' ? ($this->min_experience_years ?: null) : null,
            'max_experience_years' => $this->experience_type === 'Experienced Only' ? ($this->max_experience_years ?: null) : null,
            'gender_preference' => $this->gender_preference ?: null,
            'min_age' => $this->min_age ?: null,
            'max_age' => $this->max_age ?: null,
            'skills' => json_encode($this->selected_skills),
            'assets' => json_encode($this->selected_assets),
            'languages' => json_encode($this->selected_languages),
            'degree_requirement' => json_encode($this->selected_degrees),
            
            'interview_information' => [
                'is_walk_in' => $this->is_walk_in,
                'contact_preference' => $this->contact_preference,
            ],

            'vacancies_count' => $this->vacancies_count,
            'referral_budget' => $this->referral_budget,
            'referral_amount' => $this->vacancies_count > 0 ? ($this->referral_budget / $this->vacancies_count) : 0,
            
            'job_description' => $this->job_description,
            'banner' => $bannerPath,
            'template_id' => $this->template_id,
            
            'screening_questions' => [
                'questions' => $this->screening_questions,
                'answers' => $this->screening_answers
            ],

        ];

        if ($this->jobPostId) {
            $query = JobPost::where('id', $this->jobPostId);
            if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) {
                $query->where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } });
            }
            $jobPost = $query->firstOrFail();
            $jobPost->update($jobData);
        } else {
            $jobData['partner_id'] = $this->requirePartnerId();
            $jobData['created_by'] = auth()->id();
            $jobData['status'] = 'draft';
            $jobPost = JobPost::create($jobData);
            $this->jobPostId = $jobPost->id;
        }
    }

    public function editJobPost($id)
    {
        $query = JobPost::where(function($q) use ($id) {
            $q->where('id', $id)->orWhere('job_code', $id);
        });
        if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) {
            $query->where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } });
        }
        $jobPost = $query->firstOrFail();
        
        $this->jobPostPartnerId = $jobPost->partner_id;
        $this->jobPostId = $jobPost->id;
        $this->isEditMode = true;
        
        $this->template_id = $jobPost->template_id;
        $this->job_title = $jobPost->job_title;
        $this->job_code = $jobPost->job_code;
        $this->department_id = $jobPost->department_id;
        $this->branch_id = $jobPost->branch_id;
        $this->employment_type = $jobPost->employment_type;
        $this->is_night_shift = $jobPost->job_type === 'Night Shift';
        $this->work_location_type = $jobPost->work_location_type ?? ($jobPost->is_work_from_home ? 'Work From Home' : 'Work From Office');
        $this->job_city = $jobPost->job_city;
        $this->office_address = $jobPost->office_address;
        $this->office_address_details = $jobPost->office_address_details;
        $this->office_lat = $jobPost->office_lat;
        $this->office_lng = $jobPost->office_lng;
        $this->office_city = $jobPost->office_city;
        $this->accept_pan_india_candidates = (bool)$jobPost->accept_pan_india_candidates;
        $this->working_area = $jobPost->working_area;
        $this->distance = $jobPost->distance;
        $this->salary_type = $jobPost->salary_type ?? 'Fixed Only';
        $this->salary_min = $jobPost->salary_min;
        $this->salary_max = $jobPost->salary_max;
        $this->average_incentive = $jobPost->average_incentive;
        
        $perks = $jobPost->additional_perks;
        $this->additional_perks = is_string($perks) ? json_decode($perks, true) : (is_array($perks) ? $perks : []);
        $this->joining_fee_required = (bool)$jobPost->joining_fee_required;

        $this->minimum_education = $jobPost->minimum_education;
        $this->english_level = $jobPost->english_level;
        $this->min_experience_years = $jobPost->min_experience_years;
        $this->max_experience_years = $jobPost->max_experience_years;
        if($this->min_experience_years || $this->max_experience_years) {
            $this->experience_type = 'Experienced Only';
        } else {
            $this->experience_type = 'Any';
        }
        $this->gender_preference = $jobPost->gender_preference;
        $this->min_age = $jobPost->min_age;
        $this->max_age = $jobPost->max_age;
        
        $interview = $jobPost->interview_information;
        $interviewArr = is_string($interview) ? json_decode($interview, true) : (is_array($interview) ? $interview : []);
        $this->is_walk_in = (bool)($interviewArr['is_walk_in'] ?? false);
        $this->contact_preference = $interviewArr['contact_preference'] ?? 'All candidates';

        $this->vacancies_count = $jobPost->vacancies_count;
        $this->referral_budget = $jobPost->referral_budget;
        
        $this->job_description = $jobPost->job_description;
        
        $skillsData = $jobPost->skills;
        $this->selected_skills = is_string($skillsData) ? json_decode($skillsData, true) ?? [] : (is_array($skillsData) ? $skillsData : []);
        if(!empty($this->selected_skills)) $this->selected_requirements[] = 'skills';

        $assetsData = $jobPost->assets;
        $this->selected_assets = is_string($assetsData) ? json_decode($assetsData, true) ?? [] : (is_array($assetsData) ? $assetsData : []);
        if(!empty($this->selected_assets)) $this->selected_requirements[] = 'assets';

        $langData = $jobPost->languages;
        $this->selected_languages = is_string($langData) ? json_decode($langData, true) ?? [] : (is_array($langData) ? $langData : []);
        if(!empty($this->selected_languages)) $this->selected_requirements[] = 'languages';

        $degData = $jobPost->degree_requirement;
        $this->selected_degrees = is_string($degData) ? json_decode($degData, true) ?? [] : (is_array($degData) ? $degData : []);
        if(!empty($this->selected_degrees)) $this->selected_requirements[] = 'degrees';

        if($this->min_age || $this->max_age) $this->selected_requirements[] = 'age';
        if($this->gender_preference) $this->selected_requirements[] = 'gender';
        if($this->distance) $this->selected_requirements[] = 'distance';

        $this->existing_banner = $jobPost->banner;
        $this->job_plan_id = $jobPost->job_plan_id;
        $this->status = $jobPost->status;

        // Parse screening questions back to Builder format + Answers
        $sqData = $jobPost->screening_questions;
        $sqArray = is_string($sqData) ? json_decode($sqData, true) : (is_array($sqData) ? $sqData : []);
        
        $this->screening_questions = [];
        $this->screening_answers = [];
        
        $questionsList = $sqArray['questions'] ?? (isset($sqArray[0]) ? $sqArray : []);
        $answersList = $sqArray['answers'] ?? [];

        foreach($questionsList as $idx => $q) {
            $this->screening_questions[] = [
                'id' => $q['id'] ?? null,
                'question' => $q['question'] ?? '',
                'type' => $q['type'] ?? 'text',
                'options' => $q['options'] ?? '',
                'required' => $q['required'] ?? 1,
                'conditional_parent' => $q['conditional_parent'] ?? '',
                'conditional_value' => $q['conditional_value'] ?? '',
            ];
            $key = $q['id'] ?? $q['question'] ?? $idx;
            // Pre-populate with partner_answer from template, OR the explicitly saved answer
            $this->screening_answers[$key] = $answersList[$key] ?? $q['partner_answer'] ?? '';
        }
        
        $this->step = 2; // Jump directly to Job Details step when editing
    }

    public function goBack()
    {
        if ($this->step > 0) $this->step--;
    }

    public function cancelWizard()
    {
        if (in_array(auth()->user()->role, ['super_admin', 'admin'])) {
            return redirect()->route('admin.job-posts');
        }
        return redirect()->route('partner.hrms.job-posts');
    }

    public function selectPlan($planId)
    {
        $this->job_plan_id = $planId;
    }

    public function publishJob()
    {
        if (!$this->job_plan_id) {
            session()->flash('error', 'Please select a visibility plan.');
            return;
        }

        $this->submitDetails(); // Save final details

        $job = JobPost::where('id', $this->jobPostId)->where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->first();
        if (!$job) {
            session()->flash('error', 'Job post not found.');
            return;
        }

        $plan = JobPlan::find($this->job_plan_id);
        if (!$plan) {
            session()->flash('error', 'Invalid job plan.');
            return;
        }

        $partnerUser = \App\Models\User::findOrFail($this->getPartnerId());
        
        $activeJobsCount = JobPost::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })
                                  ->where('job_plan_id', $plan->id)
                                  ->where('status', 'active')
                                  ->count();

        // Calculate plan cost based on package limits (concurrent active jobs)
        if (empty($plan->job_limit)) {
            // Unlimited jobs: Pay only once
            $planCost = ($activeJobsCount > 0) ? 0 : $plan->price;
        } else {
            // Limited jobs: Pay whenever active jobs hit a multiple of the limit
            $planCost = ($activeJobsCount % $plan->job_limit === 0) ? $plan->price : 0;
        }

        $totalCost = $planCost + ((float)$this->referral_budget ?? 0);
        $gstAmount = $totalCost * 0.18;
        $grandTotal = $totalCost + $gstAmount;
        $walletDeducted = $partnerUser->isPartner() ? min($partnerUser->wallet_balance, $grandTotal) : 0;
        $onlinePayable = $grandTotal - $walletDeducted;

        DB::beginTransaction();
        try {
            if ($onlinePayable == 0) {
                if ($walletDeducted > 0) {
                    $partnerUser->decrement('wallet_balance', $walletDeducted);
                    \App\Models\WalletTransaction::create([
                        'user_id' => $partnerUser->id,
                        'amount' => $walletDeducted,
                        'type' => 'debit',
                        'description' => 'Published Job (Inc GST): ' . $job->job_title,
                        'reference_type' => 'job_post',
                        'reference_id' => $job->id,
                    ]);
                    
                    $admin = \App\Models\User::where('role', 'super_admin')->first();
                    if ($admin) {
                        $admin->increment('wallet_balance', $walletDeducted);
                        \App\Models\WalletTransaction::create([
                            'user_id' => $admin->id,
                            'amount' => $walletDeducted,
                            'type' => 'credit',
                            'description' => 'Received Job Post Payment from: ' . $partnerUser->name,
                            'reference_type' => 'job_post',
                            'reference_id' => $job->id,
                        ]);
                    }

                    // Log Transaction History for the Partner
                    $txnId = 'JOB-TXN-' . time() . '-' . rand(1000, 9999);
                    \App\Models\TransactionHistory::create([
                        'transaction_id' => $txnId,
                        'user_id' => $partnerUser->id,
                        'type' => 'job_post',
                        'reference_id' => $job->id,
                        'total_amount' => $grandTotal,
                        'wallet_deducted' => $walletDeducted,
                        'online_payable' => 0,
                        'gst_amount' => $gstAmount,
                        'platform_fee' => 0,
                        'net_amount' => $totalCost,
                        'status' => 'completed',
                        'description' => 'Wallet payment for Job Publish (Inc GST)',
                    ]);
                }

                $job->update([
                    'status' => 'active',
                    'job_plan_id' => $plan->id,
                    'wallet_deducted' => $walletDeducted,
                    'online_payable' => 0,
                    'gst_amount' => $gstAmount,
                    'published_at' => now(),
                    'expires_at' => now()->addDays($plan->validity_days),
                ]);

                DB::commit();
                session()->flash('success', 'Job Published Successfully!');
                if (in_array(auth()->user()->role, ['super_admin', 'admin'])) {
                    return redirect()->route('admin.job-posts');
                }
                return redirect()->route('partner.hrms.job-posts');
                
            } else {
                DB::commit();
                
                // Process Online Payment
                $transactionId = 'JOB-'.time().'-'.rand(1000, 9999);
                \Illuminate\Support\Facades\Cache::put('temp_job_post_'.$transactionId, [
                    'job_id' => $job->id,
                    'plan_id' => $plan->id,
                    'wallet_deducted' => $walletDeducted,
                    'online_payable' => $onlinePayable,
                    'gst_amount' => $gstAmount,
                    'total_cost' => $grandTotal,
                ], now()->addHours(2));
                
                $merchantId = $partnerUser->getResolvedTpiMerchantId();
                if (!$merchantId) {
                    session()->flash('error', 'Payment gateway is not configured.');
                    return;
                }
                
                $tpiService = app(\App\Services\TpiPaymentService::class);
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
                    $jobData = \Illuminate\Support\Facades\Cache::get('temp_job_post_'.$transactionId);
                    if ($jobData) {
                        $jobData['paymentId'] = $resp['paymentId'];
                        \Illuminate\Support\Facades\Cache::put('temp_job_post_'.$transactionId, $jobData, now()->addHours(2));
                        \Illuminate\Support\Facades\Cache::put('temp_job_post_pid_'.$resp['paymentId'], $transactionId, now()->addHours(2));
                    }
                    return redirect()->away($resp['paymentLink']);
                }

                session()->flash('error', 'Failed to generate payment link.');
                return;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
            return;
        }
    }

    public function viewJobPostDetails($id)
    {
        $query = JobPost::with(['department', 'branch', 'template'])->where('id', $id);
        if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) {
            $query->where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } });
        }
        $this->viewJobPost = $query->firstOrFail();
        $this->isViewModalOpen = true;
    }
}
