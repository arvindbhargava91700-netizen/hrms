<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Listing;
use App\Services\FirebaseNotificationService;

class CustomNotifications extends Component
{
    use WithFileUploads;

    public $targetAudience = 'all'; // all, customers, partners
    public $title = '';
    public $message = '';
    public $attachmentType = 'none'; // none, listing, custom
    
    public $listingId = null;
    public $customImage = null;

    public function sendNotification(FirebaseNotificationService $firebaseService)
    {

            $this->validate([
                'targetAudience' => 'required|in:all,customers,partners',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'attachmentType' => 'required|in:none,listing,custom',
                'listingId' => 'nullable|required_if:attachmentType,listing',
                'customImage' => 'nullable|required_if:attachmentType,custom|image|max:2048',
            ]);

            $imageUrl = null;

            if ($this->attachmentType === 'listing' && $this->listingId) {
                $listing = Listing::with('images')->find($this->listingId);
                if ($listing && $listing->images->isNotEmpty()) {
                    // Ensure it's an absolute URL
                    $primaryImage = $listing->images->where('is_primary', true)->first();
                    $imagePath = $primaryImage ? $primaryImage->image_path : $listing->images->first()->image_path;
                    $imageUrl = asset('storage/' . $imagePath);
                }
            } elseif ($this->attachmentType === 'custom' && $this->customImage) {
                $path = $this->customImage->store('notifications', 'public');
                $imageUrl = asset('storage/' . $path);
            }

            $query = User::whereNotNull('fcm_token');

            if ($this->targetAudience === 'customers') {
                $query->where('role', 'customer');
            } elseif ($this->targetAudience === 'partners') {
                $query->where('role', 'partner');
            }

            $users = $query->get();
            $successCount = 0;

            foreach ($users as $user) {
                $sent = $firebaseService->sendNotification(
                    $user->fcm_token,
                    $this->title,
                    $this->message,
                    ['type' => 'custom_admin'],
                    $imageUrl
                );

                if ($sent) {
                    $successCount++;
                }
            }

            // $this->reset(['title', 'message', 'attachmentType', 'listingId', 'customImage']);
            session()->flash('success', "Notification sent successfully to {$successCount} users.");

    }

    public function render()
    {
        $listings = Listing::select('id', 'title')->get();
        
        return view('livewire.admin.custom-notifications', compact('listings'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Send Notifications',
                'pageSubtitle' => 'Broadcast push notifications to users',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
