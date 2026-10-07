<?php

namespace App\Livewire\Partner\Hrms\Leaves;

use Livewire\Component;
use App\Models\EmployeeLeave;
use App\Models\LeaveCategory;
use App\Livewire\Partner\Hrms\HasPartnerId;
use Carbon\Carbon;

class ApplyLeave extends Component
{
    use HasPartnerId;

    public $start_date;
    public $end_date;
    public $leave_category_id;
    public $reason;
    public $categories = [];

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('leave_viewOwn'), 403);
        $partnerId = $this->getPartnerId();
        $this->categories = LeaveCategory::where('status', true)->get();
        if ($this->categories->isNotEmpty()) {
            $this->leave_category_id = $this->categories->first()->id;
        }
    }

    public function submit_old()
    {
        $this->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'leave_category_id' => 'required|exists:leave_categories,id',
            'reason' => 'required|string|max:500',
        ]);

        $category = LeaveCategory::findOrFail($this->leave_category_id);
        
        $requestedDays = Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date)) + 1;

        // Calculate used days for this category in current year
        $usedDays = EmployeeLeave::where('employee_id', auth()->id())
            ->where('leave_category_id', $category->id)
            ->whereIn('status', ['approved', 'pending'])
            ->whereYear('start_date', date('Y'))
            ->get()
            ->sum(function ($leave) {
                return Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
            });

        $remainingBalance = max(0, $category->days - $usedDays);

        if ($requestedDays > $remainingBalance) {
            $this->addError('leave_category_id', "Insufficient leave balance for {$category->name}. Available: {$remainingBalance} day(s), Requested: {$requestedDays} day(s).");
            return;
        }

        EmployeeLeave::create([
            'employee_id' => auth()->id(),
            'leave_category_id' => $category->id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'type' => $category->name,
            'reason' => $this->reason,
            'status' => 'pending'
        ]);

        session()->flash('message', 'Leave application submitted successfully!');
        
        $this->reset(['start_date', 'end_date', 'reason']);
    }

    public function submit()
{
    $this->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'leave_category_id' => 'required|exists:leave_categories,id',
        'reason' => 'required|string|max:500',
    ]);

    $category = LeaveCategory::findOrFail($this->leave_category_id);

    $startDate = Carbon::parse($this->start_date);
    $endDate   = Carbon::parse($this->end_date);

    $requestedDays = $startDate->diffInDays($endDate) + 1;

    /*
    |--------------------------------------------------------------------------
    | Employee Joining Date
    |--------------------------------------------------------------------------
    */

    $employee = auth()->user();

    if (!$employee->joining_date) {
        $this->addError(
            'leave_category_id',
            'Employee joining date is not available.'
        );

        return;
    }

    $joiningDate = Carbon::parse($employee->joining_date);

    /*
    |--------------------------------------------------------------------------
    | Calculate Accrued Paid Leave
    |--------------------------------------------------------------------------
    |
    | Rule:
    | - Joining date 1 to 15  => joining month gets 1 leave
    | - Joining date 16+      => joining month gets 0 leave
    | - Previous months       => NOT counted
    | - Every eligible month  => +1 leave
    |
    */

    $currentYear = $startDate->year;

    // Employee joined after the requested year
    if ($joiningDate->year > $currentYear) {

        $accruedLeave = 0;

    } 
    // Employee joined in a previous year
    elseif ($joiningDate->year < $currentYear) {

        // January = 1
        // February = 2
        // March = 3
        // ...
        $accruedLeave = $startDate->month;

    } 
    // Employee joined in the same year
    else {

        $joiningMonth = $joiningDate->month;
        $joiningDay   = $joiningDate->day;

        /*
        |--------------------------------------------------------------------------
        | If joined after 15th, joining month is not counted
        |--------------------------------------------------------------------------
        */

        if ($joiningDay > 15) {
            $joiningMonth++;
        }

        /*
        |--------------------------------------------------------------------------
        | Requested month is before eligible joining month
        |--------------------------------------------------------------------------
        */

        if ($startDate->month < $joiningMonth) {

            $accruedLeave = 0;

        } else {

            /*
            |--------------------------------------------------------------------------
            | Calculate eligible months
            |--------------------------------------------------------------------------
            */

            $accruedLeave = $startDate->month - $joiningMonth + 1;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Used Leave
    |--------------------------------------------------------------------------
    */

    $usedDays = EmployeeLeave::where('employee_id', auth()->id())
        ->where('leave_category_id', $category->id)
        ->whereIn('status', ['approved', 'pending'])
        ->whereYear('start_date', $currentYear)
        ->get()
        ->sum(function ($leave) {

            return Carbon::parse($leave->start_date)
                ->diffInDays(
                    Carbon::parse($leave->end_date)
                ) + 1;
        });

    /*
    |--------------------------------------------------------------------------
    | Remaining Paid Leave
    |--------------------------------------------------------------------------
    */

    $remainingBalance = max(
        0,
        $accruedLeave - $usedDays
    );

    /*
    |--------------------------------------------------------------------------
    | Check Leave Balance (Only if not unlimited)
    |--------------------------------------------------------------------------
    */

    if (!$category->is_unlimited && $requestedDays > $remainingBalance) {

        $this->addError(
            'leave_category_id',
            "Insufficient leave balance for {$category->name}. " .
            "Available: {$remainingBalance} day(s), " .
            "Requested: {$requestedDays} day(s)."
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Create Leave
    |--------------------------------------------------------------------------
    */

    EmployeeLeave::create([
        'employee_id'       => auth()->id(),
        'leave_category_id' => $category->id,
        'start_date'        => $this->start_date,
        'end_date'          => $this->end_date,
        'type'              => $category->name,
        'reason'            => $this->reason,
        'status'             => 'pending',
    ]);

    session()->flash(
        'message',
        'Leave application submitted successfully!'
    );

    $this->reset([
        'start_date',
        'end_date',
        'reason'
    ]);
}


    public function render()
    {
        return view('livewire.partner.hrms.leaves.apply-leave')
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Apply for Leave',
                'pageSubtitle' => 'Submit a new leave application',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
