<?php

namespace App\Livewire\Partner;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ListingReview;

class Reviews extends Component
{
    use WithPagination;
    use HasPartnerWorkspaceScope;

    public $search = '';
    public $viewingReviewId = null;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('review_viewany') || auth()->user()->canAccess('review_viewown'), 403);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function viewReview($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('review_viewany') || auth()->user()->canAccess('review_viewown'), 403);
        $this->viewingReviewId = $id;
    }

    public function closeView()
    {
        $this->viewingReviewId = null;
    }

    public function render()
    {
        $query = ListingReview::with(['customer', 'listing'])
            ->whereHas('listing', function ($query) {
                $this->scopePartnerRecords($query);
                if (!auth()->user()->isPartner() && !auth()->user()->canAccess('review_viewany')) {
                    $query->where('created_by', auth()->id());
                }
            });

        $reviews = $query->when($this->search, function ($query) {
                $query->whereHas('customer', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })->orWhereHas('listing', function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        $viewingReview = null;
        if ($this->viewingReviewId) {
            $viewQuery = ListingReview::with(['customer', 'listing'])
                ->whereHas('listing', function ($query) {
                    $this->scopePartnerRecords($query);
                    if (!auth()->user()->isPartner() && !auth()->user()->canAccess('review_viewany')) {
                        $query->where('created_by', auth()->id());
                    }
                });
            $viewingReview = $viewQuery->find($this->viewingReviewId);
        }

        return view('livewire.partner.reviews', [
            'reviews' => $reviews,
            'viewingReview' => $viewingReview
        ])->layoutData([
            'title' => 'My Listing Reviews',
            'pageTitle' => 'Listing Reviews',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
