<?php

namespace App\Http\Controllers;

use App\Models\EmployeePayroll;
use App\Models\PartnerSetting;

class PayslipController extends Controller
{
    public function download($id)
    {
        $payroll = EmployeePayroll::with('employee.department', 'employee.designation')->findOrFail($id);
        $user = auth()->user();

        // Security check: Must be the employee who owns the payslip OR their partner/manager
        if (! $user->isSuperAdmin() && $user->id !== $payroll->employee_id && $user->id !== $payroll->employee->parent_id && ! $user->isPartner()) {
            abort(403, 'Unauthorized access to payslip.');
        }

        // Must be paid to download
        if ($payroll->status !== 'paid') {
            abort(403, 'Payslip is not available until the payroll is marked as paid.');
        }

        // Get Partner ID (either the logged-in partner, or the employee's partner/parent)
        $partnerId = $payroll->employee->parent_id;

        // Fetch Payslip Settings
        $companyName = PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'payslip_company_name')->value('value') ?? 'Your Company Name';
        $companyAddress = PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'payslip_company_address')->value('value') ?? 'Your Company Address';
        $signatory = PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'payslip_authorized_signatory')->value('value') ?? 'Authorized Signatory';
        $logo = PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'payslip_logo')->value('value');
        $signature = PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'payslip_signature')->value('value');
        $termsConditions = PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'payslip_terms_conditions')->value('value');

        return view('pdf.payslip', [
            'payroll' => $payroll,
            'companyName' => $companyName,
            'companyAddress' => $companyAddress,
            'signatory' => $signatory,
            'logo' => $logo,
            'signature' => $signature,
            'termsConditions' => $termsConditions,
        ]);
    }
}
