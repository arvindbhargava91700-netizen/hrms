<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Customer Visits</h4>
            <p class="text-muted mb-0">Track and manage your visits with customers and leads</p>
        </div>
        <div>
            @if(auth()->user()->isPartner() || auth()->user()->canAccess('customervisit_create'))
            <button class="btn btn-primary" wire:click="createVisit">
                <i class="bi bi-calendar-plus"></i> Schedule Visit
            </button>
            @endif
        </div>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="search-box">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" class="form-control" 
                            placeholder="Search by customer/lead name..." wire:model.live="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="status">
                        <option value="">All Statuses</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="rescheduled">Rescheduled</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Date & Time</th>
                        <th>Customer / Lead</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visits as $visit)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="fw-bold text-dark">{{ Carbon\Carbon::parse($visit->visit_date)->format('M d, Y') }}</div>
                            <div class="text-muted small">{{ Carbon\Carbon::parse($visit->visit_date)->format('h:i A') }}</div>
                        </td>
                        <td class="py-3">
                            <div class="fw-bold text-dark">{{ $visit->lead->customer_name ?? 'Unknown' }}</div>
                            <div class="text-muted small">{{ $visit->lead->customer_mobile ?? '' }}</div>
                        </td>
                        <td class="py-3 text-muted">{{ $visit->location }}</td>
                        <td class="py-3">
                            @php
                                $statusColors = [
                                    'scheduled' => 'primary',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    'rescheduled' => 'warning'
                                ];
                                $color = $statusColors[$visit->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }} rounded-pill px-3">
                                {{ ucfirst($visit->status) }}
                            </span>
                        </td>
                        <td class="py-3 pe-4 text-end">
                            <button class="btn btn-sm btn-outline-info rounded-pill px-3 me-1" wire:click="showVisit('{{ $visit->id }}')">View</button>
                            @if(auth()->user()->isPartner() || auth()->user()->canAccess('customervisit_update'))
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1" wire:click="editVisit('{{ $visit->id }}')">Edit</button>
                            @endif
                            @if(auth()->user()->isPartner() || auth()->user()->canAccess('customervisit_delete'))
                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3" wire:click="deleteVisit('{{ $visit->id }}')" onclick="confirm('Are you sure you want to delete this visit?') || event.stopImmediatePropagation()">Delete</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted mb-2"><i class="bi bi-calendar-x fs-1 opacity-50"></i></div>
                            <div class="text-muted">No visits found</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($visits->hasPages())
        <div class="card-footer border-top bg-transparent p-4">
            {{ $visits->links() }}
        </div>
        @endif
    </div>

    <!-- Create Visit Modal -->
    <div class="modal fade" id="createVisitModal" tabindex="-1" aria-labelledby="createVisitModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="createVisitModalLabel">{{ $isEditMode ? 'Edit Visit' : 'Schedule New Visit' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Select Lead/Customer</label>
                            <select class="form-select" wire:model="newVisitLeadId">
                                <option value="">-- Choose --</option>
                                @foreach($availableLeads as $lead)
                                    <option value="{{ $lead->id }}">{{ $lead->customer_name }} ({{ $lead->customer_mobile ?: 'No Mobile' }})</option>
                                @endforeach
                            </select>
                            @error('newVisitLeadId') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" wire:model="newVisitDate">
                            @error('newVisitDate') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Time</label>
                            <input type="time" class="form-control" wire:model="newVisitTime">
                            @error('newVisitTime') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" wire:model="newVisitLocation" placeholder="Office, Client Location, Cafe, etc.">
                            @error('newVisitLocation') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Purpose / Agenda</label>
                            <input type="text" class="form-control" wire:model="newVisitPurpose" placeholder="e.g. Initial Demo, Product Pitch, Closing">
                            @error('newVisitPurpose') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" wire:model="newVisitNotes" rows="3"></textarea>
                            @error('newVisitNotes') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary px-4" wire:click="saveVisit">{{ $isEditMode ? 'Save Changes' : 'Schedule Visit' }}</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Visit Modal -->
    <div class="modal fade" id="viewVisitModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Visit Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($viewVisit)
                    <div class="mb-4">
                        <h6 class="text-muted small mb-1">Customer / Lead</h6>
                        <p class="fw-bold fs-5 mb-0">{{ $viewVisit->lead->customer_name ?? 'Unknown' }}</p>
                        <p class="text-muted mb-0"><i class="bi bi-telephone text-primary me-2"></i>{{ $viewVisit->lead->customer_mobile ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Date & Time</h6>
                            <p class="fw-bold mb-0">{{ \Carbon\Carbon::parse($viewVisit->visit_date)->format('M d, Y h:i A') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Status</h6>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill px-3">{{ ucfirst($viewVisit->status) }}</span>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-muted small mb-1">Location</h6>
                        <p class="mb-0">{{ $viewVisit->location }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="text-muted small mb-1">Purpose</h6>
                        <p class="mb-0">{{ $viewVisit->purpose ?: 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <h6 class="text-muted small mb-1">Notes</h6>
                        <div class="p-3 bg-light rounded text-muted">
                            {{ $viewVisit->notes ?: 'No notes provided.' }}
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    @script
    <script>
        $wire.on('show-visit-modal', () => {
            let modal = new bootstrap.Modal(document.getElementById('createVisitModal'));
            modal.show();
        });
        
        $wire.on('show-view-visit-modal', () => {
            let modal = new bootstrap.Modal(document.getElementById('viewVisitModal'));
            modal.show();
        });
        
        $wire.on('close-visit-modal', () => {
            let el = document.getElementById('createVisitModal');
            let modal = bootstrap.Modal.getInstance(el);
            if (modal) modal.hide();
        });
    </script>
    @endscript
</div>
