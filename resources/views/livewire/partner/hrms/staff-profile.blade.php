<div>
    <div class="row g-4">
        <!-- Sidebar / Overview -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center p-4">
                    <img src="{{ $employee->avatar_url }}" class="rounded-circle mb-3" width="100" height="100" alt="{{ $employee->name }}">
                    <h5 class="fw-bold text-dark mb-1">{{ $employee->name }}</h5>
                    <p class="text-muted mb-3">{{ $employee->designation->name ?? 'Employee' }}</p>
                    
                    @if($employee->employment_status === 'active')
                        <span class="badge bg-success px-3 py-2 rounded-pill">Active</span>
                    @elseif($employee->employment_status === 'resigned')
                        <span class="badge bg-warning px-3 py-2 rounded-pill">Resigned</span>
                    @elseif($employee->employment_status === 'terminated')
                        <span class="badge bg-danger px-3 py-2 rounded-pill">Terminated</span>
                    @else
                        <span class="badge bg-secondary px-3 py-2 rounded-pill">{{ ucfirst($employee->employment_status ?? 'Unknown') }}</span>
                    @endif
                </div>
                <div class="card-footer bg-light p-4 border-0">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Employee Code</span>
                        <span class="fw-600">{{ $employee->employee_code ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Email</span>
                        <span class="fw-600">{{ $employee->email }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Mobile</span>
                        <span class="fw-600">{{ $employee->mobile }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Joining Date</span>
                        <span class="fw-600">{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') : '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Employment Type</span>
                        <span class="fw-600">{{ ucfirst(str_replace('_', ' ', $employee->employment_type ?? 'full_time')) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Working Mode</span>
                        <span class="fw-600">{{ ucfirst(str_replace('_', ' ', $employee->working_mode ?? 'office')) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Department</span>
                        <span class="fw-600">{{ $employee->department->name ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Role</span>
                        <span class="fw-600">
                            @foreach($employee->roles as $role)
                                @if($role->name !== 'employee')
                                    {{ str_replace([$this->getPartnerId() . '_', '_' . $this->getPartnerId()], '', $role->name) }}
                                    @if(!$loop->last) , @endif
                                @endif
                            @endforeach
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Reporting To</span>
                        <span class="fw-600">{{ $employee->manager->name ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Basic Salary</span>
                        <span class="fw-600 fw-bold text-primary">₹{{ number_format($employee->basic_salary ?? 0) }}</span>
                    </div>
                </div>
        </div>

        <!-- Salary Structure Overview -->
        @if($salaryStructure)
            @php
                $totalAllowances = is_array($salaryStructure->allowances) ? array_sum($salaryStructure->allowances) : (is_numeric($salaryStructure->allowances) ? $salaryStructure->allowances : 0);
                $totalDeductions = is_array($salaryStructure->deductions) ? array_sum($salaryStructure->deductions) : (is_numeric($salaryStructure->deductions) ? $salaryStructure->deductions : 0);
            @endphp
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom pt-3 pb-2">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-wallet2 text-primary me-2"></i>Salary Details</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Salary Type</span>
                        <span class="fw-bold small">{{ ucfirst(str_replace('_', ' ', $salaryStructure->salary_type)) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Basic Salary</span>
                        <span class="fw-bold small">₹{{ number_format($salaryStructure->basic_salary, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Allowances</span>
                        <span class="fw-bold text-success small">+ ₹{{ number_format($totalAllowances, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Deductions</span>
                        <span class="fw-bold text-danger small">- ₹{{ number_format($totalDeductions, 2) }}</span>
                    </div>
                    <hr class="my-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-dark small mb-0">Net Salary</span>
                        <span class="fw-bold text-primary mb-0">₹{{ number_format($salaryStructure->net_salary, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom pt-3 pb-2">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bullseye text-success me-2"></i>Target & Commission</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Monthly Target</span>
                        <span class="fw-bold small">₹{{ number_format($salaryStructure->monthly_target, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Base Commission</span>
                        <span class="fw-bold small">{{ $salaryStructure->commission_percent }}%</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Recovery Commission</span>
                        <span class="fw-bold small">{{ $salaryStructure->recovery_percent }}%</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

        <!-- Main Content -->
        <div class="col-lg-8">
            
            <!-- Performance Overview -->
            <div class="row g-3 mb-4">
                <div class="col-sm-4">
                    <div class="card border-0 shadow-sm h-100 bg-primary bg-gradient text-white">
                        <div class="card-body p-3">
                            <div class="text-white-50 small fw-600 text-uppercase mb-1">Current Target</div>
                            <h4 class="mb-0 fw-bold">₹{{ number_format($metrics['target_required'] ?? 0) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card border-0 shadow-sm h-100 bg-success bg-gradient text-white">
                        <div class="card-body p-3">
                            <div class="text-white-50 small fw-600 text-uppercase mb-1">Achieved (New Biz)</div>
                            <h4 class="mb-0 fw-bold">₹{{ number_format($metrics['new_business'] ?? 0) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card border-0 shadow-sm h-100 bg-dark text-white">
                        <div class="card-body p-3">
                            <div class="text-white-50 small fw-600 text-uppercase mb-1">Est. Total Earning</div>
                            <h4 class="mb-0 fw-bold text-warning">₹{{ number_format($metrics['est_total_earning'] ?? 0) }}</h4>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Tabs Section -->
            <style>
                #profileTabs .nav-link {
                    color: #6c757d;
                    border-bottom-color: transparent !important;
                }
                #profileTabs .nav-link.active {
                    color: var(--bs-primary) !important;
                    border-bottom-color: var(--bs-primary) !important;
                }
            </style>
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom pt-3 pb-0">
                    <ul class="nav nav-tabs border-0" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-600 border-0 border-bottom border-3 bg-transparent px-4 pb-3" id="leads-tab" data-bs-toggle="tab" data-bs-target="#leads" type="button" role="tab" aria-controls="leads" aria-selected="true">
                                <i class="bi bi-funnel me-1"></i> Leads ({{ count($leads) }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-600 border-0 border-bottom border-3 bg-transparent px-4 pb-3" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab" aria-controls="orders" aria-selected="false">
                                <i class="bi bi-cart me-1"></i> Orders ({{ count($orders) }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-600 border-0 border-bottom border-3 bg-transparent px-4 pb-3" id="visits-tab" data-bs-toggle="tab" data-bs-target="#visits" type="button" role="tab" aria-controls="visits" aria-selected="false">
                                <i class="bi bi-geo-alt me-1"></i> Visits ({{ count($visits) }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-600 border-0 border-bottom border-3 bg-transparent px-4 pb-3" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button" role="tab" aria-controls="tasks" aria-selected="false">
                                <i class="bi bi-check2-square me-1"></i> Tasks ({{ count($tasks) }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-600 border-0 border-bottom border-3 bg-transparent px-4 pb-3" id="expenses-tab" data-bs-toggle="tab" data-bs-target="#expenses" type="button" role="tab" aria-controls="expenses" aria-selected="false">
                                <i class="bi bi-receipt me-1"></i> Expenses ({{ count($expenses) }})
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-600 border-0 border-bottom border-3 bg-transparent px-4 pb-3" id="leaves-tab" data-bs-toggle="tab" data-bs-target="#leaves" type="button" role="tab" aria-controls="leaves" aria-selected="false">
                                <i class="bi bi-calendar-minus me-1"></i> Leaves
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content" id="profileTabsContent">
                        
                        <!-- Leads Tab -->
                        <div class="tab-pane fade show active" id="leads" role="tabpanel" aria-labelledby="leads-tab">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Lead Name</th>
                                            <th>Contact</th>
                                            <th>Status</th>
                                            <th class="pe-4">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($leads as $lead)
                                        <tr>
                                            <td class="ps-4 fw-600 text-dark">{{ $lead->customer_name }}</td>
                                            <td>
                                                <div class="small">{{ $lead->customer_mobile }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $lead->status ?? 'New' }}</span>
                                            </td>
                                            <td class="pe-4 text-muted small">
                                                {{ $lead->created_at->format('d M, Y') }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-3 text-light"></i>
                                                No leads assigned yet.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Orders Tab -->
                        <div class="tab-pane fade" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Lead</th>
                                            <th>Total Amount</th>
                                            <th>Paid</th>
                                            <th>Approval Status</th>
                                            <th class="pe-4">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($orders as $order)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-600 text-dark">{{ $order->lead?->customer_name ?? 'Unknown Lead' }}</div>
                                            </td>
                                            <td class="fw-bold">₹{{ number_format($order->final_amount ?? $order->total_amount, 2) }}</td>
                                            <td class="text-success">₹{{ number_format($order->paid_amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $order->approval_status === 'completed' ? 'success' : ($order->approval_status === 'rejected' ? 'danger' : 'warning text-dark') }}">
                                                    {{ ucfirst($order->approval_status) }}
                                                </span>
                                            </td>
                                            <td class="pe-4 text-muted small">
                                                {{ $order->created_at->format('d M, Y') }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="bi bi-cart fs-1 d-block mb-3 text-light"></i>
                                                No orders created yet.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Visits Tab -->
                        <div class="tab-pane fade" id="visits" role="tabpanel" aria-labelledby="visits-tab">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Visit Date</th>
                                            <th>Lead / Customer</th>
                                            <th>Location</th>
                                            <th class="pe-4">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($visits as $visit)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-600 text-dark">{{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('d M, Y h:i A') : $visit->created_at->format('d M, Y h:i A') }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-600">{{ $visit->lead->customer_name ?? 'General Visit' }}</div>
                                                <div class="small text-muted">{{ $visit->purpose }}</div>
                                            </td>
                                            <td>
                                                <div class="small text-muted text-truncate" style="max-width: 200px;"><i class="bi bi-geo-alt"></i> {{ $visit->location ?? 'Not specified' }}</div>
                                            </td>
                                            <td class="pe-4">
                                                <span class="badge bg-secondary">{{ ucfirst($visit->status ?? 'completed') }}</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="bi bi-geo-alt fs-1 d-block mb-3 text-light"></i>
                                                No visits logged yet.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tasks Tab -->
                        <div class="tab-pane fade" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Task</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tasks as $task)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-600 text-dark">{{ $task->title }}</div>
                                            </td>
                                            <td>
                                                <div class="small {{ \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'completed' ? 'text-danger fw-bold' : 'text-muted' }}">
                                                    {{ \Carbon\Carbon::parse($task->due_date)->format('d M, Y') }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($task->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($task->status === 'in_progress')
                                                    <span class="badge bg-primary">In Progress</span>
                                                @else
                                                    <span class="badge bg-secondary">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-5 text-muted">
                                                <i class="bi bi-check2-circle fs-1 d-block mb-3 text-light"></i>
                                                No tasks assigned yet.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Expenses Tab -->
                        <div class="tab-pane fade" id="expenses" role="tabpanel" aria-labelledby="expenses-tab">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Date</th>
                                            <th>Category</th>
                                            <th>Amount</th>
                                            <th class="pe-4">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($expenses as $expense)
                                        <tr>
                                            <td class="ps-4 text-muted small">
                                                {{ \Carbon\Carbon::parse($expense->date)->format('d M, Y') }}
                                            </td>
                                            <td>
                                                <div class="fw-600 text-dark">{{ $expense->categoryRelation->name ?? $expense->category ?? 'General' }}</div>
                                                <div class="small text-muted text-truncate" style="max-width: 200px;">{{ $expense->description }}</div>
                                            </td>
                                            <td class="fw-bold">
                                                ₹{{ number_format($expense->amount, 2) }}
                                            </td>
                                            <td class="pe-4">
                                                @if($expense->status === 'approved')
                                                    <span class="badge bg-success">Approved</span>
                                                @elseif($expense->status === 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="bi bi-receipt fs-1 d-block mb-3 text-light"></i>
                                                No expenses submitted yet.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>



                        <!-- Leaves Tab -->
                        <div class="tab-pane fade" id="leaves" role="tabpanel" aria-labelledby="leaves-tab">
                            <div class="p-4">
                                <h6 class="fw-bold mb-3 text-muted border-bottom pb-2">Leave Balances ({{ date('Y') }})</h6>
                                <div class="row g-3 mb-4">
                                    @forelse($leaveBalances as $balance)
                                        <div class="col-md-4">
                                            <div class="card border border-light shadow-sm bg-light">
                                                <div class="card-body p-3">
                                                    <div class="fw-bold text-dark mb-2">{{ $balance['category'] }}</div>
                                                    <div class="d-flex justify-content-between text-muted small mb-1">
                                                        <span>Total: {{ $balance['total'] }}</span>
                                                        <span>Used: {{ $balance['used'] }}</span>
                                                    </div>
                                                    <div class="progress" style="height: 6px;">
                                                        @php $percent = $balance['total'] > 0 ? ($balance['used'] / $balance['total']) * 100 : 0; @endphp
                                                        <div class="progress-bar {{ $percent > 80 ? 'bg-danger' : 'bg-primary' }}" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <div class="mt-2 text-end fw-600 {{ $balance['remaining'] <= 0 ? 'text-danger' : 'text-success' }}">
                                                        {{ $balance['remaining'] }} remaining
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-muted small">No leave categories defined.</div>
                                    @endforelse
                                </div>
                                
                                <h6 class="fw-bold mb-3 text-muted border-bottom pb-2">Recent Leave Requests</h6>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-3">Type</th>
                                                <th>Duration</th>
                                                <th>Reason</th>
                                                <th class="pe-3">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($leaveRequests as $leave)
                                                <tr>
                                                    <td class="ps-3 fw-600">{{ $leave->leaveCategory->name ?? ucfirst($leave->type) }}</td>
                                                    <td class="small text-muted">
                                                        {{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M, Y') }}
                                                        <br>
                                                        <span class="badge bg-light text-dark border">{{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }} Days</span>
                                                    </td>
                                                    <td>
                                                        <div class="small text-muted text-truncate" style="max-width: 200px;">{{ $leave->reason }}</div>
                                                    </td>
                                                    <td class="pe-3">
                                                        @if($leave->status === 'approved')
                                                            <span class="badge bg-success">Approved</span>
                                                        @elseif($leave->status === 'rejected')
                                                            <span class="badge bg-danger">Rejected</span>
                                                        @else
                                                            <span class="badge bg-warning text-dark">Pending</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-4 text-muted">No leave requests found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
