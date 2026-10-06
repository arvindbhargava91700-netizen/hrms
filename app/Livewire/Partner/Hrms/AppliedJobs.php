<?php

namespace App\Livewire\Partner\Hrms;

use App\Models\JobApplication;
use App\Models\WalletTransaction;
use App\Models\TransactionHistory;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class AppliedJobs extends Component
{
    use HasPartnerId, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = '';

    public $isStatusModalOpen = false;
    public $selectedApplicationId = null;
    public $newStatus = '';
    public $remark = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('appliedjobpost_viewAny') ||
            auth()->user()->canAccess('appliedjobpost_viewOwn') ||
            auth()->user()->canAccess('appliedjobpost_viewTeam') ||
            auth()->user()->canAccess('appliedjobpost_viewBranch'),
            403
        );
    }

    public function openStatusModal($id)
    {
        $application = JobApplication::findOrFail($id);
        $this->selectedApplicationId = $application->id;
        $this->newStatus = $application->status;
        $this->remark = $application->remark;
        $this->isStatusModalOpen = true;
    }

    public function closeStatusModal()
    {
        $this->isStatusModalOpen = false;
        $this->reset(['selectedApplicationId', 'newStatus', 'remark']);
    }

    public function updateStatus()
    {
        $user = auth()->user();
        abort_unless($user->isPartner() || $user->canAccess('appliedjobpost_update_status'), 403);

        $this->validate([
            'newStatus' => 'required|in:' . implode(',', JobApplication::statuses()),
            'remark' => 'nullable|string',
        ]);

        $application = JobApplication::with('jobPost')->findOrFail($this->selectedApplicationId);

        if (! auth()->user()->isSuperAdmin() && $application->jobPost->partner_id != $this->getPartnerId()) {
            abort(403);
        }

        $previousStatus = $application->status;
        $application->update([
            'status' => $this->newStatus,
            'remark' => $this->remark,
        ]);

        $this->creditReferralReward($application, $previousStatus);

        if (in_array($this->newStatus, [JobApplication::STATUS_HIRED, JobApplication::STATUS_COMPLETED])) {
            $jobPost = $application->jobPost;
            if ($jobPost && $jobPost->vacancies_count > 0) {
                $successfulCount = JobApplication::where('post_id', $jobPost->id)
                    ->whereIn('status', [JobApplication::STATUS_HIRED, JobApplication::STATUS_COMPLETED])
                    ->count();

                if ($successfulCount >= $jobPost->vacancies_count && $jobPost->status !== 'closed') {
                    $jobPost->update(['status' => 'closed']);
                }
            }
        }

        session()->flash('success', 'Application status updated successfully.');
        $this->closeStatusModal();
    }

    private function creditReferralReward(JobApplication $application, string $previousStatus): void
    {
        \Log::info("creditReferralReward started for Application ID: {$application->id}");

        if (! in_array($application->status, [JobApplication::STATUS_HIRED, JobApplication::STATUS_COMPLETED])) {
            \Log::info("Returned: Status is not hired or completed. Status: {$application->status}");
            return;
        }

        if ($previousStatus === $application->status) {
            \Log::info("Returned: Status hasn't changed. Previous: {$previousStatus}");
            return;
        }

        $jobPost = $application->jobPost;

        if (! $jobPost) {
            \Log::info("Returned: Job post not found for application.");
            return;
        }

        $amount = (float) $jobPost->referral_amount;

        if ($amount <= 0) {
            \Log::info("Returned: Referral amount is zero or negative. Amount: {$amount}");
            return;
        }

        $successfulCount = JobApplication::where('post_id', $jobPost->id)
            ->whereIn('status', [JobApplication::STATUS_HIRED, JobApplication::STATUS_COMPLETED])
            ->count();

        if ($successfulCount > $jobPost->vacancies_count) {
            \Log::info("Returned: Successful count ({$successfulCount}) exceeds vacancies ({$jobPost->vacancies_count})");
            return;
        }

        if (empty($application->referral_code)) {
            \Log::info("Returned: Application does not have a referral code.");
            return;
        }

        $sharedReferral = \App\Models\SharedReferral::where('post_id', $application->post_id)
            ->where('referral_code', $application->referral_code)
            ->first();

        if (! $sharedReferral || ! $sharedReferral->user_id) {
            \Log::info("Returned: Shared referral not found for code {$application->referral_code}");
            return;
        }

        $referrer = \App\Models\User::find($sharedReferral->user_id);

        if (! $referrer) {
            \Log::info("Returned: Referrer user not found (ID: {$sharedReferral->user_id})");
            return;
        }

        $alreadyCredited = WalletTransaction::where('reference_type', 'job_application')
            ->where('reference_id', $application->id)
            ->exists();

        if ($alreadyCredited) {
            \Log::info("Returned: Transaction already exists for this application.");
            return;
        }

        $referrer->increment('wallet_balance', $amount);

        WalletTransaction::create([
            'user_id' => $referrer->id,
            'amount' => $amount,
            'type' => 'credit',
            'description' => 'Referral reward for '.$jobPost->job_title,
            'reference_type' => 'job_application',
            'reference_id' => $application->id,
        ]);

        TransactionHistory::create([
            'transaction_id' => Str::uuid()->toString(),
            'user_id' => $referrer->id,
            'type' => 'credit',
            'reference_id' => $application->id,
            'total_amount' => $amount,
            'platform_fee' => 0,
            'net_amount' => $amount,
            'status' => 'completed',
            'description' => 'Referral reward for '.$jobPost->job_title,
        ]);

        \Log::info("Success: Wallet transaction created for User ID: {$referrer->id}, Amount: {$amount}");
    }

    public function render()
    {
        $query = JobApplication::query()->with(['jobPost', 'applicant', 'referral']);
        
        $query->whereHas('jobPost', function ($q) {
            $q->where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } });
        });

        $user = auth()->user();
        if (!$user->isPartner() && !$user->canAccess('appliedjobpost_viewAny')) {
            if ($user->canAccess('appliedjobpost_viewBranch')) {
                $query->whereHas('jobPost', function ($q) use ($user) {
                    $q->where('branch_id', $user->branch_id);
                });
            } elseif ($user->canAccess('appliedjobpost_viewTeam')) {
                $query->whereHas('jobPost', function ($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                });
            } elseif ($user->canAccess('appliedjobpost_viewOwn')) {
                $query->whereHas('jobPost', function ($q) use ($user) {
                    $q->where('created_by', $user->id);
                });
            }
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('designation', 'like', "%{$search}%")
                  ->orWhereHas('applicant', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('jobPost', function ($sub) use ($search) {
                      $sub->where('job_title', 'like', "%{$search}%")
                          ->orWhere('job_code', 'like', "%{$search}%");
                  });
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $applications = $query->latest()->paginate(10);

        return view('livewire.partner.hrms.applied-jobs', [
            'applications' => $applications,
        ])->layout('layouts.app', [
            'panelName' => 'HRMS Module',
            'pageTitle' => 'Applied Job List',
            'pageSubtitle' => 'View and manage job applications',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
