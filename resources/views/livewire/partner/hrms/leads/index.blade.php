<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Leads Management</h4>
            <p class="text-muted mb-0">Manage all your potential customers and inquiries</p>
        </div>
        <div>
            @if(auth()->user()->isPartner() || auth()->user()->canAccess('lead_create'))
            <button class="btn btn-primary" wire:click="createLead">
                <i class="bi bi-plus-lg"></i> Add New Lead
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
            <div class="row g-3 mb-3">
                @if(auth()->user()->canAccess('lead_viewAny') || auth()->user()->canAccess('lead_viewteam'))
                <div class="col-12">
                    @include('partials.hrms-filters')
                </div>
                @endif
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="search-box">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" class="form-control" 
                            placeholder="Search leads..." wire:model.live="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="status">
                        <option value="">All Statuses</option>
                        <option value="new">New</option>
                        <option value="first_call">First Call</option>
                        <option value="interested">Interested</option>
                        <option value="meeting_scheduled">Meeting Scheduled</option>
                        <option value="customer_visit">Customer Visit</option>
                        <option value="quotation">Quotation</option>
                        <option value="negotiation">Negotiation</option>
                        <option value="won">Won</option>
                        <option value="lost">Lost</option>
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
                        <th class="ps-4">Name</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Value</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 40px; height: 40px;">
                                    {{ substr($lead->customer_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $lead->customer_name }}</div>
                                    @if($lead->company_name)
                                        <div class="text-secondary small fw-semibold">
                                            <i class="bi bi-building me-1"></i>{{ $lead->company_name }}
                                        </div>
                                    @endif
                                    <div class="text-muted extra-small d-flex flex-wrap gap-2 mt-0.5">
                                        @if($lead->customer_mobile)
                                            <span><i class="bi bi-telephone"></i> {{ $lead->customer_mobile }}</span>
                                        @endif
                                        @if($lead->email)
                                            <span><i class="bi bi-envelope"></i> {{ $lead->email }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 text-muted">
                            <div class="fw-medium text-dark">{{ $lead->created_at->format('M d, Y') }}</div>
                            <div class="small">{{ $lead->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill px-3">{{ ucfirst($lead->status) }}</span>
                        </td>
                        <td class="py-3 fw-bold text-dark">₹{{ number_format($lead->orders->sum('total_amount'), 2) }}</td>
                        <td class="py-3 pe-4 text-end">
                            <button class="btn btn-sm btn-outline-info rounded-pill px-3 me-1" wire:click="showLead('{{ $lead->id }}')">View</button>
                            @if(auth()->user()->isPartner() || auth()->user()->canAccess('lead_update'))
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1" wire:click="editLead('{{ $lead->id }}')">Edit</button>
                            @endif
                            @if(auth()->user()->isPartner() || auth()->user()->canAccess('lead_delete'))
                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3" wire:click="deleteLead('{{ $lead->id }}')" onclick="confirm('Are you sure you want to delete this lead?') || event.stopImmediatePropagation()">Delete</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted mb-2"><i class="bi bi-inbox fs-1 opacity-50"></i></div>
                            <div class="text-muted">No leads found</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($leads->hasPages())
        <div class="card-footer border-top bg-transparent p-4">
            {{ $leads->links() }}
        </div>
        @endif
    </div>

    <!-- Create Lead Modal -->
    <div class="modal fade" id="createLeadModal" tabindex="-1" aria-labelledby="createLeadModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-bottom py-3 px-4 bg-white d-flex justify-content-between align-items-center">
                    <h5 class="modal-title fw-bold text-dark" id="createLeadModalLabel">
                        <i class="bi bi-person-plus-fill me-2 text-primary"></i>{{ $isEditMode ? 'Edit Lead' : 'Add New Lead' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="newCustomerName" placeholder="e.g. John Doe">
                            @error('newCustomerName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase">Company Name</label>
                            <input type="text" class="form-control" wire:model="newCompanyName" placeholder="e.g. Acme Corp / Store Name">
                            @error('newCompanyName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase">Mobile Number</label>
                            <input type="text" class="form-control" wire:model="newCustomerMobile" placeholder="e.g. 9876543210">
                            @error('newCustomerMobile') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase">Email Address</label>
                            <input type="email" class="form-control" wire:model="newCustomerEmail" placeholder="e.g. john@example.com">
                            @error('newCustomerEmail') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase">Assign To</label>
                            <select class="form-select" wire:model="assignedTo">
                                <option value="">-- Assign Employee --</option>
                                <option value="{{ auth()->id() }}">Self ({{ auth()->user()->name }})</option>
                                @foreach($teamMembers as $member)
                                    @if($member->id !== auth()->id())
                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('assignedTo') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase">Status</label>
                            <select class="form-select" wire:model="editStatus">
                                <option value="new">New</option>
                                <option value="first_call">First Call</option>
                                <option value="interested">Interested</option>
                                <option value="meeting_scheduled">Meeting Scheduled</option>
                                <option value="customer_visit">Customer Visit</option>
                                <option value="quotation">Quotation</option>
                                <option value="negotiation">Negotiation</option>
                                <option value="won">Won</option>
                                <option value="lost">Lost</option>
                            </select>
                            @error('editStatus') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-muted small fw-bold text-uppercase">Notes</label>
                            <textarea class="form-control" wire:model="newNotes" rows="3" placeholder="Additional details about this lead..."></textarea>
                            @error('newNotes') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4 bg-white">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" wire:click="saveLead">{{ $isEditMode ? 'Save Changes' : 'Create Lead' }}</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Lead Modal -->
    <div class="modal fade" id="viewLeadModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-bottom py-3 px-4 bg-white d-flex justify-content-between align-items-center">
                    <h5 class="modal-title fw-bold text-dark mb-0">Lead Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    @if($viewLead)
                    <div class="row mb-4 g-3">
                        <div class="col-md-6">
                            <h6 class="text-muted extra-small text-uppercase fw-bold mb-1">Customer Name</h6>
                            <p class="fw-bold fs-5 mb-0 text-dark">{{ $viewLead->customer_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted extra-small text-uppercase fw-bold mb-1">Company Name</h6>
                            <p class="fw-bold fs-5 mb-0 text-secondary"><i class="bi bi-building me-1"></i>{{ $viewLead->company_name ?: 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted extra-small text-uppercase fw-bold mb-1">Mobile Number</h6>
                            <p class="fw-bold fs-5 mb-0"><i class="bi bi-telephone text-primary me-2"></i>{{ $viewLead->customer_mobile ?: 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted extra-small text-uppercase fw-bold mb-1">Email Address</h6>
                            <p class="fw-bold fs-5 mb-0 text-dark"><i class="bi bi-envelope text-primary me-2"></i>{{ $viewLead->email ?: 'N/A' }}</p>
                        </div>
                    </div>
                    
                    <div class="row mb-4 g-3">
                        <div class="col-md-4">
                            <h6 class="text-muted extra-small text-uppercase fw-bold mb-1">Status</h6>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill px-3 py-1.5">{{ ucfirst($viewLead->status) }}</span>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted extra-small text-uppercase fw-bold mb-1">Total Order Value</h6>
                            <p class="fw-bold text-success mb-0 fs-5">₹{{ number_format($viewLead->orders->sum('total_amount'), 2) }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted extra-small text-uppercase fw-bold mb-1">Created Date</h6>
                            <p class="mb-0 fw-semibold text-dark">{{ $viewLead->created_at->format('M d, Y') }} <small class="text-muted">({{ $viewLead->created_at->format('h:i A') }})</small></p>
                        </div>
                    </div>
                    </div>
                    
                    <h6 class="fw-bold mb-3 border-bottom pb-2">Recent Orders</h6>
                    @if($viewLead->orders->count() > 0)
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($viewLead->orders->take(5) as $order)
                                    <tr>
                                        <td>#{{ substr($order->id, 0, 8) }}</td>
                                        <td>{{ $order->created_at->format('d M Y') }}</td>
                                        <td>{{ ucfirst($order->status) }}</td>
                                        <td class="text-end fw-bold">₹{{ number_format($order->total_amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted small fst-italic mb-4">No orders placed yet.</p>
                    @endif
                    
                    <h6 class="fw-bold mb-3 border-bottom pb-2">Recent Visits</h6>
                    @if($viewLead->visits->count() > 0)
                        <ul class="list-group list-group-flush mb-0">
                            @foreach($viewLead->visits->take(5) as $visit)
                            <li class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold small">{{ $visit->visit_purpose }}</div>
                                        <div class="text-muted" style="font-size: 0.8rem;">{{ $visit->notes ?? 'No notes' }}</div>
                                    </div>
                                    <span class="badge bg-light text-dark">{{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') : $visit->created_at->format('d M Y') }}</span>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted small fst-italic">No visits recorded yet.</p>
                    @endif
                    
                    @else
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer border-top py-3 px-4 bg-white d-flex justify-content-end">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    
    @script
    <script>
        $wire.on('show-lead-modal', () => {
            let modal = new bootstrap.Modal(document.getElementById('createLeadModal'));
            modal.show();
        });
        
        $wire.on('show-view-lead-modal', () => {
            let modal = new bootstrap.Modal(document.getElementById('viewLeadModal'));
            modal.show();
        });
        
        $wire.on('close-lead-modal', () => {
            let el = document.getElementById('createLeadModal');
            let modal = bootstrap.Modal.getInstance(el);
            if (modal) modal.hide();
        });
    </script>
    @endscript
</div>
