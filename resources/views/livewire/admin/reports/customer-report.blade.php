<div>
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-4">
            <h5 class="card-title fw-bold mb-4" style="color: var(--primary-color);">Customer Report</h5>

            <!-- Filters -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Search by name, email or mobile..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" wire:model.live="startDate" title="Start Date">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" wire:model.live="endDate" title="End Date">
                </div>
                <div class="col-md-2 d-flex align-items-center">
                    <button class="btn btn-primary w-100" wire:click="export"><i class="bi bi-download"></i> Export</button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Registration Date</th>
                            <th>Total Bookings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $customer)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $customer->name }}</div>
                            </td>
                            <td>
                                <div class="small text-muted"><i class="bi bi-envelope"></i> @php
                $canViewContact = auth()->check() && auth()->user()->can('admin_view_contact_info');
                $emailParts = explode('@', $customer->email ?? '');
                $maskedEmail = $canViewContact ? ($customer->email ?? '—') : (empty($customer->email) ? '—' : str_repeat('*', max(1, strlen($emailParts[0]))) . '@' . ($emailParts[1] ?? ''));
            @endphp
            {{ $maskedEmail }}</div>
                                <div class="small text-muted"><i class="bi bi-telephone"></i> {{ \App\Helpers\AdminHelper::maskContact('mobile', $customer->mobile) }}</div>
                            </td>
                            <td>
                                @if($customer->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($customer->status === 'inactive')
                                    <span class="badge bg-secondary">Inactive</span>
                                @elseif($customer->status === 'suspended')
                                    <span class="badge bg-danger">Suspended</span>
                                @else
                                    <span class="badge bg-warning text-dark">{{ ucfirst($customer->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $customer->created_at->format('M d, Y h:i A') }}</td>
                            <td>
                                <span class="badge bg-primary rounded-pill">{{ $customer->visit_bookings_count }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No customers found for the selected criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $records->links() }}
            </div>
        </div>
    </div>
</div>
