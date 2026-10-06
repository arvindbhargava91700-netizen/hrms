<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use App\Models\Lead;
use App\Models\User;
use Carbon\Carbon;
use Livewire\WithPagination;

class LeadReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $startDate;
    public $endDate;
    public $employeeId = '';
    public $statusFilter = '';


    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updated()
    {
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $query = Lead::query();
        
        if ($this->employeeId) {
            $query->where('assigned_to', $this->employeeId);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with('assignedTo')->latest()->paginate(20);
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with('assignedTo')->latest()->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=lead_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Lead Name', 'Mobile', 'Emp ID', 'Assigned To', 'Status', 'Created At'];
        
        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->customer_name ?? '-',
                    $row->customer_mobile ?? '-',
                    optional($row->assignedTo)->employee_code ?? (optional($row->assignedTo)->id ? substr(optional($row->assignedTo)->id, 0, 8) : ''),
                    optional($row->assignedTo)->name ?? 'Unassigned',
                    $row->notes ?? '-',
                    $row->status,
                    $row->created_at->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function getEmployeesProperty()
    {
        return User::where('role', 'employee')->get();
    }

    public function render()
    {
        return view('livewire.admin.reports.hrms.lead-report', [
            'reportData' => $this->reportData,
            'employees'  => $this->employees,
        ])->layout('layouts.app', [
            'panelName'    => 'Admin Panel',
            'pageTitle'    => 'Lead Report',
            'pageSubtitle' => 'View lead performance',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
