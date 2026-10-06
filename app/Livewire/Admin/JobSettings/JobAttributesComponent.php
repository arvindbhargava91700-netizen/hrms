<?php

namespace App\Livewire\Admin\JobSettings;

use Livewire\Component;
use App\Models\JobSetting;

class JobAttributesComponent extends Component
{
    public $type = 'skill';
    public $name = '';
    public $depends_on = '';
    
    public function render()
    {
        $settings = JobSetting::where('type', $this->type)->orderBy('name')->get();
        return view('livewire.admin.job-settings.job-attributes-component', compact('settings'))
            ->layout('layouts.app', [
                'panelName' => 'Admin Panel',
                'pageTitle' => 'Job Attributes',
                'pageSubtitle' => 'Manage job skills, assets, degrees, and perks.',
                'sidebarLinks' => view('partials.sidebar-admin')->render(),
            ]);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'depends_on' => 'nullable|string',
        ]);

        JobSetting::create([
            'type' => $this->type,
            'name' => $this->name,
            'depends_on' => $this->depends_on ?: null,
        ]);

        $this->name = '';
        $this->depends_on = '';
        session()->flash('success', 'Attribute added successfully.');
    }

    public function delete($id)
    {
        JobSetting::findOrFail($id)->delete();
        session()->flash('success', 'Attribute deleted successfully.');
    }
}
