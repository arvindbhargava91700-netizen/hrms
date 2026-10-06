<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CandidateProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = auth()->user();
        
        $profile = \App\Models\CandidateProfile::firstOrCreate(
            ['user_id' => $user->id]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'profile_image_url' => $user->profile_image_url,
                ],
                'profile' => $profile
            ]
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $profile = \App\Models\CandidateProfile::firstOrCreate(
            ['user_id' => $user->id]
        );

        $validated = $request->validate([
            'highest_education' => 'nullable|string',
            'doctorate' => 'nullable|string',
            'educations' => 'nullable|array',
            
            'skills' => 'nullable|array',
            
            'preferred_job_roles' => 'nullable|array',
            'preferred_locations' => 'nullable|array',
            
            'preferred_job_type' => 'nullable|string',
            'preferred_work_mode' => 'nullable|string',
            'preferred_shift' => 'nullable|string',
            'expected_salary' => 'nullable|numeric',
            
            'documents_and_assets' => 'nullable|array',
            
            'work_experiences' => 'nullable|array',
            'total_experience_years' => 'nullable|integer',
            'total_experience_months' => 'nullable|integer',
            'current_monthly_salary' => 'nullable|numeric',
            
            'internships' => 'nullable|array',
            
            'gender' => 'nullable|string',
        ]);

        $profile->update($validated);

        // Handle resume upload separately or here
        $fileKey = $request->hasFile('resume_path') ? 'resume_path' : ($request->hasFile('resume') ? 'resume' : null);
        if ($fileKey) {
            $path = $request->file($fileKey)->store('resumes', 'public');
            $profile->update([
                'resume_path' => $path,
                'resume_updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $profile->fresh()
        ]);
    }
}
