<?php

namespace App\Livewire\Admin;

use App\Models\PartnerSubscription;
use App\Models\WalletTransaction;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PartnerSubscriptions extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $statusFilter = 'active';
    public $search = '';
    public $expiresFilter = '';

    // Properties for Manual Subscription Add
    public $showAddModal = false;
    public $newPartnerId = '';
    public $newPackageId = '';
    public $newAmount = '';
    public $newPaymentMethod = 'manual';
    public $newReference = '';
    public $partnerSearch = '';
    public $partnerSearchResults = [];
    
    public $viewSubscriptionId = null;
    public $viewSubscriptionData = null;

    protected $rules = [
        'newPartnerId' => 'required|exists:users,id',
        'newPackageId' => 'required|exists:partner_packages,id',
        'newAmount' => 'required|numeric|min:0',
        'newPaymentMethod' => 'required|in:manual,wallet',
        'newReference' => 'nullable|string|max:255',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingExpiresFilter()
    {
        $this->resetPage();
    }

    public function approve($id)
    {
        $subscription = PartnerSubscription::with('package')->findOrFail($id);
        
        if ($subscription->status !== 'pending') {
            session()->flash('error', 'Only pending subscriptions can be approved.');
            return;
        }

        DB::transaction(function () use ($subscription) {
            // Cancel current active subscription if exists
            PartnerSubscription::where('partner_id', $subscription->partner_id)
                ->where('status', 'active')
                ->where('id', '!=', $subscription->id)
                ->update(['status' => 'cancelled']);

            // Update this subscription
            $subscription->update([
                'status' => 'active',
                'starts_at' => now()->toDateString(),
                'expires_at' => $subscription->package->duration_days == 0 ? null : now()->addDays($subscription->package->duration_days - 1)->toDateString(),
            ]);
        });

        session()->flash('success', 'Subscription approved successfully.');
    }

    public function reject($id)
    {
        $subscription = PartnerSubscription::with('package')->findOrFail($id);
        
        if ($subscription->status !== 'pending') {
            session()->flash('error', 'Only pending subscriptions can be rejected.');
            return;
        }

        DB::transaction(function () use ($subscription) {
            $subscription->update(['status' => 'rejected']);
            
            // Refund wallet if price was deducted
            if ($subscription->package && $subscription->package->price > 0) {
                $partner = User::find($subscription->partner_id);
                if ($partner) {
                    $partner->increment('wallet_balance', $subscription->package->price);
                    WalletTransaction::create([
                        'user_id' => $partner->id,
                        'amount' => $subscription->package->price,
                        'type' => 'credit',
                        'description' => 'Refund for rejected Platform Package: ' . $subscription->package->name,
                        'reference_type' => 'platform_subscription_refund',
                    ]);
                }
            }
        });

        session()->flash('success', 'Subscription rejected and amount refunded.');
    }

    public function updatedPartnerSearch()
    {
        if (strlen($this->partnerSearch) >= 2) {
            $this->partnerSearchResults = User::whereIn('role', ['partner'])
                ->where(function($q) {
                    $q->where('name', 'like', '%'.$this->partnerSearch.'%')
                      ->orWhere('email', 'like', '%'.$this->partnerSearch.'%')
                      ->orWhere('mobile', 'like', '%'.$this->partnerSearch.'%');
                })->take(5)->get();
        } else {
            $this->partnerSearchResults = [];
        }
    }

    public function selectPartner($id)
    {
        $this->newPartnerId = $id;
        $this->partnerSearch = '';
        $this->partnerSearchResults = [];
    }

    public function selectPackage($id, $price)
    {
        $this->newPackageId = $id;
        $this->newAmount = $price;
    }

    public function getSelectedPartnerProperty()
    {
        if (!$this->newPartnerId) return null;
        $partner = User::find($this->newPartnerId);
        if ($partner) {
            $partner->current_subscription = PartnerSubscription::with('package')
                ->where('partner_id', $this->newPartnerId)
                ->where('status', 'active')
                ->first();
        }
        return $partner;
    }

    public function openAddModal()
    {
        $this->reset(['newPartnerId', 'newPackageId', 'newAmount', 'newReference', 'partnerSearch', 'partnerSearchResults']);
        $this->newPaymentMethod = 'manual';
        $this->showAddModal = true;
    }

    public function viewDetails($id)
    {
        $this->viewSubscriptionData = PartnerSubscription::with(['package', 'partner'])->find($id);
        $this->viewSubscriptionId = $id;
    }

    public function closeViewDetails()
    {
        $this->reset(['viewSubscriptionId', 'viewSubscriptionData']);
    }

    public function addSubscription(
        \App\Services\FirebaseNotificationService $fcmService, 
        \App\Services\WhatsAppNotificationService $waService, 
        \App\Services\NotificationService $notificationService
    ) {
        $this->validate();

        $partner = User::findOrFail($this->newPartnerId);
        $package = \App\Models\PartnerPackage::findOrFail($this->newPackageId);

        DB::transaction(function () use ($partner, $package) {
            // Deduct wallet if selected
            if ($this->newPaymentMethod === 'wallet') {
                if ($partner->wallet_balance < $this->newAmount) {
                    session()->flash('error', "Insufficient wallet balance (₹{$partner->wallet_balance}) for this transaction.");
                    return;
                }
                
                $partner->decrement('wallet_balance', $this->newAmount);
                WalletTransaction::create([
                    'user_id' => $partner->id,
                    'amount' => -$this->newAmount,
                    'type' => 'debit',
                    'description' => 'Manual Admin Subscription Assignment - ' . $package->name . ' (' . $this->newReference . ')',
                    'reference_type' => 'admin_manual_subscription',
                ]);
            } else {
                // If it's a manual cash override, you might still want a record
                WalletTransaction::create([
                    'user_id' => $partner->id,
                    'amount' => 0, // No wallet impact
                    'type' => 'credit',
                    'description' => 'Manual Subscription Created by Admin - ' . $package->name . ' (' . $this->newReference . '). Paid via ' . $this->newPaymentMethod,
                    'reference_type' => 'admin_manual_subscription',
                ]);
            }

            // Cancel current active subscription if exists
            PartnerSubscription::where('partner_id', $partner->id)
                ->where('status', 'active')
                ->update(['status' => 'cancelled']);

            // Create this subscription
            $durationDays = $package->duration_days;
            $expiresAt = $durationDays == 0 ? null : now()->addDays($durationDays - 1)->toDateString();

            PartnerSubscription::create([
                'partner_id' => $partner->id,
                'partner_package_id' => $package->id,
                'status' => 'active',
                'starts_at' => now()->toDateString(),
                'expires_at' => $expiresAt,
            ]);
        });

        // Notifications
        try {
            $msgTitle = "Subscription Activated!";
            $msgBody = "Hi {$partner->name}, your subscription for '{$package->name}' has been manually activated by the Admin.";
            
            // Push
            if ($partner->fcm_token) {
                $fcmService->sendNotification($partner->fcm_token, $msgTitle, $msgBody);
            }
            // WhatsApp
            if ($partner->mobile) {
                // Using reflection or a generic send if no specific template exists, 
                // or just call a method if you have it.
                // Assuming we can use a custom logic or a generic message if we don't have a template.
                $waMsg = "Subscription Activated \u{1F389}\n\n" . $msgBody;
                $waService->send($partner->mobile, $waMsg);
            }
            // Email
            if ($partner->email) {
                $notificationService->sendEmail($partner->email, $msgTitle, $msgBody);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send admin manual sub notifications: " . $e->getMessage());
        }

        $this->showAddModal = false;
        session()->flash('success', 'Subscription created successfully and notifications sent.');
    }

    public function export()
    {
        $query = PartnerSubscription::with(['partner', 'package'])
            ->when($this->search, function($q) {
                $q->whereHas('partner', function($pq) {
                    $pq->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter, function($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->expiresFilter, function($q) {
                if ($this->expiresFilter === 'today') {
                    $q->where('expires_at', '>=', now())
                      ->whereDate('expires_at', '<=', now()->endOfDay());
                } elseif ($this->expiresFilter === '3days') {
                    $q->where('expires_at', '>=', now())
                      ->whereDate('expires_at', '<=', now()->addDays(3)->endOfDay());
                } elseif ($this->expiresFilter === 'week') {
                    $q->where('expires_at', '>=', now())
                      ->whereDate('expires_at', '<=', now()->addDays(7)->endOfDay());
                } elseif ($this->expiresFilter === 'expired') {
                    $q->where('expires_at', '<', now());
                }
            })
            ->latest();

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Partner', 'Partner Email', 'Package', 'Amount', 'Status', 'Requested On', 'Starts At', 'Expires At']);

            $query->chunk(200, function ($subscriptions) use ($output) {
                foreach ($subscriptions as $sub) {
                    fputcsv($output, [
                        $sub->partner->name ?? 'N/A',
                        $sub->partner->email ?? '',
                        $sub->package->name ?? 'N/A',
                        $sub->package->price ?? 0,
                        $sub->status,
                        $sub->created_at?->format('Y-m-d H:i:s'),
                        $sub->starts_at ? \Carbon\Carbon::parse($sub->starts_at)->format('Y-m-d') : 'N/A',
                        $sub->expires_at ? \Carbon\Carbon::parse($sub->expires_at)->format('Y-m-d') : 'N/A',
                    ]);
                }
            });
            fclose($output);
        }, 'partner-subscriptions-' . now()->format('Y-m-d_His') . '.csv');
    }

    public function render()
    {
        $query = PartnerSubscription::with(['partner', 'package'])
            ->when($this->search, function($q) {
                $q->whereHas('partner', function($pq) {
                    $pq->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter, function($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->expiresFilter, function($q) {
                if ($this->expiresFilter === 'today') {
                    $q->where('expires_at', '>=', now())
                      ->whereDate('expires_at', '<=', now()->endOfDay());
                } elseif ($this->expiresFilter === '3days') {
                    $q->where('expires_at', '>=', now())
                      ->whereDate('expires_at', '<=', now()->addDays(3)->endOfDay());
                } elseif ($this->expiresFilter === 'week') {
                    $q->where('expires_at', '>=', now())
                      ->whereDate('expires_at', '<=', now()->addDays(7)->endOfDay());
                } elseif ($this->expiresFilter === 'expired') {
                    $q->where('expires_at', '<', now());
                }
            })
            ->latest();

        $subscriptions = $query->paginate(15);

        $stats = [
            'total'     => \App\Models\PartnerSubscription::count(),
            'pending'   => \App\Models\PartnerSubscription::where('status', 'pending')->count(),
            'active'    => \App\Models\PartnerSubscription::where('status', 'active')->where(function($q) {
                               $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                           })->count(),
            'expired'   => \App\Models\PartnerSubscription::where('status', 'expired')->orWhere(function($q) {
                               $q->where('status', 'active')->where('expires_at', '<', now());
                           })->count(),
            'cancelled' => \App\Models\PartnerSubscription::where('status', 'cancelled')->count(),
            'rejected'  => \App\Models\PartnerSubscription::where('status', 'rejected')->count(),
        ];

        // For the modal, we don't need all partners anymore. We just need packages.
        $allPackages = \App\Models\PartnerPackage::where('is_active', true)->orderBy('price')->get();

        return view('livewire.admin.partner-subscriptions', compact('subscriptions', 'stats', 'allPackages'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Partner Subscriptions',
                'pageSubtitle' => 'Manage partner package requests',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
