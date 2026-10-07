<?php

namespace App\Livewire\Partner\Hrms\JobPostings;

use App\Models\JobPost;
use App\Models\JobApplication;
use App\Models\WalletTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class JobCandidatesComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $jobPost;
    public $statusFilter = 'Action Pending'; // Action Pending, Shortlisted, Rejected, All
    public $search = '';
    public $filterHasResume = false;
    public $filterMatched = false;
    public $filterContacted = false;
    public $filterAppliedIn = null;
    public $isViewModalOpen = false;
    public $isCandidateProfileModalOpen = false;
    public $selectedCandidateId = null;

    public function viewJobPostDetails()
    {
        $this->jobPost->load(['department', 'branch', 'creator', 'partner']);
        $this->isViewModalOpen = true;
    }

    public function viewProfile($applicationId)
    {
        $this->selectedCandidateId = $applicationId;
        
        $candidate = JobApplication::find($applicationId);
        if ($candidate && $candidate->status === 'applied') {
            $candidate->update(['status' => 'viewed']);
        }
        
        $this->isCandidateProfileModalOpen = true;
    }

    #[\Livewire\Attributes\Computed]
    public function selectedCandidate()
    {
        if (!$this->selectedCandidateId) return null;
        return JobApplication::with('applicant')->find($this->selectedCandidateId);
    }

    public function closeModals()
    {
        $this->isViewModalOpen = false;
        $this->isCandidateProfileModalOpen = false;
        $this->selectedCandidateId = null;
    }

    public function mount(JobPost $jobPost)
    {
        // Ensure user can access this job post
        abort_unless(
            auth()->user()->role === 'super_admin' || 
            auth()->user()->role === 'admin' ||
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('jobpost_viewAny') ||
            (auth()->user()->canAccess('jobpost_viewBranch') && $jobPost->branch_id === auth()->user()->branch_id) ||
            (auth()->user()->canAccess('jobpost_viewTeam') && $jobPost->department_id === auth()->user()->department_id) ||
            (auth()->user()->canAccess('jobpost_viewOwn') && $jobPost->created_by === auth()->id()),
            403
        );

        $this->jobPost = $jobPost;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingFilterHasResume()
    {
        $this->resetPage();
    }

    public function updatingFilterMatched()
    {
        $this->resetPage();
    }

    public function updatingFilterContacted()
    {
        $this->resetPage();
    }

    public function setFilterAppliedIn($value)
    {
        if ($this->filterAppliedIn === $value) {
            $this->filterAppliedIn = null;
        } else {
            $this->filterAppliedIn = $value;
        }
        $this->resetPage();
    }

    public function setStatusFilter($status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function shortlist($applicationId)
    {
        $application = JobApplication::findOrFail($applicationId);
        
        // Prevent duplicate processing
        if ($application->status === 'shortlisted') {
            return;
        }

        DB::transaction(function () use ($application) {
            $application->update(['status' => 'shortlisted']);

            // Process referral commission if applicable
            if ($application->referral_id && $this->jobPost->referral_amount > 0) {
                $sharedReferral = \App\Models\SharedReferral::find($application->referral_id);
                if ($sharedReferral && $sharedReferral->user_id) {
                    $referrer = User::find($sharedReferral->user_id);
                    if ($referrer) {
                        // Check if already credited for this application
                        $alreadyCredited = \App\Models\WalletTransaction::where('reference_type', 'job_application')
                            ->where('reference_id', $application->id)
                            ->exists();

                        if (!$alreadyCredited) {
                            // Check limit
                            $successfulCount = \App\Models\JobApplication::where('post_id', $this->jobPost->id)
                                ->whereIn('status', ['shortlisted', 'completed'])
                                ->count();
                                
                            if ($successfulCount <= $this->jobPost->vacancies_count) {
                                $commissionAmount = $this->jobPost->referral_amount;

                                // Add commission to referrer's wallet
                                $referrer->increment('wallet_balance', $commissionAmount);

                                // Create transaction record for the referrer
                                \App\Models\WalletTransaction::create([
                                    'user_id' => $referrer->id,
                                    'type' => 'credit',
                                    'amount' => $commissionAmount,
                                    'description' => "Commission earned for shortlisting candidate {$application->id} on Job {$this->jobPost->job_code}",
                                    'reference_type' => 'job_application',
                                    'reference_id' => $application->id,
                                    'balance_after' => $referrer->wallet_balance,
                                    'status' => 'completed',
                                ]);
                                
                                \App\Models\TransactionHistory::create([
                                    'transaction_id' => \Illuminate\Support\Str::uuid()->toString(),
                                    'user_id' => $referrer->id,
                                    'type' => 'credit',
                                    'reference_id' => $application->id,
                                    'total_amount' => $commissionAmount,
                                    'platform_fee' => 0,
                                    'net_amount' => $commissionAmount,
                                    'status' => 'completed',
                                    'description' => "Referral reward for {$this->jobPost->job_title}",
                                ]);
                            }
                        }
                    }
                }
            }

            // Close job post if vacancies met
            if ($this->jobPost && $this->jobPost->vacancies_count > 0) {
                $successfulCount = \App\Models\JobApplication::where('post_id', $this->jobPost->id)
                    ->whereIn('status', ['shortlisted', 'completed'])
                    ->count();

                if ($successfulCount >= $this->jobPost->vacancies_count && $this->jobPost->status !== 'closed') {
                    $this->jobPost->update(['status' => 'closed']);
                }
            }
        });

        session()->flash('success', 'Candidate shortlisted successfully.');
    }

    public function reject($applicationId)
    {
        $application = JobApplication::findOrFail($applicationId);
        $application->update(['status' => 'rejected']);
        session()->flash('success', 'Candidate rejected.');
    }

    public function render()
    {
        $query = $this->getBaseQuery();

        if ($this->statusFilter === 'Action Pending') {
            $query->where(function($q) {
                $q->whereIn('status', ['applied', 'viewed'])
                  ->orWhereNull('status');
            });
        } elseif ($this->statusFilter !== 'All') {
            $query->where('status', strtolower($this->statusFilter));
        }

        if ($this->search) {
            $query->whereHas('applicant', function($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('mobile', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterHasResume) {
            $query->where(function($q) {
                $q->whereNotNull('resume')
                  ->orWhereHas('applicant.candidateProfile', function($sq) {
                      $sq->whereNotNull('resume_path');
                  });
            });
        }

        // Basic match logic: we can expand this as needed
        if ($this->filterMatched) {
            // e.g., candidate has same education level or preferred location
            if ($this->jobPost->minimum_education) {
                 $query->where('education_level', $this->jobPost->minimum_education);
            }
        }
        
        if ($this->filterAppliedIn) {
            $days = (int) str_replace('last ', '', str_replace(' days', '', $this->filterAppliedIn));
            if ($days > 0) {
                $query->where('created_at', '>=', now()->subDays($days));
            }
        }

        $candidates = $query->latest()->paginate(20);

        // Calculate counts for filters based on base job post
        $baseQuery = JobApplication::where('post_id', $this->jobPost->id);
        
        $resumeCount = (clone $baseQuery)->where(function($q) {
            $q->whereNotNull('resume')
              ->orWhereHas('applicant.candidateProfile', function($sq) {
                  $sq->whereNotNull('resume_path');
              });
        })->count();

        $matchedCount = 0;
        if ($this->jobPost->minimum_education) {
            $matchedCount = (clone $baseQuery)->where('education_level', $this->jobPost->minimum_education)->count();
        }

        // Calculate counts
        $counts = [
            'all' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where(function($q) {
                $q->whereIn('status', ['applied', 'viewed'])->orWhereNull('status');
            })->count(),
            'viewed' => (clone $baseQuery)->where('status', 'viewed')->count(),
            'shortlisted' => (clone $baseQuery)->where('status', 'shortlisted')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
            'matched' => $matchedCount,
            'resume' => $resumeCount,
            'contacted' => 0, // Placeholder
        ];

        return view('livewire.partner.hrms.job-postings.job-candidates-component', [
            'candidates' => $candidates,
            'counts' => $counts
        ])->layout('layouts.app', [
            'panelName' => 'HRMS Module',
            'pageTitle' => 'Candidates - ' . $this->jobPost->job_title,
            'pageSubtitle' => 'Manage applications for this job',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }

    private function getBaseQuery()
    {
        $query = JobApplication::where('post_id', $this->jobPost->id);

        if ($this->statusFilter === 'Action Pending') {
            $query->where(function($q) {
                $q->whereIn('status', ['applied', 'viewed'])
                  ->orWhereNull('status');
            });
        } elseif ($this->statusFilter !== 'All') {
            $query->where('status', strtolower($this->statusFilter));
        }

        if ($this->search) {
            $query->whereHas('applicant', function($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('mobile', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterHasResume) {
            $query->where(function($q) {
                $q->whereNotNull('resume')
                  ->orWhereHas('applicant.candidateProfile', function($sq) {
                      $sq->whereNotNull('resume_path');
                  });
            });
        }

        if ($this->filterMatched) {
            if ($this->jobPost->minimum_education) {
                 $query->where('education_level', $this->jobPost->minimum_education);
            }
        }
        
        if ($this->filterAppliedIn) {
            $days = (int) str_replace('last ', '', str_replace(' days', '', $this->filterAppliedIn));
            if ($days > 0) {
                $query->where('created_at', '>=', now()->subDays($days));
            }
        }

        return $query;
    }

    public function exportExcel()
    {
        $candidates = $this->getBaseQuery()->latest()->get();

        $csvData = "Name,Phone,Email,Status,Education,Experience,Gender,Location,Applied At\n";

        foreach ($candidates as $c) {
            $name = $c->applicant->name ?? 'N/A';
            $phone = $c->applicant->mobile ?? 'N/A';
            $email = $c->applicant->email ?? 'N/A';
            $status = ucfirst($c->status ?? 'Applied');
            $education = $c->education_level ?? 'N/A';
            $experience = $c->experience_years ? $c->experience_years . ' yrs' : 'N/A';
            $gender = $c->gender ?? 'N/A';
            $location = str_replace(',', ' ', $c->address ?? 'N/A');
            $applied = $c->created_at ? $c->created_at->format('Y-m-d H:i') : 'N/A';
            
            $csvData .= "\"{$name}\",\"{$phone}\",\"{$email}\",\"{$status}\",\"{$education}\",\"{$experience}\",\"{$gender}\",\"{$location}\",\"{$applied}\"\n";
        }

        return response()->streamDownload(function () use ($csvData) {
            echo $csvData;
        }, 'candidates_job_' . $this->jobPost->job_code . '.csv');
    }
}
