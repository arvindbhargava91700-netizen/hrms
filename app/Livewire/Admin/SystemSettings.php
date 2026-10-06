<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;

class SystemSettings extends Component
{
    use WithFileUploads;

    public $company_name = '';
    public $company_logo;
    public $existing_logo = '';
    public $company_address = '';
    public $company_city = '';
    public $company_state_name = '';
    public $company_state_code = '';
    public $company_pan = '';
    public $company_cin = '';
    public $company_gstin = '';
    public $bank_account_name = '';
    public $bank_name = '';
    public $bank_account_no = '';
    public $bank_ifsc = '';
    public $signatory_name = '';

    public function mount()
    {
        $this->company_name = SystemSetting::getSetting('company_name', 'FEETRACK TECH PRIVATE LIMITED');
        $this->existing_logo = SystemSetting::getSetting('company_logo', '');
        $this->company_address = SystemSetting::getSetting('company_address', 'Feetrack Headquarters, Corporate Office');
        $this->company_city = SystemSetting::getSetting('company_city', 'Default City');
        $this->company_state_name = SystemSetting::getSetting('company_state_name', 'Default State');
        $this->company_state_code = SystemSetting::getSetting('company_state_code', '00');
        $this->company_pan = SystemSetting::getSetting('company_pan', 'AAAA0000A');
        $this->company_cin = SystemSetting::getSetting('company_cin', 'U00000XX2026PTC000000');
        $this->company_gstin = SystemSetting::getSetting('company_gstin', '');
        $this->bank_account_name = SystemSetting::getSetting('bank_account_name', 'FEETRACK PVT LTD');
        $this->bank_name = SystemSetting::getSetting('bank_name', 'DEFAULT BANK');
        $this->bank_account_no = SystemSetting::getSetting('bank_account_no', '0000000000');
        $this->bank_ifsc = SystemSetting::getSetting('bank_ifsc', 'BKID0000000');
        $this->signatory_name = SystemSetting::getSetting('signatory_name', 'FEETRACK TECH PRIVATE LIMITED');
    }

    public function saveSettings()
    {
        $this->validate([
            'company_logo' => 'nullable|image|max:2048', // 2MB Max
        ]);

        if ($this->company_logo) {
            $path = $this->company_logo->store('logos', 'public');
            SystemSetting::setSetting('company_logo', $path);
            $this->existing_logo = $path;
            $this->company_logo = null;
        }

        SystemSetting::setSetting('company_name', $this->company_name);
        SystemSetting::setSetting('company_address', $this->company_address);
        SystemSetting::setSetting('company_city', $this->company_city);
        SystemSetting::setSetting('company_state_name', $this->company_state_name);
        SystemSetting::setSetting('company_state_code', $this->company_state_code);
        SystemSetting::setSetting('company_pan', $this->company_pan);
        SystemSetting::setSetting('company_cin', $this->company_cin);
        SystemSetting::setSetting('company_gstin', $this->company_gstin);
        SystemSetting::setSetting('bank_account_name', $this->bank_account_name);
        SystemSetting::setSetting('bank_name', $this->bank_name);
        SystemSetting::setSetting('bank_account_no', $this->bank_account_no);
        SystemSetting::setSetting('bank_ifsc', $this->bank_ifsc);
        SystemSetting::setSetting('signatory_name', $this->signatory_name);
        
        session()->flash('success', 'System settings saved successfully!');
    }

    public function render()
    {
        return view('livewire.admin.system-settings')
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'System Settings',
                'pageSubtitle' => 'Manage global system configurations',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
