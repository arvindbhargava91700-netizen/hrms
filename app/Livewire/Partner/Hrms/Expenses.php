<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use App\Livewire\Partner\Hrms\Traits\HasHrmsFilters;

class Expenses extends Component
{
    use HasPartnerId, HasHrmsFilters, WithFileUploads;

    public $expenses = [];
    public $employees = [];
    public $categories = [];
    
    public $isModalOpen = false;
    public $editingId = null;
    
    public $employee_id, $expense_category_id, $amount, $quantity, $unit_rate, $category, $date, $description, $status = 'pending';
    public $upload_file;
    public $existing_upload_file = null;
    public $selectedCategory = null;

    // Rich preview modal properties
    public $isPreviewModalOpen = false;
    public $previewFileUrl = null;
    public $previewFileType = 'image';
    public $previewFileTitle = '';

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('expense_viewAny') || auth()->user()->canAccess('expense_viewOwn') || auth()->user()->canAccess('expense_viewBranch') || auth()->user()->canAccess('expense_viewteam'), 403);
        $this->date = Carbon::now()->format('Y-m-d');
        // If user only has viewOwn and tries to access team scope, block them
        if (request('scope') === 'team' && !auth()->user()->isPartner() && !auth()->user()->canAccess('expense_viewAny') && !auth()->user()->canAccess('expense_viewBranch') &&  !auth()->user()->canAccess('expense_viewteam')) {
            abort(403);
        }
        $this->loadData();
    }

    public function loadData()
    {
        $partnerId = $this->getPartnerId();

        // Load active expense categories
        $this->categories = ExpenseCategory::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
            ->where('status', 'active')
            ->get();
        
        $query = Expense::with(['employee', 'categoryRelation']);
        $query = $this->applyHrmsFilters($query, 'employee_id', 'expense_viewAny');
        $query->whereHas('employee', function ($employeeQuery) {
            $employeeQuery->where(function ($q) {
                $q->whereNull('resignation_date')
                    ->orWhereColumn('users.resignation_date', '>', 'expenses.date');
            })->where(function ($q) {
                $q->whereNull('termination_date')
                    ->orWhereColumn('users.termination_date', '>', 'expenses.date');
            });
        });
        $this->expenses = $query->orderBy('date', 'desc')->get();
        
        $this->employees = User::whereIn('id', $this->getTeamEmployeeIds('expense_viewAny'))
            ->availableForHrmsAssignment()
            ->get();
    }

    public function updatedExpenseCategoryId($value)
    {
        $cat = ExpenseCategory::find($value);
        $this->selectedCategory = $cat;

        if ($cat) {
            $this->category = $cat->name;
            if ($cat->type === 'per_unit') {
                $this->unit_rate = $cat->rate_per_unit;
                $this->calculateAmount();
            } else {
                $this->quantity = null;
                $this->unit_rate = null;
            }
        } else {
            $this->selectedCategory = null;
            $this->unit_rate = null;
            $this->quantity = null;
        }
    }

    public function updatedQuantity($value)
    {
        $this->calculateAmount();
    }

    public function calculateAmount()
    {
        if ($this->selectedCategory && $this->selectedCategory->type === 'per_unit') {
            $qty = (float) $this->quantity;
            $rate = (float) ($this->selectedCategory->rate_per_unit ?? 0);
            $this->amount = round($qty * $rate, 2);
        }
    }

    public function createExpense()
    {
        abort_unless(auth()->user()->canAccess('expense_create'), 403);
        $this->reset(['editingId', 'employee_id', 'expense_category_id', 'amount', 'quantity', 'unit_rate', 'category', 'description', 'upload_file', 'existing_upload_file']);
        $this->selectedCategory = null;
        $this->employee_id = Auth::id();
        $this->date = Carbon::now()->format('Y-m-d');
        $this->status = 'pending';
        $this->isModalOpen = true;
    }

    public function editExpense($id)
    {
        abort_unless(auth()->user()->canAccess('expense_update') || Expense::where('employee_id', Auth::id())->where('id', $id)->exists(), 403);
        $expense = Expense::findOrFail($id);
        $this->editingId = $expense->id;
        $this->employee_id = $expense->employee_id;
        $this->expense_category_id = $expense->expense_category_id;
        $this->amount = $expense->amount;
        $this->quantity = $expense->quantity;
        $this->unit_rate = $expense->unit_rate;
        $this->category = $expense->category;
        $this->date = $expense->date ? Carbon::parse($expense->date)->format('Y-m-d') : null;
        $this->description = $expense->description;
        $this->existing_upload_file = $expense->upload_file;
        $this->upload_file = null;
        $this->selectedCategory = $expense->expense_category_id ? ExpenseCategory::find($expense->expense_category_id) : null;
        $this->isModalOpen = true;
    }

    public function removeUploadFile()
    {
        $this->upload_file = null;
    }

    public function removeExistingFile()
    {
        $this->existing_upload_file = null;
    }

    public function openPreviewModal($url, $title = 'Receipt Proof', $fileType = null)
    {
        $this->previewFileUrl = $url;
        $this->previewFileTitle = $title;
        if ($fileType) {
            $this->previewFileType = $fileType;
        } else {
            $cleanPath = parse_url($url, PHP_URL_PATH) ?? $url;
            $ext = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));
            $this->previewFileType = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'jfif']) ? 'image' : ($ext === 'pdf' ? 'pdf' : 'image');
        }
        $this->isPreviewModalOpen = true;
    }

    public function closePreviewModal()
    {
        $this->isPreviewModalOpen = false;
        $this->previewFileUrl = null;
        $this->previewFileTitle = '';
    }

    public function saveExpense()
    {
        abort_unless(auth()->user()->canAccess($this->editingId ? 'expense_update' : 'expense_create') || ($this->editingId && Expense::where('employee_id', Auth::id())->where('id', $this->editingId)->exists()), 403);
        
        $rules = [
            'employee_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,approved,rejected',
        ];

        // File validation: required on create, or if no existing file is attached
        if (!$this->editingId || !$this->existing_upload_file) {
            $rules['upload_file'] = 'required|file|mimes:jpg,jpeg,png,webp,pdf,doc,docx|max:10240';
        } else {
            $rules['upload_file'] = 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,doc,docx|max:10240';
        }

        if (count($this->categories) > 0) {
            $rules['expense_category_id'] = 'required|exists:expense_categories,id';
        } else {
            $rules['category'] = 'required|string|max:255';
        }

        $cat = $this->expense_category_id ? ExpenseCategory::find($this->expense_category_id) : null;
        
        if ($cat && $cat->type === 'per_unit') {
            $rules['quantity'] = 'required|numeric|min:0.01';
        } else {
            $rules['amount'] = 'required|numeric|min:0.01';
        }

        $this->validate($rules, [
            'upload_file.required' => 'Please upload a receipt/bill image or document (JPG, PNG, WEBP, PDF).',
            'upload_file.mimes'    => 'The uploaded file must be a file of type: JPG, JPEG, PNG, WEBP, PDF, DOC, DOCX.',
            'upload_file.max'      => 'The uploaded file may not be greater than 10MB.',
        ]);

        abort_unless(User::whereKey($this->employee_id)->availableForHrmsAssignment($this->date)->exists(), 403);

        // Max limit validation
        if ($cat && $cat->type === 'max_limit' && $cat->max_limit_amount > 0) {
            if ((float)$this->amount > (float)$cat->max_limit_amount) {
                $this->addError('amount', "Amount cannot exceed the category max limit of ₹" . number_format($cat->max_limit_amount, 2));
                return;
            }
        }

        if ($cat && $cat->type === 'per_unit') {
            $this->category = $cat->name;
            $this->unit_rate = $cat->rate_per_unit;
            $this->amount = round((float)$this->quantity * (float)$cat->rate_per_unit, 2);
        } elseif ($cat) {
            $this->category = $cat->name;
        }

        $filePath = $this->existing_upload_file;
        if ($this->upload_file) {
            $filePath = $this->upload_file->store('expenses', 'public');
            
            // Mirror to public/storage if directory exists
            $pubDir = public_path('storage/expenses');
            if (!file_exists($pubDir)) {
                @mkdir($pubDir, 0755, true);
            }
            $storedFullPath = storage_path('app/public/' . $filePath);
            $pubFullPath = public_path('storage/' . $filePath);
            if (file_exists($storedFullPath) && !file_exists($pubFullPath)) {
                @copy($storedFullPath, $pubFullPath);
            }
        }

        Expense::updateOrCreate(
            ['id' => $this->editingId],
            [
                'employee_id' => $this->employee_id,
                'expense_category_id' => $this->expense_category_id,
                'amount' => $this->amount,
                'quantity' => $this->quantity,
                'unit_rate' => $this->unit_rate,
                'category' => $this->category,
                'date' => $this->date,
                'description' => $this->description,
                'status' => $this->status,
                'upload_file' => $filePath,
            ]
        );

        $this->isModalOpen = false;
        session()->flash('success', 'Expense claim saved successfully.');
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.partner.hrms.expenses')
            ->layout('layouts.app', [
                'panelName'    => 'HRMS Module',
                'pageTitle'    => 'Employee Expenses',
                'pageSubtitle' => 'Manage claims and expenses',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
