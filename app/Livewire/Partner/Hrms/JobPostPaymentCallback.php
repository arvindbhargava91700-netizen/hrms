<?php

namespace App\Livewire\Partner\Hrms;

use App\Models\JobPost;
use App\Models\TransactionHistory;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\TpiPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class JobPostPaymentCallback extends Component
{
    public $status = 'verifying'; // verifying, success, failed
    public $message = 'Verifying your payment...';
    public $jobPostId;

    public function mount(Request $request)
    {
        $this->jobPostId = $request->input('job_post');
        $txId = $request->input('tx_id');
        $easepayid = $request->input('easepayid') ?? $request->input('paymentId');

        if (!$this->jobPostId && !$txId) {
            $this->status = 'failed';
            $this->message = 'Transaction ID is missing.';
            return;
        }

        $jobData = null;
        if ($txId) {
            $jobData = Cache::get('temp_job_post_' . $txId);
            
            if ($jobData && isset($jobData['paymentId']) && !$easepayid) {
                $easepayid = $jobData['paymentId'];
            }
            
            if (!$jobData && $easepayid) {
                // fallback if tx_id was lost but we have paymentId
                $txIdFallback = Cache::get('temp_job_post_pid_'.$easepayid);
                if ($txIdFallback) {
                    $jobData = Cache::get('temp_job_post_' . $txIdFallback);
                    $txId = $txIdFallback;
                }
            }
        }

        $job = null;
        if ($this->jobPostId) {
            $job = JobPost::find($this->jobPostId);
        } elseif ($jobData && isset($jobData['job_id'])) {
            $job = JobPost::find($jobData['job_id']);
        }
        
        if (!$job && !$jobData) {
            $this->status = 'failed';
            $this->message = 'Job post not found or session expired. If amount was deducted, please contact support.';
            return;
        }

        if ($job && $job->status === 'active') {
            $this->status = 'success';
            $this->message = 'Payment was already verified and the job post is active.';
            return;
        }

        if (!$easepayid && $job) {
            $easepayid = $job->transaction_id;
        }

        if (!$easepayid) {
            $this->status = 'failed';
            $this->message = 'Invalid payment callback received. No transaction ID found.';
            return;
        }

        try {
            $tpiService = app(TpiPaymentService::class);
            $check = $tpiService->checkPaymentStatus($easepayid);

            $isSuccess = false;
            if (isset($check['status']) && strtolower($check['status']) === 'success') {
                $isSuccess = true;
            } elseif (isset($check['status']) && $check['status'] === true && !isset($check['data'])) {
                $isSuccess = true;
            } elseif (isset($check['data']['status']) && strtolower($check['data']['status']) === 'success') {
                $isSuccess = true;
            }

            if ($isSuccess) {
                // Activate or create the job post
                if ($jobData && !$job) {
                    $jobData['status'] = 'active';
                    $jobData['transaction_id'] = $easepayid;
                    $job = JobPost::create($jobData);
                    Cache::forget('temp_job_post_' . $txId);
                    Cache::forget('temp_job_post_pid_' . $easepayid);
                } else if ($job) {
                    $planDays = 30; // default
                    if (isset($jobData['plan_id'])) {
                        $plan = \App\Models\JobPlan::find($jobData['plan_id']);
                        if ($plan) $planDays = $plan->validity_days;
                    }
                    
                    $job->update([
                        'status' => 'active',
                        'job_plan_id' => $jobData['plan_id'] ?? $job->job_plan_id,
                        'wallet_deducted' => $jobData['wallet_deducted'] ?? $job->wallet_deducted,
                        'online_payable' => $jobData['online_payable'] ?? $job->online_payable,
                        'gst_amount' => $jobData['gst_amount'] ?? 0,
                        'transaction_id' => $easepayid,
                        'published_at' => $job->published_at ?? now(),
                        'expires_at' => $job->expires_at ?? now()->addDays($planDays),
                    ]);
                    Cache::forget('temp_job_post_' . $txId);
                    Cache::forget('temp_job_post_pid_' . $easepayid);
                }

                // Log Partner Wallet Debit if wallet was used
                if ($job->wallet_deducted > 0) {
                    $partnerUser = User::find($job->partner_id);
                    if ($partnerUser) {
                        $partnerUser->decrement('wallet_balance', $job->wallet_deducted);
                    }

                    WalletTransaction::create([
                        'user_id' => $job->partner_id,
                        'type' => 'debit',
                        'amount' => $job->wallet_deducted,
                        'description' => 'Job Post Referral Budget (Partial Wallet Deduction) for Job: ' . $job->job_title,
                        'reference_type' => 'job_post',
                        'reference_id' => $job->id,
                    ]);
                }

                // Credit the Admin Wallet (Full Budget)
                $admin = User::where('role', 'super_admin')->first();
                if ($admin && $job->referral_budget > 0) {
                    $admin->increment('wallet_balance', $job->referral_budget);

                    WalletTransaction::create([
                        'user_id' => $admin->id,
                        'type' => 'credit',
                        'amount' => $job->referral_budget,
                        'description' => 'Received Job Post Referral Budget from Partner ID: ' . $job->partner_id,
                        'reference_type' => 'job_post',
                        'reference_id' => $job->id,
                    ]);
                }

                // Log Transaction History for the Partner
                TransactionHistory::create([
                    'transaction_id' => $easepayid,
                    'user_id' => $job->partner_id,
                    'type' => 'job_post',
                    'reference_id' => $job->id,
                    'total_amount' => $jobData['total_cost'] ?? $job->referral_budget,
                    'wallet_deducted' => $job->wallet_deducted,
                    'online_payable' => $job->online_payable,
                    'gst_amount' => $jobData['gst_amount'] ?? 0,
                    'payment_gateway_id' => $easepayid,
                    'platform_fee' => 0,
                    'net_amount' => $jobData['total_cost'] ?? $job->referral_budget,
                    'status' => 'completed',
                    'description' => 'Online payment for Job Publish (Inc GST)',
                ]);

                $this->status = 'success';
                $this->message = 'Payment successful! The job post is now active.';
            } else {
                if ($job) {
                    $job->update(['status' => 'closed']);
                }
                $this->status = 'failed';
                $this->message = 'Your payment could not be verified or was cancelled.';
            }
        } catch (\Exception $e) {
            Log::error('TPI Status Check Error for Job Post ' . $easepayid . ': ' . $e->getMessage());
            $this->status = 'failed';
            $this->message = 'An error occurred while verifying the payment. Please contact support if the amount was deducted.';
        }
    }

    public function render()
    {
        return view('livewire.partner.hrms.job-post-payment-callback')
            ->layout('layouts.app', [
                'panelName' => 'HRMS Module',
                'pageTitle' => 'Payment Status',
                'pageSubtitle' => 'Verifying your job post payment',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
