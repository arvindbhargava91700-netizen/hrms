<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Customers extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $statusFilter = '';
    public string $emailVerifiedFilter = '';
    public string $mobileVerifiedFilter = '';
    public string $subscriptionFilter = '';
    public string $sortBy = 'latest';

    // Edit Modal Properties
    public bool $showEditModal = false;
    public ?User $editingUser = null;
    public string $editName = '';
    public string $editEmail = '';
    public string $editMobile = '';
    public string $editStatus = '';
    public string $editPassword = '';

    // Add to Wallet Properties
    public bool $showWalletModal = false;
    public ?User $walletUser = null;
    public string $walletAmount = '';
    public string $walletDetails = '';

    protected function rules(): array
    {
        return [
            'editName' => 'required|string|max:255',
            'editEmail' => 'required|email|unique:users,email,' . ($this->editingUser->id ?? ''),
            'editMobile' => 'nullable|string|max:20|unique:users,mobile,' . ($this->editingUser->id ?? ''),
            'editStatus' => 'required|in:active,inactive,suspended,pending',
            'editPassword' => 'nullable|min:6',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingEmailVerifiedFilter(): void
    {
        $this->resetPage();
    }

    public function updatingMobileVerifiedFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSubscriptionFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSortBy(): void
    {
        $this->resetPage();
    }

    public function viewDetails(string $id): void
    {
        $this->redirectRoute('admin.customers.view', ['id' => $id]);
    }

    public function editCustomer(string $id): void
    {
        $this->editingUser = User::findOrFail($id);
        $this->editName = $this->editingUser->name;
        $this->editEmail = $this->editingUser->email;
        $this->editMobile = $this->editingUser->mobile ?? '';
        $this->editStatus = $this->editingUser->status;
        $this->editPassword = ''; // leave blank unless changing

        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingUser = null;
        $this->reset(['editName', 'editEmail', 'editMobile', 'editStatus', 'editPassword']);
        $this->resetValidation();
    }

    public function updateCustomer(): void
    {
        $this->validate();

        $data = [
            'name' => $this->editName,
            'email' => $this->editEmail,
            'mobile' => $this->editMobile,
            'status' => $this->editStatus,
        ];

        if (!empty($this->editPassword)) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($this->editPassword);
        }

        $this->editingUser->update($data);

        $this->closeEditModal();
        session()->flash('success', 'Customer profile updated successfully.');
    }

    public function openWalletModal(string $id): void
    {
        $this->walletUser = User::findOrFail($id);
        $this->walletAmount = '';
        $this->walletDetails = '';
        $this->showWalletModal = true;
    }

    public function closeWalletModal(): void
    {
        $this->showWalletModal = false;
        $this->walletUser = null;
        $this->reset(['walletAmount', 'walletDetails']);
        $this->resetValidation();
    }

    public function addWalletBalance(): void
    {
        $this->validate([
            'walletAmount' => 'required|numeric|min:1',
            'walletDetails' => 'nullable|string|max:255',
        ]);

        $this->walletUser->increment('wallet_balance', $this->walletAmount);

        \App\Models\WalletTransaction::create([
            'user_id' => $this->walletUser->id,
            'amount' => $this->walletAmount,
            'type' => 'credit',
            'description' => $this->walletDetails ?: 'Added by Admin',
            'reference_type' => 'admin_add',
            'reference_id' => auth()->id(),
        ]);

        $this->closeWalletModal();
        session()->flash('success', 'Wallet balance added successfully.');
    }

    public function suspendCustomer(string $userId): void
    {
        User::findOrFail($userId)->update(['status' => 'suspended']);
        session()->flash('success', 'Customer has been suspended.');
    }

    public function approveCustomer(string $userId): void
    {
        $customer = User::findOrFail($userId);
        $wasSuspended = $customer->status === 'suspended';
        $customer->update(['status' => 'active']);

        if ($wasSuspended) {
            session()->flash('success', 'Customer activated successfully.');
        } else {
            session()->flash('success', 'Customer approved and activated successfully.');
        }
    }

    public function getExportUrlProperty(): string
    {
        return route('admin.export', [
            'module' => 'customers',
            'search' => $this->search,
            'status' => $this->statusFilter,
            'email_verified' => $this->emailVerifiedFilter,
            'mobile_verified' => $this->mobileVerifiedFilter,
            'subscription' => $this->subscriptionFilter,
            'sort' => $this->sortBy,
        ]);
    }

    public function render()
    {
        $customers = User::query()
            ->where('role', 'customer')
            ->when($this->search, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('mobile', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->emailVerifiedFilter === 'verified', fn ($query) => $query->whereNotNull('email_verified_at'))
            ->when($this->emailVerifiedFilter === 'unverified', fn ($query) => $query->whereNull('email_verified_at'))
            ->when($this->mobileVerifiedFilter === 'verified', fn ($query) => $query->whereNotNull('mobile_verified_at'))
            ->when($this->mobileVerifiedFilter === 'unverified', fn ($query) => $query->whereNull('mobile_verified_at'))
            ->when($this->subscriptionFilter, function ($query) {
                if ($this->subscriptionFilter === 'has_active') {
                    $query->whereHas('subscriptions', fn ($subQuery) => $subQuery->where('status', 'active'));
                } elseif ($this->subscriptionFilter === 'no_active') {
                    $query->whereDoesntHave('subscriptions', fn ($subQuery) => $subQuery->where('status', 'active'));
                }
            })
            ->withCount([
                'subscriptions as subscriptions_count',
                'subscriptions as active_subscriptions_count' => fn ($query) => $query->where('status', 'active'),
                'payments as payments_count',
            ])
            ->with([
                'subscriptions' => fn ($query) => $query->with(['package.listing', 'payments', 'invoices'])->latest(),
                'payments' => fn ($query) => $query->with(['subscription.package.listing', 'invoice'])->latest(),
                'customerKyc'
            ]);

        $customers = match ($this->sortBy) {
            'oldest' => $customers->orderBy('created_at', 'asc'),
            'name' => $customers->orderBy('name', 'asc'),
            'email' => $customers->orderBy('email', 'asc'),
            default => $customers->latest(),
        };

        $customers = $customers->paginate(15);

        $stats = [
            'total' => User::where('role', 'customer')->count(),
            'active' => User::where('role', 'customer')->where('status', 'active')->count(),
            'suspended_inactive' => User::where('role', 'customer')->whereIn('status', ['inactive', 'suspended'])->count(),
            'pending_kyc' => User::where('role', 'customer')->where(function ($query) {
                $query->whereDoesntHave('customerKyc')->orWhereHas('customerKyc', function ($subQuery) {
                    $subQuery->where('status', 'pending');
                });
            })->count(),
        ];

        return view('livewire.admin.customers', compact('customers', 'stats'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Customers',
                'pageSubtitle' => 'Manage all customer accounts and billing data',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
