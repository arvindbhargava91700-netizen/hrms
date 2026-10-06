<?php

namespace App\Livewire\Partner\Hrms\Commission;

use Livewire\Component;
use App\Models\User;
use App\Models\CommissionPayout;
use Carbon\Carbon;
use App\Services\CommissionService;

class CommissionProcess extends Component
{
    use \App\Livewire\Partner\Hrms\HasPartnerId;
    
    public $month;
    public $year;

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() || 
            auth()->user()->canAccess('payroll_viewAny') ||
            auth()->user()->canAccess('payroll_viewBranch') ||
            auth()->user()->canAccess('payroll_viewTeam'),
            403
        );
        
        $this->month = date('m');
        $this->year = date('Y');
    }

    public function processCommissions()
    {
        $this->validate([
            'month' => 'required|numeric|min:1|max:12',
            'year' => 'required|numeric|min:2020|max:2099',
        ]);

        $formattedMonth = str_pad($this->month, 2, '0', STR_PAD_LEFT);
        $partnerId = $this->getPartnerId();
        
        // Get all employees for this partner/manager who have a target structure
        $employees = User::whereIn('id', $this->getTeamEmployeeIds('payroll_viewAny'))
            ->whereHas('payrolls') // or check for EmployeeSalaryStructure
            ->get();
            
        $processedCount = 0;
        $commissionService = new CommissionService();
        
        // Fetch Partner TDS rate
        $tdsPercent = (float)(\App\Models\PartnerSetting::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->where('key', 'commission_tds_percent')->value('value') ?? 5.00);

        foreach ($employees as $employee) {
            // Calculate commissions
            $commissionData = $commissionService->calculateEmployeeCommission($employee, $this->month, $this->year);
            $totalDirectCommission = $commissionData['total_commission'];
            $newBusiness = $commissionData['new_business'];
            $recoveryBusiness = $commissionData['recovery_business'];
            
            // Calculate upline commissions (earned by this employee from their downlines)
            $uplineData = $commissionService->calculateUplineCommissionForManager($employee, $this->month, $this->year);
            $uplineEarned = $uplineData['total'];
            
            $commissionData['upline_commission'] = $uplineEarned;
            $commissionData['upline_details'] = $uplineData['details'];
            $grossPayout = $totalDirectCommission + $uplineEarned;
            
            $tdsAmount = round(($grossPayout * $tdsPercent) / 100, 2);
            $netPayout = max(0, round($grossPayout - $tdsAmount, 2));
            
            $commissionData['tds_percent'] = $tdsPercent;
            $commissionData['tds_amount'] = $tdsAmount;
            $commissionData['net_payout'] = $netPayout;
            
            // Record commission for this employee if they have any target or earned something
            if ($commissionData['target_required'] > 0 || $grossPayout > 0) {
                // Check if already processed
                $exists = CommissionPayout::where('employee_id', $employee->id)
                    ->where('month', $formattedMonth)
                    ->where('year', $this->year)
                    ->exists();

                if (!$exists) {
                    $data=[
                        'employee_id' => $employee->id,
                        'month' => $formattedMonth,
                        'year' => $this->year,
                        'total_new_business' => $newBusiness,
                        'total_recovery_business' => $recoveryBusiness,
                        'target_amount' => $commissionData['target_required'],
                        'commission_earned' => $commissionData['base_commission'],
                        'recovery_earned' => $commissionData['recovery_commission'],
                        'upline_commission_earned' => $uplineEarned,
                        'tds_percent' => $tdsPercent,
                        'tds_amount' => $tdsAmount,
                        'net_payout' => $netPayout,
                        'total_payout' => $grossPayout,
                        'status' => 'pending',
                        'calculation_details' => $commissionData
                    ];
                    // print_r($data);
                    // echo "<pre>";
                    CommissionPayout::create([
                        'employee_id' => $employee->id,
                        'month' => $formattedMonth,
                        'year' => $this->year,
                        'total_new_business' => $newBusiness,
                        'total_recovery_business' => $recoveryBusiness,
                        'target_amount' => $commissionData['target_required'],
                        'commission_earned' => $commissionData['base_commission'],
                        'recovery_earned' => $commissionData['recovery_commission'],
                        'upline_commission_earned' => $uplineEarned,
                        'tds_percent' => $tdsPercent,
                        'tds_amount' => $tdsAmount,
                        'net_payout' => $netPayout,
                        'total_payout' => $grossPayout,
                        'status' => 'pending',
                        'calculation_details' => $commissionData
                    ]);

                    $processedCount++;
                }
            }
        }
        
        if ($processedCount > 0) {
            session()->flash('message', "Successfully generated {$processedCount} commission records for {$formattedMonth}/{$this->year}.");
        } else {
            session()->flash('info', "No new commissions to generate for this period.");
        }
    }

    public function render()
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[str_pad($m, 2, '0', STR_PAD_LEFT)] = Carbon::create()->month($m)->format('F');
        }
        
        $years = range(date('Y') - 1, date('Y') + 1);

        return view('livewire.partner.hrms.commission.commission-process', [
            'months' => $months,
            'years' => $years
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Process Commissions',
            'pageSubtitle' => 'Generate monthly commission payouts separately from payroll',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
