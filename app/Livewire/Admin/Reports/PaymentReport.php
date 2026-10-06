<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentReport extends Component
{
    use WithPagination;

    public string $search = '';
    public string $startDate = '';
    public string $endDate = '';

    protected string $paginationTheme = 'bootstrap';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStartDate() { $this->resetPage(); }
    public function updatingEndDate() { $this->resetPage(); }

    public function getPlatformFee($referenceId)
    {
        $superAdminId = \App\Models\User::where('role', 'super_admin')->first()->id ?? auth()->id();
        return \App\Models\WalletTransaction::where('reference_id', $referenceId)
            ->where('user_id', $superAdminId)
            ->where('type', 'credit')
            ->value('amount') ?? 0;
    }

    public function export()
    {
        $query = Payment::with(['subscription.customer', 'booking.customer']);
        $this->applyFilters($query);

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Payment ID', 'Transaction ID', 'Customer', 'Gross Amount', 'Platform Fee', 'Net to Partner', 'Gateway', 'Status', 'Date']);

            $query->chunk(200, function ($payments) use ($output) {
                foreach ($payments as $payment) {
                    $customerName = 'N/A';
                    if ($payment->booking && $payment->booking->customer) {
                        $customerName = $payment->booking->customer->name;
                    } elseif ($payment->subscription && $payment->subscription->customer) {
                        $customerName = $payment->subscription->customer->name;
                    }

                    $platFee = $this->getPlatformFee($payment->booking_id ?? $payment->subscription_id);
                    $netAmt = $payment->amount - $platFee;

                    fputcsv($output, [
                        $payment->id,
                        $payment->receipt_number ?? $payment->gateway_ref,
                        $customerName,
                        $payment->amount,
                        $platFee,
                        $netAmt,
                        $payment->gateway,
                        $payment->status,
                        $payment->created_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            });
            fclose($output);
        }, 'payment-report-' . now()->format('Y-m-d_His') . '.csv');
    }

    private function applyFilters($query)
    {
        if (!empty($this->search)) {
            $query->where('gateway_ref', 'like', '%' . $this->search . '%')
                  ->orWhere('id', 'like', '%' . $this->search . '%');
        }
        if (!empty($this->startDate)) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if (!empty($this->endDate)) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }
    }

    public function render()
    {
        $query = Payment::with(['subscription.customer', 'booking.customer']);
        $this->applyFilters($query);

        $records = $query->latest()->paginate(15);

        return view('livewire.admin.reports.payment-report', compact('records'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Payment Report',
                'pageSubtitle' => 'View Payment Report',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
