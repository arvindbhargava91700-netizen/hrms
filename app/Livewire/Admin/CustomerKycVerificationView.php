<?php

namespace App\Livewire\Admin;

use App\Models\CustomerKyc;
use Livewire\Component;

class CustomerKycVerificationView extends Component
{
    public string $kycId;
    public CustomerKyc $kyc;

    public function mount(string $id): void
    {
        $this->kycId = $id;
        $this->kyc = CustomerKyc::with('customer')->findOrFail($id);
    }

    public function approve(): void
    {
        $this->kyc->update(['status' => 'approved', 'reviewed_at' => now()]);
        
        $customer = $this->kyc->customer;
        
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
        $this->redirectRoute('admin.customer-kyc');
    }

    public function reject(string $reason): void
    {
        $this->kyc->update([
            'status' => 'rejected', 
            'reviewed_at' => now(),
            'rejection_reason' => $reason
        ]);

        $customer = $this->kyc->customer;

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
        $this->redirectRoute('admin.customer-kyc');
    }

    public function render()
    {
        return view('livewire.admin.customer-kyc-verification-view')
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Customer Profile Details',
                'pageSubtitle' => 'Review customer documents',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
