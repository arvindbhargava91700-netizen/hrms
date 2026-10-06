<?php

namespace App\Livewire\Admin;

use App\Models\Listing;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class Listings extends Component
{
    use WithPagination;

    public string  $search       = '';
    public string  $statusFilter = '';
    public string  $categoryFilter = '';
    public ?string $viewingId    = null;
    #[Url] public ?string $partner = null;

    protected string $paginationTheme = 'bootstrap';
    
    public function updatingSearch()       { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingCategoryFilter() { $this->resetPage(); }

    public function getExportUrlProperty(): string
    {
        return route('admin.export', [
            'module' => 'listings',
            'search' => $this->search,
            'status' => $this->statusFilter,
        ]);
    }

    public function approve(string $id): void
    {
        Listing::findOrFail($id)->update(['status' => 'approved']);
        session()->flash('success', 'Listing approved successfully.');
    }

    public function reject(string $id): void
    {
        Listing::findOrFail($id)->update(['status' => 'rejected']);
        session()->flash('success', 'Listing rejected.');
    }

    public function editListing(string $id): void
    {
        $this->redirect(route('admin.listing.edit', $id));
    }

    public function deleteListing(string $id): void
    {
        $listing = Listing::with([
            'images',
            'trainers',
            'floors.rooms.images',
        ])->findOrFail($id);

        // Admins can delete any listing

        // Delete related files
        foreach ($listing->images as $img) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($img->image_path);
        }
        foreach ($listing->trainers as $trainer) {
            if ($trainer->photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($trainer->photo_path);
            }
        }
        foreach ($listing->floors as $floor) {
            foreach ($floor->rooms as $room) {
                foreach ($room->images as $img) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($img->image_path);
                }
            }
        }
        if ($listing->cover_image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($listing->cover_image);
        }

        $listing->delete();

        session()->flash('success', 'Listing deleted completely.');
    }

    public function viewDetails(string $id): void
    {
        $this->viewingId = $id;
    }

    public function closeView(): void
    {
        $this->viewingId = null;
    }

    public function render()
    {
        $listings = Listing::with(['category', 'partner'])
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%")
                                              ->orWhereHas('partner', fn($q2) => $q2->where('name', 'like', "%{$this->search}%")))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->categoryFilter, fn($q) => $q->where('category_id', $this->categoryFilter))
            ->when($this->partner, fn($q) => $q->where('partner_id', $this->partner))
            ->latest()
            ->paginate(15);

        $viewingListing = $this->viewingId
            ? Listing::withCount(['subscriptions', 'visitBookings', 'coupons'])
                ->with([
                'category', 'partner', 'images',
                'shifts', 'trainers',
                'floors.rooms.images',
                'packages.room',
                'packages.subscriptions.customer',
                'meta.customField',
            ])->find($this->viewingId)
            : null;

        $categories = \App\Models\Category::orderBy('name')->get();

        $stats = [
            'total' => Listing::count(),
            'approved' => Listing::where('status', 'approved')->count(),
            'pending' => Listing::where('status', 'pending')->count(),
            'rejected' => Listing::where('status', 'rejected')->count(),
        ];

        return view('livewire.admin.listings', compact('listings', 'viewingListing', 'categories', 'stats'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Listings',
                'pageSubtitle' => 'Manage all platform listings',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
