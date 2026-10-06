<?php

namespace App\Livewire\Admin;

use App\Models\CustomerKyc;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerKycVerification extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';
    
    public string $search = '';
    public string $statusFilter = 'pending';
    public ?string $viewingId = null;

    public function updatingSearch(): void { $this->resetPage(); }
    
    public function approve(string $id): void
    {
        $kyc = CustomerKyc::findOrFail($id);
        $kyc->update(['status' => 'approved', 'reviewed_at' => now()]);
        
        $customer = $kyc->customer;
        
        $title = 'Profile Approved!';
        $message = 'Your complete profile has been verified. You can now access all services.';

        \App\Models\AppNotification::create([
            'user_id' => $customer->id,
            'title' => $title,
            'message' => $message,
        ]);

        if ($customer->fcm_token) {
            try {
                app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                    $customer->fcm_token,
                    $title,
                    $message,
                    [],
                    null,
                    false
                );
            } catch (\Exception $e) {
                // ignore
            }
        }

        session()->flash('success', 'Customer KYC approved.');
    }

    public function reject(string $id, string $reason): void
    {
        $kyc = CustomerKyc::findOrFail($id);
        $kyc->update([
            'status' => 'rejected', 
            'reviewed_at' => now(),
            'rejection_reason' => $reason
        ]);

        $customer = $kyc->customer;

        $title = 'Profile Update Required';
        $message = "Your KYC documents were rejected. Reason: {$reason}. Please check and re-submit clear copies.";

        \App\Models\AppNotification::create([
            'user_id' => $customer->id,
            'title' => $title,
            'message' => $message,
        ]);

        if ($customer->fcm_token) {
            try {
                app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                    $customer->fcm_token,
                    $title,
                    $message,
                    [],
                    null,
                    false
                );
            } catch (\Exception $e) {
                // ignore
            }
        }

        session()->flash('success', 'Customer KYC rejected.');
    }

    public function viewDetails(string $id): void
    {
        $this->redirectRoute('admin.customer-kyc.view', ['id' => $id]);
    }

    public function getExportUrlProperty(): string
    {
        return route('admin.export', [
            'module' => 'customer-kyc',
            'search' => $this->search,
            'status' => $this->statusFilter,
        ]);
    }

    public function render()
    {
        $docs = CustomerKyc::with('customer')
            ->when($this->search, fn($q) => $q->whereHas('customer', fn($customerQuery) => $customerQuery
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('mobile', 'like', "%{$this->search}%")))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->latest()->paginate(15);

        $stats = [
            'total' => CustomerKyc::count(),
            'pending' => CustomerKyc::where('status', 'pending')->count(),
            'approved' => CustomerKyc::where('status', 'approved')->count(),
            'rejected' => CustomerKyc::where('status', 'rejected')->count(),
        ];

        return view('livewire.admin.customer-kyc-verification', compact('docs', 'stats'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Customer KYC',
                'pageSubtitle' => 'Review and verify customer profiles',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
