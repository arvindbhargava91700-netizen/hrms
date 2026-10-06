<?php

namespace App\Livewire\Admin;

use App\Models\Listing;
use Livewire\Component;
class ListingDetails extends Component
{
    public $listingId;
    
    public function mount($id)
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->canAccess('admin_listing_view'), 403);
        $this->listingId = $id;
    }

    public function render()
    {
        $listing = Listing::with([
            'category', 
            'images', 
            'meta.customField',
            'shifts' => function($q) {
                $q->with(['subscriptions' => function($sq) {
                    $sq->where('subscriptions.status', 'active')->with('customer');
                }]);
            },
            'trainers',
            'floors' => function($q) {
                $q->with(['rooms' => function($rq) {
                    $rq->with(['images', 'subscriptions' => function($sq) {
                        $sq->where('subscriptions.status', 'active')->with('customer');
                    }]);
                }]);
            },
            'partner'
        ])->findOrFail($this->listingId);

        return view('livewire.admin.listing-details', [
            'listing' => $listing,
        ])->layout('layouts.app', [
            'panelName'    => 'Admin Panel',
            'pageTitle'    => 'Listing Details',
            'pageSubtitle' => 'View room/shift availability',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
