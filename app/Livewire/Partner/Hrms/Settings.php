<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use App\Models\PartnerSetting;
use App\Models\Holiday;
use App\Models\CommissionLevel;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class Settings extends Component
{
    use WithFileUploads;

    public $holiday_name;
    public $holiday_date;
    public $holiday_branch_id = null;

    public $activeTab = 'holidays';

    // Commission Levels
    public $level_name;
    public $level_order;
    public $commission_percent;
    public $level_description;

    // Commission TDS
    public $commission_tds_percent = 5.00;

    // Payslip Setup
    public $payslip_company_name;
    public $payslip_company_address;
    public $payslip_authorized_signatory;
    public $payslip_terms_conditions;
    public $payslip_logo;
    public $payslip_signature;
    public $new_payslip_logo;
    public $new_payslip_signature;

    // Performance Configuration (Global Default Points distribution summing to 100)
    public $perf_attendance_weight = 25;
    public $perf_tasks_weight = 25;
    public $perf_merchant_target_weight = 25;
    public $perf_monthly_target_weight = 25;

    // Staff Performance Overrides & Custom Config
    public $staffSearch = '';
    public $staffDeptFilter = '';
    public $perf_staff_overrides = [];

    // Modal state for editing a particular staff member
    public $show_employee_modal = false;
    public $editing_employee_id = null;
    public $editing_employee_name = '';
    public $editing_employee_code = '';
    public $editing_employee_dept = '';
    public $editing_employee_designation = '';
    public $editing_employee_avatar = null;

    public $employee_perf_attendance_weight = 25;
    public $employee_perf_tasks_weight = 25;
    public $employee_perf_merchant_target_weight = 25;
    public $employee_perf_monthly_target_weight = 25;

    public function mount()
    {
        // Enforce permission
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);

        $partnerId = $this->getPartnerId();
        $this->commission_tds_percent = (float)(PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'commission_tds_percent')->value('value') ?? 5.00);

        // Load Payslip Settings
        $this->payslip_company_name = PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'payslip_company_name')->value('value') ?? 'Your Company Name';
        $this->payslip_company_address = PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'payslip_company_address')->value('value') ?? 'Your Company Address';
        $this->payslip_authorized_signatory = PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'payslip_authorized_signatory')->value('value') ?? 'HR Manager';
        $this->payslip_terms_conditions = PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'payslip_terms_conditions')->value('value');
        $this->payslip_logo = PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'payslip_logo')->value('value');
        $this->payslip_signature = PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'payslip_signature')->value('value');

        // Load Performance Settings
        $this->perf_attendance_weight = (int) (PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'perf_attendance_weight')->value('value') ?? 25);
        $this->perf_tasks_weight = (int) (PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'perf_tasks_weight')->value('value') ?? 25);
        $this->perf_merchant_target_weight = (int) (PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'perf_merchant_target_weight')->value('value') ?? 25);
        $this->perf_monthly_target_weight = (int) (PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'perf_monthly_target_weight')->value('value') ?? 25);

        // Load Staff Overrides
        $rawOverrides = PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'perf_staff_overrides')->value('value');
        $this->perf_staff_overrides = !empty($rawOverrides) ? json_decode($rawOverrides, true) : [];
    }

    protected function getPartnerId()
    {
        return auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
    }

    public function addHoliday()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);

        $this->validate([
            'holiday_name' => 'required|string|max:255',
            'holiday_date' => 'required|date',
            'holiday_branch_id' => 'nullable|exists:branches,id',
        ]);

        Holiday::create([
            'partner_id' => tap($this->getPartnerId(), fn ($id) => abort_unless($id, 403, 'Select a partner before creating partner-owned HRMS data.')),
            'branch_id' => $this->holiday_branch_id ?: null,
            'name' => $this->holiday_name,
            'date' => $this->holiday_date,
        ]);

        $this->reset(['holiday_name', 'holiday_date', 'holiday_branch_id']);
        session()->flash('success_holiday', 'Holiday added successfully!');
    }

    public function deleteHoliday($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);

        $holiday = Holiday::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->find($id);
        if ($holiday) {
            $holiday->delete();
            session()->flash('success_holiday', 'Holiday removed successfully!');
        }
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function addCommissionLevel()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);

        $this->validate([
            'level_name' => 'required|string|max:255',
            'level_order' => 'required|integer|min:1',
            'commission_percent' => 'required|numeric|min:0',
        ]);

        CommissionLevel::create([
            'partner_id' => tap($this->getPartnerId(), fn ($id) => abort_unless($id, 403, 'Select a partner before creating partner-owned HRMS data.')),
            'level_name' => $this->level_name,
            'level_order' => $this->level_order,
            'commission_percent' => $this->commission_percent,
            'description' => $this->level_description,
        ]);

        $this->reset(['level_name', 'level_order', 'commission_percent', 'level_description']);
        session()->flash('success_level', 'Commission Level added successfully!');
    }

    public function saveTdsSetting()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);

        $this->validate([
            'commission_tds_percent' => 'required|numeric|min:0|max:100',
        ]);

        PartnerSetting::updateOrCreate(
            ['partner_id' => tap($this->getPartnerId(), fn ($id) => abort_unless($id, 403, 'Select a partner before creating partner-owned HRMS data.')), 'key' => 'commission_tds_percent'],
            ['value' => $this->commission_tds_percent]
        );

        session()->flash('success_tds', 'Commission TDS percentage saved successfully!');
    }

    public function deleteCommissionLevel($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);

        $level = CommissionLevel::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->find($id);
        if ($level) {
            $level->delete();
            session()->flash('success_level', 'Commission Level removed successfully!');
        }
    }

    public function savePayslipSettings()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);

        $this->validate([
            'payslip_company_name' => 'required|string|max:255',
            'payslip_company_address' => 'required|string',
            'payslip_authorized_signatory' => 'required|string|max:255',
            'payslip_terms_conditions' => 'nullable|string',
            'new_payslip_logo' => 'nullable|image|max:2048|dimensions:max_width=1024,max_height=1024',
            'new_payslip_signature' => 'nullable|image|max:2048|dimensions:max_width=1024,max_height=1024',
        ], [
            'new_payslip_logo.dimensions' => 'The logo image must not exceed 1024x1024 pixels in width and height.',
            'new_payslip_signature.dimensions' => 'The signature image must not exceed 1024x1024 pixels in width and height.'
        ]);

        $partnerId = $this->getPartnerId();

        PartnerSetting::updateOrCreate(['partner_id' => tap($partnerId, fn ($id) => abort_unless($id, 403, 'Select a partner before creating partner-owned HRMS data.')), 'key' => 'payslip_company_name'], ['value' => $this->payslip_company_name]);
        PartnerSetting::updateOrCreate(['partner_id' => tap($partnerId, fn ($id) => abort_unless($id, 403, 'Select a partner before creating partner-owned HRMS data.')), 'key' => 'payslip_company_address'], ['value' => $this->payslip_company_address]);
        PartnerSetting::updateOrCreate(['partner_id' => tap($partnerId, fn ($id) => abort_unless($id, 403, 'Select a partner before creating partner-owned HRMS data.')), 'key' => 'payslip_authorized_signatory'], ['value' => $this->payslip_authorized_signatory]);
        PartnerSetting::updateOrCreate(['partner_id' => tap($partnerId, fn ($id) => abort_unless($id, 403, 'Select a partner before creating partner-owned HRMS data.')), 'key' => 'payslip_terms_conditions'], ['value' => $this->payslip_terms_conditions]);

        if ($this->new_payslip_logo) {
            $logoPath = $this->new_payslip_logo->store('payslips/logos', 'public');
            PartnerSetting::updateOrCreate(['partner_id' => tap($partnerId, fn ($id) => abort_unless($id, 403, 'Select a partner before creating partner-owned HRMS data.')), 'key' => 'payslip_logo'], ['value' => $logoPath]);
            $this->payslip_logo = $logoPath;
        }

        if ($this->new_payslip_signature) {
            $sigPath = $this->new_payslip_signature->store('payslips/signatures', 'public');
            PartnerSetting::updateOrCreate(['partner_id' => tap($partnerId, fn ($id) => abort_unless($id, 403, 'Select a partner before creating partner-owned HRMS data.')), 'key' => 'payslip_signature'], ['value' => $sigPath]);
            $this->payslip_signature = $sigPath;
        }

        $this->new_payslip_logo = null;
        $this->new_payslip_signature = null;

        session()->flash('success_payslip', 'Payslip configuration saved successfully!');
    }

    public function setPerformancePreset($attendance, $tasks, $merchant, $monthly)
    {
        $this->perf_attendance_weight = (int) $attendance;
        $this->perf_tasks_weight = (int) $tasks;
        $this->perf_merchant_target_weight = (int) $merchant;
        $this->perf_monthly_target_weight = (int) $monthly;
        $this->resetErrorBag('performance_sum');
    }

    public function adjustPerformanceWeight($field, $delta)
    {
        if (!in_array($field, ['perf_attendance_weight', 'perf_tasks_weight', 'perf_merchant_target_weight', 'perf_monthly_target_weight'])) {
            return;
        }

        $newVal = max(0, min(100, (int)$this->$field + (int)$delta));
        $this->$field = $newVal;
        $this->resetErrorBag('performance_sum');
    }

    public function savePerformanceSettings()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);

        $this->validate([
            'perf_attendance_weight'      => 'required|integer|min:0|max:100',
            'perf_tasks_weight'           => 'required|integer|min:0|max:100',
            'perf_merchant_target_weight' => 'required|integer|min:0|max:100',
            'perf_monthly_target_weight'  => 'required|integer|min:0|max:100',
        ]);

        $total = (int)$this->perf_attendance_weight + (int)$this->perf_tasks_weight + (int)$this->perf_merchant_target_weight + (int)$this->perf_monthly_target_weight;

        if ($total !== 100) {
            $this->addError('performance_sum', "Total score weightage must equal exactly 100 points! (Current sum: {$total} pts).");
            return;
        }

        $partnerId = $this->getPartnerId();

        PartnerSetting::updateOrCreate(['partner_id' => tap($partnerId, fn ($id) => abort_unless($id, 403, 'Select a partner before creating partner-owned HRMS data.')), 'key' => 'perf_attendance_weight'], ['value' => (string) $this->perf_attendance_weight]);
        PartnerSetting::updateOrCreate(['partner_id' => tap($partnerId, fn ($id) => abort_unless($id, 403, 'Select a partner before creating partner-owned HRMS data.')), 'key' => 'perf_tasks_weight'], ['value' => (string) $this->perf_tasks_weight]);
        PartnerSetting::updateOrCreate(['partner_id' => tap($partnerId, fn ($id) => abort_unless($id, 403, 'Select a partner before creating partner-owned HRMS data.')), 'key' => 'perf_merchant_target_weight'], ['value' => (string) $this->perf_merchant_target_weight]);
        PartnerSetting::updateOrCreate(['partner_id' => tap($partnerId, fn ($id) => abort_unless($id, 403, 'Select a partner before creating partner-owned HRMS data.')), 'key' => 'perf_monthly_target_weight'], ['value' => (string) $this->perf_monthly_target_weight]);

        $this->resetErrorBag('performance_sum');
        session()->flash('success_performance', 'Company Default performance score weights saved successfully! Total: 100 pts.');
    }

    public function openEmployeePerfModal($employeeId)
    {
        $employee = \App\Models\User::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('parent_id', $this->getPartnerId()); } })->with(['department', 'designation'])->find($employeeId);
        if (!$employee) return;

        $this->editing_employee_id = $employee->id;
        $this->editing_employee_name = $employee->name;
        $this->editing_employee_code = $employee->employee_code ?: ('EMP-' . substr($employee->id, 0, 4));
        $this->editing_employee_dept = $employee->department?->name ?? 'General';
        $this->editing_employee_designation = $employee->designation?->name ?? 'Staff';
        $this->editing_employee_avatar = $employee->avatar_url ?? null;

        if (isset($this->perf_staff_overrides[$employeeId])) {
            $this->employee_perf_attendance_weight = (int)($this->perf_staff_overrides[$employeeId]['attendance'] ?? $this->perf_attendance_weight);
            $this->employee_perf_tasks_weight = (int)($this->perf_staff_overrides[$employeeId]['tasks'] ?? $this->perf_tasks_weight);
            $this->employee_perf_merchant_target_weight = (int)($this->perf_staff_overrides[$employeeId]['merchant'] ?? $this->perf_merchant_target_weight);
            $this->employee_perf_monthly_target_weight = (int)($this->perf_staff_overrides[$employeeId]['monthly'] ?? $this->perf_monthly_target_weight);
        } else {
            $this->employee_perf_attendance_weight = $this->perf_attendance_weight;
            $this->employee_perf_tasks_weight = $this->perf_tasks_weight;
            $this->employee_perf_merchant_target_weight = $this->perf_merchant_target_weight;
            $this->employee_perf_monthly_target_weight = $this->perf_monthly_target_weight;
        }

        $this->resetErrorBag('employee_perf_sum');
        $this->show_employee_modal = true;
    }

    public function closeEmployeePerfModal()
    {
        $this->show_employee_modal = false;
        $this->editing_employee_id = null;
        $this->resetErrorBag('employee_perf_sum');
    }

    public function setEmployeePerfPreset($attendance, $tasks, $merchant, $monthly)
    {
        $this->employee_perf_attendance_weight = (int) $attendance;
        $this->employee_perf_tasks_weight = (int) $tasks;
        $this->employee_perf_merchant_target_weight = (int) $merchant;
        $this->employee_perf_monthly_target_weight = (int) $monthly;
        $this->resetErrorBag('employee_perf_sum');
    }

    public function adjustEmployeePerformanceWeight($field, $delta)
    {
        if (!in_array($field, ['employee_perf_attendance_weight', 'employee_perf_tasks_weight', 'employee_perf_merchant_target_weight', 'employee_perf_monthly_target_weight'])) {
            return;
        }

        $newVal = max(0, min(100, (int)$this->$field + (int)$delta));
        $this->$field = $newVal;
        $this->resetErrorBag('employee_perf_sum');
    }

    public function saveEmployeePerformanceSettings()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);

        $this->validate([
            'employee_perf_attendance_weight'      => 'required|integer|min:0|max:100',
            'employee_perf_tasks_weight'           => 'required|integer|min:0|max:100',
            'employee_perf_merchant_target_weight' => 'required|integer|min:0|max:100',
            'employee_perf_monthly_target_weight'  => 'required|integer|min:0|max:100',
        ]);

        $total = (int)$this->employee_perf_attendance_weight + (int)$this->employee_perf_tasks_weight + (int)$this->employee_perf_merchant_target_weight + (int)$this->employee_perf_monthly_target_weight;

        if ($total !== 100) {
            $this->addError('employee_perf_sum', "Total weightage for {$this->editing_employee_name} must equal exactly 100 points! (Current: {$total} pts).");
            return;
        }

        $partnerId = $this->getPartnerId();
        $overrides = $this->perf_staff_overrides;
        $overrides[$this->editing_employee_id] = [
            'attendance' => (int)$this->employee_perf_attendance_weight,
            'tasks'      => (int)$this->employee_perf_tasks_weight,
            'merchant'   => (int)$this->employee_perf_merchant_target_weight,
            'monthly'    => (int)$this->employee_perf_monthly_target_weight,
        ];

        PartnerSetting::updateOrCreate(
            ['partner_id' => tap($partnerId, fn ($id) => abort_unless($id, 403, 'Select a partner before creating partner-owned HRMS data.')), 'key' => 'perf_staff_overrides'],
            ['value' => json_encode($overrides)]
        );

        $this->perf_staff_overrides = $overrides;
        $this->show_employee_modal = false;
        session()->flash('success_staff_performance', "Custom score weightage saved for {$this->editing_employee_name}! (Total: 100 pts)");
    }

    public function resetEmployeeToDefault($employeeId)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);

        $partnerId = $this->getPartnerId();
        $overrides = $this->perf_staff_overrides;

        if (isset($overrides[$employeeId])) {
            unset($overrides[$employeeId]);

            PartnerSetting::updateOrCreate(
                ['partner_id' => tap($partnerId, fn ($id) => abort_unless($id, 403, 'Select a partner before creating partner-owned HRMS data.')), 'key' => 'perf_staff_overrides'],
                ['value' => json_encode($overrides)]
            );

            $this->perf_staff_overrides = $overrides;
            session()->flash('success_staff_performance', "Staff performance weights reset to Company Default successfully!");
        }
    }

    public function resetAllStaffToDefault()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);

        $partnerId = $this->getPartnerId();
        PartnerSetting::updateOrCreate(
            ['partner_id' => tap($partnerId, fn ($id) => abort_unless($id, 403, 'Select a partner before creating partner-owned HRMS data.')), 'key' => 'perf_staff_overrides'],
            ['value' => json_encode([])]
        );

        $this->perf_staff_overrides = [];
        session()->flash('success_staff_performance', "All staff reset to Company Default weights successfully!");
    }

    public function render()
    {
        $partnerId = $this->getPartnerId();
        $holidays = Holiday::with('branch')->where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->orderBy('date', 'desc')->get();
        $commissionLevels = CommissionLevel::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->orderBy('level_order', 'asc')->get();
        $branches = \App\Models\HrmsBranch::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->get();

        // Query Staff for Performance Configuration
        $staffQuery = \App\Models\User::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('parent_id', $partnerId); } })
            ->where('role', 'employee')
            ->with(['department', 'designation', 'branch']);

        if (!empty($this->staffSearch)) {
            $search = '%' . $this->staffSearch . '%';
            $staffQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('employee_code', 'like', $search)
                  ->orWhere('email', 'like', $search);
            });
        }

        if (!empty($this->staffDeptFilter)) {
            $staffQuery->where('department_id', $this->staffDeptFilter);
        }

        $staffMembers = $staffQuery->orderBy('name', 'asc')->get();
        $departments = \App\Models\Department::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->get();

        return view('livewire.partner.hrms.settings', [
            'holidays' => $holidays,
            'commissionLevels' => $commissionLevels,
            'branches' => $branches,
            'staffMembers' => $staffMembers,
            'departments' => $departments,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'HRMS Settings',
            'pageSubtitle' => 'Configure holidays, commissions, and payslips',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
