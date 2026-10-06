<?php

namespace App\Livewire\Partner;

use App\Models\User;
use App\Models\Subscription;
use Livewire\Component;
use Livewire\WithPagination;

class Customers extends Component
{
    use WithPagination, HasPartnerWorkspaceScope;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public ?string $viewingId = null;
    public ?string $viewingSubscriptionId = null;

    public function mount()
    {
        abort_unless(auth()->user()->canAccess('customer_viewany') || auth()->user()->canAccess('customer_viewown'), 403, 'Unauthorized access.');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function viewCustomer(string $id): void
    {
        abort_unless(auth()->user()->canAccess('customer_viewany') || auth()->user()->canAccess('customer_viewown'), 403, 'Unauthorized access.');
        $this->viewingId = $id;
    }

    public function viewSubscriptionPayments(string $id): void
    {
        abort_unless(auth()->user()->canAccess('customer_viewany') || auth()->user()->canAccess('customer_viewown'), 403, 'Unauthorized access.');
        $this->viewingSubscriptionId = $id;
    }

    public function closeViewingSubscription(): void
    {
        $this->viewingSubscriptionId = null;
    }

    public function render()
    {
        $query = User::query()
            ->where('role', 'customer')
            ->whereHas('subscriptions.package.listing', function ($query) {
                $this->scopePartnerRecords($query);
            });
            
        if (!auth()->user()->isPartner() && !auth()->user()->canAccess('customer_viewany')) {
            $query->where('created_by', auth()->id());
        }
            
        $customers = $query->when($this->search, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('mobile', 'like', "%{$this->search}%");
                });
            })
            ->with(['subscriptions' => function ($query) {
                $query->whereHas('package.listing', function ($listingQuery) {
                    $this->scopePartnerRecords($listingQuery);
                })->with('package.listing');
            }])
            ->latest()
            ->paginate(15);

        $viewingCustomer = null;
        if ($this->viewingId) {
            $customerQuery = User::with(['subscriptions' => function ($query) {
                $query->whereHas('package.listing', function ($listingQuery) {
                    $this->scopePartnerRecords($listingQuery);
                })->with(['package.listing', 'payments']);
            }])->where('role', 'customer')
                ->whereHas('subscriptions.package.listing', function ($listingQuery) {
                    $this->scopePartnerRecords($listingQuery);
                });
            $customerQuery->whereHas('subscriptions.package.listing', function ($listingQuery) {
                $this->scopePartnerRecords($listingQuery);
            });
            
            if (!auth()->user()->isPartner() && !auth()->user()->canAccess('customer_viewany')) {
                $customerQuery->where('created_by', auth()->id());
            }
            
            $viewingCustomer = $customerQuery->find($this->viewingId);
        }

        $viewingSubscription = null;
        if ($this->viewingSubscriptionId) {
            $subQuery = Subscription::with(['payments', 'package'])
                ->whereHas('package.listing', function ($listingQuery) {
                    $this->scopePartnerRecords($listingQuery);
                });
                
            if (!auth()->user()->isPartner() && !auth()->user()->canAccess('customer_viewany')) {
                $subQuery->whereHas('customer', function($q) {
                    $q->where('created_by', auth()->id());
                });
            }
                
            $viewingSubscription = $subQuery->find($this->viewingSubscriptionId);
        }

        return view('livewire.partner.customers', compact('customers', 'viewingCustomer', 'viewingSubscription'))
            ->layout('layouts.app', [
                'panelName'    => 'Workspace',
                'pageTitle'    => 'Customers',
                'pageSubtitle' => 'View your customers and their active subscriptions',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
