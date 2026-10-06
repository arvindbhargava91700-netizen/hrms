<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Handle Partner Login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!$token = auth('partner_api')->setTTL(4320)->attempt($credentials)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid credentials.'], 401);
        }

        $user = auth('partner_api')->user();

        if ($user->role !== 'partner') {
            auth('partner_api')->logout();
            return response()->json(['status' => 'error', 'message' => 'Only partners can access this API.'], 403);
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
     * Handle Partner Logout.
     */
    public function logout(Request $request)
    {
        auth('partner_api')->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out.',
        ]);
    }

    /**
     * Handle Partner Registration.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'mobile'   => 'required|string|max:20|unique:users',
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers()],
        ]);

        $user = \App\Models\User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'mobile'   => $data['mobile'],
            'role'     => 'partner',
            'status'   => 'active',
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
        ]);

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'partner', 'guard_name' => 'web']);
        $user->assignRole('partner');

        try {
            $this->assignFreePackage($user);
            \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\PartnerRegisteredMail($user));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send api partner registration welcome email: ' . $e->getMessage());
        }

        // Generate Token
        $token = auth('partner_api')->setTTL(4320)->login($user);

        return response()->json([
            'status'  => 'success',
            'message' => 'Account created successfully! Please complete your KYC and wait for admin approval.',
            'data'    => [
                'user'  => $user,
                'token' => $token,
                'token_type' => 'bearer',
            ]
        ], 201);
    }

    /**
     * Get Partner Profile.
     */
    public function profile(Request $request)
    {
        $user = auth('partner_api')->user();
        
        if ($user && $user->profile_image) {
            $user->profile_image = url('storage/' . $user->profile_image);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
            ],
        ]);
    }

    /**
     * Update Partner Profile.
     */
    public function updateProfile(Request $request)
    {
        $user = auth('partner_api')->user();

        $data = $request->validate([
            'name'  => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'mobile' => 'sometimes|required|string|max:20|unique:users,mobile,' . $user->id,
            'profile_image' => 'sometimes|nullable|image|max:2048',
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
     * Assign a free package to a newly registered partner.
     */
    private function assignFreePackage(\App\Models\User $partner)
    {
        $freePackage = \App\Models\PartnerPackage::where('price', 0)->where('is_active', true)->latest()->first();
        
        if ($freePackage) {
            \App\Models\PartnerSubscription::create([
                'partner_id' => $partner->id,
                'partner_package_id' => $freePackage->id,
                'starts_at' => now(),
                'expires_at' => $freePackage->duration_days > 0 ? now()->addDays($freePackage->duration_days) : null,
                'status' => 'active',
            ]);
        }
    }
}
