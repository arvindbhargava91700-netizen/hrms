<?php

namespace App\Livewire\Admin;

use App\Models\AttendanceLog;
use App\Models\Listing;
use Livewire\Component;
use Livewire\WithPagination;

class Attendance extends Component
{
    use WithPagination;

    public string $search      = '';
    public string $dateFilter  = '';
    public string $listingFilter = '';

    protected string $paginationTheme = 'bootstrap';

    public function updatingSearch()        { $this->resetPage(); }
    public function updatingDateFilter()    { $this->resetPage(); }
    public function updatingListingFilter() { $this->resetPage(); }

    public function render()
    {
        $logs = AttendanceLog::with(['customer', 'listing', 'subscription.package'])
            ->when($this->search, fn($q) =>
                $q->whereHas('customer', fn($q2) =>
                    $q2->where('name', 'like', "%{$this->search}%")
                       ->orWhere('mobile', 'like', "%{$this->search}%")
                )
            )
            ->when($this->dateFilter, fn($q) => $q->whereDate('date', $this->dateFilter))
            ->when($this->listingFilter, fn($q) => $q->where('listing_id', $this->listingFilter))
            ->orderByDesc('date')
            ->orderByDesc('punch_in_at')
            ->paginate(25);

        $listings = Listing::approved()->orderBy('title')->get(['id', 'title']);

        return view('livewire.admin.attendance', compact('logs', 'listings'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Attendance Logs',
                'pageSubtitle' => 'Daily punch-in / punch-out records across all listings',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
