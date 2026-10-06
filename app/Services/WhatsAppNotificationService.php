<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Subscription;
use App\Models\VisitBooking;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    /**
     * Send message using the underlying WhatsApp service.
     */
    public function send(?string $mobile, string $message): void
    {
        if (empty($mobile)) {
            return;
        }

        try {
            WhatsAppService::sendMessage($mobile, $message);
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp message to {$mobile}: " . $e->getMessage());
        }
    }

    /**
     * Formatting helper for numbers
     */
    protected function formatCurrency($amount): string
    {
        return '₹' . number_format((float)$amount, 2);
    }

    /**
     * Booking Created (Pending OTP)
     */
    public function sendBookingCreated(Booking $booking): void
    {
        $customer = $booking->customer;
        $partner  = $booking->package?->listing?->partner;
        $listingName = $booking->package?->listing?->title ?? 'our facility';
        
        // To Customer
        if ($customer) {
            $msg = "Hi {$customer->name},\n\nYour booking at {$listingName} has been created.\n\nPlease share this OTP with the partner to confirm your booking: *{$booking->otp}*";
            $this->send($customer->mobile, $msg);
        }

        // To Partner
        if ($partner) {
            $customerName = $customer ? $customer->name : 'A customer';
            $msg = "New Booking Alert! 🎉\n\n{$customerName} has created a booking for {$listingName}.\n\nPlease ask the customer for their OTP to confirm the booking in your partner panel.";
            $this->send($partner->mobile, $msg);
        }
    }

    /**
     * Booking Confirmed (Waiting for Payment)
     */
    public function sendBookingConfirmed(Booking $booking): void
    {
        $customer = $booking->customer;
        $partner  = $booking->package?->listing?->partner;
        $listingName = $booking->package?->listing?->title ?? 'our facility';

        // To Customer
        if ($customer) {
            $msg = "Great news, {$customer->name}!\n\nYour booking at {$listingName} is confirmed.\n\nPlease complete your payment in the app to activate your subscription.";
            $this->send($customer->mobile, $msg);
        }

        // To Partner
        if ($partner) {
            $customerName = $customer ? $customer->name : 'A customer';
            $msg = "Booking Confirmed! ✅\n\nYou have verified the OTP for {$customerName}'s booking at {$listingName}.\n\nWaiting for the customer to complete payment.";
            $this->send($partner->mobile, $msg);
        }
    }

    /**
     * Payment Completed / Subscription Active
     */
    public function sendPaymentCompleted(Subscription $subscription): void
    {
        $customer = $subscription->customer;
        $partner  = $subscription->package?->listing?->partner;
        $listingName = $subscription->package?->listing?->title ?? 'our facility';
        
        $planName = $subscription->package?->name ?? 'Plan';
        $expiry = $subscription->expires_at ? $subscription->expires_at->format('d M, Y') : 'N/A';

        // To Customer
        if ($customer) {
            $msg = "Payment Successful! 💳\n\nHi {$customer->name}, your payment for {$listingName} ({$planName}) is complete.\n\nYour subscription is now ACTIVE and valid until {$expiry}. Enjoy your service!";
            $this->send($customer->mobile, $msg);
        }

        // To Partner
        if ($partner) {
            $customerName = $customer ? $customer->name : 'A customer';
            $msg = "Payment Received! 💰\n\n{$customerName} has completed their payment for {$listingName} ({$planName}).\n\nTheir subscription is now active.";
            $this->send($partner->mobile, $msg);
        }
    }

    /**
     * Booking Cancelled
     */
    public function sendBookingCancelled(Booking $booking): void
    {
        $customer = $booking->customer;
        $partner  = $booking->package?->listing?->partner;
        $listingName = $booking->package?->listing?->title ?? 'our facility';

        // To Customer
        if ($customer) {
            $msg = "Hi {$customer->name},\n\nYour booking at {$listingName} has been cancelled.\nIf this was a mistake, please book again.";
            $this->send($customer->mobile, $msg);
        }

        // To Partner
        if ($partner) {
            $customerName = $customer ? $customer->name : 'A customer';
            $msg = "Booking Cancelled ❌\n\n{$customerName}'s booking for {$listingName} has been cancelled.";
            $this->send($partner->mobile, $msg);
        }
    }

    /**
     * Subscription Cancelled
     */
    public function sendSubscriptionCancelled(Subscription $subscription): void
    {
        $customer = $subscription->customer;
        $partner  = $subscription->package?->listing?->partner;
        $listingName = $subscription->package?->listing?->title ?? 'our facility';
        $planName = $subscription->package?->name ?? 'Plan';

        // To Customer
        if ($customer) {
            $msg = "Subscription Cancelled ❌\n\nHi {$customer->name}, your subscription for {$listingName} ({$planName}) has been cancelled.";
            $this->send($customer->mobile, $msg);
        }

        // To Partner
        if ($partner) {
            $customerName = $customer ? $customer->name : 'A customer';
            $msg = "Subscription Cancelled ❌\n\n{$customerName}'s subscription for {$listingName} ({$planName}) has been cancelled.";
            $this->send($partner->mobile, $msg);
        }
    }

    /**
     * New Visit Booking
     */
    public function sendVisitRequested(VisitBooking $visit): void
    {
        $customer = $visit->customer;
        $partner  = $visit->listing?->partner;
        $listingName = $visit->listing?->title ?? 'our facility';
        
        $visitDate = $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('d M, Y') : 'N/A';
        $visitTime = $visit->visit_time ? \Carbon\Carbon::parse($visit->visit_time)->format('h:i A') : 'N/A';

        // To Customer
        if ($customer) {
            $msg = "Visit Requested! 🏢\n\nHi {$customer->name}, your request to visit {$listingName} on {$visitDate} at {$visitTime} has been sent.\n\nThe partner will review and update the status soon.";
            $this->send($customer->mobile, $msg);
        }

        // To Partner
        if ($partner) {
            $customerName = $customer ? $customer->name : 'A customer';
            $msg = "New Visit Request! 🏢\n\n{$customerName} has requested to visit {$listingName} on {$visitDate} at {$visitTime}.\n\nPlease check your partner panel to accept or reject this request.";
            $this->send($partner->mobile, $msg);
        }
    }

    /**
     * Upcoming Payment / Pending Reminder
     */
    public function sendUpcomingPaymentReminder(Subscription $subscription, int $daysRemaining): void
    {
        $customer = $subscription->customer;
        if (!$customer) return;

        $listingName = $subscription->package?->listing?->title ?? 'our facility';
        $planName = $subscription->package?->name ?? 'Plan';
        $expiry = $subscription->expires_at ? $subscription->expires_at->format('d M, Y') : 'N/A';

        if ($daysRemaining > 0) {
            $msg = "Friendly Reminder! ⏳\n\nHi {$customer->name}, your subscription for {$listingName} ({$planName}) will expire in {$daysRemaining} day(s) on {$expiry}.\n\nPlease renew it soon to avoid any interruption in service.";
        } else {
            $msg = "Subscription Expired! ❗\n\nHi {$customer->name}, your subscription for {$listingName} ({$planName}) has expired today.\n\nPlease renew it to continue your service.";
        }

        $this->send($customer->mobile, $msg);
    }

    /**
     * Wallet Recharge Success
     */
    public function sendWalletRechargeSuccess(\App\Models\User $user, $amount): void
    {
        if (!$user) return;

        $formattedAmount = $this->formatCurrency($amount);
        $msg = "Wallet Recharge Successful! 💰\n\nHi {$user->name}, your wallet has been recharged with {$formattedAmount}.\n\nYour new balance is " . $this->formatCurrency($user->wallet_balance) . ".";

        $this->send($user->mobile, $msg);
    }
}