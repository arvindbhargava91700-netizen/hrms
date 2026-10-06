<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Payroll Approval</h5>
            <span class="badge bg-warning text-dark">{{ $pendingPayrolls->total() }} Pending</span>
        </div>
        <div class="card-body p-0">
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
                        <th class="text-end">Net Pay</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                    <tbody>
                        @forelse($pendingPayrolls as $payroll)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="fw-bold text-dark">{{ $payroll->employee->name }}</div>
                                </td>
                                <td class="px-4 py-3 text-muted fw-bold">
                                    {{ date("F", mktime(0, 0, 0, $payroll->month, 1)) }} {{ $payroll->year }}
                                </td>
                                <td class="px-4 py-3 text-end text-muted">
                                    ₹{{ number_format($payroll->basic_salary, 2) }}
                                </td>
                                <td class="px-4 py-3 text-end text-success">
                                    @php
                                        $totalAllowances = 0;
                                        if (is_array($payroll->allowances_breakdown)) {
                                            $totalAllowances = array_sum(array_map('floatval', $payroll->allowances_breakdown));
                                        }
                                    @endphp
                                    ₹{{ number_format($totalAllowances, 2) }}
                                    @if(is_array($payroll->allowances_breakdown))
                                        @foreach($payroll->allowances_breakdown as $key => $val)
                                            @if($val > 0)
                                                <div class="small mt-1" style="font-size: 10px; color: #198754;"><i class="bi bi-info-circle me-1"></i>{{ ucwords(str_replace('_', ' ', $key)) }}: ₹{{ number_format($val, 2) }}</div>
                                            @endif
                                        @endforeach
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-end text-danger">
                                    ₹{{ number_format($payroll->deductions, 2) }}
                                    @if(isset($payroll->deductions_breakdown['salary_advance']) && $payroll->deductions_breakdown['salary_advance'] > 0)
                                        <div class="small mt-1" style="font-size: 10px; color: #dc3545;"><i class="bi bi-info-circle me-1"></i>Advance: ₹{{ number_format($payroll->deductions_breakdown['salary_advance'], 2) }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-end text-success">
                                    ₹{{ number_format($payroll->bonuses, 2) }}
                                </td>
                                <td class="px-4 py-3 text-end fw-bold text-dark fs-6 bg-light bg-opacity-50 border-start">
                                    ₹{{ number_format($payroll->net_pay, 2) }}
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <button wire:click="approvePayroll({{ $payroll->id }})" class="btn btn-sm btn-primary px-3 fw-bold shadow-sm">
                                        <i class="bi bi-check2-circle"></i> Mark Paid
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="bi bi-check2-all fs-2 text-success"></i></div>
                                    <p class="mb-0 fw-bold">All caught up!</p>
                                    <p class="small">There are no pending payrolls to approve right now.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
            </table>
        </div>
        @if($pendingPayrolls->hasPages())
            <div class="card-footer">
                {{ $pendingPayrolls->links() }}
            </div>
        @endif
    </div>
</div>
