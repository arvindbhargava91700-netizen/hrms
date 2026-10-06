<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use App\Models\LeadOrder;
use App\Models\User;
use Carbon\Carbon;
use Livewire\WithPagination;

class OrderReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $employeeId = '';
    public $startDate = '';
    public $endDate = '';
    public $status = '';

    public function updated($propertyName)
    {
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $query = LeadOrder::query();
        
        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }
        
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        }
        
        if ($this->status) {
            $query->where('approval_status', $this->status);
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with(['lead', 'lead.assignedTo'])->latest()->paginate(20);
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['lead', 'lead.assignedTo'])->latest()->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=order_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Lead Name', 'Mobile', 'Emp ID', 'Assigned To', 'Total Amount', 'Discount', 'Final Amount', 'Paid Amount', 'Remaining Balance', 'Payment Status', 'Approval Status', 'Created At'];
        
        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    optional($row->lead)->customer_name ?? '-',
                    optional($row->lead)->customer_mobile ?? '-',
                    optional(optional($row->lead)->assignedTo)->employee_code ?? (optional(optional($row->lead)->assignedTo)->id ? substr(optional(optional($row->lead)->assignedTo)->id, 0, 8) : ''),
                    optional(optional($row->lead)->assignedTo)->name ?? 'Unassigned',
                    $row->total_amount,
                    $row->discount,
                    $row->final_amount,
                    $row->paid_amount,
                    $row->remaining_balance,
                    $row->payment_status,
                    $row->approval_status,
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
        return view('livewire.admin.reports.hrms.order-report', [
            'reportData' => $this->reportData,
            'employees' => $this->employees
        ])->layout('layouts.app', [
            'panelName'    => 'Admin Panel',
            'pageTitle'    => 'Order Report',
            'pageSubtitle' => 'View lead order performance',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
