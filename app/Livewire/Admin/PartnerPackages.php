<?php

namespace App\Livewire\Admin;

use App\Models\PartnerPackage;
use Livewire\Component;

class PartnerPackages extends Component
{
    public $name, $price = 0, $duration_days = 30, $commission_type = 'fixed', $commission_value = 5, $is_active = true;
    public $is_free_trial = false, $category_limit = null, $listing_limit = null;
    public $email_notification = false, $app_notification = false, $sms_notification = false, $whatsapp_notification = false;
    public $offline_commission_type = 'fixed', $offline_commission_value = 5;
    public $commission_ranges = [];
    public $offline_commission_ranges = [];
    public $payment_gateways = [];
    public $selected_modules = [];
    public $editingId = null;
    public $viewPackage = null;

    public $currentStep = 1;

    public function addCommissionRange()
    {
        $this->commission_ranges[] = [
            'min_amount' => 0,
            'max_amount' => null,
            'type'       => 'percent',
            'value'      => 0,
        ];
    }

    public function removeCommissionRange($index)
    {
        unset($this->commission_ranges[$index]);
        $this->commission_ranges = array_values($this->commission_ranges);
    }

    public function addOfflineCommissionRange()
    {
        $this->offline_commission_ranges[] = [
            'min_amount' => 0,
            'max_amount' => null,
            'type'       => 'percent',
            'value'      => 0,
        ];
    }

    public function removeOfflineCommissionRange($index)
    {
        unset($this->offline_commission_ranges[$index]);
        $this->offline_commission_ranges = array_values($this->offline_commission_ranges);
    }

    public function viewPackageDetails($id)
    {
        $this->viewPackage = \App\Models\PartnerPackage::with('systemModules')->find($id)->toArray();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:0',
            'commission_type' => 'required|in:fixed,percent',
            'commission_value' => 'required|numeric|min:0',
            'commission_ranges' => 'nullable|array',
            'commission_ranges.*.min_amount' => 'nullable|numeric|min:0',
            'commission_ranges.*.max_amount' => 'nullable|numeric|min:0',
            'commission_ranges.*.type' => 'nullable|in:fixed,percent',
            'commission_ranges.*.value' => 'nullable|numeric|min:0',
            'is_free_trial' => 'boolean',
            'category_limit' => 'nullable|integer|min:0',
            'listing_limit' => 'nullable|integer|min:0',
            'email_notification' => 'boolean',
            'app_notification' => 'boolean',
            'sms_notification' => 'boolean',
            'whatsapp_notification' => 'boolean',
            'offline_commission_type' => 'required|in:fixed,percent',
            'offline_commission_value' => 'required|numeric|min:0',
            'offline_commission_ranges' => 'nullable|array',
            'offline_commission_ranges.*.min_amount' => 'nullable|numeric|min:0',
            'offline_commission_ranges.*.max_amount' => 'nullable|numeric|min:0',
            'offline_commission_ranges.*.type' => 'nullable|in:fixed,percent',
            'offline_commission_ranges.*.value' => 'nullable|numeric|min:0',
            'payment_gateways' => 'array',
            'selected_modules' => 'array',
        ];
    }

    public function nextStep()
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'duration_days' => 'required|integer|min:0',
                'payment_gateways' => 'array',
            ]);
        } elseif ($this->currentStep === 2) {
            $this->validate([
                'commission_type' => 'required|in:fixed,percent',
                'commission_value' => 'required|numeric|min:0',
                'commission_ranges' => 'nullable|array',
                'offline_commission_type' => 'required|in:fixed,percent',
                'offline_commission_value' => 'required|numeric|min:0',
                'offline_commission_ranges' => 'nullable|array',
            ]);
        } elseif ($this->currentStep === 3) {
            $this->validate([
                'selected_modules' => 'array',
            ]);
        } elseif ($this->currentStep === 4) {
            $this->validate([
                'is_free_trial' => 'boolean',
                'category_limit' => 'nullable|integer|min:0',
                'listing_limit' => 'nullable|integer|min:0',
                'email_notification' => 'boolean',
                'app_notification' => 'boolean',
                'sms_notification' => 'boolean',
                'whatsapp_notification' => 'boolean',
            ]);
        }
        
        $this->currentStep++;
    }

    public function previousStep()
    {
        $this->currentStep--;
    }

    public function resetForm()
    {
        $this->reset([
            'name', 'price', 'duration_days', 'commission_type', 'commission_value', 'commission_ranges', 'is_active', 'is_free_trial', 'category_limit', 'listing_limit', 
            'email_notification', 'app_notification', 'sms_notification', 'whatsapp_notification', 
            'offline_commission_type', 'offline_commission_value', 'offline_commission_ranges', 'payment_gateways',
            'selected_modules', 'editingId', 'currentStep'
        ]);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'price' => $this->price,
            'duration_days' => $this->duration_days,
            'commission_type' => $this->commission_type,
            'commission_value' => $this->commission_value,
            'commission_ranges' => !empty($this->commission_ranges) ? array_values($this->commission_ranges) : null,
            'is_active' => $this->is_active,
            'is_free_trial' => $this->is_free_trial,
            'category_limit' => $this->category_limit !== '' ? $this->category_limit : null,
            'listing_limit' => $this->listing_limit !== '' ? $this->listing_limit : null,
            'email_notification' => $this->email_notification,
            'app_notification' => $this->app_notification,
            'sms_notification' => $this->sms_notification,
            'whatsapp_notification' => $this->whatsapp_notification,
            'offline_commission_type' => $this->offline_commission_type,
            'offline_commission_value' => $this->offline_commission_value,
            'offline_commission_ranges' => !empty($this->offline_commission_ranges) ? array_values($this->offline_commission_ranges) : null,
            'payment_gateways' => $this->payment_gateways,
        ];

        if ($this->editingId) {
            $pkg = PartnerPackage::find($this->editingId);
            $pkg->update($data);
            $pkg->systemModules()->sync($this->selected_modules);
        } else {
            $pkg = PartnerPackage::create($data);
            $pkg->systemModules()->sync($this->selected_modules);
        }

        $this->resetForm();
        $this->dispatch('close-modal');
    }

    public function edit($id)
    {
        $pkg = PartnerPackage::with('systemModules')->find($id);
        $this->editingId = $pkg->id;
        $this->name = $pkg->name;
        $this->price = $pkg->price;
        $this->duration_days = $pkg->duration_days;
        $this->commission_type = $pkg->commission_type;
        $this->commission_value = $pkg->commission_value;
        $this->commission_ranges = $pkg->commission_ranges ?? [];
        $this->is_active = $pkg->is_active;
        $this->is_free_trial = $pkg->is_free_trial;
        $this->category_limit = $pkg->category_limit;
        $this->listing_limit = $pkg->listing_limit;
        $this->email_notification = $pkg->email_notification;
        $this->app_notification = $pkg->app_notification;
        $this->sms_notification = $pkg->sms_notification;
        $this->whatsapp_notification = $pkg->whatsapp_notification;
        $this->offline_commission_type = $pkg->offline_commission_type;
        $this->offline_commission_value = $pkg->offline_commission_value;
        $this->offline_commission_ranges = $pkg->offline_commission_ranges ?? [];
        $this->payment_gateways = $pkg->payment_gateways ?? [];
        $this->selected_modules = $pkg->systemModules->pluck('id')->toArray();
        $this->currentStep = 1;
        $this->dispatch('open-edit-modal');
    }

    public function toggleActive($id)
    {
        $pkg = PartnerPackage::find($id);
        $pkg->update(['is_active' => !$pkg->is_active]);
    }

    public function deletePackage($id)
    {
        $pkg = PartnerPackage::find($id);
        if (!$pkg) return;

        // Check if package has active subscriptions
        $hasActiveSubscriptions = \App\Models\PartnerSubscription::where('partner_package_id', $id)
            ->where('status', 'active')
            ->exists();
            
        if ($hasActiveSubscriptions) {
            session()->flash('error', 'Cannot delete package because it has active subscriptions.');
            return;
        }
        
        $pkg->delete();
        session()->flash('success', 'Package deleted successfully.');
    }

    public function render()
    {
        $packages = PartnerPackage::with('systemModules')->latest()->get();
        $systemModules = \App\Models\SystemModule::where('is_active', true)->get();
        
        $stats = [
            'total' => PartnerPackage::count(),
            'active' => PartnerPackage::where('is_active', true)->count(),
            'inactive' => PartnerPackage::where('is_active', false)->count(),
        ];
        
        return view('livewire.admin.partner-packages', compact('packages', 'stats', 'systemModules'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Partner Packages',
                'pageSubtitle' => 'Manage subscription plans for partners',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
