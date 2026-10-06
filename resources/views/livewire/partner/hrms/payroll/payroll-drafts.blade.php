<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Payroll Drafts</h5>
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
                        <th>Period</th>
                        <th class="text-end">Basic Salary</th>
                        <th class="text-end">Allowances</th>
                        <th class="text-end">Deductions</th>
                        <th class="text-end">Bonuses</th>
                        <th class="text-end">Gross Pay</th>
                        <th class="text-end">Net Pay</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                    <tbody>
                        @forelse($drafts as $draft)
                            <tr class="{{ $editingPayrollId === $draft->id ? 'bg-light bg-opacity-50' : '' }}">
                                <td class="px-4 py-3">
                                    <div class="fw-bold text-dark">{{ $draft->employee->name }}</div>
                                    <div class="small text-muted">{{ $draft->employee->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-muted fw-bold">
                                    {{ date("F", mktime(0, 0, 0, $draft->month, 1)) }} {{ $draft->year }}
                                </td>
                                <td class="px-4 py-3 text-end text-muted">
                                    ₹{{ number_format($draft->basic_salary, 2) }}
                                </td>
                                <td class="px-4 py-3 text-end text-success">
                                    @php
                                        $totalAllowances = 0;
                                        if (is_array($draft->allowances_breakdown)) {
                                            $totalAllowances = array_sum(array_map('floatval', $draft->allowances_breakdown));
                                        }
                                    @endphp
                                    ₹{{ number_format($totalAllowances, 2) }}
                                    @if(is_array($draft->allowances_breakdown))
                                        @foreach($draft->allowances_breakdown as $key => $val)
                                            @if($val > 0)
                                                <div class="small mt-1" style="font-size: 10px; color: #198754;"><i class="bi bi-info-circle me-1"></i>{{ ucwords(str_replace('_', ' ', $key)) }}: ₹{{ number_format($val, 2) }}</div>
                                            @endif
                                        @endforeach
                                    @endif
                                </td>
                                   <td class="px-4 py-3 text-end text-danger">
                                        ₹{{ number_format($draft->deductions, 2) }}
                                        @if(is_array($draft->deductions_breakdown))
                                            @foreach($draft->deductions_breakdown as $key => $val)
                                                @if($val > 0)
                                                    <div class="small mt-1" style="font-size: 10px; color: #dc3545;"><i class="bi bi-info-circle me-1"></i>{{ ucwords(str_replace('_', ' ', $key)) }}: ₹{{ number_format($val, 2) }}</div>
                                                @endif
                                            @endforeach
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-end text-success">
                                        ₹{{ number_format($draft->bonuses, 2) }}
                                    </td>
                                
                                @if($editingPayrollId === $draft->id)
                              
                                    <td class="px-4 py-3 text-end fw-bold text-success fs-6">
                                        ₹{{ number_format($draft->gross_pay, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-end fw-bold text-primary fs-6">
                                        <!-- Real-time net pay preview -->
                                        ₹{{ number_format($draft->net_pay, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <button wire:click="viewPayroll({{ $draft->id }})" class="btn btn-sm btn-light px-3 mb-2 border">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <button wire:click="cancelEdit" class="btn btn-sm btn-light px-3 border">Cancel</button>
                                    </td>
                                @else
                  
                                    <td class="px-4 py-3 text-end fw-bold text-success fs-6">
                                        ₹{{ number_format($draft->gross_pay, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-end fw-bold text-dark fs-6">
                                        ₹{{ number_format($draft->net_pay, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <button wire:click="editPayroll({{ $draft->id }}, {{ $draft->deductions }}, {{ $draft->bonuses }})" class="btn btn-sm btn-light border px-3">
                                            <i class="bi bi-sliders"></i> Adjustment
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="bi bi-file-earmark-check fs-2"></i></div>
                                    <p class="mb-0 fw-bold">No draft payrolls found.</p>
                                    <p class="small">Use "Payroll Process" to generate drafts for the month.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
            </table>
        </div>
        @if($drafts->hasPages())
            <div class="card-footer">
                {{ $drafts->links() }}
            </div>
        @endif
    </div>

    @if($viewingPayroll)
        <div class="modal fade show d-block" id="payrollDetailModal" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-1">Payroll Details — {{ $viewingPayroll->employee?->name }}</h5>
                            <div class="small text-muted">Edit any amount below — totals update automatically</div>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeView"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="small text-muted">Period</div>
                                <div class="fw-bold">{{ date("F", mktime(0, 0, 0, $viewingPayroll->month, 1)) }} {{ $viewingPayroll->year }}</div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="small text-muted">Status</div>
                                <span class="badge bg-warning text-dark">{{ ucfirst($viewingPayroll->status) }}</span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-header bg-white fw-bold text-success py-2">
                                        <i class="bi bi-plus-circle me-1"></i> Added to Salary
                                    </div>
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-center py-1">
                                            <span class="fw-bold">Basic Salary</span>
                                            <div class="input-group input-group-sm" style="width: 160px;">
                                                <span class="input-group-text bg-white text-muted">₹</span>
                                                <input type="number" wire:model.live="editBasicSalary" class="form-control text-end fw-bold border-success" step="0.01" min="0">
                                            </div>
                                        </div>

                                        <div class="rounded-3 p-2 my-2" style="background: linear-gradient(90deg, #ecfdf5, #f0fdf4); border: 1px dashed #10b981;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-success small text-uppercase" style="letter-spacing: .3px;">
                                                    <i class="bi bi-calendar2-check me-1"></i>This Month Salary
                                                </span>
                                                <span class="fw-bold text-success fs-6">
                                                    @php
                                                        $liveRateBase = array_diff_key(is_array($editAllowances) ? $editAllowances : [], ['expense_reimbursement' => 0]);
                                                        $liveDailyRate = round(((float) $editBasicSalary + array_sum(array_map('floatval', $liveRateBase))) / max(1, $monthSalaryData['days_in_month'] ?? 30), 2);
                                                        $liveMonthSalary = ($liveDailyRate * (int) $editPresentDays)
                                                            + ($liveDailyRate * 0.5 * (int) $editHalfDays)
                                                            + ($liveDailyRate * (int) $editPaidLeaveDays);
                                                    @endphp
                                                    + ₹{{ number_format($liveMonthSalary, 2) }}
                                                </span>
                                            </div>
                                            <div class="row g-2 mt-2">
                                                <div class="col-6">
                                                    <label class="form-label small mb-1 text-success">Present</label>
                                                    <input type="number" wire:model.live="editPresentDays" class="form-control form-control-sm text-center border-success fw-bold" min="0" max="31">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small mb-1 text-warning">Half Day</label>
                                                    <input type="number" wire:model.live="editHalfDays" class="form-control form-control-sm text-center border-warning fw-bold" min="0" max="31">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small mb-1 text-primary">Paid Leave</label>
                                                    <input type="number" wire:model.live="editPaidLeaveDays" class="form-control form-control-sm text-center border-primary fw-bold" min="0" max="31">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small mb-1 text-danger">Unpaid Leave</label>
                                                    <input type="number" wire:model.live="editUnpaidLeaveDays" class="form-control form-control-sm text-center border-danger fw-bold" min="0" max="31">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small mb-1 text-danger-emphasis">Absent</label>
                                                    <input type="number" wire:model.live="editAbsentDays" class="form-control form-control-sm text-center border-danger fw-bold" min="0" max="31">
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-1 mt-2">
                                                <span class="badge text-bg-success rounded-pill px-2 py-1"><i class="bi bi-person-check me-1"></i>{{ $editPresentDays }} Present</span>
                                                @if((int) $editHalfDays > 0)
                                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1"><i class="bi bi-sun-half me-1"></i>{{ $editHalfDays }} Half Day</span>
                                                @endif
                                                @if((int) $editPaidLeaveDays > 0)
                                                    <span class="badge text-bg-primary rounded-pill px-2 py-1"><i class="bi bi-suitcase me-1"></i>{{ $editPaidLeaveDays }} Paid Leave</span>
                                                @endif
                                                @if((int) $editUnpaidLeaveDays > 0)
                                                    <span class="badge text-bg-danger rounded-pill px-2 py-1"><i class="bi bi-x-circle me-1"></i>{{ $editUnpaidLeaveDays }} Unpaid Leave</span>
                                                @endif
                                                @if((int) $editAbsentDays > 0)
                                                    <span class="badge text-bg-danger rounded-pill px-2 py-1"><i class="bi bi-person-x me-1"></i>{{ $editAbsentDays }} Absent</span>
                                                @endif
                                                <span class="badge text-bg-light border rounded-pill px-2 py-1 text-muted"><i class="bi bi-tag me-1"></i>₹{{ number_format($liveDailyRate, 2) }}/day</span>
                                            </div>
                                        </div>

                                        <div class="fw-bold small text-uppercase text-muted mb-1 mt-3" style="letter-spacing: .3px;">Allowances</div>
                                        @if(is_array($editAllowances) && count($editAllowances) > 0)
                                            @foreach($editAllowances as $key => $val)
                                                @if((float) $val > 0 || $key === 'expense_reimbursement')
                                                    <div class="d-flex justify-content-between align-items-center py-1">
                                                        <span class="ps-1 small">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                                        <div class="input-group input-group-sm" style="width: 160px;">
                                                            <span class="input-group-text bg-white text-success">+</span>
                                                            <input type="number" wire:model.live="editAllowances.{{ $key }}" class="form-control text-end border-success text-success" step="0.01" min="0">
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @else
                                            <div class="small text-muted py-1">No allowances.</div>
                                        @endif

                                        @php
                                            $liveRateBase = array_diff_key(is_array($editAllowances) ? $editAllowances : [], ['expense_reimbursement' => 0]);
                                            $liveDailyRate = round(((float) $editBasicSalary + array_sum(array_map('floatval', $liveRateBase))) / max(1, $monthSalaryData['days_in_month'] ?? 30), 2);
                                            $liveMonthSalary = ($liveDailyRate * (int) $editPresentDays)
                                                + ($liveDailyRate * 0.5 * (int) $editHalfDays)
                                                + ($liveDailyRate * (int) $editPaidLeaveDays);
                                            $liveUnpaidDeduction = $liveDailyRate * (int) $editUnpaidLeaveDays;
                                            $liveAbsentDeduction = $liveDailyRate * (int) $editAbsentDays;
                                            $liveTotalAllowances = array_sum(array_map('floatval', is_array($editAllowances) ? $editAllowances : []));
                                            $liveGross = round($liveMonthSalary + $liveTotalAllowances + (float) $editBonuses, 2);
                                        @endphp
                                        <div class="d-flex justify-content-between py-1 border-top mt-1">
                                            <span class="fw-bold">Total Allowances</span>
                                            <span class="fw-bold text-success">₹{{ number_format($liveTotalAllowances, 2) }}</span>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center py-1">
                                            <span class="fw-bold">Bonuses</span>
                                            <div class="input-group input-group-sm" style="width: 160px;">
                                                <span class="input-group-text bg-white text-success">+</span>
                                                <input type="number" wire:model.live="editBonuses" class="form-control text-end border-success text-success" step="0.01" min="0">
                                            </div>
                                        </div>

                                        @php
                                            $liveGross = (float) $liveMonthSalary + $liveTotalAllowances + (float) $editBonuses;
                                        @endphp
                                        <div class="d-flex justify-content-between py-2 border-top mt-1" style="background: #f0fdf4;">
                                            <span class="fs-5 fw-bold text-dark">Total Salary</span>
                                            <span class="fs-5 fw-bold text-success">₹{{ number_format($liveGross, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-header bg-white fw-bold text-danger py-2">
                                        <i class="bi bi-dash-circle me-1"></i> Deducted from Salary
                                    </div>
                                    <div class="card-body py-2">
                                        <div class="fw-bold small text-uppercase text-muted mb-1" style="letter-spacing: .3px;">Deductions</div>
                                        <div class="d-flex justify-content-between align-items-center py-1 mb-2">
                                            <span class="ps-1 small fw-bold text-danger"><i class="bi bi-wallet2 me-1"></i>Advance Pay</span>
                                            <div class="input-group input-group-sm" style="width: 160px;">
                                                <span class="input-group-text bg-white text-danger">-</span>
                                                <input type="number" wire:model.live="editAdvancePay" class="form-control text-end border-danger text-danger" step="0.01" min="0">
                                            </div>
                                        </div>
                                        @if(is_array($editDeductions) && count($editDeductions) > 0)
                                            @foreach($editDeductions as $key => $val)
                                                @if($key !== 'salary_advance')
                                                    <div class="d-flex justify-content-between align-items-center py-1">
                                                        <span class="ps-1 small">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                                        <div class="input-group input-group-sm" style="width: 160px;">
                                                            <span class="input-group-text bg-white text-danger">-</span>
                                                            <input type="number" wire:model.live="editDeductions.{{ $key }}" class="form-control text-end border-danger text-danger" step="0.01" min="0">
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @else
                                            <div class="small text-muted py-1">No itemized deductions.</div>
                                        @endif
                                        @php
                                            $liveItemizedDeductions = 0;
                                            foreach ((is_array($editDeductions) ? $editDeductions : []) as $key => $val) {
                                                if ($key !== 'salary_advance') $liveItemizedDeductions += (float) $val;
                                            }
                                            $liveTotalDeductions = round($liveItemizedDeductions + (float) $editAdvancePay, 2);
                                            $liveTotalDeductionsWithUnpaid = round($liveTotalDeductions + $liveUnpaidDeduction + $liveAbsentDeduction, 2);
                                            $liveNet = max(0, $liveGross - $liveTotalDeductionsWithUnpaid);
                                        @endphp
                                        @if($liveUnpaidDeduction > 0)
                                            <div class="d-flex justify-content-between align-items-center py-1">
                                                <span class="ps-1 small fw-bold text-danger">Unpaid Leave</span>
                                                <span class="fw-bold text-danger">- ₹{{ number_format($liveUnpaidDeduction, 2) }}</span>
                                            </div>
                                        @endif
                                        @if($liveAbsentDeduction > 0)
                                            <div class="d-flex justify-content-between align-items-center py-1">
                                                <span class="ps-1 small fw-bold text-danger">Absent</span>
                                                <span class="fw-bold text-danger">- ₹{{ number_format($liveAbsentDeduction, 2) }}</span>
                                            </div>
                                        @endif
                                        <div class="d-flex justify-content-between py-1 border-top mt-1">
                                            <span class="fw-bold">Total Deductions</span>
                                            <span class="fw-bold text-danger">- ₹{{ number_format($liveTotalDeductionsWithUnpaid, 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 bg-light shadow-sm">
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between py-1">
                                            <span>Gross Pay</span>
                                            <span class="fw-bold">₹{{ number_format($liveGross, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between py-1 border-top mt-1">
                                            <span class="fs-5 fw-bold text-dark">Net Pay</span>
                                            <span class="fs-5 fw-bold text-primary">₹{{ number_format($liveNet, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeView">
                            <i class="bi bi-x-lg me-1"></i>Cancel</button>
                        <button type="button" class="btn btn-success px-4" wire:click="saveAdjustments">
                            <i class="bi bi-check-lg me-1"></i>Save Adjustments
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
