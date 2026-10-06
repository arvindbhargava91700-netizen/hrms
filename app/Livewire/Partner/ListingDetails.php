<?php

namespace App\Livewire\Partner;

use App\Models\Listing;
use Livewire\Component;
class ListingDetails extends Component
{
    use HasPartnerWorkspaceScope;

    public $listingId;
    
    public function mount($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('listing_viewany') || auth()->user()->canAccess('listing_viewown'), 403);
        $this->listingId = $id;
    }

    public function render()
    {
        $query = $this->scopePartnerRecords(Listing::with([
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
            }
        ]));
        
        if (!auth()->user()->isPartner() && !auth()->user()->canAccess('listing_viewany') && auth()->user()->canAccess('listing_viewown')) {
            $query->where('created_by', auth()->id());
        }
        
        $listing = $query->findOrFail($this->listingId);

        return view('livewire.partner.listing-details', [
            'listing' => $listing,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Listing Details',
            'pageSubtitle' => 'View room/shift availability',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
