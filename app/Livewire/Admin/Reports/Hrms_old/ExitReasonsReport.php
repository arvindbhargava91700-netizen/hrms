<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ExitReason;
use App\Models\EmployeeExit;

class ExitReasonsReport extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $filterType = 'all';
    public $search = '';
    public $partnerFilter = '';
    public $perPage = 10;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterType() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('exitreason_view') ||
            auth()->user()->canAccess('exitreason_viewAny'),
            403
        );
    }

    public function getPartnerId()
    {
        return auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
    }

    public function render()
    {
        

        $query = ExitReason::where(function ($q) {
            $q->whereNull('partner_id')->orWhere('partner_id', "");
        });

        if ($this->filterType !== 'all' && !empty($this->filterType)) {
            $query->where('type', $this->filterType);
        }

        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $allReasons = (clone $query)->get();

        $totalReasons = $allReasons->count();
        $voluntaryCount = $allReasons->where('type', 'voluntary')->count();
        $involuntaryCount = $allReasons->where('type', 'involuntary')->count();

        $paginatedReasons = $query->orderBy('name')->paginate($this->perPage);

        return view('livewire.admin.reports.hrms.exit-reasons-report', [
            'totalReasons'     => $totalReasons,
            'voluntaryCount'   => $voluntaryCount,
            'involuntaryCount' => $involuntaryCount,
            'paginatedReasons' => $paginatedReasons,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Exit Reasons Report',
            'pageSubtitle' => 'Configured exit reason categories and type distribution',
            'sidebarLinks' => view(auth()->check() && auth()->user()->role === 'employee' ? 'partials.sidebar-employee' : 'partials.sidebar-admin'),
        ]);
    }
}
