<?php

namespace App\Livewire\Partner;

use App\Models\Category;
use App\Models\Listing;
use App\Services\PackageService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Listings extends Component
{
    use HasPartnerWorkspaceScope;

    public bool    $showCategoryPicker = false;
    public string  $pickedCategoryId   = '';
    public ?string $viewingId          = null;

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('listing_viewany') || auth()->user()->canAccess('listing_viewown'), 403);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('listing_create'), 403);
        
        $partnerId = $this->requirePartnerIdForWrite();
        $listingLimit = PackageService::getListingLimit($partnerId);
        
        if ($listingLimit !== null) {
            $currentCount = Listing::where('partner_id', $partnerId)->count();
            if ($currentCount >= $listingLimit) {
                session()->flash('error', "Your current package allows a maximum of {$listingLimit} listing(s). Please upgrade your plan to add more.");
                return;
            }
        }
        
        $partnerCategoryIds = \Illuminate\Support\Facades\DB::table('partner_categories')
            ->where('user_id', $partnerId)
            ->pluck('category_id');

        if ($partnerCategoryIds->count() === 1) {
            $this->pickedCategoryId = $partnerCategoryIds->first();
            $this->proceedWithCategory();
            return;
        }

        $this->pickedCategoryId = '';
        $this->showCategoryPicker = true;
    }

    public function viewListing(string $id): void
    {
        $this->viewingId = $id;
    }

    public function proceedWithCategory(): void
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('listing_create'), 403);
        $this->validate(['pickedCategoryId' => 'required|exists:categories,id']);
        
        $partnerId = $this->requirePartnerIdForWrite();
        $categoryLimit = PackageService::getCategoryLimit($partnerId);
        
        if ($categoryLimit !== null) {
            $usedCategories = Listing::where('partner_id', $partnerId)
                ->distinct('category_id')
                ->count('category_id');
            $targetCategoryUsed = Listing::where('partner_id', $partnerId)
                ->where('category_id', $this->pickedCategoryId)
                ->exists();
            
            if (!$targetCategoryUsed && $usedCategories >= $categoryLimit) {
                session()->flash('error', "Your current package allows listings in a maximum of {$categoryLimit} category(s). Please upgrade your plan.");
                $this->showCategoryPicker = false;
                return;
            }
        }
        
        $params = ['category' => $this->pickedCategoryId];
        if (auth()->user()->isSuperAdmin()) {
            $params['partner_id'] = $this->requirePartnerIdForWrite();
        }

        $this->redirect(route('partner.listing.create', $params));
    }

    public function editListing(string $id): void
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('listing_update'), 403);
        $this->redirect(route('partner.listing.edit', $id));
    }

    public function deleteListing(string $id): void
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('listing_delete'), 403);
        
        $query = $this->scopePartnerRecords(Listing::with([
            'images',
            'trainers',
            'floors.rooms.images',
        ]));
        
        // Scope to created_by if they only have viewown
        if (!auth()->user()->isPartner() && !auth()->user()->canAccess('listing_viewany')) {
            $query->where('created_by', auth()->id());
        }
        
        $listing = $query->findOrFail($id);

        if ($listing->status !== 'draft') {
            session()->flash('error', 'Only draft listings can be deleted.');
            return;
        }

        foreach ($listing->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        foreach ($listing->trainers as $trainer) {
            if ($trainer->photo) {
                Storage::disk('public')->delete($trainer->photo);
            }
        }

        foreach ($listing->floors as $floor) {
            foreach ($floor->rooms as $room) {
                foreach ($room->images as $image) {
                    Storage::disk('public')->delete($image->image_path);
                }
            }
        }

        $listing->forceDelete();

        if ($this->viewingId === $id) {
            $this->viewingId = null;
        }

        session()->flash('success', 'Listing deleted successfully.');
    }

    public function render()
    {
        $query = $this->scopePartnerRecords(Listing::with('category'));
            
        // Scope to created_by if they only have viewown
        if (!auth()->user()->isPartner() && !auth()->user()->canAccess('listing_viewany')) {
            $query->where('created_by', auth()->id());
        }
            
        $listings = $query->latest()->paginate(15);

        $partnerCategoryQuery = \Illuminate\Support\Facades\DB::table('partner_categories');
        if (!auth()->user()->isSuperAdmin()) {
            $partnerCategoryQuery->where('user_id', $this->getPartnerId());
        } elseif ($partnerId = $this->selectedWorkspacePartnerId()) {
            $partnerCategoryQuery->where('user_id', $partnerId);
        }
        $partnerCategoryIds = $partnerCategoryQuery->pluck('category_id');
            
        if ($partnerCategoryIds->isNotEmpty()) {
            $categories = Category::active()->whereIn('id', $partnerCategoryIds)->get();
        } else {
            $categories = Category::active()->get();
        }

        $viewingQuery = $this->scopePartnerRecords(Listing::with([
                'category',
                'images',
                'shifts',
                'trainers',
                'floors.rooms.images',
                'packages.room',
                'packages.subscriptions.customer',
                'meta.customField',
            ]));
            
        // Scope to created_by if they only have viewown
        if (!auth()->user()->isPartner() && !auth()->user()->canAccess('listing_viewany')) {
            $viewingQuery->where('created_by', auth()->id());
        }
            
        $viewingListing = $this->viewingId ? $viewingQuery->find($this->viewingId) : null;

        return view('livewire.partner.listings', compact('listings', 'categories', 'viewingListing'))
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'My Listings',
                'pageSubtitle' => 'Manage your services/properties',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
