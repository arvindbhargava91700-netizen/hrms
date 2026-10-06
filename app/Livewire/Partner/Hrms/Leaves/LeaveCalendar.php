<?php

namespace App\Livewire\Partner\Hrms\Leaves;

use Livewire\Component;
use App\Models\EmployeeLeave;
use App\Models\Holiday;
use App\Livewire\Partner\Hrms\HasPartnerId;
use App\Livewire\Partner\Hrms\Traits\HasHrmsFilters;
use Carbon\Carbon;

class LeaveCalendar extends Component
{
    use HasPartnerId, HasHrmsFilters;
    
    public $currentMonth;
    public $currentYear;
    public $statusFilter = ''; // '' = all (approved + pending), 'approved', 'pending', 'rejected'
    
    public $selectedLeave = null;
    public $selectedDayLeaves = [];
    public $selectedDayDate = '';
    
    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() || 
            auth()->user()->canAccess('leave_viewAny') || 
            auth()->user()->canAccess('leave_viewBranch') || 
            auth()->user()->canAccess('leave_viewTeam') || 
            auth()->user()->canAccess('leave_viewOwn'), 
            403
        );
        
        $this->currentMonth = (int)date('n');
        $this->currentYear = (int)date('Y');
    }
    
    public function previousMonth()
    {
        if ($this->currentMonth == 1) {
            $this->currentMonth = 12;
            $this->currentYear--;
        } else {
            $this->currentMonth--;
        }
    }
    
    public function nextMonth()
    {
        if ($this->currentMonth == 12) {
            $this->currentMonth = 1;
            $this->currentYear++;
        } else {
            $this->currentMonth++;
        }
    }

    public function goToToday()
    {
        $this->currentMonth = (int)date('n');
        $this->currentYear = (int)date('Y');
    }

    public function viewLeaveDetails($leaveId)
    {
        $this->selectedLeave = EmployeeLeave::with(['employee.department', 'employee.branch', 'leaveCategory'])->find($leaveId);
        $this->dispatch('open-leave-modal');
    }

    public function viewDayLeaves($day)
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, $day);
        $this->selectedDayDate = $date->format('l, d M Y');
        
        $query = EmployeeLeave::with(['employee.department', 'employee.branch', 'leaveCategory']);
        $query = $this->applyHrmsFilters($query, 'employee_id', 'leave_viewAny');
        
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        } else {
            $query->whereIn('status', ['approved', 'pending']);
        }
        
        $dateStr = $date->toDateString();
        $this->selectedDayLeaves = $query->where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr)
            ->get();
            
        $this->dispatch('open-day-modal');
    }

    public function render()
    {
        $partnerId = $this->getPartnerId();
        
        // Month calculations
        $dateObj = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $daysInMonth = $dateObj->daysInMonth;
        $monthStart = $dateObj->copy()->startOfDay();
        $monthEnd = Carbon::create($this->currentYear, $this->currentMonth, $daysInMonth)->endOfDay();
        
        // Start day of week (0 = Sunday, 1 = Monday, ... 6 = Saturday)
        $startDayOfWeek = $monthStart->dayOfWeek;
        
        // Query leaves
        $query = EmployeeLeave::with(['employee.department', 'employee.branch', 'leaveCategory']);
        
        // Apply scope & HRMS filters (branch, dept, search)
        $query = $this->applyHrmsFilters($query, 'employee_id', 'leave_viewAny');
        
        // Filter by status if specified
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        } else {
            $query->whereIn('status', ['approved', 'pending']);
        }
        
        // Fetch leaves that overlap with this month
        $monthStartStr = $monthStart->toDateString();
        $monthEndStr = $monthEnd->toDateString();
        
        $leaves = $query->where(function($q) use ($monthStartStr, $monthEndStr) {
            $q->whereBetween('start_date', [$monthStartStr, $monthEndStr])
              ->orWhereBetween('end_date', [$monthStartStr, $monthEndStr])
              ->orWhere(function($sub) use ($monthStartStr, $monthEndStr) {
                  $sub->where('start_date', '<=', $monthStartStr)
                      ->where('end_date', '>=', $monthEndStr);
              });
        })->get();
        
        // Metrics
        $totalApproved = $leaves->where('status', 'approved')->count();
        $totalPending = $leaves->where('status', 'pending')->count();
        $uniqueEmployees = $leaves->pluck('employee_id')->filter()->unique()->count();
        
        // Fetch holidays for this month
        $holidays = Holiday::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
            ->whereYear('date', $this->currentYear)
            ->whereMonth('date', $this->currentMonth)
            ->get()
            ->keyBy(function($h) {
                return (int)Carbon::parse($h->date)->day;
            });
            
        // Map leaves to days of month
        $leavesByDay = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $leavesByDay[$i] = collect();
        }
        
        foreach ($leaves as $leave) {
            $leaveStart = Carbon::parse($leave->start_date)->startOfDay();
            $leaveEnd = Carbon::parse($leave->end_date)->endOfDay();
            
            $start = $leaveStart->lt($monthStart) ? $monthStart->copy() : $leaveStart->copy();
            $end = $leaveEnd->gt($monthEnd) ? $monthEnd->copy() : $leaveEnd->copy();
            
            for ($curr = $start->copy(); $curr->lte($end); $curr->addDay()) {
                $dayNum = (int)$curr->day;
                if (isset($leavesByDay[$dayNum])) {
                    $leavesByDay[$dayNum]->push($leave);
                }
            }
        }

        return view('livewire.partner.hrms.leaves.leave-calendar', [
            'daysInMonth'       => $daysInMonth,
            'startDayOfWeek'    => $startDayOfWeek,
            'leavesByDay'       => $leavesByDay,
            'holidays'          => $holidays,
            'monthName'         => $dateObj->format('F'),
            'totalApproved'     => $totalApproved,
            'totalPending'      => $totalPending,
            'uniqueEmployees'   => $uniqueEmployees,
            'branches'          => $this->getFilterBranches(),
            'departments'       => $this->getFilterDepartments(),
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'Leave Calendar',
            'pageSubtitle' => 'View approved and pending employee leaves',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
