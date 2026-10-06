<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\VisitBooking;
use Livewire\Attributes\Url;

class Visits extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    #[Url] public ?string $partner = null;
    #[Url] public ?string $customer = null;
    #[Url] public ?string $listing = null;
    #[Url] public string $statusFilter = 'pending';

    public function render()
    {
        $baseQuery = VisitBooking::with('customer','partner','listing')
            ->when($this->partner, fn($q) => $q->where('partner_id', $this->partner))
            ->when($this->customer, fn($q) => $q->where('customer_id', $this->customer))
            ->when($this->listing, fn($q) => $q->where('listing_id', $this->listing));

        $counts = (clone $baseQuery)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        $counts['total'] = array_sum($counts);

        $visits = $baseQuery
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->latest()->paginate(30);

        return view('livewire.admin.visits', compact('visits', 'counts'))
            ->layout('layouts.app', [
                'panelName' => 'Admin Panel',
                'pageTitle' => 'All Visit Requests',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }

    public function updateStatus(string $id, string $status, ?string $note = null)
    {
        if (!in_array($status, ['pending','accepted','rejected','cancelled'])) return;
        $visit = VisitBooking::findOrFail($id);
        
        $data = ['status' => $status];
        if ($note !== null) {
            $data['note'] = $note;
        }
        
        $visit->update($data);
        session()->flash('success', 'Status updated');
    }
}
