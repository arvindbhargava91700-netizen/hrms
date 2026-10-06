<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OtpService;

class TestOtpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:otp {mobile? : The mobile number to send the OTP to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sending an SMS OTP to a given mobile number';

    /**
     * Execute the console command.
     */
    public function handle(OtpService $otpService)
    {
        $mobile = $this->argument('mobile');

        if (!$mobile) {
            $mobile = $this->ask('Please enter a mobile number to test');
        }

        if (!$mobile) {
            $this->error('Mobile number is required!');
            return 1;
        }

        $this->info("Generating OTP for {$mobile}...");
        $otp = $otpService->generateOtp($mobile);

        $this->info("Sending OTP ({$otp}) to {$mobile} via SMS...");
        
        try {
            $otpService->sendSmsOtp($mobile, $otp, 'TestUser');
            $this->info('OTP SMS request sent successfully! Check logs for API response details if any.');
        } catch (\Exception $e) {
            $this->error('Failed to send OTP SMS: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
