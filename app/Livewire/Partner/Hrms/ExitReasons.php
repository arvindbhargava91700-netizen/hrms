<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ExitReason;
use App\Models\EmployeeExit;

class ExitReasons extends Component
{
    use WithPagination, HasPartnerId;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filterType = 'all';
    public $filterStatus = 'all';
    public $perPage = 10;

    // Modal Form Properties
    public $isModalOpen = false;
    public $editingId = null;
    public $name = '';
    public $type = 'voluntary';
    public $is_active = true;

    protected $listeners = ['refreshExitReasons' => '$refresh'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterType() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    private function exitReasonQuery($partnerId)
    {
        $query = ExitReason::query();

        if (!auth()->user()->isSuperAdmin()) {
            $query->where(function ($q) use ($partnerId) {
                $q->whereNull('partner_id')->orWhere('partner_id', $partnerId);
            });
        }

        return $query;
    }

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('exit_reason_view') ||
            auth()->user()->canAccess('exitreason_viewany') ||
            auth()->user()->canAccess('exitreason_viewBranch') ||
            auth()->user()->canAccess('exitreason_viewTeam') ||
            auth()->user()->canAccess('attrition_viewAny'),
            403
        );
    }

    public function openCreateModal()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('exit_reason_create') ||
            auth()->user()->canAccess('exitreason_create'),
            403
        );
        $this->reset(['editingId', 'name', 'type', 'is_active']);
        $this->type = 'voluntary';
        $this->is_active = true;
        $this->resetValidation();
        $this->isModalOpen = true;
    }

    public function editExitReason($id)
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('exit_reason_edit') ||
            auth()->user()->canAccess('exitreason_update'),
            403
        );
        $partnerId = $this->getPartnerId();
        $reason = $this->exitReasonQuery($partnerId)->findOrFail($id);

        $this->editingId = $reason->id;
        $this->name = $reason->name;
        $this->type = $reason->type;
        $this->is_active = (bool) $reason->is_active;
        $this->resetValidation();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
    }

    public function saveExitReason()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess($this->editingId ? 'exit_reason_edit' : 'exit_reason_create') ||
            auth()->user()->canAccess($this->editingId ? 'exitreason_update' : 'exitreason_create'),
            403
        );

        $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:voluntary,involuntary',
            'is_active' => 'required|boolean',
        ]);

        $partnerId = $this->getPartnerId();

        // Unique check within partner scope (including global default names)
        $duplicateQuery = ExitReason::where('name', trim($this->name))
            ->where(function ($q) use ($partnerId) {
                $q->whereNull('partner_id')
                  ->orWhere('partner_id', $partnerId);
            });

        if ($this->editingId) {
            $duplicateQuery->where('id', '!=', $this->editingId);
        }

        if ($duplicateQuery->exists()) {
            $this->addError('name', 'This exit reason already exists.');
            return;
        }

        if ($this->editingId) {
            $reason = $this->exitReasonQuery($partnerId)->findOrFail($this->editingId);

            $reason->update([
                'name' => trim($this->name),
                'type' => $this->type,
                'is_active' => $this->is_active,
            ]);

            session()->flash('success', 'Exit reason updated successfully.');
        } else {
            ExitReason::create([
                'partner_id' => $this->requirePartnerId(),
                'name' => trim($this->name),
                'type' => $this->type,
                'is_active' => $this->is_active,
            ]);

            session()->flash('success', 'Exit reason added successfully.');
        }

        $this->isModalOpen = false;
    }

    public function toggleStatus($id)
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('exit_reason_edit') ||
            auth()->user()->canAccess('exitreason_update'),
            403
        );

        $partnerId = $this->getPartnerId();
        $reason = $this->exitReasonQuery($partnerId)->findOrFail($id);

        $reason->update(['is_active' => !$reason->is_active]);
        session()->flash('success', 'Exit reason status updated successfully.');
    }

    public function deleteExitReason($id)
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('exit_reason_delete') ||
            auth()->user()->canAccess('exitreason_delete'),
            403
        );

        $partnerId = $this->getPartnerId();
        $reason = $this->exitReasonQuery($partnerId)->findOrFail($id);

        // Check if exit reason is referenced in EmployeeExit
        $isUsed = EmployeeExit::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
            ->where('exit_reason', $reason->name)
            ->exists();

        if ($isUsed) {
            session()->flash('error', 'This exit reason is already being used and cannot be deleted.');
            return;
        }

        $reason->delete();
        session()->flash('success', 'Exit reason deleted successfully.');
    }

    public function render()
    {
        $partnerId = $this->getPartnerId();

        $query = $this->exitReasonQuery($partnerId);

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($this->filterType !== 'all' && !empty($this->filterType)) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus !== 'all' && $this->filterStatus !== '') {
            $query->where('is_active', $this->filterStatus === 'active' || $this->filterStatus === '1');
        }

        $reasons = $query->orderBy('name', 'asc')->paginate($this->perPage);

        return view('livewire.partner.hrms.exit-reasons', [
            'reasons' => $reasons,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'Exit Reasons',
            'pageSubtitle' => 'Manage employee voluntary and involuntary exit reasons',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
