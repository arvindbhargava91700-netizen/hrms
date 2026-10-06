<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProcessNote;
// No need to import HasPartnerId as it is in the same namespace

class ProcessNotes extends Component
{
    use WithPagination;
    use HasPartnerId;

    public $isModalOpen = false;
    public $editingId = null;
    
    public $search = '';
    public $title;
    public $description;
    public $status = 'active';

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'status' => 'required|in:active,inactive',
    ];

    public function mount()
    {
        // Add any necessary authorization checks here
    }

    public function createNote()
    {
        if (auth()->user()->role === 'employee') abort(403);
        $this->reset(['editingId', 'title', 'description', 'status']);
        $this->isModalOpen = true;
    }

    public function editNote($id)
    {
        if (auth()->user()->role === 'employee') abort(403);
        $note = ProcessNote::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->findOrFail($id);

        $this->editingId = $note->id;
        $this->title = $note->title;
        $this->description = $note->description;
        $this->status = $note->status;
        
        $this->isModalOpen = true;
    }

    public function saveNote()
    {
        if (auth()->user()->role === 'employee') abort(403);
        $this->validate();

        ProcessNote::updateOrCreate(
            ['id' => $this->editingId],
            [
                'partner_id' => $this->requirePartnerId(),
                'title' => $this->title,
                'description' => $this->description,
                'status' => $this->status,
            ]
        );

        $this->isModalOpen = false;
        session()->flash('success', 'Process note saved successfully.');
    }

    public function deleteNote($id)
    {
        if (auth()->user()->role === 'employee') abort(403);
        $note = ProcessNote::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->findOrFail($id);
        $note->delete();

        session()->flash('success', 'Process note deleted successfully.');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = ProcessNote::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } });
        
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // If user is employee, only show active process notes
        if (auth()->user()->role === 'employee') {
            $query->where('status', 'active');
        }

        $notes = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.partner.hrms.process-notes', [
            'notes' => $notes
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'Process Notes',
            'pageSubtitle' => 'Manage and view process notes',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
