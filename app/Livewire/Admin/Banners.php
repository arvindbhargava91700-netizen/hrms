<?php

namespace App\Livewire\Admin;

use App\Models\Banner;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithFileUploads;

class Banners extends Component
{
    use WithFileUploads;
    protected string $paginationTheme = 'bootstrap';

    public $showModal = false;
    public $bannerId;
    public $title;
    public $image;
    public $category_id = '';
    public $is_active = true;
    public $sort_order = 0;

    public $existing_image;

    protected $rules = [
        'title'       => 'required|string|max:255',
        'image'       => 'nullable|image|max:2048|dimensions:width=1200,height=500', // max 2MB, strictly 1200x500
        'category_id' => 'nullable|exists:categories,id',
        'sort_order'  => 'required|integer',
    ];

    public function updatedImage()
    {
        $this->validateOnly('image');
    }

    public function create()
    {
        $this->reset(['bannerId', 'title', 'image', 'existing_image', 'category_id']);
        $this->is_active = true;
        $this->sort_order = Banner::max('sort_order') + 1;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $banner = Banner::findOrFail($id);
        $this->bannerId       = $banner->id;
        $this->title          = $banner->title;
        $this->category_id    = $banner->category_id ?? '';
        $this->is_active      = $banner->is_active;
        $this->sort_order     = $banner->sort_order;
        $this->existing_image = $banner->image_path;
        $this->image          = null;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if (!$this->bannerId && !$this->image) {
            $this->addError('image', 'An image is required for a new banner.');
            return;
        }

        $data = [
            'title'       => $this->title,
            'category_id' => $this->category_id ?: null,
            'is_active'   => $this->is_active,
            'sort_order'  => $this->sort_order,
        ];

        if ($this->image) {
            $path = $this->image->store('banners', 'public');
            $data['image_path'] = $path;
        }

        if ($this->bannerId) {
            Banner::find($this->bannerId)->update($data);
            session()->flash('success', 'Banner updated successfully.');
        } else {
            Banner::create($data);
            session()->flash('success', 'Banner created successfully.');
        }

        $this->showModal = false;
    }

    public function toggleActive($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->update(['is_active' => !$banner->is_active]);
    }

    public function delete($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();
        session()->flash('success', 'Banner deleted successfully.');
    }

    public function render()
    {
        $banners = Banner::with('category')->orderBy('sort_order')->get();
        $categories = Category::active()->pluck('name', 'id');

        return view('livewire.admin.banners', compact('banners', 'categories'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'App Banners',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
