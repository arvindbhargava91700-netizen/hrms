<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-1">Leave Balance - {{ $currentYear }}</h4>
            <p class="text-muted mb-0">Summary of your leave statistics for the current year.</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-calendar-check fs-3"></i>
                    </div>
                    <h3 class="fw-600 text-dark mb-1">{{ $totalDaysTaken }}</h3>
                    <p class="text-muted mb-0 fw-600">Total Approved Days Taken</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                    <h3 class="fw-600 text-dark mb-1">{{ $pendingRequests }}</h3>
                    <p class="text-muted mb-0 fw-600">Pending Requests</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-600">Category Breakdown</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Category Name</th>
                                <th>Total Allocated</th>
                                <th>Used Days</th>
                                <th>Pending Days</th>
                                <th>Rejected Days</th>
                                <th>Available Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categoryBalances as $cat)
                                <tr>
                                    <td class="fw-bold">{{ $cat['name'] }}</td>
                                    <td><span class="badge bg-secondary">{{ $cat['total'] }} Days</span></td>
                                    <td><span class="badge bg-danger">{{ $cat['used'] }} Days</span></td>
                                    <td><span class="badge bg-warning text-dark">{{ $cat['pending'] }} Days</span></td>
                                    <td><span class="badge bg-dark">{{ $cat['rejected'] }} Days</span></td>
                                    <td><span class="badge bg-success fs-6">{{ $cat['remaining'] }} Days</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No leave categories configured.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 d-flex justify-content-between align-items-center bg-light rounded m-3 border">
                    <div>
                        <h5 class="fw-bold mb-1">Need time off?</h5>
                        <p class="text-muted mb-0 small">Submit a new leave application according to your available balance.</p>
                    </div>
                      @if(auth()->user()->canAccess('leave_create'))
                    <a href="{{ route('partner.hrms.leaves.apply') }}" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-plus-lg me-1"></i> Apply for Leave
                    </a>
                       @endif
                </div>
            </div>
        </div>
    </div>
</div>
