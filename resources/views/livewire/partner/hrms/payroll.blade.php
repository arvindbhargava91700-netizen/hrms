<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2 text-success"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body bg-light rounded-top d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <select class="form-select w-auto" wire:model.live="selectedMonth">
                    @for($m=1; $m<=12; ++$m)
                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>
                <select class="form-select w-auto" wire:model.live="selectedYear">
                    @for($y=date('Y')-1; $y<=date('Y')+1; $y++)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
            @if(Auth::user()->canAccess('payroll_create'))
            <button class="btn btn-primary btn-sm" wire:click="generatePayroll">
                <i class="bi bi-cash me-1"></i> Generate Payroll
            </button>
            @endif
        </div>
        
        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Basic Salary</th>
                        <th>Bonuses</th>
                        <th>Deductions</th>
                        <th>Net Pay</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $pr)
                        <tr>
                            <td class="fw-600 text-dark">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px; font-size:12px;">
                                        {{ substr($pr->employee->name ?? '?', 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="text-dark fw-600">{{ $pr->employee->name ?? 'Unknown' }}</div>
                                        <div class="text-muted small">{{ $pr->employee->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>₹{{ number_format($pr->basic_salary, 2) }}</td>
                            <td class="text-success">+ ₹{{ number_format($pr->bonuses, 2) }}</td>
                            <td class="text-danger">- ₹{{ number_format($pr->deductions, 2) }}</td>
                            <td class="fw-bold">₹{{ number_format($pr->net_pay, 2) }}</td>
                            <td>
                                @if($pr->status === 'paid')
                                    <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle"></i> Paid</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning"><i class="bi bi-clock"></i> Pending</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(Auth::user()->canAccess('payroll_status_update'))
                                    @if($pr->status !== 'paid')
                                        <button class="btn btn-sm btn-outline-success" wire:click="markAsPaid({{ $pr->id }})">
                                            Mark Paid
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-light" disabled>Paid</button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-cash-coin display-4 mb-3 d-block text-light"></i>
                                No payroll records for this month.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    @if($isModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="savePayroll">
                    <div class="modal-header">
                        <h5 class="modal-title">Generate Salary Slip</h5>
                        <button type="button" class="btn-close" wire:click="$set('isModalOpen', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-600">Select Employee</label>
                            <select class="form-select" wire:model.live="employeeId">
                                <option value="">Select Employee</option>
                                @foreach($staff as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                            @error('employeeId') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-600">Basic Salary (₹)</label>
                                <input type="number" step="0.01" class="form-control" wire:model.live="basicSalary">
                                @error('basicSalary') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600 text-success">Bonuses/Allowances (₹)</label>
                                <input type="number" step="0.01" class="form-control" wire:model.live="bonuses">
                                @error('bonuses') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600 text-danger">Deductions (₹)</label>
                                <input type="number" step="0.01" class="form-control" wire:model.live="deductions">
                                @error('deductions') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">Net Pay (₹)</label>
                                <input type="text" class="form-control fw-bold" readonly wire:model="netPay" style="background-color: #f8f9fa;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('isModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary" @if(!$employeeId) disabled @endif>Generate & Pay</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
