<?php

namespace App\Livewire\Admin;

use App\Models\KycDocument;
use App\Models\User;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class Partners extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $categoryFilter = '';
    public ?User $selectedPartner = null;
    public bool $showModal = false;
    public int $partnerSubscriptionsCount = 0;

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
            'editMobile' => 'required|string|max:20',
            'editStatus' => 'required|in:active,inactive,suspended,pending',
            'editPassword' => 'nullable|min:6',
        ];
    }

    protected string $paginationTheme = 'bootstrap';
    
    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingCategoryFilter() { $this->resetPage(); }

    public function getExportUrlProperty(): string
    {
        return route('admin.export', [
            'module' => 'partners',
            'search' => $this->search,
            'status' => $this->statusFilter,
            'category' => $this->categoryFilter,
        ]);
    }

    public function viewDetails(string $userId): void
    {
        $this->selectedPartner = User::with(['kycDocument', 'listings', 'subscriptions.package', 'subscriptions.payments'])
            ->withCount(['listings', 'visitBookings'])
            ->findOrFail($userId);
            
        $this->partnerSubscriptionsCount = \App\Models\Subscription::whereHas('package.listing', fn($q) => $q->where('partner_id', $userId))->count();

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedPartner = null;
    }

    public function approvePartner(string $userId): void
    {
        $partner = User::findOrFail($userId);
        $wasSuspended = $partner->status === 'suspended';
        $partner->update(['status' => 'active']);

        if ($wasSuspended) {
            session()->flash('success', 'Partner activated successfully.');
        } else {
            session()->flash('success', 'Partner approved and activated successfully.');
        }

        // Refresh selected partner if modal is open
        if ($this->showModal && $this->selectedPartner?->id == $userId) {
            $this->selectedPartner = User::with(['kycDocument', 'listings', 'subscriptions.package', 'subscriptions.payments'])
                ->withCount(['listings', 'visitBookings'])
                ->findOrFail($userId);
        }
    }

    public function suspendPartner(string $userId): void
    {
        User::findOrFail($userId)->update(['status' => 'suspended']);
        session()->flash('success', 'Partner has been suspended.');

        // Refresh selected partner if modal is open
        if ($this->showModal && $this->selectedPartner?->id == $userId) {
            $this->selectedPartner = User::with(['kycDocument', 'listings', 'subscriptions.package', 'subscriptions.payments'])
                ->withCount(['listings', 'visitBookings'])
                ->findOrFail($userId);
        }
    }

    public function editPartner(string $id): void
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

    public function updatePartner(): void
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
        session()->flash('success', 'Partner profile updated successfully.');
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

    public function render()
    {
        $partners = User::where('role', 'partner')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('mobile', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->categoryFilter, fn($q) => $q->whereHas('listings', fn($q2) => $q2->where('category_id', $this->categoryFilter)))
            ->with(['kycDocument', 'listings.category'])
            ->withCount('listings')
            ->latest()
            ->paginate(15);

        $categories = Category::orderBy('name')->get();

        $stats = [
            'total' => User::where('role', 'partner')->count(),
            'active' => User::where('role', 'partner')->where('status', 'active')->count(),
            'inactive' => User::where('role', 'partner')->where('status', 'inactive')->count(),
            'suspended' => User::where('role', 'partner')->where('status', 'suspended')->count(),
            'pendingKyc' => User::where('role', 'partner')->where(function ($query) {
                $query->whereDoesntHave('kycDocument')->orWhereHas('kycDocument', function ($subQuery) {
                    $subQuery->where('status', 'pending');
                });
            })->count(),
        ];

        return view('livewire.admin.partners', compact('partners', 'categories', 'stats'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Partners',
                'pageSubtitle' => 'Manage all registered business partners',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
