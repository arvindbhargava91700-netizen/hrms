<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        :root { color-scheme: light; }
        @page { size: A4; margin: 12mm; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            background: #f5f7fb;
            color: #1f2937;
        }
        .page {
            max-width: 900px;
            margin: 24px auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 18px 24px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
        }
        .title { margin: 0; font-size: 22px; }
        .subtitle { margin: 4px 0 0; font-size: 13px; opacity: .9; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 10px;
            border: 0;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-light { background: #fff; color: #1d4ed8; }
        .btn-dark { background: #111827; color: #fff; }
        .content { padding: 24px; }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 18px;
        }
        .panel {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 16px;
        }
        .label { color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
        .value { margin-top: 6px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 12px;
            text-align: left;
            vertical-align: top;
        }
        th { width: 32%; background: #f9fafb; }
        .footer {
            padding: 16px 24px 24px;
            color: #6b7280;
            font-size: 12px;
        }
        @media print {
            body { background: #fff; }
            .page { margin: 0; border: 0; border-radius: 0; box-shadow: none; }
            .topbar, .no-print { display: none !important; }
            .content { padding-top: 0; }
        }
        @media (max-width: 720px) {
            .grid { grid-template-columns: 1fr; }
            .topbar { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar no-print">
            <div>
                <h1 class="title">{{ $title }}</h1>
                <p class="subtitle">Invoice #{{ $invoice?->invoice_number ?? 'N/A' }} · {{ $customer->name ?? 'Customer' }}</p>
            </div>
            <div class="actions">
                <a class="btn btn-light" href="{{ route($download_route, ['type' => $type, 'id' => $id]) }}">Download PDF</a>
                <button class="btn btn-dark" onclick="window.print()">Print</button>
            </div>
        </div>

        <div class="content">
            <div class="grid">
                <div class="panel">
                    <div class="label">Customer</div>
                    <div class="value">{{ $customer->name ?? 'N/A' }}</div>
                    <div>{{ $customer->email ?? '' }}</div>
                    <div>{{ $customer->mobile ?? '' }}</div>
                </div>
                <div class="panel">
                    <div class="label">Listing & Package</div>
                    <div class="value">{{ $listing->title ?? 'N/A' }}</div>
                    @if(isset($listing) && $listing->listing_number)
                        <div>LST: #{{ $listing->listing_number }}</div>
                    @endif
                    <div>{{ $subscription?->package?->name ?? $booking?->package?->name ?? 'N/A' }}</div>
                    @if(isset($subscription))
                        <div>Subscription #{{ $subscription->subscription_number ?? ($subscription->id ?? 'N/A') }}</div>
                    @endif
                    @if(isset($booking))
                        <div>Booking #{{ $booking->booking_number ?? ($booking->id ?? 'N/A') }}</div>
                    @endif
                </div>
            </div>

            <table>
                <tbody>
                    <tr><th>Invoice No</th><td>{{ $invoice?->invoice_number ?? 'N/A' }}</td></tr>
                    <tr><th>Receipt Ref</th><td>{{ $payment?->receipt_number ?? $payment?->gateway_ref ?? 'N/A' }}</td></tr>
                    <tr><th>Gateway</th><td>{{ $payment?->gateway ?? 'N/A' }}</td></tr>
                    <tr><th>Amount</th><td>INR {{ number_format((float) ($invoice?->amount ?? $payment?->amount ?? 0), 2) }}</td></tr>
                    <tr><th>Tax</th><td>INR {{ number_format((float) ($invoice?->tax ?? 0), 2) }}</td></tr>
                    <tr><th>Total</th><td>INR {{ number_format((float) ($invoice?->total ?? $payment?->amount ?? 0), 2) }}</td></tr>
                    <tr><th>Status</th><td>{{ $invoice?->status ?? $payment?->status ?? 'N/A' }}</td></tr>
                    <tr><th>Due Date</th><td>{{ $invoice?->due_date?->format('d M Y') ?? 'N/A' }}</td></tr>
                    <tr><th>Paid At</th><td>{{ $payment?->paid_at?->format('d M Y, h:i A') ?? 'N/A' }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="footer">
            This receipt was generated by Feetrack. You can print this page or download the PDF version.
        </div>
    </div>

    <script>
        if (new URLSearchParams(window.location.search).has('autoprint')) {
            window.addEventListener('load', () => window.print());
        }
    </script>
</body>
</html>
