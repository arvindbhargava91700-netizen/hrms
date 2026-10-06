<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\CompanyDocument;
use Illuminate\Support\Facades\Storage;

class CompanyDocuments extends Component
{
    use WithPagination;
    use WithFileUploads;
    use HasPartnerId;

    public $search = '';
    public $title;
    public $files = [];
    public $status = 'active';

    public $editingId = null;
    public $isModalOpen = false;

    protected $rules = [
        'title' => 'required|string|max:255',
        'files.*' => 'max:10240', // 10MB max per file
        'status' => 'required|in:active,inactive',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->title = '';
        $this->files = [];
        $this->status = 'active';
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function createDocument()
    {
        if (auth()->user()->role === 'employee') abort(403);
        $this->openModal();
    }

    public function editDocument($id)
    {
        if (auth()->user()->role === 'employee') abort(403);
        $document = CompanyDocument::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->findOrFail($id);
        $this->editingId = $id;
        $this->title = $document->title;
        $this->status = $document->status;
        $this->isModalOpen = true;
    }

    public function saveDocument()
    {
        if (auth()->user()->role === 'employee') abort(403);
        
        $this->validate();

        $filePaths = [];
        
        if ($this->editingId) {
            $document = CompanyDocument::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->findOrFail($this->editingId);
            $filePaths = $document->files ?? [];
        }

        if ($this->files) {
            foreach ($this->files as $file) {
                $path = $file->store('company_documents', 'public');
                $filePaths[] = $path;
            }
        }

        if (!$this->editingId && empty($filePaths)) {
            $this->addError('files', 'Please upload at least one file.');
            return;
        }

        CompanyDocument::updateOrCreate(
            ['id' => $this->editingId],
            [
                'partner_id' => $this->editingId
                    ? CompanyDocument::findOrFail($this->editingId)->partner_id
                    : $this->requirePartnerId(),
                'title' => $this->title,
                'files' => $filePaths,
                'status' => $this->status,
            ]
        );

        $this->closeModal();
        session()->flash('message', $this->editingId ? 'Document Updated Successfully.' : 'Document Created Successfully.');
    }

    public function deleteDocument($id)
    {
        if (auth()->user()->role === 'employee') abort(403);
        $document = CompanyDocument::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->findOrFail($id);
        
        if ($document->files) {
            foreach ($document->files as $file) {
                Storage::disk('public')->delete($file);
            }
        }
        
        $document->delete();
        session()->flash('message', 'Document Deleted Successfully.');
    }

    public function render()
    {
        $query = CompanyDocument::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })
            ->where('title', 'like', '%' . $this->search . '%');

        if (auth()->user()->role === 'employee') {
            $query->where('status', 'active');
        }

        $documents = $query->latest()->paginate(10);

        return view('livewire.partner.hrms.company-documents', [
            'documents' => $documents
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'Company Documents',
            'pageSubtitle' => 'Manage and view company documents',
            'sidebarLinks' => auth()->user()->role === 'employee' ? view('partials.sidebar-employee') : view('partials.sidebar-partner'),
        ]);
    }
}
