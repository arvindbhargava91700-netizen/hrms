<?php

namespace App\Livewire\Admin;

use App\Models\KycDocument;
use Livewire\Component;
use Livewire\WithPagination;

class KycVerification extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';
    
    public string $search = '';
    public string $statusFilter = 'pending';
    public ?string $viewingId = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function approve(string $id): void
    {
        $kyc = KycDocument::findOrFail($id);
        $kyc->update(['status' => 'approved', 'reviewed_at' => now()]);
        $kyc->partner->update(['status' => 'active']);

        $partner = $kyc->partner;
        try {
            \Illuminate\Support\Facades\Mail::to($partner->email)->queue(new \App\Mail\PartnerKycStatusMail($partner, 'approved'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send KYC approval mail: ' . $e->getMessage());
        }

        if ($partner->fcm_token) {
            app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                $partner->fcm_token,
                'KYC Approved!',
                'Your KYC documents have been successfully verified. You can now access your dashboard and create listings.'
            );
        }

        session()->flash('success', 'KYC approved – partner activated.');
    }

    public function reject(string $id): void
    {
        $kyc = KycDocument::findOrFail($id);
        $kyc->update(['status' => 'rejected', 'reviewed_at' => now()]);

        $partner = $kyc->partner;
        $reason = 'Documents unclear or mismatched. Please re-upload clear copies of Aadhaar and PAN cards.';
        try {
            \Illuminate\Support\Facades\Mail::to($partner->email)->queue(new \App\Mail\PartnerKycStatusMail($partner, 'rejected', $reason));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send KYC rejection mail: ' . $e->getMessage());
        }

        if ($partner->fcm_token) {
            app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                $partner->fcm_token,
                'KYC Update Required',
                'Your KYC documents were rejected. Please check your email or dashboard for more details.'
            );
        }

        session()->flash('success', 'KYC rejected.');
    }

    public function viewDetails(string $id): void
    {
        $this->redirectRoute('admin.kyc.view', ['id' => $id]);
    }

    public function getExportUrlProperty(): string
    {
        return route('admin.export', [
            'module' => 'kyc',
            'search' => $this->search,
            'status' => $this->statusFilter,
        ]);
    }

    public function render()
    {
        $docs = KycDocument::with('partner')
            ->when($this->search, fn($q) => $q->whereHas('partner', fn($partnerQuery) => $partnerQuery
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->latest()->paginate(15);

        $stats = [
            'total' => KycDocument::count(),
            'pending' => KycDocument::where('status', 'pending')->count(),
            'approved' => KycDocument::where('status', 'approved')->count(),
            'rejected' => KycDocument::where('status', 'rejected')->count(),
        ];

        return view('livewire.admin.kyc-verification', compact('docs', 'stats'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'KYC Verification',
                'pageSubtitle' => 'Review and verify partner KYC documents',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
