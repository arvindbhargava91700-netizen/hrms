<?php

namespace App\Livewire\Partner;

use App\Models\Listing;
use App\Models\Package;
use App\Models\Room;
use Livewire\Component;
use Livewire\WithPagination;

class Packages extends Component
{

    use WithPagination;
    use HasPartnerWorkspaceScope;
    protected string $paginationTheme = 'bootstrap';

    public bool $showModal = false;
    public ?string $editId = null;

    public string $listing_id = '';
    public string $room_type = '';
    public string $occupancy_type = 'standard';
    public string $name = '';
    public int $duration_days = 30;
    public string $price = '';
    public string $type = 'monthly';
    public string $featuresInput = '';

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('package_viewany') || auth()->user()->canAccess('package_viewown'), 403);
    }

    public function openCreate()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('package_create'), 403);
        $this->reset(['editId', 'listing_id', 'room_type', 'occupancy_type', 'name', 'duration_days', 'price', 'type', 'featuresInput']);
        $this->occupancy_type = 'standard';
        $this->showModal = true;
    }

    public function openEdit(string $id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('package_update'), 403);
        
        $packageQuery = Package::with(['listing.category', 'room'])->whereHas('listing', function($q) {
            $this->scopePartnerRecords($q);
            if (!auth()->user()->isPartner() && !auth()->user()->canAccess('package_viewany')) {
                $q->where('created_by', auth()->id());
            }
        });
        
        $package = $packageQuery->findOrFail($id);
        
        $this->editId = $id;
        $this->listing_id = $package->listing_id;
        $this->room_type = $package->room_type ?? '';
        $this->occupancy_type = $package->occupancy_type ?? 'standard';
        $this->name = $package->name;
        $this->duration_days = $package->duration_days;
        $this->price = $package->price;
        $this->type = $package->type;
        $this->featuresInput = $package->features ? implode("\n", $package->features) : '';
        $this->showModal = true;
    }

    public function updatedListingId(): void
    {
        $this->room_type = '';
        $listing = $this->getSelectedListingProperty();

        if (!$this->isRoomBasedListing($listing)) {
            $this->occupancy_type = 'standard';
        }
    }

    public function save()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess($this->editId ? 'package_update' : 'package_create'), 403);
        $rules = [
            'listing_id' => 'required|exists:listings,id',
            'name' => 'required|string|max:100',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'type' => 'required|string',
            'featuresInput' => 'nullable|string',
            'occupancy_type' => 'required|in:standard,single,full_room,per_bed',
        ];

        if ($this->isRoomBasedListing()) {
            $rules['room_type'] = 'required|string';
        } else {
            $rules['room_type'] = 'nullable|string';
        }

        $this->validate($rules);

        // Verify the listing belongs to the partner
        $listingQuery = $this->scopePartnerRecords(Listing::with('category'));
        
        if (!auth()->user()->isPartner() && !auth()->user()->canAccess('package_viewany')) {
            $listingQuery->where('created_by', auth()->id());
        }
        
        $listing = $listingQuery->findOrFail($this->listing_id);

        $features = array_filter(array_map('trim', explode("\n", $this->featuresInput)));

        $data = [
            'listing_id' => $this->listing_id,
            'room_type' => $this->isRoomBasedListing($listing) ? $this->room_type : null,
            'occupancy_type' => $this->isRoomBasedListing($listing) ? $this->occupancy_type : 'standard',
            'name' => $this->name,
            'duration_days' => $this->duration_days,
            'price' => $this->price,
            'type' => $this->type,
            'features' => empty($features) ? null : $features,
        ];

        if ($this->editId) {
            Package::whereHas('listing', fn($q) => $this->scopePartnerRecords($q))
                ->findOrFail($this->editId)
                ->update($data);
            session()->flash('success', 'Package updated.');
        } else {
            Package::create($data);
            session()->flash('success', 'Package created.');
        }

        $this->showModal = false;
    }

    public function render()
    {
        $packageQuery = Package::with(['listing.category', 'room'])
            ->whereHas('listing', function($q) {
                $this->scopePartnerRecords($q);
                if (!auth()->user()->isPartner() && !auth()->user()->canAccess('package_viewany')) {
                    $q->where('created_by', auth()->id());
                }
            });
            
        $packages = $packageQuery->latest()->paginate(15);

        $listingsQuery = $this->scopePartnerRecords(Listing::with(['category', 'floors.rooms']));
            
        if (!auth()->user()->isPartner() && !auth()->user()->canAccess('package_viewany')) {
            $listingsQuery->where('created_by', auth()->id());
        }
            
        $listings = $listingsQuery->get();
        
        $selectedListing = $this->listing_id
            ? $listings->firstWhere('id', $this->listing_id)
            : null;

        // Extract unique room types from the listing's rooms
        $roomTypes = $this->isRoomBasedListing($selectedListing)
            ? $selectedListing->floors->flatMap->rooms->pluck('room_type')->unique()->filter()->values()
            : collect();

        return view('livewire.partner.packages', compact('packages', 'listings', 'selectedListing', 'roomTypes'))
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Packages',
                'pageSubtitle' => 'Manage pricing plans and packages',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }

    public function isRoomBasedListing(?Listing $listing = null): bool
    {
        $listing = $listing ?? ($this->listing_id ? Listing::with('category')->find($this->listing_id) : null);

        return (bool) $listing && in_array(strtolower($listing->category?->name ?? ''), ['pg / hostel', 'room rental'], true);
    }

    public function getSelectedListingProperty(): ?Listing
    {
        if (!$this->listing_id) {
            return null;
        }

        return Listing::with('category')->find($this->listing_id);
    }
}
