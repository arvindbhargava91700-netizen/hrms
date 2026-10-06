<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerKyc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CustomerKycController extends Controller
{
    public function show(Request $request)
    {
        $kyc = CustomerKyc::where('customer_id', $request->user()->id)->first();
        
        if (!$kyc) {
            return response()->json([
                'status' => 'success',
                'data' => null,
                'message' => 'KYC profile not found.'
            ]);
        }
        
        // Map full URLs directly into the original keys
        $data = $kyc->toArray();
        $data['live_photo'] = $kyc->getStoredFileUrl($kyc->live_photo);
        $data['aadhaar_front'] = $kyc->getStoredFileUrl($kyc->aadhaar_front);
        $data['aadhaar_back'] = $kyc->getStoredFileUrl($kyc->aadhaar_back);
        $data['pan_front'] = $kyc->getStoredFileUrl($kyc->pan_front);
        $data['pan_back'] = $kyc->getStoredFileUrl($kyc->pan_back);
        $data['bank_statement'] = $kyc->getStoredFileUrl($kyc->bank_statement);
        $data['passport_front'] = $kyc->getStoredFileUrl($kyc->passport_front);
        $data['passport_back'] = $kyc->getStoredFileUrl($kyc->passport_back);
        $data['driving_license_front'] = $kyc->getStoredFileUrl($kyc->driving_license_front);
        $data['driving_license_back'] = $kyc->getStoredFileUrl($kyc->driving_license_back);
        
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function submit(Request $request)
    {
        $user = $request->user();
        
        $kyc = CustomerKyc::where('customer_id', $user->id)->first();
        
        if ($kyc && $kyc->status === 'approved') {
            return response()->json([
                'status' => 'error',
                'message' => 'Your KYC is already approved and cannot be updated.'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'live_photo' => 'nullable|string', // base64 or file
            'live_photo_file' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'aadhaar_front' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'aadhaar_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'pan_front' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'pan_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'bank_statement' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'passport_front' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'passport_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'driving_license_front' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'driving_license_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Ensure required fields are provided if creating for the first time
        if (!$kyc) {
            $hasLivePhoto = $request->hasFile('live_photo_file') || $request->filled('live_photo');
            if (!$hasLivePhoto || !$request->hasFile('aadhaar_front') || !$request->hasFile('pan_front') || !$request->hasFile('bank_statement')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Live photo, Aadhaar front, PAN front, and Bank statement are required.'
                ], 422);
            }
        }

        $paths = [];
        $baseDir = "kyc/customer/{$user->id}";

        $fileFields = [
            'aadhaar_front', 'aadhaar_back', 'pan_front', 'pan_back', 
            'bank_statement', 'passport_front', 'passport_back', 
            'driving_license_front', 'driving_license_back'
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $paths[$field] = $request->file($field)->store("{$baseDir}/{$field}", 'public');
            }
        }
        
        // Handle Live Photo (either as file or base64)
        if ($request->hasFile('live_photo_file')) {
            $paths['live_photo'] = $request->file('live_photo_file')->store("{$baseDir}/live_photo", 'public');
        } elseif ($request->filled('live_photo')) {
            $base64Image = $request->input('live_photo');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, etc.
                
                if (in_array($type, ['jpg', 'jpeg', 'png'])) {
                    $base64Image = str_replace(' ', '+', $base64Image);
                    $imageName = \Illuminate\Support\Str::random(40) . '.' . $type;
                    $path = "{$baseDir}/live_photo/{$imageName}";
                    Storage::disk('public')->put($path, base64_decode($base64Image));
                    $paths['live_photo'] = $path;
                }
            }
        }

        $isResubmit = $kyc !== null;

        if (!$kyc) {
            $kyc = new CustomerKyc();
            $kyc->customer_id = $user->id;
        }

        foreach ($paths as $field => $path) {
            // Delete old file if updating
            if ($kyc->$field && Storage::disk('public')->exists($kyc->$field)) {
                Storage::disk('public')->delete($kyc->$field);
            }
            $kyc->$field = $path;
        }

        $kyc->status = 'pending'; // Reset to pending if updated
        $kyc->save();
        
        $title = $isResubmit ? 'KYC Resubmitted' : 'KYC Submitted';
        $message = $isResubmit 
            ? 'Your KYC details have been successfully resubmitted and are pending review.'
            : 'Your KYC details have been successfully submitted and are pending review.';
        
        \App\Models\AppNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
        ]);

        if ($user->fcm_token) {
            try {
                app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                    $user->fcm_token,
                    $title,
                    $message,
                    [],
                    null,
                    false // do not double-log
                );
            } catch (\Exception $e) {
                // ignore
            }
        }

        // Notify admins
        $admins = \App\Models\User::where('role', 'super_admin')->get();
        $adminTitle = $isResubmit ? 'Customer KYC Resubmitted' : 'Customer KYC Submitted';
        $adminMessage = $isResubmit 
            ? "Customer {$user->name} has resubmitted their KYC details for review."
            : "Customer {$user->name} has submitted their KYC details for review.";

        foreach ($admins as $admin) {
            \App\Models\AppNotification::create([
                'user_id' => $admin->id,
                'title' => $adminTitle,
                'message' => $adminMessage,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'KYC details submitted successfully and are pending review.'
        ]);
    }
}
