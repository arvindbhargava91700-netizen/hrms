<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <img src="{{ $customer->avatar_url }}" class="rounded-circle mb-3" width="88" height="88">
                <h5 class="mb-1">{{ $customer->name }}</h5>
                @php
                    $canViewContact = auth()->user()->can('admin_view_contact_info');
                    $emailParts = explode('@', $customer->email);
                    $maskedEmail = $canViewContact ? $customer->email : str_repeat('*', max(1, strlen($emailParts[0]))) . '@' . ($emailParts[1] ?? '');
                    $maskedMobile = $canViewContact ? $customer->mobile : ($customer->mobile ? str_repeat('*', max(0, strlen($customer->mobile) - 4)) . substr($customer->mobile, -4) : null);
                @endphp
                <div class="text-muted">{{ $maskedEmail }}</div>
                <div class="text-muted small">{{ $maskedMobile ?? 'No mobile number' }}</div>
                <div class="mt-3">
                    <span class="badge-status badge-{{ $customer->status }}">{{ ucfirst($customer->status) }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Account Summary</h6>
            </div>
            <div class="card-body">
                <div class="mb-2"><strong>Joined:</strong> {{ $customer->created_at->format('d M, Y H:i') }}</div>
                <div class="mb-2"><strong>Email Verified:</strong> {{ $customer->email_verified_at ? $customer->email_verified_at->format('d M, Y H:i') : 'No' }}</div>
                <div class="mb-2"><strong>Mobile Verified:</strong> {{ $customer->mobile_verified_at ? $customer->mobile_verified_at->format('d M, Y H:i') : 'No' }}</div>
                <div class="mb-2"><strong>Subscriptions:</strong> {{ $customer->subscriptions->count() }}</div>
                <div class="mb-2"><strong>Payments:</strong> {{ $customer->payments->count() }}</div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <a href="{{ route('admin.subscriptions', ['customer' => $customer->id]) }}" class="text-decoration-none">
                    <div class="card bg-light border-0 h-100 transition shadow-sm">
                        <div class="card-body text-center py-3">
                            <i class="bi bi-card-checklist fs-4 text-primary mb-1"></i>
                            <div class="text-muted small mb-1">Subscriptions</div>
                            <div class="fw-bold fs-5 text-dark">{{ $customer->subscriptions_count ?? 0 }}</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-4">
                <a href="{{ route('admin.payments', ['customer' => $customer->id]) }}" class="text-decoration-none">
                    <div class="card bg-light border-0 h-100 transition shadow-sm">
                        <div class="card-body text-center py-3">
                            <i class="bi bi-currency-rupee fs-4 text-success mb-1"></i>
                            <div class="text-muted small mb-1">Payments</div>
                            <div class="fw-bold fs-5 text-dark">{{ $customer->payments_count ?? 0 }}</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-4">
                <a href="{{ route('admin.visits', ['customer' => $customer->id]) }}" class="text-decoration-none">
                    <div class="card bg-light border-0 h-100 transition shadow-sm">
                        <div class="card-body text-center py-3">
                            <i class="bi bi-calendar-event fs-4 text-warning mb-1"></i>
                            <div class="text-muted small mb-1">Visit Requests</div>
                            <div class="fw-bold fs-5 text-dark">{{ $customer->customer_visits_count ?? 0 }}</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Subscriptions</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-feetrack mb-0">
                    <thead>
                        <tr>
                            <th>Package</th>
                            <th>Listing</th>
                            <th>Period</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->subscriptions as $subscription)
                            <tr>
                                <td>{{ $subscription->package->name ?? 'N/A' }}</td>
                                <td>{{ $subscription->package->listing->title ?? 'N/A' }}</td>
                                <td>{{ $subscription->starts_at?->format('d M, Y') }} - {{ $subscription->expires_at?->format('d M, Y') }}</td>
                                <td><span class="badge-status badge-{{ $subscription->status }}">{{ $subscription->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-3 text-muted">No subscriptions found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Payments</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-feetrack mb-0">
                    <thead>
                        <tr>
                            <th>Ref</th>
                            <th>Subscription</th>
                            <th>Gateway</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Paid At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->payments as $payment)
                            <tr>
                                <td>{{ $payment->gateway_ref ?? $payment->id }}</td>
                                <td>{{ $payment->subscription->package->name ?? 'N/A' }}</td>
                                <td>{{ $payment->gateway }}</td>
                                <td>₹{{ number_format((float) $payment->amount, 2) }}</td>
                                <td><span class="badge-status badge-{{ $payment->status }}">{{ $payment->status }}</span></td>
                                <td>{{ $payment->paid_at?->format('d M, Y H:i') ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-3 text-muted">No payments found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Invoices</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-feetrack mb-0">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Subscription</th>
                            <th>Amount</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php($invoices = $customer->subscriptions->flatMap(fn ($subscription) => $subscription->invoices))
                        @forelse($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->subscription->package->name ?? 'N/A' }}</td>
                                <td>₹{{ number_format((float) $invoice->amount, 2) }}</td>
                                <td>₹{{ number_format((float) $invoice->total, 2) }}</td>
                                <td><span class="badge-status badge-{{ $invoice->status }}">{{ $invoice->status }}</span></td>
                                <td>{{ $invoice->due_date?->format('d M, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-3 text-muted">No invoices found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
