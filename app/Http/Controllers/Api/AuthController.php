<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\CustomerRegisteredMail;
use App\Models\SharedReferral;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\TransactionHistory;
use App\Services\FirebaseNotificationService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {

        $this->otpService = $otpService;

    }

    /**
     * Request OTP for Registration
     */
    public function registerRequestOtp(Request $request)
    {

        $data = $request->validate([

            'name' => 'required|string|max:100',

            'email' => 'required|email|unique:users',

            'mobile' => 'required|string|max:20|unique:users',

        ]);

        // Generate OTPs

        $emailOtp = $this->otpService->generateOtp('email_'.$data['email']);

        $mobileOtp = $this->otpService->generateOtp('mobile_'.$data['mobile']);

        // Send OTPs (Stubs)

        $this->otpService->sendEmailOtp($data['email'], $emailOtp);

        $this->otpService->sendSmsOtp($data['mobile'], $mobileOtp, $data['name']);

        // Store intermediate user data in cache to use during verification

        Cache::put("registration_{$data['mobile']}", $data, now()->addMinutes(10));

        return response()->json([

            'status' => 'success',

            'message' => 'OTPs sent to your email and mobile. Please verify.',

        ]);

    }

    /**
     * Resend Registration OTPs if a pending registration exists.
     */
    public function resendRegisterOtp(Request $request)
    {

        $data = $request->validate([

            'email' => 'required|email',

            'mobile' => 'required|string|max:20',

        ]);

        if (User::where('email', $data['email'])->exists() || User::where('mobile', $data['mobile'])->exists()) {

            return response()->json([

                'status' => 'error',

                'message' => 'Email or mobile already registered. Please login instead.',

            ], 422);

        }

        $cacheKey = "registration_{$data['mobile']}";

        $userData = Cache::get($cacheKey);

        if (! $userData || $userData['email'] !== $data['email']) {

            return response()->json([

                'status' => 'error',

                'message' => 'No pending registration found for this mobile/email or the session has expired.',

            ], 404);

        }

        $emailOtp = $this->otpService->generateOtp('email_'.$data['email']);

        $mobileOtp = $this->otpService->generateOtp('mobile_'.$data['mobile']);

        $this->otpService->sendEmailOtp($data['email'], $emailOtp);

        $this->otpService->sendSmsOtp($data['mobile'], $mobileOtp, $data['name']);

        Cache::put($cacheKey, $userData, now()->addMinutes(10));

        return response()->json([

            'status' => 'success',

            'message' => 'OTP resent to your email and mobile. Please verify.',

        ]);

    }

    /**
     * Verify Registration OTPs and create user
     */
    public function registerVerify_old(Request $request)
    {
       

        $request->validate([

            'email' => 'required|email',

            'mobile' => 'required|string',

            'email_otp' => 'required|string',

            'mobile_otp' => 'required|string',

            'device_id' => 'sometimes|nullable|string',

            'referral_code' => 'sometimes|nullable|string|exists:shared_referrals,referral_code',

        ]);

        $userData = Cache::get("registration_{$request->mobile}");

        if (! $userData || $userData['email'] !== $request->email) {

            return response()->json(['status' => 'error', 'message' => 'Registration session expired or invalid.'], 400);

        }

        $isEmailValid = $this->otpService->verifyOtp('email_'.$request->email, $request->email_otp);

        $isMobileValid = $this->otpService->verifyOtp('mobile_'.$request->mobile, $request->mobile_otp);

        if (! $isEmailValid || ! $isMobileValid) {

            return response()->json(['status' => 'error', 'message' => 'Invalid OTP(s). Please try again.'], 400);

        }

        // Create user

        $user = User::create([

            'name' => $userData['name'],

            'email' => $userData['email'],

            'mobile' => $userData['mobile'],

            'role' => 'customer',

            'status' => 'active',

            'password' => Hash::make(Str::random(16)), // Password not used directly via Mobile OTP flow

        ]);

        $user->assignRole('customer');

        try {

            // Mail::to($user->email)->queue(new CustomerRegisteredMail($user));

        } catch (\Exception $e) {

            Log::error('Failed to send api registration welcome email: '.$e->getMessage());

        }

        // ── Referral: credit app commission to the referral code owner's wallet ──
        if ($request->filled('referral_code')) {
           
          

            $referral = SharedReferral::where('referral_code', $request->referral_code)->first();
             

            if ($referral && $referral->user_id !== $user->id && $referral->app_com_status !== 'credited') {

                $commission = (float) SystemSetting::getSetting('app_comission', 100);

                $owner = User::find($referral->user_id);

                if ($owner) {

                    DB::transaction(function () use ($owner, $commission, $referral) {
                        
                        $alreadyCredited = WalletTransaction::where('reference_type', SharedReferral::class)
                            ->where('reference_id', $referral->id)
                            ->exists();

                        if (! $alreadyCredited) {
                            $owner->increment('wallet_balance', $commission);

                            WalletTransaction::create([
                                'user_id' => $owner->id,
                                'type' => 'credit',
                                'amount' => $commission,
                                'description' => 'Referral commission for code '.$referral->referral_code,
                                'reference_type' => SharedReferral::class,
                                'reference_id' => $referral->id,
                            ]);

                            $referral->update(['app_com_status' => 'credited']);
                        }

                    });

                }

            }

        }

        Cache::forget("registration_{$request->mobile}");

        // Generate JWT Token

        $token = auth('api')->setTTL(4320)->login($user);

        return response()->json([

            'status' => 'success',

            'message' => 'Account created and verified successfully.',

            'data' => [

                'user' => $user,

                'token' => $token,

                'token_type' => 'bearer',

            ],

        ], 201);

    }


    public function registerVerify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'mobile' => 'required|string',
            'email_otp' => 'required|string',
            'mobile_otp' => 'required|string',
            'device_id' => 'sometimes|nullable|string',
            'referral_code' => 'sometimes|nullable|string|exists:shared_referrals,referral_code',
        ]);

        $userData = Cache::get("registration_{$request->mobile}");

        if (! $userData || $userData['email'] !== $request->email) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration session expired or invalid.'
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Verify OTP
        |--------------------------------------------------------------------------
        */

        $isEmailValid = $this->otpService->verifyOtp(
            'email_' . $request->email,
            $request->email_otp
        );

        $isMobileValid = $this->otpService->verifyOtp(
            'mobile_' . $request->mobile,
            $request->mobile_otp
        );

        if (! $isEmailValid || ! $isMobileValid) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OTP(s). Please try again.'
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Check Whether Device Is Already Registered
        |--------------------------------------------------------------------------
        |
        | Same device se registration allowed hai.
        | Sirf referral commission block hoga.
        |
        */

        $deviceAlreadyRegistered = false;

        if ($request->filled('device_id')) {
            $deviceAlreadyRegistered = User::where(
                'device_id',
                $request->device_id
            )->exists();
        }

        /*
        |--------------------------------------------------------------------------
        | Create User
        |--------------------------------------------------------------------------
        */

        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'mobile' => $userData['mobile'],
            'device_id' => $request->device_id,
            'role' => 'customer',
            'status' => 'active',
            'password' => Hash::make(Str::random(16)),
        ]);

        $user->assignRole('customer');

        try {
            // Mail::to($user->email)->queue(new CustomerRegisteredMail($user));
        } catch (\Exception $e) {
            Log::error(
                'Failed to send api registration welcome email: ' .
                $e->getMessage()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Referral Commission
        |--------------------------------------------------------------------------
        |
        | Agar device already registered hai:
        | commission nahi milega.
        |
        | Agar device new hai:
        | commission milega.
        |
        */

        if (
            $request->filled('referral_code') &&
            ! $deviceAlreadyRegistered
        ) {

            $referral = SharedReferral::where(
                'referral_code',
                $request->referral_code
            )->first();

            if (
                $referral &&
                $referral->user_id !== $user->id &&
                $referral->app_com_status !== 'credited'
            ) {

                $commission = (float) SystemSetting::getSetting(
                    'app_comission',
                    100
                );

                $owner = User::find($referral->user_id);

                if ($owner) {

                    DB::transaction(function () use (
                        $owner,
                        $commission,
                        $referral
                    ) {

                        $alreadyCredited = WalletTransaction::where(
                            'reference_type',
                            SharedReferral::class
                        )
                        ->where(
                            'reference_id',
                            $referral->id
                        )
                        ->exists();

                        if (! $alreadyCredited) {

                            /*
                            |--------------------------------------------------------------------------
                            | Wallet Increment
                            |--------------------------------------------------------------------------
                            */

                            $owner->increment(
                                'wallet_balance',
                                $commission
                            );

                            /*
                            |--------------------------------------------------------------------------
                            | Wallet Transaction
                            |--------------------------------------------------------------------------
                            */

                            WalletTransaction::create([
                                'user_id' => $owner->id,
                                'type' => 'credit',
                                'amount' => $commission,
                                'description' =>
                                    'Referral commission for code ' .
                                    $referral->referral_code,
                                'reference_type' =>
                                    SharedReferral::class,
                                'reference_id' => $referral->id,
                            ]);

                            /*
                            |--------------------------------------------------------------------------
                            | Transaction History
                            |--------------------------------------------------------------------------
                            */

                            TransactionHistory::create([
                                'transaction_id' => Str::uuid()->toString(),
                                'user_id' => $owner->id,
                                'type' => 'credit',
                                'reference_id' => $referral->id,
                                'total_amount' => $commission,
                                'platform_fee' => 0,
                                'net_amount' => $commission,
                                'status' => 'completed',
                                'description' =>
                                    'Referral commission for code ' .
                                    $referral->referral_code,
                            ]);

                            /*
                            |--------------------------------------------------------------------------
                            | Mark Referral As Credited
                            |--------------------------------------------------------------------------
                            */

                            $referral->update([
                                'app_com_status' => 'credited'
                            ]);
                        }
                    });
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Clear Registration Cache
        |--------------------------------------------------------------------------
        */

        Cache::forget("registration_{$request->mobile}");

        /*
        |--------------------------------------------------------------------------
        | Generate JWT Token
        |--------------------------------------------------------------------------
        */

        $token = auth('api')
            ->setTTL(4320)
            ->login($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Account created and verified successfully.',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'bearer',
            ],
        ], 201);
    }



    /**
     * Request OTP for Login
     */
    public function loginRequestOtp(Request $request)
    {

        $request->validate([

            'mobile' => 'required|string|exists:users,mobile',

            'channel' => 'sometimes|string|in:sms,whatsapp',

        ]);

        $user = User::where('mobile', $request->mobile)->first();

        if ($user->role !== 'customer') {

            return response()->json(['status' => 'error', 'message' => 'Only customers can login via Mobile App.'], 403);

        }

        $otp = $this->otpService->generateOtp('login_'.$request->mobile);

        if ($request->filled('channel')) {

            if ($request->channel === 'sms') {

                $this->otpService->sendSmsOtp($request->mobile, $otp, $user->name);

                $deliveryMethod = 'SMS';

            } else {

                $this->otpService->sendWhatsappOtp($request->mobile, $otp, $user->name);

                $deliveryMethod = 'WhatsApp';

            }

            return response()->json([

                'status' => 'success',

                'message' => "OTP sent to your mobile via {$deliveryMethod}.",

            ]);

        }

        // Default behavior: send via both SMS and WhatsApp

        $this->otpService->sendSmsOtp($request->mobile, $otp, $user->name);

        $this->otpService->sendWhatsappOtp($request->mobile, $otp, $user->name);

        return response()->json([

            'status' => 'success',

            'message' => 'OTP sent to your mobile number via SMS and WhatsApp.',

        ]);

    }

    /**
     * Verify Login OTP
     */
    public function loginVerify(Request $request)
    {

        $request->validate([

            'mobile' => 'required|string',

            'otp' => 'required|string',

        ]);

        $isValid = $this->otpService->verifyOtp('login_'.$request->mobile, $request->otp);

        if (! $isValid) {

            return response()->json(['status' => 'error', 'message' => 'Invalid or expired OTP.'], 401);

        }

        $user = User::where('mobile', $request->mobile)->first();

        if (! $user) {

            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);

        }

        // Generate JWT Token

        $token = auth('api')->setTTL(4320)->login($user);

        return response()->json([

            'status' => 'success',

            'message' => 'Logged in successfully.',

            'data' => [

                'user' => $user,

                'token' => $token,

                'token_type' => 'bearer',

            ],

        ]);

    }

    public function profile()
    {

        $data = auth('api')->user();

        if ($data && $data->profile_image) {

            $data->profile_image = asset('storage/'.$data->profile_image);

        }

        return response()->json([

            'status' => 'success',

            'data' => $data,

        ]);

    }

    public function updateProfile(Request $request)
    {

        $user = auth('api')->user();

        $data = $request->validate([

            'name' => 'sometimes|required|string|max:100',

            'email' => 'sometimes|required|email|unique:users,email,'.$user->id,

            'mobile' => 'sometimes|required|string|max:20|unique:users,mobile,'.$user->id,

            'profile_image' => 'sometimes|nullable|image|max:2048',

        ]);

        if ($request->hasFile('profile_image')) {

            $path = $request->file('profile_image')->store('profile_images', 'public');

            $data['profile_image'] = $path;

        }

        $user->update($data);

        if ($user->fcm_token) {

            app(FirebaseNotificationService::class)->sendNotification(

                $user->fcm_token,

                'Profile Updated',

                'Your profile information has been successfully updated.',

                ['type' => 'profile_updated']

            );

        }

        return response()->json([

            'status' => 'success',

            'message' => 'Profile updated successfully.',

            'data' => $user->fresh(),

        ]);

    }

    public function logout()
    {

        auth('api')->logout();

        return response()->json([

            'status' => 'success',

            'message' => 'Logged out successfully',

        ]);

    }

    /**
     * Update FCM Token for push notifications.
     */
    public function updateFcmToken(Request $request)
    {

        $request->validate([

            'fcm_token' => 'required|string',

        ]);

        $user = auth('api')->user();

        $user->update([

            'fcm_token' => $request->fcm_token,

        ]);

        return response()->json([

            'status' => 'success',

            'message' => 'FCM token updated successfully.',

        ]);

    }

    /**
     * Destroy the authenticated user's account after performing necessary checks.
     */
    public function destroyAccount(Request $request)
    {
        $user = auth('api')->user();

        // Perform any necessary checks before deletion (e.g., pending bookings, subscriptions, etc.)
        // For example:
        // if ($user->bookings()->where('status', 'pending')->exists()) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'You have pending bookings. Please resolve them before deleting your account.'
        //     ], 400);
        // }

        // Delete the user account
        // $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Your account has been deleted successfully.',
        ]);
    }
}
