<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center border-0 pb-0">
            <h5 class="mb-0">Salary Management</h5>
        </div>
        
        <div class="card-body pb-0">
            @include('partials.hrms-filters', ['viewAnyPermission' => 'salary_viewAny'])
        </div>
            @if (session()->has('message'))
                <div class="alert alert-success m-3 d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                    <div>{{ session('message') }}</div>
                </div>
            @endif

        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th class="text-end">Basic Salary</th>
                        <th class="text-end">Gross Salary</th>
                        <th class="text-end">Net Salary</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            @php
                                $structure = $structures->get($employee->id);
                                $basic = (float)($structure ? $structure->basic_salary : ($employee->basic_salary ?: 0));
                                $allowancesList = ($structure && is_array($structure->allowances)) ? $structure->allowances : [];
                                $totalAllowances = array_sum(array_map('floatval', $allowancesList));
                                $perfIncentive = (float)($allowancesList['performance_incentive'] ?? 0);
                                $salesIncentive = (float)($allowancesList['sales_incentive'] ?? 0);
                                $totalIncentives = $perfIncentive + $salesIncentive;
                                $gross = $structure ? (float)$structure->gross_salary : ($basic + $totalAllowances);
                                
                                $deductionsList = ($structure && is_array($structure->deductions)) ? $structure->deductions : [];
                                $totalDeductions = array_sum(array_map('floatval', $deductionsList));
                                $net = $structure ? (float)$structure->net_salary : ($gross - $totalDeductions);
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                            {{ substr($employee->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $employee->name }}</div>
                                            <div class="small text-muted">{{ $employee->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-muted">
                                    {{ $employee->department ? $employee->department->name : 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-end fw-bold text-dark fs-6">
                                    ₹{{ number_format($basic, 2) }}
                                </td>
                                <td class="px-4 py-3 text-end">
                                    @php
                                        $perfIncentive = (float)($allowancesList['performance_incentive'] ?? 0);
                                        $salesIncentive = (float)($allowancesList['sales_incentive'] ?? 0);
                                        $totalIncentives = $perfIncentive + $salesIncentive;
                                        $incentiveAmount = $totalIncentives > 0 ? $totalIncentives : $totalAllowances;
                                    @endphp
                                    <div class="fw-bold text-success fs-6">
                                        +₹{{ number_format($incentiveAmount, 2) }}
                                    </div>
                                    <div class="text-muted extra-small" style="font-size: 0.72rem; line-height: 1.25;">
                                        @if($perfIncentive > 0 && $salesIncentive > 0)
                                            ₹{{ number_format($perfIncentive, 0) }} Performance + ₹{{ number_format($salesIncentive, 0) }} Incentive
                                        @elseif($perfIncentive > 0)
                                            ₹{{ number_format($perfIncentive, 0) }} Performance Incentive
                                        @elseif($salesIncentive > 0)
                                            ₹{{ number_format($salesIncentive, 0) }} Sales Incentive
                                        @elseif($totalAllowances > 0)
                                            ₹{{ number_format($totalAllowances, 0) }} Performance & Allowance
                                        @else
                                            ₹0 Performance + ₹0 Incentive
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-end fw-bold text-primary fs-6">
                                    ₹{{ number_format($net, 2) }}
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <div class="d-flex justify-content-end align-items-center">
                                        <button wire:click="viewSalary('{{ $employee->id }}')" class="btn btn-sm btn-info px-3 shadow-sm text-white me-2">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        @if(auth()->user()->isPartner() || auth()->user()->canAccess('salary_update'))
                                        <button wire:click="editSalary('{{ $employee->id }}')" class="btn btn-sm btn-primary px-3 shadow-sm">
                                            <i class="bi bi-pencil-square"></i> Manage Structure
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="bi bi-wallet2 fs-2"></i></div>
                                    <p class="mb-0 fw-bold">No employees found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
            </table>
        </div>
        @if($employees->hasPages())
            <div class="card-footer">
                {{ $employees->links() }}
            </div>
        @endif
    </div>

    <!-- Edit Modal -->
    @if($showModal)
    <div class="modal fade show" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);" aria-modal="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-bottom py-3 px-4 bg-white d-flex justify-content-between align-items-center">
                    <h5 class="modal-title fw-bold text-dark mb-0">
                        <i class="bi bi-cash-stack me-2 text-primary"></i> Salary Structure: <span class="text-primary">{{ $employeeName }}</span>
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeEdit"></button>
                </div>
                
                <div class="modal-body p-4 bg-light">
                    
                    <!-- Live Salary Calculation Summary Banner -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #f0fdf4 0%, #eff6ff 100%); border: 1.5px solid #86efac !important;">
                        <div class="card-body p-3.5">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 rounded-pill px-3 py-1.5 fw-bold" style="font-size: 0.78rem;">
                                    <i class="bi bi-calculator me-1.5"></i> Live Gross & Net Salary Calculation Preview
                                </span>
                                <div class="small fw-bold text-dark">
                                    Calculation: <span class="text-secondary">Base (₹{{ number_format((float)$basic_salary, 0) }})</span> + <span class="text-success">Allowances (₹{{ number_format($this->totalAllowances, 0) }})</span> = <span class="text-success fw-bolder">Gross (₹{{ number_format($this->grossSalary, 0) }})</span>
                                </div>
                            </div>

                            <div class="row g-3 text-center align-items-stretch mt-1">
                                <div class="col-md-3 col-6">
                                    <div class="bg-white rounded-3 p-2.5 shadow-sm h-100 border">
                                        <div class="text-muted extra-small fw-bold text-uppercase" style="font-size: 0.7rem;">Basic Salary</div>
                                        <div class="fw-bold fs-5 text-dark mt-1">₹{{ number_format((float)$basic_salary, 2) }}</div>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary extra-small rounded-pill px-2 py-0.5 mt-1">Fixed Base Wage</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="bg-white rounded-3 p-2.5 shadow-sm h-100 border">
                                        <div class="text-muted extra-small fw-bold text-uppercase" style="font-size: 0.7rem;">Total Allowances & Incentives</div>
                                        <div class="fw-bold fs-5 text-success mt-1">+₹{{ number_format($this->totalAllowances, 2) }}</div>
                                        @if($this->totalIncentives > 0)
                                            <span class="badge bg-info bg-opacity-10 text-info extra-small rounded-pill px-2 py-0.5 mt-1" title="Performance & Sales incentives">
                                                ₹{{ number_format($this->totalIncentives, 0) }} Incentives
                                            </span>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success extra-small rounded-pill px-2 py-0.5 mt-1">Allowances</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="bg-white rounded-3 p-2.5 shadow-sm h-100 border" style="border: 1.5px solid #86efac !important; background: #f0fdf4 !important;">
                                        <div class="text-success extra-small fw-bold text-uppercase" style="font-size: 0.7rem;">Calculated Gross Salary</div>
                                        <div class="fw-bold fs-5 text-success mt-1">₹{{ number_format($this->grossSalary, 2) }}</div>
                                        <div class="text-muted extra-small" style="font-size: 0.68rem;">
                                            (₹{{ number_format((float)$basic_salary, 0) }} + ₹{{ number_format($this->totalAllowances, 0) }})
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="bg-white rounded-3 p-2.5 shadow-sm h-100 border" style="border: 1.5px solid #93c5fd !important; background: #eff6ff !important;">
                                        <div class="text-primary extra-small fw-bold text-uppercase" style="font-size: 0.7rem;">Net Take-Home</div>
                                        <div class="fw-bold fs-5 text-primary mt-1">₹{{ number_format($this->netSalary, 2) }}</div>
                                        <div class="text-muted extra-small" style="font-size: 0.68rem;">
                                            (-₹{{ number_format($this->totalDeductions, 0) }} Deductions)
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($this->totalAllowances > 0)
                            <div class="mt-2.5 p-2 bg-white bg-opacity-80 rounded-3 border border-success border-opacity-25 small d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-1.5 text-muted extra-small">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <span><strong>Gross Salary Formula:</strong> Basic (₹{{ number_format((float)$basic_salary, 2) }}) + Allowances (₹{{ number_format($this->totalAllowances, 2) }}) = <strong class="text-success">₹{{ number_format($this->grossSalary, 2) }}</strong></span>
                                </div>
                                @if($this->totalIncentives > 0)
                                    <span class="badge bg-warning bg-opacity-20 text-dark border border-warning border-opacity-30 rounded-pill px-2.5 py-1 extra-small fw-semibold">
                                        <i class="bi bi-trophy-fill text-warning me-1"></i> ₹{{ number_format($this->totalIncentives, 2) }} Active Performance/Incentive
                                    </span>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Basic Salary Section -->
                        <div class="col-12">
                            <div class="card shadow-sm border-0 rounded-4">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-piggy-bank text-warning me-2"></i> Core Compensation & Commissions</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-bold">SALARY TYPE</label>
                                            <select class="form-select form-select-lg fw-bold" wire:model.live="salary_type" {{ $viewMode ? 'disabled' : '' }}>
                                                <option value="base_plus_target">Base Salary + Target Commission</option>
                                                <option value="commission_only">Commission Only (Minimum Target Base)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-bold">BASIC SALARY (₹) <span class="text-danger">*</span></label>
                                            <input type="number" wire:model.live="basic_salary" class="form-control form-control-lg fw-bold" step="0.01" placeholder="e.g. 10000" {{ $viewMode ? 'disabled' : '' }}>
                                            @error('basic_salary') <span class="text-danger small">{{ $message }}</span> @enderror
                                            @if($salary_type === 'commission_only')
                                                <small class="text-muted" style="font-size:0.7rem;">(Optional basic payout if min target not met)</small>
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small fw-bold">MONTHLY BUSINESS TARGET (₹)</label>
                                            <input type="number" wire:model.live="monthly_target" class="form-control" step="0.01" {{ $viewMode ? 'disabled' : '' }}>
                                            <small class="text-muted" style="font-size:0.7rem;">Target to achieve before commissions apply</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small fw-bold">MERCHANT TARGET</label>
                                            <input type="number" wire:model.live="merchant_target" class="form-control" step="0.01" {{ $viewMode ? 'disabled' : '' }}>
                                            <small class="text-muted" style="font-size:0.7rem;">Merchant target count/volume</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small fw-bold">COMMISSION (%)</label>
                                            <input type="number" wire:model.live="commission_percent" class="form-control" step="0.01" {{ $viewMode ? 'disabled' : '' }}>
                                            <small class="text-muted" style="font-size:0.7rem;">Earned on business *above* target</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small fw-bold">RECOVERY BUSINESS (%)</label>
                                            <input type="number" wire:model.live="recovery_percent" class="form-control" step="0.01" {{ $viewMode ? 'disabled' : '' }}>
                                            <small class="text-muted" style="font-size:0.7rem;">Commission on collected old payments</small>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label text-muted small fw-bold">COMMISSION HIERARCHY LEVEL (Optional)</label>
                                            <select class="form-select" wire:model="commission_level_id" {{ $viewMode ? 'disabled' : '' }}>
                                                <option value="">No Override Level (Direct Commission Only)</option>
                                                @foreach($commissionLevels as $level)
                                                    <option value="{{ $level->id }}">{{ $level->level_name }} ({{ $level->commission_percent }}%) - Order #{{ $level->level_order }}</option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted" style="font-size:0.7rem;">Assign a level to enable hierarchical commissions for this employee.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Allowances Section -->
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0 rounded-4 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold mb-0 text-success"><i class="bi bi-plus-circle-fill me-2"></i> Allowances (Earnings)</h6>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 extra-small fw-bold">
                                            Total: +₹{{ number_format($this->totalAllowances, 2) }}
                                        </span>
                                    </div>
                                    
                                    <div class="row g-2.5">
                                        @php
                                            $allowanceLabels = [
                                                'hra' => 'House Rent Allowance (HRA)',
                                                'da' => 'Dearness Allowance (DA)',
                                                'conveyance' => 'Conveyance Allowance',
                                                'medical' => 'Medical Allowance',
                                                'special' => 'Special Allowance',
                                                'travel' => 'Travel Allowance',
                                                'internet' => 'Internet/Mobile Allowance',
                                                'food' => 'Food Allowance',
                                                'performance_incentive' => 'Performance Incentive (Target Bonus)',
                                                'sales_incentive' => 'Sales Incentive (Revenue Reward)',
                                                'bonus' => 'Bonus',
                                                'overtime' => 'Overtime Allowance',
                                                'shift' => 'Shift Allowance',
                                                'other' => 'Other Allowances',
                                            ];
                                        @endphp
                                        @foreach($allowanceLabels as $key => $label)
                                        <div class="col-12">
                                            <div class="p-2 rounded-3 {{ in_array($key, ['performance_incentive', 'sales_incentive']) ? 'bg-warning bg-opacity-10 border border-warning border-opacity-25' : 'bg-light border-0' }} d-flex align-items-center justify-content-between">
                                                <div class="text-secondary small fw-medium {{ in_array($key, ['performance_incentive', 'sales_incentive']) ? 'text-dark fw-bold' : '' }}">
                                                    @if(in_array($key, ['performance_incentive', 'sales_incentive']))
                                                        <i class="bi bi-star-fill text-warning me-1"></i>
                                                    @endif
                                                    {{ $label }}
                                                </div>
                                                <div class="input-group input-group-sm" style="width: 140px;">
                                                    <span class="input-group-text bg-white">₹</span>
                                                    <input type="number" wire:model.live="allowances.{{ $key }}" class="form-control text-end fw-semibold" step="0.01" placeholder="0.00" {{ $viewMode ? 'disabled' : '' }}>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Deductions Section -->
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0 rounded-4 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold mb-0 text-danger"><i class="bi bi-dash-circle-fill me-2"></i> Deductions</h6>
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2.5 py-1 extra-small fw-bold">
                                            Total: -₹{{ number_format($this->totalDeductions, 2) }}
                                        </span>
                                    </div>
                                    
                                    <div class="row g-2.5">
                                        @php
                                            $deductionLabels = [
                                                'pf' => 'Employee PF',
                                                'esi' => 'Employee ESI',
                                                'pt' => 'Professional Tax (PT)',
                                                'tds' => 'TDS',
                                                'lwf' => 'Labour Welfare Fund (LWF)',
                                                'notice_period' => 'Notice Period Recovery',
                                                'other' => 'Other Deductions',
                                            ];
                                        @endphp
                                        @foreach($deductionLabels as $key => $label)
                                        <div class="col-12">
                                            <div class="p-2 rounded-3 bg-light d-flex align-items-center justify-content-between">
                                                <div class="text-secondary small fw-medium">{{ $label }}</div>
                                                <div class="input-group input-group-sm" style="width: 140px;">
                                                    <span class="input-group-text bg-white">₹</span>
                                                    <input type="number" wire:model.live="deductions.{{ $key }}" class="form-control text-end fw-semibold" step="0.01" placeholder="0.00" {{ $viewMode ? 'disabled' : '' }}>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-top py-3 px-4 bg-white d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        <strong>Gross Total:</strong> ₹{{ number_format($this->grossSalary, 2) }} &bull; <strong>Net Take-Home:</strong> ₹{{ number_format($this->netSalary, 2) }}
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4" wire:click="closeEdit">{{ $viewMode ? 'Close' : 'Cancel' }}</button>
                        @if(!$viewMode)
                        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" wire:click="saveSalary">Save Salary Structure</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
