<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ListingReview;

class Reviews extends Component
{
    use WithPagination;

    public $search = '';
    public $viewingReviewId = null;
    public $editingReviewId = null;
    public $editRating = 5;
    public $editComment = '';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function viewReview($id)
    {
        $this->viewingReviewId = $id;
    }

    public function closeView()
    {
        $this->viewingReviewId = null;
    }

    public function deleteReview($id)
    {
        $review = ListingReview::findOrFail($id);
        $review->delete();
        session()->flash('success', 'Review deleted successfully.');
    }

    public function editReview($id)
    {
        $review = ListingReview::findOrFail($id);
        $this->editingReviewId = $review->id;
        $this->editRating = $review->rating;
        $this->editComment = $review->comment;
        $this->dispatch('show-edit-review-modal');
    }

    public function updateReview()
    {
        $this->validate([
            'editRating' => 'required|integer|min:1|max:5',
            'editComment' => 'nullable|string',
        ]);

        $review = ListingReview::findOrFail($this->editingReviewId);
        $review->update([
            'rating' => $this->editRating,
            'comment' => $this->editComment,
        ]);

        $this->dispatch('hide-edit-review-modal');
        $this->reset(['editingReviewId', 'editRating', 'editComment']);
        session()->flash('success', 'Review updated successfully.');
    }

    public function render()
    {
        $reviews = ListingReview::with(['customer', 'listing'])
            ->when($this->search, function ($query) {
                $query->whereHas('customer', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })->orWhereHas('listing', function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        $viewingReview = $this->viewingReviewId 
            ? ListingReview::with(['customer', 'listing'])->find($this->viewingReviewId) 
            : null;

        return view('livewire.admin.reviews', [
            'reviews' => $reviews,
            'viewingReview' => $viewingReview
        ])->layoutData([
            'title' => 'Reviews Management',
            'pageTitle' => 'Reviews',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
