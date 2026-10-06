<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Subscription;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BookingService
{
    public function __construct(
        protected OtpService $otpService,
        protected WhatsAppNotificationService $waNotify
    ) {}

    public function sendOtp(Booking $booking): void
    {
        if ($booking->status === 'confirmed' || $booking->status === 'completed') {
            throw new \InvalidArgumentException('Booking already confirmed.');
        }

        $otp = $this->otpService->generateOtp('booking_' . $booking->id);
        $booking->update(['otp' => $otp, 'status' => 'pending_otp']);

        try {
            $customer = $booking->customer;
            if ($customer?->mobile) {
                $this->otpService->sendSmsOtp($customer->mobile, $otp, $customer->name);
                // Removed the old whatsapp OTP to use the new centralized notification
            }
            
            // Centralized WhatsApp notifications
            $this->waNotify->sendBookingCreated($booking);

            if ($customer?->fcm_token) {
                app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                    $customer->fcm_token,
                    'Booking Created',
                    'Your booking is pending OTP verification.',
                    ['booking_id' => $booking->id, 'type' => 'booking_pending']
                );
            }

        } catch (\Exception $e) {
            Log::error('Failed to send booking OTP/WhatsApp: ' . $e->getMessage());
        }

        Log::info("Booking OTP for booking {$booking->id}: {$otp}");
    }

    public function canResendOtp(Booking $booking): bool
    {
        return ! Cache::has("booking_otp_cooldown_{$booking->id}");
    }

    public function markOtpSent(Booking $booking): void
    {
        Cache::put("booking_otp_cooldown_{$booking->id}", true, now()->addSeconds(60));
    }

    public function verifyOtp(Booking $booking, string $otp): bool
    {
        if ($booking->status === 'confirmed' || $booking->status === 'completed') {
            throw new \InvalidArgumentException('Booking already confirmed.');
        }

        $valid = $this->otpService->verifyOtp('booking_' . $booking->id, $otp)
            || ($booking->otp && hash_equals((string) $booking->otp, $otp));

        if (! $valid) {
            return false;
        }

        $this->confirmBooking($booking);

        return true;
    }

    public function confirmBooking(Booking $booking): void
    {
        $booking->update([
            'status'          => 'confirmed',
            'otp_verified_at' => now(),
            'otp'             => null,
        ]);

        try {
            $customer = $booking->customer;
            if ($customer?->fcm_token) {
                app(FirebaseNotificationService::class)->sendNotification(
                    $customer->fcm_token,
                    'Booking Confirmed',
                    'Your booking is confirmed. Please complete payment in the app.',
                    ['booking_id' => $booking->id, 'type' => 'booking_confirmed']
                );
            }
            
            $this->waNotify->sendBookingConfirmed($booking);

        } catch (\Exception $e) {
            Log::error('Booking confirm notify failed: ' . $e->getMessage());
        }
    }

    public function partnerBookingQuery(int|string $partnerId)
    {
        return Booking::with(['customer', 'package.listing', 'invoices', 'payments'])
            ->whereHas('package.listing', fn ($q) => $q->where('partner_id', $partnerId));
    }

    public function partnerSubscriptionQuery(int|string $partnerId)
    {
        return Subscription::with(['customer', 'package.listing', 'invoices', 'payments'])
            ->whereHas('package.listing', fn ($q) => $q->where('partner_id', $partnerId));
    }
}
