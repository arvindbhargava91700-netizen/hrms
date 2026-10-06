<?php

namespace App\Livewire\Admin;

use App\Models\KycDocument;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class KycVerificationView extends Component
{
    public KycDocument $doc;

    public function mount(string $id): void
    {
        $this->doc = KycDocument::with('partner')->findOrFail($id);
    }

    public function approve(): void
    {
        $this->doc->update(['status' => 'approved', 'reviewed_at' => now()]);
        $this->doc->partner->update(['status' => 'active']);

        $partner = $this->doc->partner;
        try {
            Mail::to($partner->email)->queue(new \App\Mail\PartnerKycStatusMail($partner, 'approved'));
        } catch (\Exception $e) {
            Log::error('Failed to send KYC approval mail: ' . $e->getMessage());
        }

        if ($partner->fcm_token) {
            app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                $partner->fcm_token,
                'KYC Approved!',
                'Your KYC documents have been successfully verified. You can now access your dashboard and create listings.'
            );
        }

        session()->flash('success', 'KYC approved – partner activated.');

        $this->redirectRoute('admin.kyc');
    }

    public function reject(): void
    {
        $this->doc->update(['status' => 'rejected', 'reviewed_at' => now()]);

        $partner = $this->doc->partner;
        $reason = 'Documents unclear or mismatched. Please re-upload clear copies of Aadhaar and PAN cards.';
        try {
            Mail::to($partner->email)->queue(new \App\Mail\PartnerKycStatusMail($partner, 'rejected', $reason));
        } catch (\Exception $e) {
            Log::error('Failed to send KYC rejection mail: ' . $e->getMessage());
        }

        if ($partner->fcm_token) {
            app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                $partner->fcm_token,
                'KYC Update Required',
                'Your KYC documents were rejected. Please check your email or dashboard for more details.'
            );
        }

        session()->flash('success', 'KYC rejected.');

        $this->redirectRoute('admin.kyc');
    }

    public function render()
    {
        return view('livewire.admin.kyc-verification-view')
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'KYC Details',
                'pageSubtitle' => 'Review full KYC submission details',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
