<?php

namespace App\Livewire\Partner\Hrms\Attendance;

use Livewire\Component;
use App\Models\AttendanceChecklist;

class AttendanceSettings extends Component
{
    public $checklists = [];
    public $newQuestion = '';
    public $newMode = 'both';

    public function mount()
    {
        abort_unless(auth()->user()->canAccess('attendance_update'), 403);
        $this->loadChecklists();
    }

    public function loadChecklists()
    {
        $this->checklists = AttendanceChecklist::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', auth()->id()); } })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function addQuestion()
    {
        abort_unless(auth()->user()->canAccess('attendance_update'), 403);
        $this->validate([
            'newQuestion' => 'required|string|max:255',
            'newMode' => 'required|in:punch_in,punch_out,both',
        ]);

        AttendanceChecklist::create([
            'partner_id' => auth()->id(),
            'question' => $this->newQuestion,
            'mode' => $this->newMode,
            'is_active' => true,
        ]);

        $this->newQuestion = '';
        $this->newMode = 'both';
        $this->loadChecklists();
        session()->flash('message', 'Checklist question added successfully.');
    }

    public function toggleActive($id)
    {
        abort_unless(auth()->user()->canAccess('attendance_update'), 403);
        $checklist = AttendanceChecklist::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', auth()->id()); } })->find($id);
        if ($checklist) {
            $checklist->update([
                'is_active' => !$checklist->is_active
            ]);
            $this->loadChecklists();
            session()->flash('message', 'Status updated successfully.');
        }
    }

    public function deleteQuestion($id)
    {
        abort_unless(auth()->user()->canAccess('attendance_update'), 403);
        $checklist = AttendanceChecklist::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', auth()->id()); } })->find($id);
        if ($checklist) {
            $checklist->delete();
            $this->loadChecklists();
            session()->flash('message', 'Checklist question deleted successfully.');
        }
    }

    public function render()
    {
        return view('livewire.partner.hrms.attendance.attendance-settings')
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Attendance Settings',
                'pageSubtitle' => 'Manage attendance checklist requirements',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
