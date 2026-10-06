<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmployeeProbation;
use App\Models\User;
use Carbon\Carbon;

class EmployeeProbations extends Component
{
    use WithPagination, HasPartnerId;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $search = '';
    public $statusFilter = '';

    // Form Fields
    public $probationId = null;
    public $isEditMode = false;
    public $employee_id = '';
    public $start_date = '';
    public $confirmation_due_date = '';
    public $extended_due_date = '';
    public $is_extended = false;
    public $extension_reason = '';
    public $asset_allocation = '';
    public $selectedAssetId = '';
    public $selectedAssets = [];
    public $status = 'on_probation';
    public $confirmation_date = '';
    public $evaluation_notes = '';

    // Modals
    public $isFormModalOpen = false;
    public $isViewModalOpen = false;
    public $viewProbation = null;

    protected function rules()
    {
        return [
            'employee_id'           => 'required|exists:users,id',
            'start_date'            => 'required|date',
            'confirmation_due_date' => 'required|date|after_or_equal:start_date',
            'extended_due_date'      => 'nullable|date|after_or_equal:confirmation_due_date',
            'is_extended'           => 'boolean',
            'extension_reason'      => 'nullable|string',
            'asset_allocation'      => 'nullable|string',
            'status'                => 'required|in:on_probation,extended,confirmed,rejected_failed',
            'confirmation_date'      => 'nullable|date',
            'evaluation_notes'      => 'nullable|string',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('probation_viewAny') ||
            auth()->user()->canAccess('probation_viewBranch') ||
            auth()->user()->canAccess('probation_viewTeam') ||
            auth()->user()->canAccess('probation_viewOwn'),
            403
        );
    }

    public function render()
    {
        $user = auth()->user();
        $query = EmployeeProbation::query()->with(['employee', 'creator']);

        $query->where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } });
        
        $allowedIds = $this->getTeamEmployeeIds('probation_viewAny');
        $query->where(function ($q) use ($allowedIds) {
            $q->whereIn('employee_id', $allowedIds)
              ->orWhereIn('created_by', $allowedIds);
        });

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee', function ($eq) use ($search) {
                    $eq->where('name', 'like', "%{$search}%")
                       ->orWhere('employee_code', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('asset_allocation', 'like', "%{$search}%")
                ->orWhere('extension_reason', 'like', "%{$search}%")
                ->orWhere('evaluation_notes', 'like', "%{$search}%");
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $probations = $query->orderBy('created_at', 'desc')->paginate(10);

        // Metrics Summary Calculation
        $baseMetricsQuery = EmployeeProbation::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } });
        $totalOnProbation = (clone $baseMetricsQuery)->whereIn('status', ['on_probation', 'extended'])->count();
        $totalExtended    = (clone $baseMetricsQuery)->where(function($q) {
            $q->where('is_extended', true)->orWhere('status', 'extended');
        })->count();
        $totalDueSoon     = (clone $baseMetricsQuery)->whereIn('status', ['on_probation', 'extended'])
            ->where(function($q) {
                $q->whereBetween('confirmation_due_date', [Carbon::now()->subDays(30), Carbon::now()->addDays(14)])
                  ->orWhere(function($sq) {
                      $sq->whereNotNull('extended_due_date')
                         ->whereBetween('extended_due_date', [Carbon::now()->subDays(30), Carbon::now()->addDays(14)]);
                  });
            })->count();
        $totalConfirmed   = (clone $baseMetricsQuery)->where('status', 'confirmed')->count();

        // Team members available for probation assignment
        $employees = User::where('role', 'employee')->orderBy('name')->get();

        // Assets available for allocation dropdown
        $assetsForAllocation = \App\Models\Asset::where('status', 'available')->orderBy('name')->get();

return view('livewire.partner.hrms.employee-probations', [
            'probations'        => $probations,
            'employees'         => $employees,
            'totalOnProbation'  => $totalOnProbation,
            'totalExtended'     => $totalExtended,
            'totalDueSoon'      => $totalDueSoon,
            'totalConfirmed'    => $totalConfirmed,
            'assetsForAllocation' => $assetsForAllocation,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'Employee Probation & Asset Tracking',
            'pageSubtitle' => 'Track employees on probation, asset allocation, confirmation due dates, extended probation, and confirmed staff',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }

    public function createProbation()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('probation_create'), 403);
        $this->resetValidation();
              $this->reset(['probationId', 'isEditMode', 'employee_id', 'start_date', 'confirmation_due_date', 'extended_due_date', 'is_extended', 'extension_reason', 'asset_allocation', 'confirmation_date', 'evaluation_notes', 'selectedAssets', 'selectedAssetId']);
        $this->status = 'on_probation';
        $this->start_date = date('Y-m-d');
        $this->confirmation_due_date = date('Y-m-d', strtotime('+90 days'));
        $this->isFormModalOpen = true;
    }

    public function editProbation($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('probation_update'), 403);
        $prob = EmployeeProbation::findOrFail($id);

        $this->resetValidation();
        $this->probationId = $prob->id;
        $this->isEditMode = true;
        $this->employee_id = (string) $prob->employee_id;
        $this->start_date = $prob->start_date ? $prob->start_date->format('Y-m-d') : '';
        $this->confirmation_due_date = $prob->confirmation_due_date ? $prob->confirmation_due_date->format('Y-m-d') : '';
        $this->extended_due_date = $prob->extended_due_date ? $prob->extended_due_date->format('Y-m-d') : '';
        $this->is_extended = (bool) $prob->is_extended;
        $this->extension_reason = $prob->extension_reason;
        $this->asset_allocation = $prob->asset_allocation;
                $this->selectedAssets = [];
        if ($prob->asset_allocation) {
            foreach (explode(',', $prob->asset_allocation) as $item) {
                $item = trim($item);
                if ($item !== '') {
                    $this->selectedAssets[] = ['id' => null, 'label' => $item];
                }
            }
        }
        $this->status = (string) $prob->status;
        $this->confirmation_date = $prob->confirmation_date ? $prob->confirmation_date->format('Y-m-d') : '';
        $this->evaluation_notes = $prob->evaluation_notes;

        $this->isFormModalOpen = true;
    }

    public function viewProbationDetails($id)
    {
        $this->viewProbation = EmployeeProbation::with(['employee', 'creator', 'partner'])->findOrFail($id);
        $this->isViewModalOpen = true;
    }

        public function addAssetToAllocation()
    {
        if (!$this->selectedAssetId) return;

        $asset = \App\Models\Asset::find($this->selectedAssetId);
        if (!$asset) return;

        $label = $asset->asset_code . ' - ' . $asset->name;

        $exists = collect($this->selectedAssets)->contains('id', $asset->id);
        if (!$exists) {
            $this->selectedAssets[] = ['id' => $asset->id, 'label' => $label];
            $this->asset_allocation = collect($this->selectedAssets)->pluck('label')->implode(', ');
        }

        $this->selectedAssetId = '';
    }

    public function removeAssetFromAllocation($index)
    {
        unset($this->selectedAssets[$index]);
        $this->selectedAssets = array_values($this->selectedAssets);
        $this->asset_allocation = collect($this->selectedAssets)->pluck('label')->implode(', ');
    }

    public function saveProbation()
    {
        $user = auth()->user();
        abort_unless($user->isPartner() || auth()->user()->canAccess($this->isEditMode ? 'probation_update' : 'probation_create'), 403);

        $this->validate();

        // Auto-set confirmation date if status is confirmed and date not set
        if ($this->status === 'confirmed' && empty($this->confirmation_date)) {
            $this->confirmation_date = date('Y-m-d');
        }

        // Auto-flag extension if extended_due_date or status is extended
        if ($this->status === 'extended' || !empty($this->extended_due_date)) {
            $this->is_extended = true;
        }

        if ($this->isEditMode && $this->probationId) {
            $prob = EmployeeProbation::findOrFail($this->probationId);
            $prob->update([
                'employee_id'           => $this->employee_id,
                'start_date'            => $this->start_date,
                'confirmation_due_date' => $this->confirmation_due_date,
                'extended_due_date'     => $this->extended_due_date ?: null,
                'is_extended'           => $this->is_extended,
                'extension_reason'      => $this->extension_reason,
                'asset_allocation'      => $this->asset_allocation,
                'status'                => $this->status,
                'confirmation_date'     => $this->confirmation_date ?: null,
                'evaluation_notes'      => $this->evaluation_notes,
            ]);
            session()->flash('success', 'Probation record updated successfully.');
        } else {
            EmployeeProbation::create([
                'employee_id'           => $this->employee_id,
                'created_by'            => auth()->id(),
                'start_date'            => $this->start_date,
                'confirmation_due_date' => $this->confirmation_due_date,
                'extended_due_date'     => $this->extended_due_date ?: null,
                'is_extended'           => $this->is_extended,
                'extension_reason'      => $this->extension_reason,
                'asset_allocation'      => $this->asset_allocation,
                'status'                => $this->status,
                'confirmation_date'     => $this->confirmation_date ?: null,
                'evaluation_notes'      => $this->evaluation_notes,
            ]);
            session()->flash('success', 'Probation record created successfully.');
        }

        $this->closeModals();
    }

    public function deleteProbation($id)
    {
        $user = auth()->user();
        abort_unless($user->isPartner() || $user->canAccess('probation_delete'), 403);

        $prob = EmployeeProbation::findOrFail($id);
        $prob->delete();

        session()->flash('success', 'Probation record deleted successfully.');
    }

    public function closeModals()
    {
        $this->isFormModalOpen = false;
        $this->isViewModalOpen = false;
               $this->reset(['probationId', 'isEditMode', 'employee_id', 'start_date', 'confirmation_due_date', 'extended_due_date', 'is_extended', 'extension_reason', 'asset_allocation', 'confirmation_date', 'evaluation_notes', 'viewProbation', 'selectedAssets', 'selectedAssetId']);
    }
}
