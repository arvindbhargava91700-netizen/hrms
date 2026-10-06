<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\SharedReferral;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobPostController extends Controller
{
    /**
     * Get a list of active job posts with relations.
     */
    public function index(Request $request)
    {
        $query = JobPost::with(['department', 'branch', 'creator'])
            ->select('job_posts.*')
            ->where('job_posts.status', 'active');

        if ($request->filled('partner_id')) {
            $query->where('job_posts.partner_id', $request->partner_id);
        }

        if ($request->filled('id')) {
            $query->where('job_posts.id', $request->id);
        }

        if ($request->filled('job_title')) {
            $query->where('job_posts.job_title', 'like', '%' . $request->job_title . '%');
        }

        // Filter logic
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $lat = (float) $request->latitude;
            $lng = (float) $request->longitude;
            $userCity = $request->job_city ?? null;

            if (!$userCity) {
                try {
                    $response = \Illuminate\Support\Facades\Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
                        'latlng' => "{$lat},{$lng}",
                        'key' => env('GOOGLE_MAPS_API_KEY')
                    ]);
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        if (!empty($data['results'])) {
                            foreach ($data['results'][0]['address_components'] as $component) {
                                if (in_array('locality', $component['types'])) {
                                    $userCity = $component['long_name'];
                                    break;
                                }
                                if (in_array('administrative_area_level_2', $component['types'])) {
                                    $userCity = $component['long_name'];
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore and let userCity be null
                }
            }

            $distanceFormula = '( 6371 * acos( cos( radians(?) ) * cos( radians( hrms_branches.lat ) ) * cos( radians( hrms_branches.lng ) - radians(?) ) + sin( radians(?) ) * sin( radians( hrms_branches.lat ) ) ) )';

            $query->leftJoin('hrms_branches', 'job_posts.branch_id', '=', 'hrms_branches.id')
                  ->selectRaw("$distanceFormula AS calculated_distance", [$lat, $lng, $lat]);

            if ($request->filled('distance')) {
                $requestedDist = strtolower(trim($request->distance));
                if (str_contains($requestedDist, '10') || str_contains($requestedDist, '25')) {
                    $val = (int) filter_var($requestedDist, FILTER_SANITIZE_NUMBER_INT);
                    $query->whereRaw("$distanceFormula <= ?", [$lat, $lng, $lat, $val]);
                } else if ($requestedDist === 'entire city') {
                    if ($userCity) {
                        $query->where('job_posts.job_city', 'like', '%' . $userCity . '%');
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                } else if ($requestedDist !== 'pan india') {
                    $query->where('job_posts.distance', $request->distance);
                }
            }
            
            // ALWAYS enforce job inherent Distance/Radius rules
            $query->where(function ($q) use ($distanceFormula, $lat, $lng, $userCity) {
                // 10 km
                $q->where(function ($q1) use ($distanceFormula, $lat, $lng) {
                    $q1->whereIn('job_posts.distance', ['10 km', '10km'])
                       ->whereRaw("$distanceFormula <= 10", [$lat, $lng, $lat]);
                });

                // 25 km
                $q->orWhere(function ($q2) use ($distanceFormula, $lat, $lng) {
                    $q2->whereIn('job_posts.distance', ['25 km', '25km'])
                       ->whereRaw("$distanceFormula <= 25", [$lat, $lng, $lat]);
                });

                // Pan India
                $q->orWhere('job_posts.distance', 'Pan India');

                // Entire City
                $q->orWhere(function ($q3) use ($userCity) {
                    $q3->where('job_posts.distance', 'Entire City');
                    if ($userCity) {
                        $q3->where('job_posts.job_city', 'like', '%' . $userCity . '%');
                    } else {
                        // If we couldn't detect the city, hide "Entire City" jobs to prevent showing out-of-city jobs
                        $q3->whereRaw('1 = 0');
                    }
                });

                // Null fallback
                $q->orWhereNull('job_posts.distance');
            });
        } else {
            // Standard fallback filters if no lat/lng provided
            if ($request->filled('job_city')) {
                $query->where('job_posts.job_city', 'like', '%' . $request->job_city . '%');
            }
            if ($request->filled('distance')) {
                $query->where('job_posts.distance', $request->distance);
            }
        }

        $jobPosts = $query->latest('job_posts.created_at')->paginate($request->per_page ?? 10);

        $jobPosts->getCollection()->transform(function ($job) {
            $skills = $job->skills ? explode(',', $job->skills) : [];
            $skills = array_filter(array_map('trim', $skills));

            $experience = $job->experience ?? (($job->min_experience_years !== null || $job->max_experience_years !== null) ? ($job->min_experience_years ?? '0') . ' - ' . ($job->max_experience_years ?? 'Any') . ' Yrs' : null);
            $salary = $job->salary ?? (($job->min_salary !== null || $job->max_salary !== null) ? '₹' . number_format((float)$job->min_salary, 2) . ' - ₹' . number_format((float)$job->max_salary, 2) : null);

            return [
                'id' => $job->id,
                'job_code' => $job->job_code,
                'job_title' => $job->job_title,
                'job_city' => $job->job_city,
                'country' => 'India',
                'radius_rule' => $job->distance,
                'distance_km' => isset($job->calculated_distance) ? round((float) $job->calculated_distance, 2) : null,
                'employment_type' => $job->employment_type,
                'salary' => $salary,
                'experience' => $experience,
                'skills' => $job->skills,
                'referral_amount' => $job->vacancies_count > 0 ? (float) ($job->referral_budget / $job->vacancies_count) : 0,
                'banner_url' => $job->banner ? asset('public/storage/' . $job->banner) : null,
                'status' => $job->status,
                'created_at' => $job->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $jobPosts
        ]);
    }

    /**
     * Get details of a single job post.
     */
    public function show($id)
    {
        $jobPost = JobPost::with(['department', 'branch', 'creator', 'partner'])
            ->where('status', 'active')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->formatJobPost($jobPost)
        ]);
    }

    /**
     * Get job post details by referral code.
     */
    public function getReferralPost($code)
    {
        $referral = SharedReferral::where('referral_code', $code)->first();

        if (! $referral) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid referral code.',
            ], 404);
        }

        $jobPost = JobPost::with(['department', 'branch', 'creator', 'partner'])
            ->where('status', 'active')
            ->find($referral->post_id);

        if (! $jobPost) {
            return response()->json([
                'success' => false,
                'message' => 'Job post not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => array_merge(
                $this->formatJobPost($jobPost),
                [
                    'referral' => [
                        'id' => $referral->id,
                        'referral_code' => $referral->referral_code,
                        'app_com_status' => $referral->app_com_status,
                        'post_com_status' => $referral->post_com_status,
                    ],
                ]
            ),
        ]);
    }

    /**
     * Build the formatted job post detail payload.
     */
    protected function formatJobPost(JobPost $jobPost): array
    {
        $skills = $jobPost->skills ? explode(',', $jobPost->skills) : [];
        $skills = array_filter(array_map('trim', $skills));

        // Split job description by newlines for responsibilities array
        $responsibilities = $jobPost->job_description ? explode("\n", strip_tags($jobPost->job_description)) : [];
        $responsibilities = array_filter(array_map('trim', $responsibilities));

        $companyName = $jobPost->partner ? $jobPost->partner->name : ($jobPost->creator->name ?? 'Company Name');
        $companyLogo = $jobPost->partner && $jobPost->partner->profile_image
            ? asset('storage/' . $jobPost->partner->profile_image)
            : null;
        $location = $jobPost->branch ? $jobPost->branch->name : 'Remote';

        $authId = auth()->id();
        $type = "job_post";
        $shareLink = url('/job-details/' . 'post_id=' . $jobPost->id . '?refer_id=' . $authId. '?type=' . $type);

        $experience = $jobPost->experience ?? (($jobPost->min_experience_years !== null || $jobPost->max_experience_years !== null) ? ($jobPost->min_experience_years ?? '0') . ' - ' . ($jobPost->max_experience_years ?? 'Any') . ' Yrs' : null);
        $salary = $jobPost->salary ?? (($jobPost->min_salary !== null || $jobPost->max_salary !== null) ? '₹' . number_format((float)$jobPost->min_salary, 2) . ' - ₹' . number_format((float)$jobPost->max_salary, 2) : null);

        return [
            'header' => [
                'logo_url' => $companyLogo,
                'job_title' => $jobPost->job_title,
                'company_name' => $companyName,
                'employment_type' => $jobPost->employment_type ?? 'Fulltime',
                'applicants_count' => $jobPost->applications()->count(),
                'location' => $location,
            ],
            'job_detail' => [
                'overview' => [
                    'salary' => $salary ?? 'Not specified',
                    'type' => $jobPost->employment_type ?? 'Fulltime',
                    'work_mode' => $jobPost->is_work_from_home ? 'Work From Home' : 'On-site',
                    'level' => $experience ?? 'Mid-Level',
                    'job_city' => $jobPost->job_city,
                    'distance' => $jobPost->distance,
                    'minimum_education' => $jobPost->minimum_education,
                    'english_level' => $jobPost->english_level,
                    'gender_preference' => $jobPost->gender_preference,
                ],
                'descriptions' => $jobPost->job_description,
                'skills' => array_values($skills),
                'responsibilities' => array_values($responsibilities),
                'screening_questions'  => $this->formatScreeningQuestionsForDisplay($jobPost->screening_questions),
                'additional_perks' => $jobPost->additional_perks ?? [],
                'interview_information' => $jobPost->interview_information,
                'joining_fee_required' => (bool)$jobPost->joining_fee_required,
                'salary_type' => $jobPost->salary_type,
            ],
            'company_detail' => [
                'logo_url' => $jobPost->branch && $jobPost->branch->company_logo ? asset('storage/' . $jobPost->branch->company_logo) : $companyLogo,
                'company_name' => $jobPost->branch ? $jobPost->branch->name : $companyName,
                'about_company' => $jobPost->branch ? $jobPost->branch->about_company : null,
                'details' => [
                    'website' => $jobPost->branch && $jobPost->branch->website ? $jobPost->branch->website : 'www.' . strtolower(str_replace(' ', '', $companyName)) . '.com',
                    'headquarters' => $jobPost->branch && $jobPost->branch->address ? $jobPost->branch->address : $location,
                    'industry' => $jobPost->branch ? $jobPost->branch->industry : null,
                    'company_size' => $jobPost->branch ? $jobPost->branch->company_size : null,
                    'company_type' => $jobPost->branch ? $jobPost->branch->company_type : null,
                    'founded_year' => $jobPost->branch ? $jobPost->branch->founded_year : null,
                ],
                'social_links' => [
                    'linkedin' => $jobPost->branch ? $jobPost->branch->linkedin_url : null,
                    'facebook' => $jobPost->branch ? $jobPost->branch->facebook_url : null,
                    'instagram' => $jobPost->branch ? $jobPost->branch->instagram_url : null,
                ]
            ],
            // Extra info for backward compatibility
            'id' => $jobPost->id,
            'job_code' => $jobPost->job_code,
            'share_link' => $shareLink,
            'referral_amount' => $jobPost->vacancies_count > 0 ? (float) ($jobPost->referral_budget / $jobPost->vacancies_count) : 0,
            'banner_url' => $jobPost->banner ? asset('public/storage/' . $jobPost->banner) : null,
            'department' => $jobPost->department,
            'branch' => $jobPost->branch,
        ];
    }

    /**
     * Parse and format screening questions for customer display.
     * Handles both raw JSON blobs and already-decoded arrays.
     * Returns a flat array of question objects ready for the mobile app to render.
     */
    protected function formatScreeningQuestionsForDisplay($raw): array
    {
        if (empty($raw)) return [];

        // Decode if string
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }

        if (!is_array($raw)) return [];

        // Handle nested structure: { questions: [...], answers: {...} }
        $questions = isset($raw['questions']) ? $raw['questions'] : $raw;
        $answers   = isset($raw['answers'])   ? $raw['answers']   : [];

        if (!is_array($questions)) return [];

        return array_values(array_map(function ($q) use ($answers) {
            $options = [];
            if (!empty($q['options'])) {
                $rawOpts = is_array($q['options'])
                    ? $q['options']
                    : array_filter(array_map('trim', explode(',', $q['options'])));
                $options = array_values($rawOpts);
            }

            $questionKey = $q['id'] ?? $q['question'] ?? null;
            $answer = $questionKey ? ($answers[$questionKey] ?? null) : null;

            return [
                'id'                 => $q['id']       ?? null,
                'question'           => $q['question'] ?? '',
                'type'               => $q['type']     ?? 'text',  // text|textarea|radio|select|checkbox
                'options'            => $options,
                'required'           => (bool)($q['required'] ?? false),
                'conditional_parent' => $q['conditional_parent'] ?? null,
                'conditional_value'  => $q['conditional_value']  ?? null,
                'answer'             => $answer,   // Pre-filled answer if exists (for job-specific answers)
            ];
        }, $questions));
    }

    /**
     * Apply for a job post via referral code. Stores the applying user and application status.
     **/

    public function apply(Request $request)
    {
        $request->validate([
            'post_id' => 'required|integer|exists:job_posts,id',
            'referral_code' => 'sometimes|nullable|string',

            'designation' => 'required|string|max:255',
            'address' => 'required|string|max:1000',
            'screening_answers' => 'nullable|string',

            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $screeningAnswers = null;
        if ($request->filled('screening_answers')) {
            $screeningAnswers = json_decode($request->screening_answers, true);
        }

        $userId = auth()->id();

        // Check already applied
        $alreadyApplied = JobApplication::where('post_id', $request->post_id)
            ->where('apply_user_id', $userId)
            ->exists();

        if ($alreadyApplied) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied for this job post.',
            ], 409);
        }

        // Referral validation
        $referral = null;

        if ($request->filled('referral_code')) {

            $referral = SharedReferral::where(
                'referral_code',
                $request->referral_code
            )->first();

            if (!$referral) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid referral code.',
                ], 422);
            }
        }

        $candidateProfile = \App\Models\CandidateProfile::where('user_id', $userId)->first();

        // Upload Resume
        $resumePath = null;

        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('resumes', 'public');
            
            // Sync to CandidateProfile for future use
            if ($candidateProfile) {
                $candidateProfile->update([
                    'resume_path' => $resumePath,
                    'resume_updated_at' => now(),
                ]);
            } else {
                $candidateProfile = \App\Models\CandidateProfile::create([
                    'user_id' => $userId,
                    'resume_path' => $resumePath,
                    'resume_updated_at' => now(),
                ]);
            }
        } else {
            // Use existing from profile
            if ($candidateProfile && $candidateProfile->resume_path) {
                $resumePath = $candidateProfile->resume_path;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Please upload a resume or complete your candidate profile first.',
                ], 422);
            }
        }

        // Create application
        $application = JobApplication::create([
            'post_id' => $request->post_id,

            'referral_id' => $referral?->id,
            'referral_code' => $referral?->referral_code,

            'apply_user_id' => $userId,

            'designation' => $request->designation,
            'address' => $request->address,
            'resume' => $resumePath,
            'screening_answers' => $screeningAnswers,

            'status' => JobApplication::STATUS_APPLIED,
            
            // Candidate Details Snapshot
            'education_level' => $candidateProfile ? $candidateProfile->highest_education : null,
            'experience_years' => $candidateProfile ? $candidateProfile->total_experience_years : null,
            'gender' => $candidateProfile ? $candidateProfile->gender : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully.',
            'data' => $application,
        ], 201);
    }



    /**
     * Update the status of a job application.
     */
    public function updateApplicationStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:'.implode(',', JobApplication::statuses()),
        ]);

        $application = JobApplication::findOrFail($id);
        $previousStatus = $application->status;
        $application->update(['status' => $request->status]);

        $this->creditReferralReward($application, $previousStatus);

        return response()->json([
            'success' => true,
            'message' => 'Application status updated to '.$request->status.'.',
            'data' => $application->fresh(),
        ]);
    }

    /**
     * Give the referral reward to the referrer when a candidate is hired/completed.
     * Amount is per post (referral_budget / vacancies_count). If the post already has
     * vacancies_count (e.g. 2) successful hires, no further wallet credit is added.
     */
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
    }

    /**
     * List all applications of the authenticated user.
     */
    public function applications(Request $request)
    {
        $applications = JobApplication::with(['jobPost', 'referral'])
            ->where('apply_user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $applications,
        ]);
    }

    public function applicationsDetail(Request $request,$id)
    {
        $applications = JobApplication::with(['jobPost', 'referral'])
            ->where('id', $id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $applications,
        ]);
    }

    /**
     * Share a job referral.
     */
    public function shareReferral(Request $request)
    {
        $request->validate([
            'post_id' => 'required|integer|exists:job_posts,id',
            'referral_code' => 'required|string|unique:shared_referrals,referral_code',
        ], [
            'referral_code.unique' => 'This referral code already exists.',
        ]);

        $authId = auth()->id();

        // Check same user already shared this post
        $alreadyShared = SharedReferral::where('referral_code', $request->referral_code)
            ->exists();

        if ($alreadyShared) {
            return response()->json([
                'success' => false,
                'message' => 'You have already shared this job post.',
            ], 422);
        }

        $referral = SharedReferral::create([
            'user_id' => $authId,
            'post_id' => $request->post_id,
            'referral_code' => $request->referral_code,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Referral shared successfully.',
            'data' => $referral,
        ], 201);
    }
}
