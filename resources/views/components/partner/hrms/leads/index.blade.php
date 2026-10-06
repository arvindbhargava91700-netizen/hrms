<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 text-white">Leads Management</h4>
            <p class="text-white-50 mb-0">Manage all your potential customers and inquiries</p>
        </div>
        <div>
            <button class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add New Lead
            </button>
        </div>
    </div>

    <div class="card mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 16px;">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-secondary text-white-50">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control bg-transparent border-secondary text-white" 
                            placeholder="Search leads..." wire:model.live="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select bg-dark border-secondary text-white" wire:model.live="status">
                        <option value="">All Statuses</option>
                        <option value="new">New</option>
                        <option value="contacted">Contacted</option>
                        <option value="qualified">Qualified</option>
                        <option value="proposal">Proposal</option>
                        <option value="negotiation">Negotiation</option>
                        <option value="won">Won</option>
                        <option value="lost">Lost</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 16px;">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="border-bottom border-secondary text-white-50 font-weight-normal py-3 px-4">Name</th>
                        <th class="border-bottom border-secondary text-white-50 font-weight-normal py-3">Company</th>
                        <th class="border-bottom border-secondary text-white-50 font-weight-normal py-3">Status</th>
                        <th class="border-bottom border-secondary text-white-50 font-weight-normal py-3">Value</th>
                        <th class="border-bottom border-secondary text-white-50 font-weight-normal py-3 text-end px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    {{ substr($lead->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-white fw-medium">{{ $lead->name }}</div>
                                    <div class="text-white-50 small">{{ $lead->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 text-white-50">{{ $lead->company_name ?? '-' }}</td>
                        <td class="py-3">
                            <span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary">{{ ucfirst($lead->status) }}</span>
                        </td>
                        <td class="py-3 text-white">${{ number_format($lead->expected_value ?? 0, 2) }}</td>
                        <td class="py-3 px-4 text-end">
                            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3">View</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-white-50 mb-2"><i class="bi bi-inbox fs-1"></i></div>
                            <div>No leads found</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($leads->hasPages())
        <div class="card-footer border-top border-secondary bg-transparent p-4">
            {{ $leads->links() }}
        </div>
        @endif
    </div>
</div>
