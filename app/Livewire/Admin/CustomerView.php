<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;

class CustomerView extends Component
{
    public User $customer;

    public function mount(string $id): void
    {
        $this->customer = User::with([
            'subscriptions.package.listing',
            'subscriptions.payments',
            'subscriptions.invoices.payments',
            'payments.subscription.package.listing',
            'payments.invoice',
        ])
        ->withCount(['subscriptions', 'payments', 'customerVisits'])
        ->where('role', 'customer')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.admin.customer-view')
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Customer Details',
                'pageSubtitle' => 'Full customer profile, subscriptions, billing, and payments',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
