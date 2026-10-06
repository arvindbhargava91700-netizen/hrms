<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center border-0 pb-0">
            <h5 class="mb-0">Leave Approvals</h5>
        </div>
        
        <div class="card-body pb-0">
            @include('partials.hrms-filters', ['viewAnyPermission' => 'leave_viewAny'])
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
                        <th>Date Applied</th>
                        <th>Leave Period</th>
                        <th>Type & Duration</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                    <tbody>
                        @forelse($requests as $request)
                            @php
                                $start = \Carbon\Carbon::parse($request->start_date);
                                $end = \Carbon\Carbon::parse($request->end_date);
                                $days = $start->diffInDays($end) + 1;
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                            {{ substr($request->employee->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $request->employee->name }}</div>
                                            <div class="small text-muted">{{ $request->employee->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-muted">
                                    {{ $request->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 fw-bold">
                                    {{ $start->format('M d, Y') }} - {{ $end->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($request->type == 'casual')
                                        <span class="badge bg-info text-dark d-block mb-1">Casual (CL)</span>
                                    @elseif($request->type == 'sick')
                                        <span class="badge bg-danger d-block mb-1">Sick (SL)</span>
                                    @else
                                        <span class="badge bg-secondary d-block mb-1">Unpaid (LWP)</span>
                                    @endif
                                    <span class="small text-muted">{{ $days }} Day(s)</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="d-inline-block text-truncate" style="max-width: 200px;" title="{{ $request->reason }}">
                                        {{ $request->reason }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($request->status == 'pending')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Pending</span>
                                    @elseif($request->status == 'approved')
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Approved</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Rejected</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($request->status == 'pending')
                                        <div class="d-flex gap-2">
                                             @if(auth()->user()->canAccess('leave_approved'))
                                            <button wire:click="updateStatus({{ $request->id }}, 'approved')" class="btn btn-sm btn-success px-3">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            @endif
                                             @if(auth()->user()->canAccess('leave_rejected'))
                                            <button wire:click="updateStatus({{ $request->id }}, 'rejected')" class="btn btn-sm btn-outline-danger px-3">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted small">Processed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="bi bi-inbox fs-2"></i></div>
                                    <p class="mb-0 fw-bold">No leave requests found.</p>
                                    <p class="small">There are currently no leave requests from any employees.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="card-footer">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
