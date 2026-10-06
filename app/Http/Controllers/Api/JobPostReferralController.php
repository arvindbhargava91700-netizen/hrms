<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobPostReferral;
use App\Models\JobPost;
use Illuminate\Http\Request;

class JobPostReferralController extends Controller
{
    /**
     * Get a list of referrals made by the authenticated user.
     */
    public function index(Request $request)
    {
        $referrals = JobPostReferral::with(['jobPost'])
            ->where('referred_by', auth()->id())
            ->latest()
            ->paginate($request->per_page ?? 10);

        $referrals->getCollection()->transform(function ($referral) {
            if ($referral->resume_path) {
                $referral->resume_url = asset('storage/' . $referral->resume_path);
            } else {
                $referral->resume_url = null;
            }
            return $referral;
        });

        return response()->json([
            'success' => true,
            'data' => $referrals
        ]);
    }

    /**
     * Store a new job post referral.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_post_id' => 'required|exists:job_posts,id',
            'candidate_name' => 'required|string|max:255',
            'candidate_email' => 'required|email|max:255',
            'candidate_phone' => 'required|string|max:20',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $userId = auth()->id();
        $jobPostId = $validated['job_post_id'];
        $email = $validated['candidate_email'];
        $phone = $validated['candidate_phone'];

        // Check uniqueness constraint manually to return a nice message
        $existsEmail = JobPostReferral::where('job_post_id', $jobPostId)
            ->where('referred_by', $userId)
            ->where('candidate_email', $email)
            ->exists();

        $existsPhone = JobPostReferral::where('job_post_id', $jobPostId)
            ->where('referred_by', $userId)
            ->where('candidate_phone', $phone)
            ->exists();

        if ($existsEmail || $existsPhone) {
            return response()->json([
                'error' => 'You have already referred this candidate for this job post.'
            ], 422);
        }

        $resumePath = null;
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('resumes/referrals', 'public');
        }

        $referral = JobPostReferral::create([
            'job_post_id' => $jobPostId,
            'referred_by' => $userId,
            'candidate_name' => $validated['candidate_name'],
            'candidate_email' => $email,
            'candidate_phone' => $phone,
            'resume_path' => $resumePath,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Candidate referred successfully.',
            'data' => $referral
        ], 201);
    }
}
