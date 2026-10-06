<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EmployeeDocuments extends Component
{
    use WithPagination;
    use WithFileUploads;
    use HasPartnerId;
    use Traits\HasHrmsFilters;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $search = '';
    public $categoryFilter = '';
    public $statusFilter = '';
    public $expiringFilter = false;

    // Form Fields
    public $documentId = null;
    public $isEditMode = false;
    public $employee_id = '';
    public $document_category = 'kyc';
    public $document_type = 'aadhaar';
    public $title = '';
    public $document_number = '';
    public $new_file;
    public $existing_file_path = '';
    public $issue_date = '';
    public $expiry_date = '';
    public $status = 'pending_verification';
    public $remarks = '';

    // Modals
    public $isFormModalOpen = false;
    public $isViewModalOpen = false;
    public $viewDocument = null;

    protected function rules()
    {
        return [
            'employee_id'       => 'required|exists:users,id',
            'document_category' => 'required|in:kyc,offer_letter,appointment_letter,agreement,other',
            'document_type'     => 'required|string|max:100',
            'title'             => 'required|string|max:255',
            'document_number'   => 'nullable|string|max:100',
            'new_file'          => $this->isEditMode ? 'nullable|file|max:10240' : 'required|file|max:10240', // Max 10MB
            'issue_date'        => 'nullable|date',
            'expiry_date'       => 'nullable|date|after_or_equal:issue_date',
            'status'            => 'required|in:pending_verification,verified,rejected,expired',
            'remarks'           => 'nullable|string',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function toggleExpiringFilter()
    {
        $this->expiringFilter = !$this->expiringFilter;
        $this->resetPage();
    }

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('document_viewAny') ||
            auth()->user()->canAccess('document_viewBranch') ||
            auth()->user()->canAccess('document_viewTeam') ||
            auth()->user()->canAccess('document_viewOwn'),
            403
        );
    }

    public function render()
    {
        $user = auth()->user();
        $query = EmployeeDocument::query()->with(['employee', 'creator']);



        $allowedIds = $this->getFilteredEmployeeIds('document_viewAny');
        $query->where(function ($q) use ($allowedIds) {
            $q->whereIn('employee_id', $allowedIds)
              ->orWhereIn('created_by', $allowedIds);
        });

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('name', 'like', "%{$search}%")
                         ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        if ($this->categoryFilter) {
            $query->where('document_category', $this->categoryFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->expiringFilter) {
            $query->whereNotNull('expiry_date')
                  ->where('expiry_date', '<=', Carbon::now()->addDays(30));
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(10);

        // Compute Stat Card Counts
        $baseQuery = EmployeeDocument::where(function ($q) use ($allowedIds) {
                $q->whereIn('employee_id', $allowedIds)
                  ->orWhereIn('created_by', $allowedIds);
            });

        $totalDocs    = (clone $baseQuery)->count();
        $verifiedDocs = (clone $baseQuery)->where('status', 'verified')->count();
        $pendingDocs  = (clone $baseQuery)->where('status', 'pending_verification')->count();
        $expiringDocs = (clone $baseQuery)->whereNotNull('expiry_date')
                            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
                            ->count();

        // Employees List for Form Dropdown
        // Show all employees for document assignment
        $employees = User::where('role', 'employee')->orderBy('name')->get();

        return view('livewire.partner.hrms.employee-documents', [
            'documents'         => $documents,
            'employees'         => $employees,
            'totalDocs'         => $totalDocs,
            'verifiedDocs'      => $verifiedDocs,
            'pendingDocs'       => $pendingDocs,
            'expiringDocs'      => $expiringDocs,
            'filterBranches'    => $this->getFilterBranches(),
            'filterDepartments' => $this->getFilterDepartments(),
            'filterEmployees'   => $this->getFilterEmployees('document_viewAny'),
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'Employee Documents & KYC',
            'pageSubtitle' => 'Manage Aadhaar, PAN, Bank details, Offer/Appointment letters, Contracts, and document expiry tracking',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }

    public function createDocument()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('document_create'), 403);
        $this->resetValidation();
        $this->reset([
            'documentId', 'isEditMode', 'employee_id', 'document_category', 
            'document_type', 'title', 'document_number', 'new_file', 
            'existing_file_path', 'issue_date', 'expiry_date', 'remarks'
        ]);
        $this->document_category = 'kyc';
        $this->document_type = 'aadhaar';
        $this->status = 'pending_verification';
        $this->isFormModalOpen = true;
    }

    public function editDocument($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('document_update'), 403);
        $doc = EmployeeDocument::findOrFail($id);

        $this->resetValidation();
        $this->documentId = $doc->id;
        $this->isEditMode = true;
        $this->employee_id = (string) $doc->employee_id;
        $this->document_category = (string) $doc->document_category;
        $this->document_type = (string) $doc->document_type;
        $this->title = $doc->title;
        $this->document_number = $doc->document_number;
        $this->existing_file_path = $doc->file_path;
        $this->new_file = null;
        $this->issue_date = $doc->issue_date ? $doc->issue_date->format('Y-m-d') : '';
        $this->expiry_date = $doc->expiry_date ? $doc->expiry_date->format('Y-m-d') : '';
        $this->status = (string) $doc->status;
        $this->remarks = $doc->remarks;

        $this->isFormModalOpen = true;
    }

    public function viewDocumentDetails($id)
    {
        $this->viewDocument = EmployeeDocument::with(['employee', 'creator', 'partner'])->findOrFail($id);
        $this->isViewModalOpen = true;
    }

    public function saveDocument()
    {
        $user = auth()->user();
        abort_unless($user->isPartner() || $user->canAccess($this->isEditMode ? 'document_update' : 'document_create'), 403);

        $this->validate();

        $filePath = $this->existing_file_path;

        if ($this->new_file) {
            $filePath = $this->new_file->store('employee_documents', 'public');
        }

        if ($this->isEditMode && $this->documentId) {
            $doc = EmployeeDocument::findOrFail($this->documentId);
            $doc->update([
                'employee_id'       => $this->employee_id,
                'document_category' => $this->document_category,
                'document_type'     => $this->document_type,
                'title'             => $this->title,
                'document_number'   => $this->document_number,
                'file_path'         => $filePath,
                'issue_date'        => $this->issue_date ?: null,
                'expiry_date'       => $this->expiry_date ?: null,
                'status'            => $this->status,
                'remarks'           => $this->remarks,
            ]);
            session()->flash('success', 'Employee Document updated successfully.');
        } else {
            EmployeeDocument::create([
                'partner_id'        => $this->requirePartnerId(),
                'created_by'        => auth()->id(),
                'employee_id'       => $this->employee_id,
                'document_category' => $this->document_category,
                'document_type'     => $this->document_type,
                'title'             => $this->title,
                'document_number'   => $this->document_number,
                'file_path'         => $filePath,
                'issue_date'        => $this->issue_date ?: null,
                'expiry_date'       => $this->expiry_date ?: null,
                'status'            => $this->status,
                'remarks'           => $this->remarks,
            ]);
            session()->flash('success', 'Employee Document uploaded successfully.');
        }

        $this->closeModals();
    }

    public function deleteDocument($id)
    {
        $user = auth()->user();
        abort_unless($user->isPartner() || $user->canAccess('document_delete'), 403);

        $doc = EmployeeDocument::findOrFail($id);
        if ($doc->file_path) {
            Storage::disk('public')->delete($doc->file_path);
        }
        $doc->delete();

        session()->flash('success', 'Employee Document deleted successfully.');
    }

    public function closeModals()
    {
        $this->isFormModalOpen = false;
        $this->isViewModalOpen = false;
        $this->reset([
            'documentId', 'isEditMode', 'employee_id', 'document_category', 
            'document_type', 'title', 'document_number', 'new_file', 
            'existing_file_path', 'issue_date', 'expiry_date', 'remarks', 'viewDocument'
        ]);
    }
}
