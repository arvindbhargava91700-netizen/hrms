<?php

namespace App\Livewire\Partner;

use App\Models\Coupon;
use App\Models\Listing;
use Livewire\Component;
use Livewire\WithPagination;

class Coupons extends Component
{
    use WithPagination;
    use HasPartnerWorkspaceScope;
    protected string $paginationTheme = 'bootstrap';

    public $search = '';
    public string $statusFilter = '';
    public string $typeFilter = '';

    public $showModal = false;

    public $couponId;
    public $code;
    public $title;
    public $description;
    public $type = 'flat';
    public $value;
    public $min_amount = 0;
    public $max_discount;
    public $listing_id = '';
    public $max_uses = 0;
    public $expires_at;
    public $is_active = true;

    public function updatedSearch() { $this->resetPage(); }
    public function updatedStatusFilter() { $this->resetPage(); }
    public function updatedTypeFilter() { $this->resetPage(); }

    protected function getPartnerListingIds(): array
    {
        $query = Listing::query();
        if (auth()->user()->isSuperAdmin()) {
            $partnerId = $this->selectedWorkspacePartnerId();
            if (filled($partnerId)) {
                $this->validateSelectedPartner($partnerId);
                $query->where('partner_id', $partnerId);
            }
        } else {
            $query->where('partner_id', auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id);
        }

        return $query->pluck('id')->toArray();
    }

    public function create()
    {
        $this->reset(['couponId', 'title', 'description', 'value', 'min_amount', 'max_discount', 'listing_id', 'expires_at']);
        $this->code = strtoupper(\Illuminate\Support\Str::random(8));
        $this->type = 'flat';
        $this->max_uses = 0;
        $this->is_active = true;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();

        $listingIds = $this->getPartnerListingIds();
        $coupon = Coupon::whereIn('listing_id', $listingIds)->findOrFail($id);

        $this->couponId     = $coupon->id;
        $this->code         = $coupon->code;
        $this->title        = $coupon->title;
        $this->description  = $coupon->description;
        $this->type         = $coupon->type;
        $this->value        = $coupon->value;
        $this->min_amount   = $coupon->min_amount;
        $this->max_discount = $coupon->max_discount;
        $this->listing_id   = $coupon->listing_id;
        $this->max_uses     = $coupon->max_uses;
        $this->expires_at   = $coupon->expires_at ? $coupon->expires_at->format('Y-m-d') : null;
        $this->is_active    = $coupon->is_active;

        $this->showModal = true;
    }

    public function save()
    {
        $listingIds = $this->getPartnerListingIds();

        $rules = [
            'code'         => 'required|string|unique:coupons,code' . ($this->couponId ? ',' . $this->couponId : ''),
            'title'        => 'required|string|max:255',
            'type'         => 'required|in:flat,percent',
            'value'        => 'required|numeric|min:0',
            'min_amount'   => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'listing_id'   => 'required|in:' . implode(',', $listingIds),
            'max_uses'     => 'required|integer|min:0',
            'expires_at'   => 'nullable|date',
        ];

        $data = $this->validate($rules);
        $data['is_active'] = $this->is_active;

        if ($this->couponId) {
            Coupon::whereIn('listing_id', $listingIds)->findOrFail($this->couponId)->update($data);
            session()->flash('success', 'Coupon updated successfully.');
        } else {
            Coupon::create($data);
            session()->flash('success', 'Coupon created successfully.');
        }

        $this->showModal = false;
    }

    public function toggleActive($id)
    {
        $listingIds = $this->getPartnerListingIds();
        $coupon = Coupon::whereIn('listing_id', $listingIds)->findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);
    }

    public function render()
    {
        $listingIds = $this->getPartnerListingIds();
        $coupons = Coupon::whereIn('listing_id', $listingIds)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('code', 'like', '%' . $this->search . '%')
                      ->orWhere('title', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter === 'active');
            })
            ->when($this->typeFilter, function ($query) {
                $query->where('type', $this->typeFilter);
            })
            ->latest()
            ->paginate(10);

        $myListings = Listing::whereIn('id', $listingIds)
            ->approved()
            ->pluck('title', 'id');

        $stats = [
            'total'    => Coupon::whereIn('listing_id', $listingIds)->count(),
            'active'   => Coupon::whereIn('listing_id', $listingIds)->where('is_active', true)->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->count(),
            'expired'  => Coupon::whereIn('listing_id', $listingIds)->where('expires_at', '<', now())->count(),
            'used'     => Coupon::whereIn('listing_id', $listingIds)->where('used_count', '>', 0)->sum('used_count'),
        ];

        return view('livewire.partner.coupons', compact('coupons', 'myListings', 'stats'))
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'My Coupons',
                'pageSubtitle' => 'Create and manage discount coupons for your listings',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
