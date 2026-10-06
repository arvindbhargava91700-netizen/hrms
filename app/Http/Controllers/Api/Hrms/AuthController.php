<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Login via Email or Mobile + Password
     */
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string', // can be email or mobile
            'password' => 'required|string',
        ]);

        $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        $credentials = [
            $fieldType => $request->login,
            'password' => $request->password
        ];

        if (!$token = auth('hrms_api')->setTTL(4320)->attempt($credentials)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid credentials.'
            ], 401);
        }

        $user = auth('hrms_api')->user();

        // Check if user is an employee or has admin/partner roles
        if ($user->role !== 'employee' && !$user->isAdmin() && !$user->isPartner()) {
            auth('hrms_api')->logout();
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized access. Only employees can log in to HRMS.'
            ], 403);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Logged in successfully.',
            'data'    => [
                'user'  => $user,
                'token' => $token,
                'token_type' => 'bearer',
            ]
        ]);
    }

    /**
     * Get User Profile
     */
    public function profile(Request $request)
    {
        $user = auth('hrms_api')->user();
        
        $data = $user->toArray();
        $data['roles'] = $user->getRoleNames();
        $data['permissions'] = $user->getAllPermissions()->pluck('name');
        
        if ($user->profile_image) {
            $data['profile_image'] = asset('storage/' . $user->profile_image);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    /**
     * Update User Profile
     */
    public function updateProfile(Request $request)
    {
        $user = auth('hrms_api')->user();

        $data = $request->validate([
            'name'  => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'mobile' => 'sometimes|required|string|max:20|unique:users,mobile,' . $user->id,
            'profile_image' => 'sometimes|nullable|image|max:2048',
            'fcm_token' => 'sometimes|nullable|string',
        ]);

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $data['profile_image'] = $path;
        }

        $user->update($data);

        if ($user->fcm_token) {
            try {
                app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                    $user->fcm_token,
                    'Profile Updated',
                    'Your profile information has been successfully updated.',
                    ['type' => 'profile_updated'], null, false
                );
            } catch (\Exception $e) {
                // ignore
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Profile updated successfully.',
            'data'    => $user->fresh()
        ]);
    }

    /**
     * Logout
     */
    public function logout()
    {
        auth('hrms_api')->logout();

        return response()->json([
            'status'  => 'success',
            'message' => 'Logged out successfully'
        ]);
    }

    // destroy account
    public function destroyAccount(Request $request)
    {
        $user = auth('hrms_api')->user();
        // $user->delete();
        return response()->json([
            'status'  => 'success',
            'message' => 'Account deleted successfully'
        ]);
    }
}
