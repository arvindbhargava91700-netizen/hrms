<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TpiMerchant;

class PaymentGateway extends Component
{
    use WithPagination;
    
    public $selected_partner_id = '';
    public $new_tpi_merchant_id = '';
    public $new_kyc_id = '';
    public $new_kyc_status = 'PENDING';

    public $edit_merchant_id = null;
    public $edit_tpi_merchant_id = '';
    public $edit_kyc_id = '';
    public $edit_kyc_status = '';
    public $edit_status = '';

    
    protected $paginationTheme = 'bootstrap';

    public function saveMerchant()
    {
        $this->validate([
            'selected_partner_id' => 'required|exists:users,id',
            'new_tpi_merchant_id' => 'required|string',
            'new_kyc_id' => 'required|string',
        ]);

        TpiMerchant::updateOrCreate(
            ['user_id' => $this->selected_partner_id],
            [
                'tpi_merchant_id' => $this->new_tpi_merchant_id,
                'tpi_kyc_id' => $this->new_kyc_id,
                'tpi_kyc_status' => $this->new_kyc_status,
                'details' => [],
            ]
        );

        $this->reset(['selected_partner_id', 'new_tpi_merchant_id', 'new_kyc_id', 'new_kyc_status']);
        
        session()->flash('success', 'Merchant details saved successfully.');
        $this->dispatch('close-modal', 'addMerchantModal');
    }

    public function editMerchant($id)
    {
        $merchant = TpiMerchant::findOrFail($id);
        $this->edit_merchant_id = $merchant->id;
        $this->edit_tpi_merchant_id = $merchant->tpi_merchant_id;
        $this->edit_kyc_id = $merchant->tpi_kyc_id;
        $this->edit_kyc_status = $merchant->tpi_kyc_status;
        $this->edit_status = $merchant->status ?? 'active';
        $this->dispatch('open-modal', 'editMerchantModal');
    }

    public function updateMerchant()
    {
        $this->validate([
            'edit_tpi_merchant_id' => 'required|string',
            'edit_kyc_id' => 'required|string',
            'edit_status' => 'required|in:active,inactive',
        ]);

        $merchant = TpiMerchant::findOrFail($this->edit_merchant_id);
        $merchant->update([
            'tpi_merchant_id' => $this->edit_tpi_merchant_id,
            'tpi_kyc_id' => $this->edit_kyc_id,
            'tpi_kyc_status' => $this->edit_kyc_status,
            'status' => $this->edit_status,
        ]);

        session()->flash('success', 'Merchant updated successfully.');
        $this->dispatch('close-modal', 'editMerchantModal');
    }

    public function toggleStatus($id)
    {
        $merchant = TpiMerchant::findOrFail($id);
        $merchant->status = $merchant->status === 'active' ? 'inactive' : 'active';
        $merchant->save();
        session()->flash('success', 'Merchant status updated successfully.');
    }


    public function render()
    {
        $merchants = TpiMerchant::with('user')->latest()->paginate(10);
        $available_partners = \App\Models\User::where('role', 'partner')->orderBy('name')->get();
        
        return view('livewire.admin.payment-gateway', [
            'merchants' => $merchants,
            'available_partners' => $available_partners
        ])->layout('layouts.app', [
            'panelName'    => 'Admin Panel',
            'pageTitle'    => 'Payment Gateway',
            'pageSubtitle' => 'View all TPI merchants details',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
