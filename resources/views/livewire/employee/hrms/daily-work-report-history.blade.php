<div class="container-fluid py-4 bg-light min-vh-100">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                        <i class="bi bi-clock-history text-primary me-2"></i> My Report History
                    </h5>
                    
                    <div class="d-flex gap-2 flex-wrap">
                        <input type="date" wire:model.live="dateFilter" class="form-control form-control-sm bg-light border-0" style="width: 150px;">
                        
                        <select wire:model.live="statusFilter" class="form-select form-select-sm bg-light border-0" style="width: 150px;">
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="submitted">Submitted</option>
                            <option value="manager_approved">Manager Approved</option>
                            <option value="partner_approved">Partner Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive mt-3">
                        <table class="table table-hover align-middle border-top">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-secondary fw-semibold small">DATE</th>
                                    <th class="text-secondary fw-semibold small">COMPLETION</th>
                                    <th class="text-secondary fw-semibold small">ITEMS</th>
                                    <th class="text-secondary fw-semibold small">STATUS</th>
                                    <th class="text-secondary fw-semibold small text-end">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $report->report_date->format('d M Y') }}</div>
                                            <small class="text-muted">{{ $report->report_date->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $report->completion_percentage }}%;"></div>
                                                </div>
                                                <small class="text-muted fw-bold">{{ $report->completion_percentage }}%</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $report->items->count() }} items</span>
                                        </td>
                                        <td>
                                            @if($report->status === 'draft')
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">Draft</span>
                                            @elseif($report->status === 'submitted')
                                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">Submitted</span>
                                            @elseif($report->status === 'manager_approved')
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">Manager Approved</span>
                                            @elseif($report->status === 'partner_approved')
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">Partner Approved</span>
                                            @elseif($report->status === 'rejected')
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">Rejected</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button wire:click="viewReport({{ $report->id }})" class="btn btn-sm btn-outline-primary shadow-sm px-3">
                                                View Details
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                            No report history found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $reports->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Report Modal -->
    @if($viewingReportId && $viewingReport)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold">
                            Work Report Details
                            <span class="text-muted fw-normal ms-2">({{ $viewingReport->report_date->format('d M Y') }})</span>
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeReport"></button>
                    </div>
                    <div class="modal-body p-4 bg-light">
                        <div class="row">
                            <div class="col-md-4 mb-4 mb-md-0">
                                <!-- Summary & Details -->
                                <div class="card shadow-sm border-0 h-100">
                                    <div class="card-body">
                                        <h6 class="fw-bold text-muted mb-3"><i class="bi bi-chat-left-text me-2 text-primary"></i> Overall Summary</h6>
                                        <div class="bg-light p-3 rounded text-dark" style="min-height: 150px; white-space: pre-line;">
                                            {{ $viewingReport->summary ?: 'No summary provided.' }}
                                        </div>
                                        
                                        <hr class="my-4">
                                        
                                        <h6 class="fw-bold text-muted mb-3"><i class="bi bi-info-circle me-2 text-info"></i> Report Details</h6>
                                        <ul class="list-group list-group-flush small">
                                            <li class="list-group-item px-0 d-flex justify-content-between bg-transparent">
                                                <span class="text-muted">Total Items:</span>
                                                <span class="fw-bold">{{ $viewingReport->items->count() }}</span>
                                            </li>
                                            <li class="list-group-item px-0 d-flex justify-content-between bg-transparent">
                                                <span class="text-muted">Completion:</span>
                                                <span class="fw-bold text-primary">{{ $viewingReport->completion_percentage }}%</span>
                                            </li>
                                            <li class="list-group-item px-0 d-flex justify-content-between bg-transparent">
                                                <span class="text-muted">Current Status:</span>
                                                <span class="fw-bold text-capitalize">{{ str_replace('_', ' ', $viewingReport->status) }}</span>
                                            </li>
                                            @if($viewingReport->manager_id)
                                                <li class="list-group-item px-0 d-flex justify-content-between bg-transparent">
                                                    <span class="text-muted">Manager Approved By:</span>
                                                    <span class="fw-bold">{{ $viewingReport->manager->name ?? '' }}</span>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <!-- Work Items List -->
                                <div class="card shadow-sm border-0 h-100">
                                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                        <h6 class="fw-bold text-dark"><i class="bi bi-list-check me-2 text-primary"></i> Work Items Logged</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="accordion" id="workItemsAccordion">
                                            @forelse($viewingReport->items as $index => $item)
                                                <div class="accordion-item mb-2 border rounded">
                                                    <h2 class="accordion-header" id="heading{{ $item->id }}">
                                                        <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $item->id }}">
                                                            <div class="d-flex w-100 justify-content-between align-items-center me-3">
                                                                <div class="fw-semibold text-dark">{{ $item->title }}</div>
                                                                <div class="d-flex align-items-center gap-3">
                                                                    @if($item->status === 'Completed')
                                                                        <span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle"></i> Completed</span>
                                                                    @else
                                                                        <span class="badge bg-warning bg-opacity-10 text-warning"><i class="bi bi-hourglass"></i> {{ $item->status }}</span>
                                                                    @endif
                                                                    <span class="badge bg-light text-dark border">{{ $item->time_spent_hours }}h {{ $item->time_spent_minutes }}m</span>
                                                                </div>
                                                            </div>
                                                        </button>
                                                    </h2>
                                                    <div id="collapse{{ $item->id }}" class="accordion-collapse collapse" data-bs-parent="#workItemsAccordion">
                                                        <div class="accordion-body bg-light border-top text-dark small">
                                                            @if($item->details)
                                                                <div class="mb-3">
                                                                    <strong>Details:</strong><br>
                                                                    {{ $item->details }}
                                                                </div>
                                                            @endif
                                                            <div class="d-flex flex-wrap gap-4 text-muted">
                                                                <div><strong>Category:</strong> {{ $item->category }}</div>
                                                                <div><strong>Priority:</strong> {{ $item->priority }}</div>
                                                                @if($item->task)
                                                                    <div><strong>Linked Task:</strong> <a href="#" class="text-primary text-decoration-none">{{ $item->task->title }}</a></div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center py-4 text-muted">
                                                    No work items found in this report.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-top-0 d-flex justify-content-end">
                        <button type="button" class="btn btn-light px-4 border shadow-sm" wire:click="closeReport">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
