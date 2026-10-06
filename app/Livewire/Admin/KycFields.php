<?php

namespace App\Livewire\Admin;

use App\Models\KycRequirement;
use Illuminate\Support\Str;
use Livewire\Component;

class KycFields extends Component
{
    public bool $showModal = false;
    public ?string $editId = null;
    public string $search = '';
    public string $typeFilter = '';

    public string $label = '';
    public string $key = '';
    public string $fieldType = 'text';
    public string $inputType = 'text';
    public bool $hasValueField = true;
    public string $documentMode = 'single';
    public string $valueLabel = '';
    public string $placeholder = '';
    public string $helpText = '';
    public bool $isRequired = true;
    public bool $isActive = true;

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $requirement = KycRequirement::findOrFail($id);

        $this->editId = $requirement->id;
        $this->label = $requirement->label;
        $this->key = $requirement->key;
        $this->fieldType = $requirement->field_type;
        $this->inputType = $requirement->input_type;
        $this->hasValueField = $requirement->has_value_field;
        $this->documentMode = $requirement->document_mode;
        $this->valueLabel = $requirement->value_label ?? '';
        $this->placeholder = $requirement->placeholder ?? '';
        $this->helpText = $requirement->help_text ?? '';
        $this->isRequired = $requirement->is_required;
        $this->isActive = $requirement->is_active;
        $this->showModal = true;
    }

    public function updatedLabel(): void
    {
        if (!$this->editId || $this->key === '' || $this->key === Str::snake($this->label)) {
            $this->key = Str::snake($this->label);
        }
    }

    public function save(): void
    {
        $rules = [
            'label' => 'required|string|max:120',
            'key' => 'required|string|max:120|alpha_dash',
            'fieldType' => 'required|in:text,document',
            'inputType' => 'required|in:text,number,email,tel',
            'hasValueField' => 'boolean',
            'documentMode' => 'required|in:single,front_back',
            'valueLabel' => 'nullable|string|max:120',
            'placeholder' => 'nullable|string|max:255',
            'helpText' => 'nullable|string|max:255',
            'isRequired' => 'boolean',
            'isActive' => 'boolean',
        ];

        $validated = $this->validate($rules);

        $data = [
            'label' => $validated['label'],
            'key' => Str::snake($validated['key']),
            'field_type' => $validated['fieldType'],
            'input_type' => $validated['inputType'],
            'has_value_field' => $validated['hasValueField'],
            'document_mode' => $validated['documentMode'],
            'value_label' => $validated['valueLabel'] ?: null,
            'placeholder' => $validated['placeholder'] ?: null,
            'help_text' => $validated['helpText'] ?: null,
            'is_required' => $validated['isRequired'],
            'is_active' => $validated['isActive'],
        ];

        if ($this->editId) {
            KycRequirement::findOrFail($this->editId)->update($data);
        } else {
            $data['sort_order'] = KycRequirement::count() + 1;
            KycRequirement::create($data);
        }

        $this->showModal = false;
        session()->flash('success', 'KYC field saved successfully.');
        $this->resetForm(false);
    }

    public function delete(string $id): void
    {
        KycRequirement::findOrFail($id)->delete();
        session()->flash('success', 'KYC field deleted.');
    }

    public function resetForm(bool $clearModal = true): void
    {
        $this->reset([
            'editId',
            'label',
            'key',
            'fieldType',
            'inputType',
            'hasValueField',
            'documentMode',
            'valueLabel',
            'placeholder',
            'helpText',
            'isRequired',
            'isActive',
        ]);

        $this->fieldType = 'text';
        $this->inputType = 'text';
        $this->hasValueField = true;
        $this->documentMode = 'single';
        $this->isRequired = true;
        $this->isActive = true;

        if ($clearModal) {
            $this->showModal = false;
        }
    }

    public function updatingSearch(): void { }
    public function updatingTypeFilter(): void { }

    public function getExportUrlProperty(): string
    {
        return route('admin.export', [
            'module' => 'kyc-fields',
            'search' => $this->search,
            'type' => $this->typeFilter,
        ]);
    }

    public function render()
    {
        $requirements = KycRequirement::query()
            ->when($this->search, fn($q) => $q->where(function ($subQuery) {
                $subQuery->where('label', 'like', "%{$this->search}%")
                    ->orWhere('key', 'like', "%{$this->search}%");
            }))
            ->when($this->typeFilter, fn($q) => $q->where('field_type', $this->typeFilter))
            ->orderBy('sort_order')->get();

        return view('livewire.admin.kyc-fields', compact('requirements'))
            ->layout('layouts.app', [
                'panelName' => 'Admin Panel',
                'pageTitle' => 'KYC Field Setup',
                'pageSubtitle' => 'Configure partner KYC fields and document uploads',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
