<?php

namespace App\Livewire\Admin;

use App\Models\Coupon;
use App\Models\Listing;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class Coupons extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $search = '';
    public string $statusFilter = '';
    public string $typeFilter = '';
    public string $scopeFilter = '';
    public $showModal = false;

    #[Url] public ?string $listing = null;

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

    protected $rules = [
        'code'         => 'required|string|unique:coupons,code',
        'title'        => 'required|string',
        'type'         => 'required|in:flat,percent',
        'value'        => 'required|numeric|min:0',
        'min_amount'   => 'required|numeric|min:0',
        'max_discount' => 'nullable|numeric|min:0',
        'listing_id'   => 'nullable|exists:listings,id',
        'max_uses'     => 'required|integer|min:0',
        'expires_at'   => 'nullable|date',
    ];

    public function mount()
    {
        $this->code = strtoupper(\Illuminate\Support\Str::random(8));
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedStatusFilter() { $this->resetPage(); }
    public function updatedTypeFilter() { $this->resetPage(); }
    public function updatedScopeFilter() { $this->resetPage(); }

    public function create()
    {
        $this->reset(['couponId', 'title', 'description', 'value', 'min_amount', 'max_discount', 'listing_id', 'expires_at']);
        $this->code = strtoupper(\Illuminate\Support\Str::random(8));
        $this->type = 'flat';
        $this->max_uses = 0;
        $this->is_active = true;
        
        // Reset validation errors
        $this->resetValidation();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        
        $coupon = Coupon::findOrFail($id);
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
        $rules = $this->rules;
        if ($this->couponId) {
            $rules['code'] = 'required|string|unique:coupons,code,' . $this->couponId;
        }

        $data = $this->validate($rules);
        $data['is_active'] = $this->is_active;
        $data['listing_id'] = empty($this->listing_id) ? null : $this->listing_id;

        if ($this->couponId) {
            Coupon::find($this->couponId)->update($data);
            session()->flash('success', 'Coupon updated successfully.');
        } else {
            Coupon::create($data);
            session()->flash('success', 'Coupon created successfully.');
        }

        $this->showModal = false;
    }

    public function toggleActive($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);
    }

    public function render()
    {
        $coupons = Coupon::when($this->search, function ($query) {
            $query->where(function($q) {
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
        ->when($this->scopeFilter, function ($query) {
            if ($this->scopeFilter === 'global') {
                $query->whereNull('listing_id');
            } elseif ($this->scopeFilter === 'listing') {
                $query->whereNotNull('listing_id');
            }
        })
        ->when($this->listing, function ($query) {
            $query->where('listing_id', $this->listing);
        })
        ->latest()->paginate(10);

        $listings = Listing::approved()->pluck('title', 'id');

        $stats = [
            'total' => Coupon::count(),
            'active' => Coupon::where('is_active', true)->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->count(),
            'global' => Coupon::whereNull('listing_id')->count(),
            'partner' => Coupon::whereNotNull('listing_id')->count(),
        ];

        return view('livewire.admin.coupons', compact('coupons', 'listings', 'stats'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Manage Coupons',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
