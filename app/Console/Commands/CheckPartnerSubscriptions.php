<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PartnerSubscription;
use App\Models\PartnerPackage;
use App\Services\NotificationService;
use Carbon\Carbon;

class CheckPartnerSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-partners';
    protected $description = 'Check partner subscriptions for expiration and send reminders';

    public function handle()
    {
        $this->info('Checking partner subscriptions...');

        $now = Carbon::now();

        // 1. Process Expiring Subscriptions (Reminders)
        $this->sendReminders(3);
        $this->sendReminders(1);
        $this->sendReminders(0); // Today

        // 2. Process Expired Subscriptions
        $expiredSubscriptions = PartnerSubscription::with('partner')
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now->startOfDay())
            ->get();

        if ($expiredSubscriptions->isEmpty()) {
            $this->info('No expired subscriptions found.');
        } else {
            // Find Free Package
            $freePackage = PartnerPackage::where('price', 0)->where('is_active', true)->latest()->first();

            foreach ($expiredSubscriptions as $sub) {
                // Mark as expired
                $sub->update(['status' => 'expired']);
                $this->info("Subscription {$sub->id} marked as expired.");

                // Fallback to free package
                if ($freePackage && $sub->partner) {
                    PartnerSubscription::create([
                        'partner_id' => $sub->partner_id,
                        'partner_package_id' => $freePackage->id,
                        'starts_at' => now(),
                        'expires_at' => $freePackage->duration_days > 0 ? now()->addDays($freePackage->duration_days) : null,
                        'status' => 'active',
                    ]);
                    $this->info("Assigned free package {$freePackage->id} to partner {$sub->partner_id}.");

                    // Notify partner about fallback
                    if ($sub->partner->fcm_token) {
                        $notificationService = app(\App\Services\NotificationService::class);
                        $notificationService->sendPushNotification(
                            $sub->partner,
                            'Package Expired',
                            'Your subscription has expired. You have been switched to the Free tier.'
                        );
                    }
                }
            }
        }

        $this->info('Finished checking partner subscriptions.');
    }

    private function sendReminders($days)
    {
        $targetDate = Carbon::now()->addDays($days)->format('Y-m-d');
        
        $expiring = PartnerSubscription::with('partner.package')
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', $targetDate)
            ->get();

        foreach ($expiring as $sub) {
            if ($sub->partner && $sub->partner->fcm_token) {
                $daysText = $days === 0 ? 'today' : "in {$days} days";
                $title = "Subscription Expiring";
                $body = "Your package is expiring {$daysText}. Please renew to keep your premium benefits.";
                
                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->sendPushNotification(
                    $sub->partner,
                    $title,
                    $body
                );
            }
        }
    }
}
