<?php

namespace App\Livewire\Admin;

use App\Models\SystemModule;
use App\Models\SystemSetting;
use Livewire\Component;

class SystemModules extends Component
{
    public $tab = 'modules';

    public $modules = [];

    public $appCommission = 100;

    public $editingModuleId = null;

    public $moduleName;

    public $moduleSlug;

    public $moduleIcon;

    public $moduleDescription;

    public $moduleIsActive = true;

    public $isModuleModalOpen = false;

    public function mount()
    {
        $this->tab = request('tab') === 'commission' ? 'commission' : 'modules';
        $this->loadModules();
        $this->appCommission = (float) SystemSetting::getSetting('app_comission', 100);
    }

    public function loadModules()
    {
        $this->modules = SystemModule::get();
    }

    public function createModule()
    {
        $this->reset(['editingModuleId', 'moduleName', 'moduleSlug', 'moduleIcon', 'moduleDescription', 'moduleIsActive']);
        $this->isModuleModalOpen = true;
    }

    public function editModule($id)
    {
        $module = SystemModule::findOrFail($id);
        $this->editingModuleId = $module->id;
        $this->moduleName = $module->name;
        $this->moduleSlug = $module->slug;
        $this->moduleIcon = $module->icon;
        $this->moduleDescription = $module->description;
        $this->moduleIsActive = $module->is_active;
        $this->isModuleModalOpen = true;
    }

    public function saveModule()
    {
        $this->validate([
            'moduleName' => 'required|string|max:255',
            'moduleSlug' => 'required|string|max:255|unique:system_modules,slug,'.$this->editingModuleId,
        ]);

        SystemModule::updateOrCreate(
            ['id' => $this->editingModuleId],
            [
                'name' => $this->moduleName,
                'slug' => $this->moduleSlug,
                'icon' => $this->moduleIcon,
                'description' => $this->moduleDescription,
                'is_active' => $this->moduleIsActive,
            ]
        );

        $this->isModuleModalOpen = false;
        session()->flash('success', 'Module saved successfully.');
        $this->loadModules();
    }

    public function switchTab($tab)
    {
        $this->tab = $tab;

        if ($tab === 'commission') {
            $this->appCommission = (float) SystemSetting::getSetting('app_comission', 100);
        }
    }

    public function saveCommission()
    {
        $this->validate([
            'appCommission' => 'required|numeric|min:0',
        ]);

        SystemSetting::setSetting('app_comission', $this->appCommission);

        session()->flash('success', 'App commission updated successfully.');
    }

    public function deleteModule($id)
    {
        SystemModule::findOrFail($id)->delete();
        $this->loadModules();
    }

    public function render()
    {
        return view('livewire.admin.system-modules')
            ->layout('layouts.app', [
                'panelName' => 'Admin Panel',
                'pageTitle' => 'System Modules',
                'pageSubtitle' => 'Manage SaaS modules and pricing packages',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
