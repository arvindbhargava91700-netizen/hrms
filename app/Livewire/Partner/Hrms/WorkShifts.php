<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use App\Models\WorkShift;
use App\Models\HrmsBranch;
use Illuminate\Support\Facades\Auth;

class WorkShifts extends Component
{
    use HasPartnerId;

    public $shifts = [];
    public $branches = [];
    public $name, $start_time, $end_time, $branch_id, $auto_mark_attendance = false, $auto_mark_status = 'absent', $late_tolerance_minutes = 15;
    public $min_present_mins, $min_half_day_mins, $auto_absent_mark_mins, $week_off_days = [];
    public $shiftId = null;
    public $isOpen = false;

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() || 
            auth()->user()->canAccess('shift_viewAny') ||
            auth()->user()->canAccess('shift_viewBranch') ||
            auth()->user()->canAccess('shift_viewTeam') ||
            auth()->user()->canAccess('shift_viewOwn'), 
            403
        );
        $this->loadData();
    }

    public function loadData()
    {
        $this->branches = HrmsBranch::all();
        
        $query = WorkShift::with('branch');
        
        if (!auth()->user()->isPartner() && !auth()->user()->canAccess('shift_viewAny')) {
            if (auth()->user()->canAccess('shift_viewBranch') || auth()->user()->canAccess('shift_viewTeam') || auth()->user()->canAccess('shift_viewOwn')) {
                $query->where('branch_id', auth()->user()->branch_id);
            }
        }
        
        $this->shifts = $query->latest()->get();
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetInputFields();
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->start_time = '';
        $this->end_time = '';
        $this->branch_id = '';
        $this->auto_mark_attendance = false;
        $this->auto_mark_status = 'absent';
        $this->late_tolerance_minutes = 15;
        $this->min_present_mins = '';
        $this->min_half_day_mins = '';
        $this->auto_absent_mark_mins = '';
        $this->week_off_days = [];
        $this->shiftId = null;
    }

    public function calculateMins()
    {
        if ($this->start_time && $this->end_time) {
            try {
                $start = \Carbon\Carbon::createFromFormat('H:i', $this->start_time);
                $end = \Carbon\Carbon::createFromFormat('H:i', $this->end_time);
                
                if ($end->lt($start)) {
                    $end->addDay();
                }

                $totalMins = $start->diffInMinutes($end);
                
                $this->min_present_mins = $totalMins;
                $this->min_half_day_mins = (int) round($totalMins / 2);
                
                if (empty($this->auto_absent_mark_mins)) {
                    $this->auto_absent_mark_mins = $totalMins + 120; // 2 hours after shift ends
                }
            } catch (\Exception $e) {
                // Ignore parse errors
            }
        }
    }

    public function updatedStartTime()
    {
        $this->calculateMins();
    }

    public function updatedEndTime()
    {
        $this->calculateMins();
    }

    public function store()
    {
        // abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('shift_manage'), 403);
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('shift_create'), 403);

        $this->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'branch_id' => 'nullable|exists:branches,id',
            'auto_mark_status' => 'required|in:absent,present,half_day',
            'late_tolerance_minutes' => 'required|integer|min:0',
            'min_present_mins' => 'nullable|integer|min:0',
            'min_half_day_mins' => 'nullable|integer|min:0',
            'auto_absent_mark_mins' => 'nullable|integer|min:0',
            'week_off_days' => 'nullable|array',
        ]);

        WorkShift::updateOrCreate(['id' => $this->shiftId], [
            'partner_id' => null,
            'branch_id' => $this->branch_id ?: null,
            'name' => $this->name,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'auto_mark_attendance' => $this->auto_mark_attendance ? 1 : 0,
            'auto_mark_status' => $this->auto_mark_status,
            'late_tolerance_minutes' => $this->late_tolerance_minutes,
            'min_present_mins' => $this->min_present_mins !== '' ? $this->min_present_mins : null,
            'min_half_day_mins' => $this->min_half_day_mins !== '' ? $this->min_half_day_mins : null,
            'auto_absent_mark_mins' => $this->auto_absent_mark_mins !== '' ? $this->auto_absent_mark_mins : null,
            'week_off_days' => $this->week_off_days ? json_encode($this->week_off_days) : null,
        ]);

        session()->flash('message', $this->shiftId ? 'Shift Updated Successfully.' : 'Shift Created Successfully.');
        $this->closeModal();
        $this->loadData();
    }

    public $viewShift = null;
    public $isViewOpen = false;

    public function view($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('shift_viewAny'), 403);
        
        $this->viewShift = WorkShift::with(['branch', 'employees'])->findOrFail($id);
        $this->isViewOpen = true;
    }

    public function closeViewModal()
    {
        $this->isViewOpen = false;
        $this->viewShift = null;
    }

    public function edit($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('shift_update'), 403);
        
        $shift = WorkShift::findOrFail($id);
        $this->shiftId = $id;
        $this->name = $shift->name;
        $this->start_time = \Carbon\Carbon::parse($shift->start_time)->format('H:i');
        $this->end_time = \Carbon\Carbon::parse($shift->end_time)->format('H:i');
        $this->branch_id = $shift->branch_id;
        $this->auto_mark_attendance = $shift->auto_mark_attendance;
        $this->auto_mark_status = $shift->auto_mark_status;
        $this->late_tolerance_minutes = $shift->late_tolerance_minutes;
        $this->min_present_mins = $shift->min_present_mins;
        $this->min_half_day_mins = $shift->min_half_day_mins;
        $this->auto_absent_mark_mins = $shift->auto_absent_mark_mins;
        $this->week_off_days = $shift->week_off_days ? json_decode($shift->week_off_days, true) : [];
        $this->openModal();
    }

    public function delete($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('shift_delete'), 403);
        
        WorkShift::find($id)->delete();
        session()->flash('message', 'Shift Deleted Successfully.');
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.partner.hrms.work-shifts')
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Work Shifts',
                'pageSubtitle' => 'Manage employee working hours and auto-attendance',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
