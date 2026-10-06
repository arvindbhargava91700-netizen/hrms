<?php

namespace App\Livewire\Partner\Hrms;

use App\Models\Lead;
use Livewire\Component;
use App\Models\User;
use App\Models\EmployeeSalaryStructure;
use App\Models\CommissionPayout;
use App\Services\CommissionService;
use Carbon\Carbon;

class EmployeeTargets extends Component
{
    public $activeTab = 'my_target';
    public $currentMonth;
    public $currentYear;

    public function mount()
    {
        $this->currentMonth = (int) date('m');
        $this->currentYear  = (int) date('Y');
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function prevMonth()
    {
        $dt = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $dt->month;
        $this->currentYear  = $dt->year;
    }

    public function nextMonth()
    {
        $dt = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        // Don't go beyond current month
        if ($dt->lessThanOrEqualTo(now()->startOfMonth())) {
            $this->currentMonth = $dt->month;
            $this->currentYear  = $dt->year;
        }
    }

    public function getMyTargetData()
    {
        $user = auth()->user();
        $commissionService = new CommissionService();
        $data = $commissionService->calculateEmployeeCommission($user, $this->currentMonth, $this->currentYear);

        $structure = EmployeeSalaryStructure::where('employee_id', $user->id)->first();
        $history   = CommissionPayout::where('employee_id', $user->id)
                        ->orderBy('year', 'desc')
                        ->orderBy('month', 'desc')
                        ->limit(12)
                        ->get();

        return [
            'metrics'   => $data,
            'structure' => $structure,
            'history'   => $history,
        ];
    }

    public function getTeamTargetData()
    {
        $user = auth()->user();
        $teamMembers = $user->subordinates()->get();

        $commissionService = new CommissionService();
        $teamData = [];

        foreach ($teamMembers as $member) {
            $metrics   = $commissionService->calculateEmployeeCommission($member, $this->currentMonth, $this->currentYear);
            $structure = EmployeeSalaryStructure::where('employee_id', $member->id)->first();

            $teamData[] = [
                'user'      => $member,
                'metrics'   => $metrics,
                'structure' => $structure,
            ];
        }

        return $teamData;
    }

    public function render()
    {
          $user      = auth()->user();
         $stats        = [];
    

        //-------arvind------------
          
         $target = EmployeeSalaryStructure::where('employee_id', $user->id)
            ->value('merchant_target') ?? 0;

        // Employee ke assigned Won leads
        $wonLeads = Lead::where('assigned_to', $user->id)
            ->where('status', 'Won')
            ->count();

        // Achievement percentage
        $percentage = $target > 0
            ? round(($wonLeads / $target) * 100, 2)
            : 0;

             $stats['target']= $target;
            $stats['won_leads']= $wonLeads;
             $stats['percentage'] = $percentage;

            //  dd($stats['target'],$stats['won_leads'],$stats['percentage']);
        //-------endarvind------------

        return view('livewire.partner.hrms.employee-targets', [
            'stats'   => $stats,
            'myData'   => $this->getMyTargetData(),
            'teamData' => $this->getTeamTargetData(),
        ])->layout('layouts.app', [
            'panelName'   => 'Employee Panel',
            'pageTitle'   => 'My Targets & Commissions',
            'pageSubtitle' => 'Track your monthly target and commission payouts',
            'sidebarLinks' => view('partials.sidebar-employee'),
        ]);
    }
}
