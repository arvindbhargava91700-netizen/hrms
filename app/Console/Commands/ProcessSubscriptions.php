<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProcessSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and process subscription expiries and auto-renewals';

    /**
     * Execute the console command.
     */
    public function handle(SubscriptionService $service)
    {
        $this->info('Processing subscriptions...');

        $service->processDailyRenewals();
        $service->sendPaymentReminders();

        $this->info('Subscriptions processed successfully.');
    }
}
