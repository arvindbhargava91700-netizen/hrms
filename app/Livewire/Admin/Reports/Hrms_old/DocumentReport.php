<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use App\Models\EmployeeDocument;
use App\Models\User;
use Carbon\Carbon;
use Livewire\WithPagination;

class DocumentReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';
    public $partnerFilter = '';


    public $startDate;
    public $endDate;
    public $employeeId = '';
    public $categoryFilter = '';
    public $statusFilter = '';
    public $expiringFilter = false;
    public $search = '';

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updated()
    {
        $this->resetPage();
    }

    public function toggleExpiringFilter()
    {
        $this->expiringFilter = !$this->expiringFilter;
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $query = EmployeeDocument::query();
        if ($this->partnerFilter) { $query->where('partner_id', $this->partnerFilter); }


        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
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

        if ($this->startDate && $this->endDate && !$this->expiringFilter) {
            $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        }

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

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with(['employee', 'creator'])->latest()->paginate(20);
    }

    public function getEmployeesProperty()
    {
        return User::where(function ($q) {
            $q
              ->orWhere('id', "");
        })->orderBy('name')->get();
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['employee', 'creator'])->latest()->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=employee_documents_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Emp ID', 'Employee Name', 'Document Category', 'Document Type', 'Document Title', 'Document Number', 'Issue Date', 'Expiry Date', 'Verification Status'];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    optional($row->employee)->employee_code ?? substr(optional($row->employee)->id, 0, 8),
                    optional($row->employee)->name,
                    ucwords(str_replace('_', ' ', $row->document_category)),
                    ucwords(str_replace('_', ' ', $row->document_type)),
                    $row->title,
                    $row->document_number ?? '-',
                    $row->issue_date ? $row->issue_date->format('Y-m-d') : '-',
                    $row->expiry_date ? $row->expiry_date->format('Y-m-d') : 'No Expiry',
                    ucwords(str_replace('_', ' ', $row->status))
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.admin.reports.hrms.document-report', [
            'reportData' => $this->reportData,
            'employees'  => $this->employees,
        ])->layout('layouts.app', [
            'panelName'    => 'Admin Panel',
            'pageTitle'    => 'Documents & KYC Report',
            'pageSubtitle' => 'Aadhaar, PAN, Bank details, Offer/Appointment letters, Contracts & Expiry Alerts Report',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
