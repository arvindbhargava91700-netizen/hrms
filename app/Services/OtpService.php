<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\Msg91Service;
use App\Services\WhatsAppService;

class OtpService
{

    public function __construct(
        protected Msg91Service $msg91Service,
        protected WhatsAppService $whatsAppService
    ) {}

    /**
     * Generate and cache an OTP for a given identifier (mobile or email).
     */
    public function generateOtp(string $identifier): string
    {
        // For testing/development, you can use a static OTP if needed.
        // But here we generate a real random one.
        // $otp = (string) random_int(100000, 999999);
        $otp = '123456'; // Static OTP for testing

        // Store in cache for 10 minutes
        Cache::put("otp_{$identifier}", $otp, now()->addMinutes(10));

        return $otp;
    }

    /**
     * Verify the OTP against the cached value.
     */
    public function verifyOtp(string $identifier, string $otp): bool
    {
        $cachedOtp = Cache::get("otp_{$identifier}");

        if ($cachedOtp && $cachedOtp === $otp) {
            Cache::forget("otp_{$identifier}"); // Invalidate after successful use
            return true;
        }

        return false;
    }

    /**
     * STUB: Send OTP via SMS
     */
    public function sendSmsOtp(string $mobile, string $otp, $name = 'User'): void
    {
        $this->msg91Service->sendSms($mobile, '67ea46a4d6fc055704034879', ['var1' => $name, 'var2' => $otp]);
    }

    /**
     * STUB: Send OTP via WhatsApp
     */
    public function sendWhatsappOtp(string $mobile, string $otp, $name = 'User'): void
    {
        $this->whatsAppService->sendWhatsappMessage($mobile, "Your OTP is: {$otp}");
        Log::info("WHATSAPP SENT to {$mobile} | OTP: {$otp}");
    }

    /**
     * Send OTP via Email
     */
    public function sendEmailOtp(string $email, string $otp): void
    {
        \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\SendOtpMail($otp));
        Log::info("EMAIL SENT to {$email} | OTP: {$otp}");
    }
}
