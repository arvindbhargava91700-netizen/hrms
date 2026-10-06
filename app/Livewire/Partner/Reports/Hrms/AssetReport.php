<?php

namespace App\Livewire\Partner\Reports\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Department;
use App\Models\HrmsBranch;
use App\Models\Asset;
use Carbon\Carbon;

class AssetReport extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $filterCategory = 'all';
    public $filterStatus = 'all';
    public $filterBranchId = '';
    public $teamId = '';
    public $filterDepartmentId = '';
    public $search = '';
    public $perPage = 10;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterCategory() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingFilterBranchId() { $this->teamId = ''; $this->resetPage(); }
    public function updatedTeamId() { $this->resetPage(); }
    public function updatingFilterDepartmentId() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('asset_viewAny') ||
            auth()->user()->canAccess('asset_view') ||
            auth()->user()->canAccess('asset_viewteam'),
            403
        );
    }

    public function getPartnerId()
    {
        return auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
    }

    public function render()
    {
        $partnerId = $this->getPartnerId();

        // 1. Base Query for Assets
        $assetsQuery = Asset::where('partner_id', $partnerId);

        if ($this->filterCategory !== 'all' && !empty($this->filterCategory)) {
            $assetsQuery->where('category', $this->filterCategory);
        }
        if ($this->filterStatus !== 'all' && !empty($this->filterStatus)) {
            $assetsQuery->where('status', $this->filterStatus);
        }
        if (!empty($this->filterBranchId)) {
            $assetsQuery->where('branch_id', $this->filterBranchId);
        }
        if (!empty($this->teamId)) {
            $assetsQuery->whereHas('creator', function ($q) {
                $q->where('reporting_to', $this->teamId);
            });
        }
        if (!empty($this->filterDepartmentId)) {
            $assetsQuery->where('department_id', $this->filterDepartmentId);
        }
        if (!empty($this->search)) {
            $s = '%' . $this->search . '%';
            $assetsQuery->where(function($q) use ($s) {
                $q->where('name', 'like', $s)
                  ->orWhere('asset_code', 'like', $s)
                  ->orWhere('serial_number', 'like', $s)
                  ->orWhere('imei_number', 'like', $s);
            });
        }

        $allFilteredAssets = (clone $assetsQuery)->get();

        // 2. Summary KPI Metrics
        $totalAssetsCount = $allFilteredAssets->count();
        $availableCount = $allFilteredAssets->where('status', 'available')->count();
        $issuedCount = $allFilteredAssets->where('status', 'issued')->count();
        $damagedCount = $allFilteredAssets->where('status', 'damaged')->count();
        $totalCost = $allFilteredAssets->sum('purchase_cost');

        // 3. Category Breakdown for Chart.js
        $categoriesList = ['laptop', 'mobile', 'sim', 'id_card', 'other'];
        $categoryBreakdown = [];
        foreach ($categoriesList as $cat) {
            $catAssets = $allFilteredAssets->where('category', $cat);
            $categoryBreakdown[] = [
                'category'  => $cat,
                'label'     => strtoupper(str_replace('_', ' ', $cat)),
                'total'     => $catAssets->count(),
                'available' => $catAssets->where('status', 'available')->count(),
                'issued'    => $catAssets->where('status', 'issued')->count(),
                'damaged'   => $catAssets->where('status', 'damaged')->count(),
            ];
        }

        // 4. Paginated Assets Table
        $paginatedAssets = $assetsQuery->orderByDesc('id')->paginate($this->perPage);

        // Filter Options
        $branches = HrmsBranch::where('partner_id', $partnerId)->where('status', 'active')->orderBy('name')->get();
        $departments = Department::where('partner_id', $partnerId)->orderBy('name')->get();
        $teams = $this->getTeams($partnerId);

        return view('livewire.partner.reports.hrms.asset-report', [
            'totalAssetsCount'  => $totalAssetsCount,
            'availableCount'   => $availableCount,
            'issuedCount'      => $issuedCount,
            'damagedCount'     => $damagedCount,
            'totalCost'        => $totalCost,
            'categoryBreakdown' => $categoryBreakdown,
            'paginatedAssets'  => $paginatedAssets,
            'branches'         => $branches,
            'departments'      => $departments,
            'teams'            => $teams,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Asset Report',
            'pageSubtitle' => 'Company asset inventory and status distribution report',
            'sidebarLinks' => view(auth()->check() && auth()->user()->role === 'employee' ? 'partials.sidebar-employee' : 'partials.sidebar-partner'),
        ]);
    }

    private function getTeams($partnerId)
    {
        $reportingQuery = User::where('parent_id', $partnerId)
            ->where('role', 'employee')
            ->whereNotNull('reporting_to');

        if ($this->filterBranchId) {
            $reportingQuery->where('branch_id', $this->filterBranchId);
        }

        $leadIds = $reportingQuery->pluck('reporting_to')->unique();

        if ($leadIds->isNotEmpty()) {
            return User::whereIn('id', $leadIds)->orderBy('name')->get();
        }

        return User::where('parent_id', $partnerId)
            ->where('role', 'employee')
            ->whereHas('reportees')
            ->orderBy('name')
            ->get();
    }
}
