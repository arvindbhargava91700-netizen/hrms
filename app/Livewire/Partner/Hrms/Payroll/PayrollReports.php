<?php

namespace App\Livewire\Partner\Hrms\Payroll;

use Livewire\Component;
use App\Models\EmployeePayroll;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollReports extends Component
{
    use \App\Livewire\Partner\Hrms\HasPartnerId;
    
    public $year;
    
    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('payroll_viewAny') ||
            auth()->user()->canAccess('payroll_viewBranch') ||
            auth()->user()->canAccess('payroll_viewTeam') ||
            auth()->user()->canAccess('payroll_viewOwn'),
            403
        );
        $this->year = date('Y');
    }

    public function render()
    {
        // Aggregate payroll data for the selected year
        $monthlyData = EmployeePayroll::whereIn('employee_id', $this->getTeamEmployeeIds('payroll_viewAny'))
            ->where('year', $this->year)
            ->where('status', 'paid')
            ->select('month', 
                DB::raw('SUM(basic_salary) as total_basic'),
                DB::raw('SUM(deductions) as total_deductions'),
                DB::raw('SUM(bonuses) as total_bonuses'),
                DB::raw('SUM(net_pay) as total_net')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();
            
        // Format data for chart
        $months = [];
        $netPays = [];
        
        $totalAnnualExpense = 0;
        
        for ($m = 1; $m <= 12; $m++) {
            $formattedMonth = str_pad($m, 2, '0', STR_PAD_LEFT);
            $monthLabel = Carbon::create()->month($m)->format('M');
            
            $dataForMonth = $monthlyData->firstWhere('month', $formattedMonth);
            
            $months[] = $monthLabel;
            
            if ($dataForMonth) {
                $netPays[] = (float)$dataForMonth->total_net;
                $totalAnnualExpense += (float)$dataForMonth->total_net;
            } else {
                $netPays[] = 0;
            }
        }

        $years = range(date('Y') - 3, date('Y'));

        return view('livewire.partner.hrms.payroll.payroll-reports', [
            'months' => $months,
            'netPays' => $netPays,
            'years' => $years,
            'totalAnnualExpense' => $totalAnnualExpense
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Payroll Reports',
            'pageSubtitle' => 'View annual payroll expenses and statistics',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
