<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $payroll->employee->name }} - {{ date('F', mktime(0,0,0,$payroll->month,1)) }} {{ $payroll->year }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #fff;
            color: #000;
            font-size: 11px;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            border: 1px solid #000;
        }
        .header-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding: 5px;
            text-transform: uppercase;
        }
        
        .top-section {
            display: flex;
            border-bottom: 1px solid #000;
        }
        .company-details {
            flex: 6;
            padding: 10px;
            border-right: 1px solid #000;
        }
        .company-details img {
            max-width: 150px;
            max-height: 50px;
            margin-bottom: 10px;
            display: block;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-address {
            white-space: pre-line;
            line-height: 1.4;
        }
        
        .invoice-details {
            flex: 4;
            display: flex;
            flex-direction: column;
        }
        .invoice-details > div {
            flex: 1;
            padding: 5px 10px;
            border-bottom: 1px solid #000;
        }
        .invoice-details > div:last-child {
            border-bottom: none;
        }
        .invoice-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-details table td {
            vertical-align: top;
            width: 50%;
        }
        
        .buyer-section {
            display: flex;
            border-bottom: 1px solid #000;
        }
        .buyer-left {
            flex: 6;
            padding: 10px;
            border-right: 1px solid #000;
        }
        .buyer-right {
            flex: 4;
            padding: 10px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th, .items-table td {
            border-bottom: 1px solid #000;
            border-right: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
        }
        .items-table th:last-child, .items-table td:last-child {
            border-right: none;
        }
        .items-table th {
            text-align: center;
            font-weight: bold;
        }
        
        .amount-words {
            padding: 5px 10px;
            border-bottom: 1px solid #000;
            font-style: italic;
            font-size: 11px;
        }
        
        .footer-section {
            display: flex;
            padding: 10px;
        }
        .footer-left {
            flex: 6;
            border-right: 1px solid #000;
            padding-right: 10px;
        }
        .footer-right {
            flex: 4;
            padding-left: 10px;
            text-align: right;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .computer-generated {
            text-align: center;
            padding: 5px;
            border-top: 1px solid #000;
            font-size: 10px;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-weight-bold { font-weight: bold; }
        
        .action-buttons {
            text-align: right;
            max-width: 800px;
            margin: 0 auto 15px auto;
        }
        .print-btn {
            padding: 8px 15px;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            font-size: 12px;
        }
        
        @media print {
            .action-buttons { display: none; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>
    <div class="action-buttons">
        <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>
    </div>

    <div class="invoice-box">
        <div class="header-title">PAYSLIP FOR {{ strtoupper(date('F Y', mktime(0,0,0,$payroll->month,1, $payroll->year))) }}</div>
        
        <div class="top-section">
            <div class="company-details">
                @if($logo)
                    <img src="{{ asset('storage/' . $logo) }}" alt="Company Logo">
                @endif
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-address">{{ $companyAddress }}</div>
            </div>
            <div class="invoice-details">
                <div>
                    <table border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>
                                <div>Payslip No.</div>
                                <div class="font-weight-bold">PS-{{ str_pad($payroll->id, 5, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td>
                                <div>Dated</div>
                                <div class="font-weight-bold">{{ now()->format('d-M-Y') }}</div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div>
                    <table border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>
                                <div>Month / Year</div>
                                <div class="font-weight-bold">{{ date('F', mktime(0,0,0,$payroll->month,1)) }} {{ $payroll->year }}</div>
                            </td>
                            <td>
                                <div>Status</div>
                                <div class="font-weight-bold">PAID</div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="buyer-section">
            <div class="buyer-left">
                <div class="font-weight-bold">Employee Details:</div>
                <div><b>Name:</b> {{ $payroll->employee->name }}</div>
                <div><b>Email:</b> {{ $payroll->employee->email }}</div>
                <div><b>Mobile:</b> {{ $payroll->employee->mobile ?? 'N/A' }}</div>
            </div>
            <div class="buyer-right">
                <div><b>Department:</b> {{ $payroll->employee->department->name ?? 'N/A' }}</div>
                <div><b>Role:</b> {{ ucwords(str_replace('_', ' ', $payroll->employee->role)) }}</div>
                <div><b>Bank A/c:</b> XXXXXXXXXX</div>
            </div>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th width="40%">Earnings</th>
                    <th width="10%">Amount (₹)</th>
                    <th width="40%">Deductions</th>
                    <th width="10%">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <table style="width:100%; border:none;">
                            <tr><td style="border:none; padding: 2px 0;">Basic Salary</td><td style="border:none; padding: 2px 0; text-align:right;">{{ number_format($payroll->basic_salary, 2) }}</td></tr>
                            
                            @if(is_array($payroll->allowances_breakdown))
                                @foreach($payroll->allowances_breakdown as $key => $val)
                                    @if($val > 0)
                                    <tr><td style="border:none; padding: 2px 0;">{{ ucwords(str_replace('_', ' ', $key)) }}</td><td style="border:none; padding: 2px 0; text-align:right;">{{ number_format($val, 2) }}</td></tr>
                                    @endif
                                @endforeach
                            @endif
                            
                            @if($payroll->commissions > 0)
                            <tr><td style="border:none; padding: 2px 0;">Commissions</td><td style="border:none; padding: 2px 0; text-align:right;">{{ number_format($payroll->commissions, 2) }}</td></tr>
                            @endif
                            
                            @if($payroll->bonuses > 0)
                            <tr><td style="border:none; padding: 2px 0;">Bonuses / Other</td><td style="border:none; padding: 2px 0; text-align:right;">{{ number_format($payroll->bonuses, 2) }}</td></tr>
                            @endif
                        </table>
                    </td>
                    @php
                        $totalAllowances = is_array($payroll->allowances_breakdown) ? array_sum(array_map('floatval', $payroll->allowances_breakdown)) : 0;
                        $totalEarnings = $payroll->basic_salary + $totalAllowances + $payroll->commissions + $payroll->bonuses;
                    @endphp
                    <td class="text-right font-weight-bold" style="vertical-align: bottom;">{{ number_format($totalEarnings, 2) }}</td>
                    
                    <td>
                        <table style="width:100%; border:none;">
                            @php
                                $hasDeductions = false;
                                $breakdownSum = is_array($payroll->deductions_breakdown) ? array_sum(array_map('floatval', $payroll->deductions_breakdown)) : 0;
                                $unexplained = $payroll->deductions - $breakdownSum;
                            @endphp
                            
                            @if(is_array($payroll->deductions_breakdown))
                                @foreach($payroll->deductions_breakdown as $key => $val)
                                    @if($val > 0)
                                        @php $hasDeductions = true; @endphp
                                        <tr><td style="border:none; padding: 2px 0;">{{ ucwords(str_replace('_', ' ', $key)) }}</td><td style="border:none; padding: 2px 0; text-align:right;">{{ number_format($val, 2) }}</td></tr>
                                    @endif
                                @endforeach
                            @endif
                            
                            @if($unexplained > 0.01)
                                @php $hasDeductions = true; @endphp
                                <tr><td style="border:none; padding: 2px 0;">Other Deductions</td><td style="border:none; padding: 2px 0; text-align:right;">{{ number_format($unexplained, 2) }}</td></tr>
                            @endif
                            
                            @if(!$hasDeductions)
                                <tr><td style="border:none; padding: 2px 0; color: #777;">No deductions</td></tr>
                            @endif
                        </table>
                    </td>
                    <td class="text-right font-weight-bold" style="vertical-align: bottom;">{{ number_format($payroll->deductions, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right font-weight-bold">Total Earnings (A)</td>
                    <td class="text-right font-weight-bold">{{ number_format($totalEarnings, 2) }}</td>
                    <td class="text-right font-weight-bold">Total Deductions (B)</td>
                    <td class="text-right font-weight-bold">{{ number_format($payroll->deductions, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right font-weight-bold" style="font-size: 14px; padding: 10px;">Net Salary Payable (A - B)</td>
                    <td class="text-right font-weight-bold" style="font-size: 14px; padding: 10px;">₹{{ number_format($payroll->net_pay, 2) }}</td>
                </tr>
            </tbody>
        </table>
        
        <div class="amount-words">
            Amount Chargeable (in words)<br>
            <b>INR {{ class_exists('NumberFormatter') ? ucwords((new NumberFormatter("en", NumberFormatter::SPELLOUT))->format($payroll->net_pay)) : $payroll->net_pay }} Only</b>
        </div>
        
        <div class="footer-section">
            <div class="footer-left">
                @if($termsConditions)
                <div class="font-weight-bold">Terms & Conditions</div>
                <div style="font-size: 10px; margin-top: 5px;">{!! $termsConditions !!}</div>
                @else
                <div class="font-weight-bold">Declaration</div>
                <div style="font-size: 10px; margin-top: 5px;">We declare that this payslip shows the actual remuneration details and that all particulars are true and correct.</div>
                @endif
            </div>
            <div class="footer-right">
                <div style="margin-top: 10px;">
                    @if($signature)
                        <img src="{{ asset('storage/' . $signature) }}" alt="Authorized Signature" style="width: 100px; max-height: 50px; display: inline-block;">
                    @else
                        <br><br><br>
                    @endif
                    <div class="font-weight-bold">{{ $companyName }}</div>
                    <div style="margin-top: 5px; font-size: 10px;">{{ $signatory }}</div>
                    <div style="font-size: 10px;">Authorised Signatory</div>
                </div>
            </div>
        </div>
        
        <div class="computer-generated">
            This is a computer-generated payslip and does not require a physical signature.
        </div>
    </div>
</body>
</html>
