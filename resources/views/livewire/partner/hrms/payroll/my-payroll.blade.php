<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">My Payslips</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Month & Year</th>
                        <th class="text-end">Basic Salary</th>
                        <th class="text-end">Allowances</th>
                        <th class="text-end">Deductions</th>
                        <th class="text-end">Bonuses</th>
                        <th class="text-end">Net Pay</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                    <tbody>
                        @forelse($payrolls as $payroll)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="fw-bold text-dark fs-6">
                                        <i class="bi bi-calendar-event text-primary me-2"></i>
                                        {{ date("F", mktime(0, 0, 0, $payroll->month, 1)) }} {{ $payroll->year }}
                                    </div>
                                    <div class="small text-muted mt-1">Generated: {{ $payroll->created_at->format('M d, Y') }}</div>
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
                                    -₹{{ number_format($payroll->deductions, 2) }}
                                    @if(isset($payroll->deductions_breakdown['salary_advance']) && $payroll->deductions_breakdown['salary_advance'] > 0)
                                        <div class="small mt-1" style="font-size: 10px; color: #dc3545;"><i class="bi bi-info-circle me-1"></i>Advance: ₹{{ number_format($payroll->deductions_breakdown['salary_advance'], 2) }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-end text-success">
                                    +₹{{ number_format($payroll->bonuses, 2) }}
                                </td>
                                <td class="px-4 py-3 text-end fw-bold text-dark fs-6 bg-light bg-opacity-50 border-start border-end">
                                    ₹{{ number_format($payroll->net_pay, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($payroll->status == 'paid')
                                        <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle me-1"></i> Paid</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-3 py-2"><i class="bi bi-clock me-1"></i> Pending</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-end">
                                    @if($payroll->status == 'paid')
                                        <a href="{{ route('partner.hrms.payslip.download', $payroll->id) }}" target="_blank" class="btn btn-sm btn-outline-primary px-3 shadow-sm">
                                            <i class="bi bi-download me-1"></i> Download
                                        </a>
                                    @else
                                        <span class="text-muted small">Available once paid</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="bi bi-receipt fs-2"></i></div>
                                    <p class="mb-0 fw-bold">No payslips found.</p>
                                    <p class="small">Your salary slips will appear here once they are generated by HR.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
            </table>
        </div>
        @if($payrolls->hasPages())
            <div class="card-footer">
                {{ $payrolls->links() }}
            </div>
        @endif
    </div>
</div>
