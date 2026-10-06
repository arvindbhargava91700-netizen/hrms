<?php

namespace App\Livewire\Partner;

use App\Models\AttendanceLog;
use App\Models\Listing;
use App\Models\Subscription;
use Livewire\Component;
use Livewire\WithPagination;

class Attendance extends Component
{
    use WithPagination;
    use HasPartnerWorkspaceScope;

    // ── Filters ───────────────────────────────────────────────────
    public string $search        = '';
    public string $dateFilter    = '';
    public string $listingFilter = '';

    // ── Mark Attendance (Punch-In / Punch-Out for a customer) ─────
    public bool   $showMarkModal    = false;
    public string $markSearch       = '';  // search customer subscription
    public ?string $markSubscriptionId = null;
    public string  $markAction      = 'punch_in'; // punch_in | punch_out
    public string  $markNote        = '';
    public string  $markDate        = '';
    public ?array  $markPreview     = null; // found subscription preview
    public ?string $markError       = null;

    // ── Edit Attendance record ─────────────────────────────────────
    public bool    $showEditModal  = false;
    public ?int    $editLogId      = null;
    public string  $editPunchIn    = '';   // HH:MM
    public string  $editPunchOut   = '';   // HH:MM
    public string  $editNote       = '';

    protected string $paginationTheme = 'bootstrap';

    protected function scopeAttendancePartner($query)
    {
        if (auth()->user()->isSuperAdmin()) {
            $partnerId = $this->selectedWorkspacePartnerId();
            if (filled($partnerId)) {
                $this->validateSelectedPartner($partnerId);
                $query->where('partner_id', $partnerId);
            }

            return $query;
        }

        return $query->where('partner_id', auth()->id());
    }

    protected $rules = [
        'editPunchIn'  => 'required|date_format:H:i',
        'editPunchOut' => 'nullable|date_format:H:i',
        'editNote'     => 'nullable|string|max:255',
    ];

    public function updatingSearch()        { $this->resetPage(); }
    public function updatingDateFilter()    { $this->resetPage(); }
    public function updatingListingFilter() { $this->resetPage(); }

    // ─────────────────────────────────────────────────────────────
    // Open Mark Modal
    // ─────────────────────────────────────────────────────────────
    public function openMarkModal(): void
    {
        $this->reset(['markSearch', 'markSubscriptionId', 'markAction', 'markNote', 'markPreview', 'markError']);
        $this->markDate   = today()->toDateString();
        $this->markAction = 'punch_in';
        $this->showMarkModal = true;
    }

    // Search subscriptions by customer name / mobile for this partner's listings
    public function searchSubscription(): void
    {
        $this->markPreview = null;
        $this->markError   = null;

        if (strlen(trim($this->markSearch)) < 2) {
            $this->markError = 'Enter at least 2 characters to search.';
            return;
        }

        $subscription = Subscription::with(['customer', 'package.listing.category'])
            ->where('status', 'active')
            ->whereHas('package.listing', fn($q) => $this->scopeAttendancePartner($q))
            ->whereHas('customer', fn($q) =>
                $q->where('name', 'like', "%{$this->markSearch}%")
                  ->orWhere('mobile', 'like', "%{$this->markSearch}%")
            )
            ->latest()
            ->first();

        if (!$subscription) {
            $this->markError = 'No active subscription found matching that name / mobile.';
            return;
        }

        $listing  = $subscription->package->listing;
        $category = $listing->category;



        $this->markSubscriptionId = $subscription->id;

        // Check if a log already exists for this date
        $existing = AttendanceLog::where('subscription_id', $subscription->id)
            ->whereDate('date', $this->markDate)
            ->first();

        $this->markPreview = [
            'customer_name'   => $subscription->customer->name,
            'customer_mobile' => $subscription->customer->mobile,
            'listing'         => $listing->title,
            'plan'            => $subscription->package->name,
            'existing_status' => $existing ? ($existing->isDone() ? 'completed' : 'open') : null,
            'punch_in_at'     => $existing?->punch_in_at?->format('h:i A'),
            'punch_out_at'    => $existing?->punch_out_at?->format('h:i A'),
        ];

        // Auto-select the right action based on existing log
        if (!$existing) {
            $this->markAction = 'punch_in';
        } elseif ($existing->isOpen()) {
            $this->markAction = 'punch_out';
        }
    }

    // Save mark attendance (punch-in or punch-out)
    public function saveMark(): void
    {
        $this->markError = null;

        if (!$this->markSubscriptionId) {
            $this->markError = 'Please search and select a customer first.';
            return;
        }

        $subscription = Subscription::with(['package.listing.category'])
            ->whereHas('package.listing', fn($q) => $this->scopeAttendancePartner($q))
            ->find($this->markSubscriptionId);

        if (!$subscription) {
            $this->markError = 'Subscription not found or access denied.';
            return;
        }

        $listing = $subscription->package->listing;
        $date    = $this->markDate ?: today()->toDateString();

        $log = AttendanceLog::where('subscription_id', $subscription->id)
            ->whereDate('date', $date)
            ->first();

        if ($this->markAction === 'punch_in') {
            if ($log) {
                $this->markError = $log->isOpen()
                    ? 'Customer already punched in today.'
                    : 'Attendance already completed for today.';
                return;
            }

            AttendanceLog::create([
                'subscription_id' => $subscription->id,
                'customer_id'     => $subscription->customer_id,
                'listing_id'      => $listing->id,
                'date'            => $date,
                'punch_in_at'     => now(),
                'note'            => $this->markNote ?: null,
            ]);

            session()->flash('success', "Punched in: {$subscription->customer->name}");
        } else {
            if (!$log || !$log->isOpen()) {
                $this->markError = !$log
                    ? 'No punch-in found for this customer today.'
                    : 'Customer has already punched out.';
                return;
            }

            $punchOutAt = now();
            $log->update([
                'punch_out_at'     => $punchOutAt,
                'duration_minutes' => (int) $log->punch_in_at->diffInMinutes($punchOutAt),
                'note'             => $this->markNote ?: $log->note,
            ]);

            session()->flash('success', "Punched out: {$subscription->customer->name}");
        }

        $this->showMarkModal = false;
    }

    // ─────────────────────────────────────────────────────────────
    // Open Edit Modal
    // ─────────────────────────────────────────────────────────────
    public function openEdit(int $logId): void
    {
        $log = AttendanceLog::whereHas('listing', fn($q) => $this->scopeAttendancePartner($q))
            ->findOrFail($logId);

        $this->editLogId   = $log->id;
        $this->editPunchIn  = $log->punch_in_at?->format('H:i') ?? '';
        $this->editPunchOut = $log->punch_out_at?->format('H:i') ?? '';
        $this->editNote     = $log->note ?? '';
        $this->showEditModal = true;
        $this->resetErrorBag();
    }

    // Save edited attendance log
    public function saveEdit(): void
    {
        $this->validate();

        $log = AttendanceLog::whereHas('listing', fn($q) => $this->scopeAttendancePartner($q))
            ->findOrFail($this->editLogId);

        $dateStr = $log->date->toDateString();

        $punchIn  = $dateStr . ' ' . $this->editPunchIn . ':00';
        $punchOut = $this->editPunchOut ? ($dateStr . ' ' . $this->editPunchOut . ':00') : null;

        $duration = null;
        if ($punchIn && $punchOut) {
            $duration = (int) \Carbon\Carbon::parse($punchIn)->diffInMinutes(\Carbon\Carbon::parse($punchOut));
        }

        $log->update([
            'punch_in_at'      => $punchIn,
            'punch_out_at'     => $punchOut,
            'duration_minutes' => $duration,
            'note'             => $this->editNote ?: null,
        ]);

        $this->showEditModal = false;
        session()->flash('success', 'Attendance record updated.');
    }

    // ─────────────────────────────────────────────────────────────
    // Render
    // ─────────────────────────────────────────────────────────────
    public function render()
    {
        $listingQuery = $this->scopeAttendancePartner(Listing::query());
        $listingIds = $listingQuery->pluck('id');

        $logs = AttendanceLog::with(['customer', 'listing', 'subscription.package'])
            ->whereIn('listing_id', $listingIds)
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
            ->paginate(20);

        $listings = $this->scopeAttendancePartner(Listing::query())->orderBy('title')->get(['id', 'title']);

        // Today stats
        $todayTotal = AttendanceLog::whereIn('listing_id', $listingIds)->whereDate('date', today())->count();
        $todayOpen  = AttendanceLog::whereIn('listing_id', $listingIds)->whereDate('date', today())->whereNull('punch_out_at')->whereNotNull('punch_in_at')->count();
        $todayDone  = $todayTotal - $todayOpen;

        return view('livewire.partner.attendance', compact('logs', 'listings', 'todayTotal', 'todayOpen', 'todayDone'))
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Attendance',
                'pageSubtitle' => 'Mark and manage customer daily attendance',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
