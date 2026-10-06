<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\CustomField;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Categories extends Component
{
    use WithFileUploads;
    protected string $paginationTheme = 'bootstrap';

    public bool $showModal = false;
    public bool $showFieldModal = false;
    public ?string $editId = null;
    public ?string $selectedCategoryId = null;

    public string $name = '';
    public $icon = 'bi-grid';
    public bool $is_active = true;
    public string $search = '';
    public string $statusFilter = '';

    // Modules
    public bool $has_shifts = false;
    public bool $has_trainers = false;
    public bool $has_rooms = false;
    public bool $has_packages = false;
    public bool $has_attendance = false;

    // Custom field form
    public string $fieldLabel = '';
    public string $fieldType  = 'text';
    public bool   $fieldRequired = false;

    public function openCreate(): void
    {
        $this->reset(['name','icon','is_active','editId', 'has_shifts', 'has_trainers', 'has_rooms', 'has_packages', 'has_attendance']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $cat = Category::findOrFail($id);
        $this->editId    = $id;
        $this->name      = $cat->name;
        $this->icon      = $cat->icon ?? 'bi-grid';
        $this->is_active = $cat->is_active;
        $this->has_shifts    = $cat->has_shifts    ?? false;
        $this->has_trainers  = $cat->has_trainers  ?? false;
        $this->has_rooms     = $cat->has_rooms     ?? false;
        $this->has_packages  = $cat->has_packages  ?? false;
        $this->has_attendance = $cat->has_attendance ?? false;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'icon' => is_string($this->icon) ? 'nullable|string|max:255' : 'nullable|image|max:2048|mimes:png,jpg,jpeg,svg,gif',
        ]);

        $iconPath = $this->icon;
        
        if (!is_string($this->icon) && $this->icon) {
            $iconPath = $this->icon->store('categories', 'public');
            
            // Delete old icon if editing and it's an image
            if ($this->editId) {
                $oldCategory = Category::find($this->editId);
                if ($oldCategory && $oldCategory->icon && !Str::startsWith($oldCategory->icon, 'bi-')) {
                    Storage::disk('public')->delete($oldCategory->icon);
                }
            }
        }

        Category::updateOrCreate(
            ['id' => $this->editId],
            [
                'name'      => $this->name,
                'slug'      => Str::slug($this->name),
                'icon'      => $iconPath,
                'is_active' => $this->is_active,
                'has_shifts'    => $this->has_shifts,
                'has_trainers'  => $this->has_trainers,
                'has_rooms'     => $this->has_rooms,
                'has_packages'  => $this->has_packages,
                'has_attendance' => $this->has_attendance,
            ]
        );

        session()->flash('success', $this->editId ? 'Category updated.' : 'Category created.');
        $this->showModal = false;
    }

    public function delete(string $id): void
    {
        $cat = Category::findOrFail($id);
        if ($cat->listings()->count() > 0) {
            session()->flash('error', 'Cannot delete category. It has active listings.');
            return;
        }
        
        if ($cat->icon && !Str::startsWith($cat->icon, 'bi-')) {
            Storage::disk('public')->delete($cat->icon);
        }
        
        $cat->delete();
        session()->flash('success', 'Category deleted.');
    }

    public function getExportUrlProperty(): string
    {
        return route('admin.export', [
            'module' => 'categories',
            'search' => $this->search,
            'status' => $this->statusFilter,
        ]);
    }

    public function openFields(string $categoryId): void
    {
        $this->selectedCategoryId = $categoryId;
        $this->showFieldModal = true;
    }

    public function addField(): void
    {
        $this->validate(['fieldLabel' => 'required|string|max:100']);
        CustomField::create([
            'category_id' => $this->selectedCategoryId,
            'label'       => $this->fieldLabel,
            'field_type'  => $this->fieldType,
            'is_required' => $this->fieldRequired,
            'sort_order'  => CustomField::where('category_id', $this->selectedCategoryId)->count() + 1,
        ]);
        $this->reset(['fieldLabel','fieldType','fieldRequired']);
    }

    public function deleteField(int $id): void
    {
        CustomField::destroy($id);
    }

    public function render()
    {
        $categories = Category::withCount(['listings','customFields'])
            ->root()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->statusFilter !== '', fn($q) => $q->where('is_active', $this->statusFilter === 'active'))
            ->orderBy('sort_order')->get();

        $selectedFields = $this->selectedCategoryId
            ? CustomField::where('category_id', $this->selectedCategoryId)->orderBy('sort_order')->get()
            : collect();

        $stats = [
            'total' => Category::root()->count(),
            'active' => Category::root()->where('is_active', true)->count(),
            'inactive' => Category::root()->where('is_active', false)->count(),
        ];

        return view('livewire.admin.categories', compact('categories','selectedFields', 'stats'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Categories',
                'pageSubtitle' => 'Manage service categories and dynamic fields',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
