<div class="container-fluid py-4">
<div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">My Leave Requests</h5>
              @if(auth()->user()->canAccess('leave_create'))
            <a href="{{ route('partner.hrms.leaves.apply') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-plus-lg me-1"></i> Apply New
            </a>
             @endif
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Date Applied</th>
                        <th>Leave Period</th>
                        <th>Type</th>
                        <th>Duration</th>
                        <th>Reason</th>
                        <th>Status</th>
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
                                <td class="px-4 py-3 text-muted">
                                    {{ $request->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 fw-bold">
                                    {{ $start->format('M d, Y') }} - {{ $end->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($request->type == 'casual')
                                        <span class="badge bg-info text-dark">Casual (CL)</span>
                                    @elseif($request->type == 'sick')
                                        <span class="badge bg-danger">Sick (SL)</span>
                                    @else
                                        <span class="badge bg-secondary">Unpaid (LWP)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-muted">
                                    {{ $days }} Day(s)
                                </td>
                                <td class="px-4 py-3">
                                    <span class="d-inline-block text-truncate" style="max-width: 250px;" title="{{ $request->reason }}">
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="bi bi-calendar-x fs-2"></i></div>
                                    <p class="mb-0 fw-bold">No leave requests found.</p>
                                    <p class="small">You haven't applied for any leaves yet.</p>
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
