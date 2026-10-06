<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Mail\SendOtpMail;
use App\Mail\PartnerRegisteredMail;
use App\Mail\PartnerKycSubmittedMail;
use App\Mail\PartnerKycStatusMail;
use App\Mail\CustomerRegisteredMail;
use App\Mail\CustomerSubscriptionMail;
use App\Mail\CustomerPaymentReminderMail;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Mail;

class TestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test {--fcm-token=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger all transactional emails and test Firebase notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting notification and email test...');

        // Find or create test entities
        $partner = User::where('role', 'partner')->first();
        if (!$partner) {
            $partner = User::create([
                'name' => 'Test Partner',
                'email' => 'partner_test@feetrack.com',
                'mobile' => '9999999991',
                'role' => 'partner',
                'status' => 'pending',
                'password' => bcrypt('password123'),
            ]);
            $partner->assignRole('partner');
        }

        $customer = User::where('role', 'customer')->first();
        if (!$customer) {
            $customer = User::create([
                'name' => 'Test Customer',
                'email' => 'customer_test@feetrack.com',
                'mobile' => '9999999992',
                'role' => 'customer',
                'status' => 'active',
                'password' => bcrypt('password123'),
            ]);
            $customer->assignRole('customer');
        }

        // Find or create package, subscription, invoice
        $package = Package::first();
        if (!$package) {
            $listing = \App\Models\Listing::first();
            if (!$listing) {
                $cat = \App\Models\Category::firstOrCreate(['name' => 'Gym', 'slug' => 'gym']);
                $listing = \App\Models\Listing::create([
                    'partner_id' => $partner->id,
                    'category_id' => $cat->id,
                    'title' => 'Titan Gym',
                    'description' => 'Best Gym',
                    'address' => '123 Test St',
                    'status' => 'approved',
                ]);
            }
            $package = Package::create([
                'listing_id' => $listing->id,
                'name' => 'Monthly Pro Pack',
                'duration_days' => 30,
                'price' => 1500,
                'type' => 'monthly',
                'features' => ['Cardio', 'Weights'],
            ]);
        }

        $subscription = Subscription::first();
        if (!$subscription) {
            $subscription = Subscription::create([
                'customer_id' => $customer->id,
                'package_id' => $package->id,
                'starts_at' => now(),
                'expires_at' => now()->addDays(30),
                'status' => 'active',
                'auto_renew' => true,
            ]);
        }

        $invoice = Invoice::first();
        if (!$invoice) {
            $invoice = Invoice::create([
                'subscription_id' => $subscription->id,
                'invoice_number' => 'INV-TEST1234',
                'amount' => $package->price,
                'tax' => $package->price * 0.18,
                'total' => $package->price * 1.18,
                'due_date' => now()->addDays(3),
                'status' => 'sent',
            ]);
        }

        // --- 1. Send all Mails ---
        $this->info('1. Sending OTP email...');
        Mail::to($customer->email)->queue(new SendOtpMail('123456'));

        $this->info('2. Sending Partner Registration email...');
        Mail::to($partner->email)->queue(new PartnerRegisteredMail($partner));

        $this->info('3. Sending KYC Submitted email...');
        Mail::to($partner->email)->queue(new PartnerKycSubmittedMail($partner));

        $this->info('4. Sending KYC Approved email...');
        Mail::to($partner->email)->queue(new PartnerKycStatusMail($partner, 'approved'));

        $this->info('5. Sending KYC Rejected email...');
        Mail::to($partner->email)->queue(new PartnerKycStatusMail($partner, 'rejected', 'Documents unclear or blurred.'));

        $this->info('6. Sending Customer Welcome email...');
        Mail::to($customer->email)->queue(new CustomerRegisteredMail($customer));

        $this->info('7. Sending Customer Subscription Confirmed email...');
        Mail::to($customer->email)->queue(new CustomerSubscriptionMail($customer, $subscription, $package));

        $this->info('8. Sending Payment Overdue email...');
        Mail::to($customer->email)->queue(new CustomerPaymentReminderMail($customer, $invoice, $package, 'due'));

        $this->info('9. Sending Autopay Reminder email...');
        Mail::to($customer->email)->queue(new CustomerPaymentReminderMail($customer, $invoice, $package, 'next'));

        $this->info('All emails dispatched. Check storage/logs/laravel.log to see the output content.');

        // --- 2. Test Firebase FCM ---
        $fcmToken = $this->option('fcm-token') ?: $customer->fcm_token;
        if ($fcmToken) {
            $this->info("FCM token specified: {$fcmToken}. Sending test push notification...");
            $sent = app(FirebaseNotificationService::class)->queueNotification(
                $fcmToken,
                'Test Notification',
                'This is a real-time notification test from the CLI.',
                ['click_action' => 'FLUTTER_NOTIFICATION_CLICK', 'type' => 'test']
            );
            if ($sent) {
                $this->info('Firebase notification sent successfully.');
            } else {
                $this->error('Failed to send Firebase notification (check logs).');
            }
        } else {
            $this->warn('No FCM token provided. Skipping real push test. Pass one using --fcm-token=YOUR_TOKEN.');
            
            $this->info('Running service credential configuration check...');
            $credentialsPath = storage_path('app/firebase-credentials.json');
            if (file_exists($credentialsPath)) {
                try {
                    $fcmService = app(FirebaseNotificationService::class);
                    $reflector = new \ReflectionClass(FirebaseNotificationService::class);
                    $method = $reflector->getMethod('getAccessToken');
                    $method->setAccessible(true);
                    $token = $method->invoke($fcmService);
                    $this->info('Firebase Authentication check PASSED! OAuth2 access token retrieved successfully.');
                } catch (\Exception $e) {
                    $this->error('Firebase Authentication check FAILED: ' . $e->getMessage());
                }
            } else {
                $this->warn('Firebase credentials file NOT found at storage/app/firebase-credentials.json. (Ignore this if you do not have a Firebase JSON file yet).');
            }
        }
    }
}
