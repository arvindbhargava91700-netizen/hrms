<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Services\WhatsAppNotificationService;
use Carbon\Carbon;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-payment-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send WhatsApp payment reminders for upcoming or expired subscriptions';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppNotificationService $waNotify)
    {
        $today = Carbon::today();
        
        // Find subscriptions expiring in exactly 3 days
        $expiringIn3Days = Subscription::where('status', 'active')
            ->whereDate('expires_at', $today->copy()->addDays(3))
            ->get();
            
        foreach ($expiringIn3Days as $sub) {
            $waNotify->sendUpcomingPaymentReminder($sub, 3);
        }
        
        // Find subscriptions expiring in exactly 1 day
        $expiringIn1Day = Subscription::where('status', 'active')
            ->whereDate('expires_at', $today->copy()->addDays(1))
            ->get();
            
        foreach ($expiringIn1Day as $sub) {
            $waNotify->sendUpcomingPaymentReminder($sub, 1);
        }
        
        // Find subscriptions expiring today
        $expiringToday = Subscription::where('status', 'active')
            ->whereDate('expires_at', $today)
            ->get();
            
        foreach ($expiringToday as $sub) {
            $waNotify->sendUpcomingPaymentReminder($sub, 0);
            // Optionally, mark as expired here if you don't have another job doing it
            $sub->update(['status' => 'expired']);
        }

        $this->info('Payment reminders sent successfully!');
    }
}
