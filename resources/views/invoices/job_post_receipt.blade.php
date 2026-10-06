<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>TAX INVOICE</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 20px;
            background: #f4f7f6;
            color: #333;
            font-size: 12px;
            -webkit-font-smoothing: antialiased;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e0e0e0;
            overflow: hidden;
        }
        
        .header {
            background: #1a237e;
            color: #fff;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-title {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 0;
        }
        .header-subtitle {
            font-size: 12px;
            opacity: 0.8;
            margin-top: 4px;
        }
        
        .section {
            padding: 20px 30px;
            border-bottom: 1px solid #eee;
        }
        
        .top-info {
            display: flex;
            justify-content: space-between;
        }
        
        .info-block {
            width: 48%;
        }
        
        .info-title {
            font-size: 11px;
            text-transform: uppercase;
            color: #757575;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        
        .info-content {
            font-size: 13px;
            line-height: 1.6;
        }
        .info-content strong {
            color: #212121;
        }
        
        .invoice-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            background: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #eee;
        }
        .meta-item {
            display: flex;
            flex-direction: column;
        }
        .meta-label {
            font-size: 10px;
            color: #757575;
            text-transform: uppercase;
            font-weight: 600;
        }
        .meta-val {
            font-size: 13px;
            font-weight: 600;
            color: #212121;
            margin-top: 3px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .items-table th {
            background: #f4f6f8;
            color: #455a64;
            font-weight: 600;
            text-align: left;
            padding: 12px;
            font-size: 11px;
            text-transform: uppercase;
            border-bottom: 2px solid #ddd;
        }
        .items-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #eee;
            color: #424242;
            vertical-align: top;
        }
        .items-table th.text-right, .items-table td.text-right {
            text-align: right;
        }
        .items-table th.text-center, .items-table td.text-center {
            text-align: center;
        }
        
        .totals-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 20px 30px;
            background: #fafafa;
            border-bottom: 1px solid #eee;
        }
        
        .amount-words {
            width: 50%;
            font-size: 11px;
            color: #616161;
        }
        .amount-words b {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: #212121;
        }
        
        .totals-table {
            width: 40%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 6px 10px;
            font-size: 13px;
        }
        .totals-table td.label {
            color: #616161;
            text-align: right;
        }
        .totals-table td.value {
            text-align: right;
            font-weight: 600;
            color: #212121;
        }
        .totals-table tr.grand-total td {
            padding-top: 12px;
            font-size: 16px;
            font-weight: 700;
            color: #1a237e;
        }
        
        .tax-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 11px;
        }
        .tax-table th, .tax-table td {
            border: 1px solid #e0e0e0;
            padding: 8px;
            text-align: center;
        }
        .tax-table th { 
            background: #f4f6f8;
            color: #455a64;
            font-weight: 600;
        }
        .tax-table .row-total td {
            background: #fafafa;
            font-weight: 700;
        }
        
        .footer-grid {
            display: flex;
            justify-content: space-between;
            padding: 25px 30px;
            font-size: 11px;
            color: #616161;
            line-height: 1.6;
        }
        .footer-col {
            width: 45%;
        }
        .footer-title {
            color: #212121;
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        .signature-box {
            text-align: right;
            margin-top: 30px;
        }
        .signature-box .sign-name {
            font-weight: 600;
            color: #212121;
            margin-bottom: 40px;
        }
        
        .computer-generated {
            text-align: center;
            padding: 15px;
            font-size: 10px;
            color: #9e9e9e;
            background: #fff;
            border-top: 1px dashed #e0e0e0;
        }
        
        .no-print {
            text-align: right;
            max-width: 800px;
            margin: 0 auto 20px auto;
        }
        .print-btn {
            padding: 10px 20px;
            background: #1a237e;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: background 0.3s;
        }
        .print-btn:hover { background: #283593; }
        
        @media print {
            @page { margin: 5mm; }
            body { background: #fff; padding: 0; font-size: 11px; }
            .invoice-box { box-shadow: none; border: none; width: 100%; }
            .no-print { display: none; }
            .header { -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 10px 20px; }
            .section, .totals-wrapper, .footer-grid { padding: 10px 20px; }
            .tax-table { margin: 10px 0; }
            .signature-box { margin-top: 10px; }
            .signature-box .sign-name { margin-bottom: 25px; }
            .items-table td, .items-table th { padding: 8px 12px; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">Download / Print PDF</button>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div style="display: flex; align-items: center;">
                @php
                    $logoPath = \App\Models\SystemSetting::getSetting('company_logo');
                    $logoBase64 = null;
                    if ($logoPath && file_exists(public_path('storage/' . $logoPath))) {
                        $mime = mime_content_type(public_path('storage/' . $logoPath));
                        $data = file_get_contents(public_path('storage/' . $logoPath));
                        $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode($data);
                    }
                @endphp
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo" style="max-height: 50px; background: white; padding: 4px 10px; border-radius: 4px;">
                @else
                    <h2 style="margin: 0; font-size: 18px;">{{ strtoupper(\App\Models\SystemSetting::getSetting('company_name', config('app.name', 'FEETRACK'))) }}</h2>
                @endif
            </div>
            <div style="text-align: right;">
                <h1 class="header-title">TAX INVOICE</h1>
                <div class="header-subtitle">Original for Recipient</div>
            </div>
        </div>
        
        <div class="section top-info">
            <div class="info-block">
                <div class="info-title">Billed By</div>
                <div class="info-content">
                    <strong>{{ strtoupper(\App\Models\SystemSetting::getSetting('company_name', config('app.name', 'FEETRACK') . ' TECH PRIVATE LIMITED')) }}</strong><br>
                    {{ \App\Models\SystemSetting::getSetting('company_address', 'Feetrack Headquarters, Corporate Office') }}<br>
                    City: {{ \App\Models\SystemSetting::getSetting('company_city', 'Default City') }}<br>
                    State Name: {{ \App\Models\SystemSetting::getSetting('company_state_name', 'Default State') }}, Code: {{ \App\Models\SystemSetting::getSetting('company_state_code', '00') }}<br>
                    @if(\App\Models\SystemSetting::getSetting('company_gstin'))
                    GSTIN: <strong>{{ \App\Models\SystemSetting::getSetting('company_gstin') }}</strong><br>
                    @endif
                    @if(isset($partner->gst_number))
                    GSTIN: <strong>{{ $partner->gst_number }}</strong>
                    @endif
                </div>
            </div>
            
            <div class="info-block">
                <div class="invoice-meta">
                    <div class="meta-item">
                        <span class="meta-label">Invoice No.</span>
                        <span class="meta-val">FT-{{ $transaction->id }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Invoice Date</span>
                        <span class="meta-val">{{ $transaction->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Payment Mode</span>
                        <span class="meta-val">{{ ucfirst($transaction->payment_method ?? 'Paid') }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Reference ID</span>
                        <span class="meta-val">{{ $transaction->reference_id ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="info-title">Billed To (Buyer)</div>
            <div class="info-content">
                <strong>{{ $partner->company_name ?? $partner->name ?? 'N/A' }}</strong><br>
                {{ $partner->address ?? 'Address not provided' }}<br>
                @if(isset($partner->gstin)) GSTIN/UIN: <strong>{{ $partner->gstin }}</strong><br> @endif
                @if(isset($partner->pan)) PAN/IT No: <strong>{{ $partner->pan }}</strong><br> @endif
                Email: {{ $partner->email ?? '' }} | Phone: {{ $partner->mobile ?? '' }}
            </div>
        </div>
        
        <div class="section" style="padding-bottom: 0; border-bottom: none;">
            <table class="items-table">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th width="45%">Description of Services</th>
                        <th width="10%" class="text-center">HSN/SAC</th>
                        <th width="10%" class="text-center">Qty</th>
                        <th width="15%" class="text-right">Rate</th>
                        <th width="15%" class="text-right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalAmt = (float) ($transaction->total_amount ?? $transaction->amount ?? 0);
                        $taxable = $totalAmt / 1.18;
                        $igst = $totalAmt - $taxable;
                    @endphp
                    <tr>
                        <td class="text-center">1</td>
                        <td>
                            <strong style="color: #1a237e;">{{ config('app.name', 'Feetrack') }} Services: Job Post Credits</strong><br>
                            <span style="font-size: 11px; color: #757575; display: inline-block; margin-top: 4px;">
                                Job Title: {{ $jobPost?->job_title ?? 'N/A' }} @if($jobPost && $jobPost->job_code) (#{{ $jobPost->job_code }}) @endif<br>
                                Plan: {{ $jobPost?->plan?->name ?? 'Job Plan' }}
                                @if((float)($jobPost?->referral_budget ?? 0) > 0)
                                <br>Referral Budget Deposit: ₹{{ number_format($jobPost->referral_budget, 2) }}
                                @endif
                            </span>
                        </td>
                        <td class="text-center">998519</td>
                        <td class="text-center">1</td>
                        <td class="text-right">{{ number_format($taxable, 2) }}</td>
                        <td class="text-right">{{ number_format($taxable, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="totals-wrapper">
            <div class="amount-words">
                Amount Chargeable (in words)
                <b>INR {{ class_exists('NumberFormatter') ? ucwords((new NumberFormatter("en", NumberFormatter::SPELLOUT))->format($totalAmt)) : $totalAmt }} Only</b>
            </div>
            
            <table class="totals-table">
                <tr>
                    <td class="label">Taxable Value:</td>
                    <td class="value">₹{{ number_format($taxable, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">IGST (18%):</td>
                    <td class="value">₹{{ number_format($igst, 2) }}</td>
                </tr>
                <tr class="grand-total">
                    <td class="label">Total Amount:</td>
                    <td class="value">₹{{ number_format($totalAmt, 2) }}</td>
                </tr>
            </table>
        </div>
        
        <div class="footer-grid">
            <div class="footer-col">
                <div class="footer-title">Company Information</div>
                <div><b>PAN:</b> {{ \App\Models\SystemSetting::getSetting('company_pan', 'AAAA0000A') }}</div>
                <div><b>CIN:</b> {{ \App\Models\SystemSetting::getSetting('company_cin', 'U00000XX2026PTC000000') }}</div>
                <div style="margin-top: 10px;">Whether tax is payable on a reverse charge basis: <b>No</b></div>
                
                <div class="footer-title" style="margin-top: 20px;">Declaration</div>
                <div style="font-size: 10px; color: #757575;">We declare that this invoice shows the actual price of the goods/ services described and that all particulars are true and correct. All disputes subject to jurisdiction only.</div>
            </div>
            
            <div class="footer-col">
                <div class="footer-title">Bank Details</div>
                <table style="width: 100%; font-size: 11px;">
                    <tr><td width="35%">Account Name:</td><td><b>{{ strtoupper(\App\Models\SystemSetting::getSetting('bank_account_name', config('app.name', 'FEETRACK') . ' PVT LTD')) }}</b></td></tr>
                    <tr><td>Bank Name:</td><td><b>{{ \App\Models\SystemSetting::getSetting('bank_name', 'DEFAULT BANK') }}</b></td></tr>
                    <tr><td>Account No:</td><td><b>{{ \App\Models\SystemSetting::getSetting('bank_account_no', '0000000000') }}</b></td></tr>
                    <tr><td>IFSC Code:</td><td><b>{{ \App\Models\SystemSetting::getSetting('bank_ifsc', 'BKID0000000') }}</b></td></tr>
                </table>
                
                <div class="signature-box">
                    <div class="sign-name">For {{ strtoupper(\App\Models\SystemSetting::getSetting('signatory_name', config('app.name', 'FEETRACK') . ' TECH PRIVATE LIMITED')) }}</div>
                    <div>___________________________</div>
                    <div style="margin-top: 5px; font-size: 10px;">Authorised Signatory</div>
                </div>
            </div>
        </div>
        
        <div class="computer-generated">
            This is a computer-generated document and does not require a physical signature.
        </div>
    </div>
</body>
</html>
