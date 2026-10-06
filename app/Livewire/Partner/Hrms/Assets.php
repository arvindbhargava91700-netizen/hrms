<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Department;
use App\Models\HrmsBranch;
use App\Models\Asset;
use App\Livewire\Partner\Hrms\Traits\HasHrmsFilters;
use Carbon\Carbon;

class Assets extends Component
{
    use WithPagination, HasPartnerId, HasHrmsFilters;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $filterCategory = 'all';
    public $filterStatus = 'all';
    public $filterCondition = 'all';
    public $filterBranchId = '';
    public $filterDepartmentId = '';
    public $search = '';
    public $perPage = 10;

    // Asset Master Modal State
    public $showAssetModal = false;
    public $editingAssetId = null;
    public $asset_code = '';
    public $category = 'laptop';
    public $name = '';
    public $brand = '';
    public $model = '';
    public $serial_number = '';
    public $imei_number = '';
    public $mobile_number = '';
    public $sim_number = '';
    public $card_number = '';
    public $purchase_date = '';
    public $purchase_cost = null;
    public $condition = 'good';
    public $status = 'available';
    public $description = '';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterCategory() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingFilterCondition() { $this->resetPage(); }
    public function updatingFilterBranchId() { $this->resetPage(); }
    public function updatingFilterDepartmentId() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('asset_view') ||
            auth()->user()->canAccess('asset_viewany') ||
            auth()->user()->canAccess('asset_viewBranch') ||
            auth()->user()->canAccess('asset_viewTeam') ||
            auth()->user()->canAccess('employeecost_viewany'),
            403
        );
    }

    // ── Asset Master CRUD ─────────────────────────────────────────────
    public function openAssetModal($id = null)
    {
        $this->resetValidation();
        $partnerId = $this->getPartnerId();

        if ($id) {
            $asset = Asset::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->findOrFail($id);
            $this->editingAssetId = $asset->id;
            $this->asset_code = $asset->asset_code;
            $this->category = $asset->category;
            $this->name = $asset->name;
            $this->brand = $asset->brand;
            $this->model = $asset->model;
            $this->serial_number = $asset->serial_number;
            $this->imei_number = $asset->imei_number;
            $this->mobile_number = $asset->mobile_number;
            $this->sim_number = $asset->sim_number;
            $this->card_number = $asset->card_number;
            $this->purchase_date = $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '';
            $this->purchase_cost = $asset->purchase_cost;
            $this->condition = $asset->condition;
            $this->status = $asset->status ?? 'available';
            $this->description = $asset->description;
        } else {
            $this->editingAssetId = null;
            $this->category = 'laptop';
            $this->generateAssetCode();
            $this->name = '';
            $this->brand = '';
            $this->model = '';
            $this->serial_number = '';
            $this->imei_number = '';
            $this->mobile_number = '';
            $this->sim_number = '';
            $this->card_number = '';
            $this->purchase_date = date('Y-m-d');
            $this->purchase_cost = null;
            $this->condition = 'good';
            $this->status = 'available';
            $this->description = '';
        }

        $this->showAssetModal = true;
    }

    public function updatedCategory()
    {
        if (!$this->editingAssetId) {
            $this->generateAssetCode();
        }
    }

    public function generateAssetCode()
    {
        $prefixMap = [
            'laptop' => 'LAP',
            'mobile' => 'MOB',
            'sim' => 'SIM',
            'id_card' => 'ID',
            'other' => 'AST',
        ];
        $prefix = $prefixMap[$this->category] ?? 'AST';
        $partnerId = $this->getPartnerId();
        $count = Asset::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('category', $this->category)->count() + 1;
        $this->asset_code = sprintf('%s-%04d', $prefix, $count);
    }

    public function closeAssetModal()
    {
        $this->showAssetModal = false;
    }

    public function saveAsset()
    {
        $partnerId = $this->getPartnerId();

        $this->validate([
            'asset_code' => 'required|string|max:100',
            'category' => 'required|in:laptop,mobile,sim,id_card,other',
            'name' => 'required|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'condition' => 'required|in:new,good,fair,damaged,lost',
            'status' => 'required|in:available,issued,damaged',
        ]);

        // Unique asset code check per partner
        $existing = Asset::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
            ->where('asset_code', $this->asset_code)
            ->where('id', '!=', $this->editingAssetId)
            ->first();

        if ($existing) {
            $this->addError('asset_code', 'This asset code is already in use for your organization.');
            return;
        }

        $match = ['id' => $this->editingAssetId];
        if (!auth()->user()->isSuperAdmin()) {
            $match['partner_id'] = $partnerId;
        } elseif (!$this->editingAssetId) {
            $match['partner_id'] = $this->requirePartnerId();
        }

        Asset::updateOrCreate(
            $match,
            [
                'asset_code' => $this->asset_code,
                'category' => $this->category,
                'name' => $this->name,
                'brand' => $this->brand,
                'model' => $this->model,
                'serial_number' => $this->serial_number,
                'imei_number' => $this->imei_number,
                'mobile_number' => $this->mobile_number,
                'sim_number' => $this->sim_number,
                'card_number' => $this->card_number,
                'purchase_date' => $this->purchase_date ?: null,
                'purchase_cost' => $this->purchase_cost,
                'condition' => $this->condition,
                'status' => $this->status,
                'description' => $this->description,
                'created_by' => auth()->id(),
            ]
        );

        session()->flash('success', $this->editingAssetId ? 'Asset updated successfully.' : 'New asset registered successfully.');
        $this->closeAssetModal();
    }

    public function deleteAsset($id)
    {
        $partnerId = $this->getPartnerId();
        $asset = Asset::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->findOrFail($id);

        // Delete any related assignment records cleanly if present
        if (method_exists($asset, 'assignments')) {
            $asset->assignments()->delete();
        }

        $asset->delete();
        session()->flash('success', 'Asset record deleted successfully.');
    }

    public function render()
    {
        $partnerId = $this->getPartnerId();

        $assetsQuery = Asset::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } });
        
        if (!auth()->user()->isPartner() && !auth()->user()->canAccess('asset_viewAny')) {
            if (auth()->user()->canAccess('asset_viewBranch') || auth()->user()->canAccess('asset_viewTeam') || auth()->user()->canAccess('asset_viewOwn')) {
                $assetsQuery->where('branch_id', auth()->user()->branch_id);
            }
        }

        if ($this->filterCategory !== 'all' && !empty($this->filterCategory)) {
            $assetsQuery->where('category', $this->filterCategory);
        }
        if ($this->filterStatus !== 'all' && !empty($this->filterStatus)) {
            $assetsQuery->where('status', $this->filterStatus);
        }
        if ($this->filterCondition !== 'all' && !empty($this->filterCondition)) {
            $assetsQuery->where('condition', $this->filterCondition);
        }
        if (!empty($this->filterBranchId)) {
            $assetsQuery->where('branch_id', $this->filterBranchId);
        }
        if (!empty($this->filterDepartmentId)) {
            $assetsQuery->where('department_id', $this->filterDepartmentId);
        }
        if (!empty($this->search)) {
            $s = '%' . $this->search . '%';
            $assetsQuery->where(function ($q) use ($s) {
                $q->where('asset_code', 'like', $s)
                    ->orWhere('name', 'like', $s)
                    ->orWhere('brand', 'like', $s)
                    ->orWhere('model', 'like', $s)
                    ->orWhere('serial_number', 'like', $s)
                    ->orWhere('imei_number', 'like', $s)
                    ->orWhere('mobile_number', 'like', $s)
                    ->orWhere('sim_number', 'like', $s);
            });
        }

        $allFilteredAssets = (clone $assetsQuery)->get();

        // Key KPI statistics
        $totalAssets = $allFilteredAssets->count();
        $availableAssets = $allFilteredAssets->where('status', 'available')->count();
        $damagedAssets = $allFilteredAssets->where('status', 'damaged')->count();
        $totalAssetValue = $allFilteredAssets->sum('purchase_cost');

        // Category breakdown for Chart
        $categoryBreakdown = [
            ['label' => 'Laptop', 'category' => 'laptop', 'total' => $allFilteredAssets->where('category', 'laptop')->count()],
            ['label' => 'Mobile', 'category' => 'mobile', 'total' => $allFilteredAssets->where('category', 'mobile')->count()],
            ['label' => 'SIM Card', 'category' => 'sim', 'total' => $allFilteredAssets->where('category', 'sim')->count()],
            ['label' => 'ID Card', 'category' => 'id_card', 'total' => $allFilteredAssets->where('category', 'id_card')->count()],
            ['label' => 'Other', 'category' => 'other', 'total' => $allFilteredAssets->where('category', 'other')->count()],
        ];

        $paginatedAssets = $assetsQuery->orderByDesc('id')->paginate($this->perPage);

        $branches = HrmsBranch::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->orderBy('name')->get();
        $departments = Department::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->orderBy('name')->get();

        return view('livewire.partner.hrms.assets', [
            'totalAssets'       => $totalAssets,
            'availableAssets'   => $availableAssets,
            'damagedAssets'     => $damagedAssets,
            'totalAssetValue'   => $totalAssetValue,
            'categoryBreakdown' => $categoryBreakdown,
            'paginatedAssets'   => $paginatedAssets,
            'branches'          => $branches,
            'departments'       => $departments,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Assets Management',
            'pageSubtitle' => 'Manage laptops, mobiles, SIM cards, ID cards, and company hardware assets',
            'sidebarLinks' => view(auth()->check() && auth()->user()->role === 'employee' ? 'partials.sidebar-employee' : 'partials.sidebar-partner'),
        ]);
    }
}
