<?php

namespace App\Livewire\Partner\Hrms\LeadOrders;

use App\Models\LeadOrderPayment;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class RecoveryHistory extends Component
{
    use WithPagination;
    use \App\Livewire\Partner\Hrms\HasPartnerId;
    use \App\Livewire\Partner\Hrms\Traits\HasHrmsFilters;

    protected string $paginationTheme = 'bootstrap';

    public $search = '';
    public $paymentMethod = '';
    public $fromDate = '';
    public $toDate = '';

    public $selectedPaymentId = null;
    public $showDetailModal = false;

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner()
            || auth()->user()->canAccess('recoveryhistory_viewAny')
            || auth()->user()->canAccess('recoveryhistory_viewOwn')
            || auth()->user()->canAccess('recoveryhistory_viewBranch')
            || auth()->user()->canAccess('recoveryhistory_viewTeam'),
            403
        );
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedPaymentMethod()
    {
        $this->resetPage();
    }

    public function updatedFromDate()
    {
        $this->resetPage();
    }

    public function updatedToDate()
    {
        $this->resetPage();
    }

    public function viewDetails($paymentId)
    {
        $this->selectedPaymentId = $paymentId;
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedPaymentId = null;
    }

    public function exportCsv()
    {
        abort_unless(
            auth()->user()->isPartner()
            || auth()->user()->canAccess('recoveryhistory_export')
            || auth()->user()->canAccess('recoveryhistory_viewAny'),
            403
        );

        $partnerId = $this->getPartnerId();

        $query = LeadOrderPayment::query()
            ->whereHas('order', function ($oq) use ($partnerId) {
                $oq->where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } });
            })
            ->with(['order.lead', 'order.items.product', 'paidBy']);

        $user = auth()->user();
        if ($user->role === 'employee'
            && $user->canAccess('recoveryhistory_viewOwn')
            && !$user->canAccess('recoveryhistory_viewAny')
            && !$user->canAccess('recoveryhistory_viewBranch')
            && !$user->canAccess('recoveryhistory_viewTeam')
        ) {
            $query->where('paid_by', $user->id);
        }

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('order.lead', function ($lq) use ($search) {
                        $lq->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('customer_mobile', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($this->paymentMethod)) {
            $query->where('payment_method', $this->paymentMethod);
        }

        if (!empty($this->fromDate)) {
            $query->whereDate('payment_date', '>=', $this->fromDate);
        }

        if (!empty($this->toDate)) {
            $query->whereDate('payment_date', '<=', $this->toDate);
        }

        $payments = $query->latest('payment_date')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=recovery_history_" . date('Y_m_d_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Receipt ID', 'Date', 'Customer Name', 'Mobile', 'Order Total (₹)', 'Recovered Amount (₹)', 'Payment Method', 'Reference', 'Collected By', 'Notes'];

        $callback = function () use ($payments, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($payments as $item) {
                fputcsv($file, [
                    $item->id,
                    Carbon::parse($item->payment_date)->format('d-m-Y'),
                    $item->order?->lead?->customer_name ?? 'N/A',
                    $item->order?->lead?->customer_mobile ?? 'N/A',
                    number_format((float)($item->order?->final_amount ?? 0), 2),
                    number_format((float)$item->amount, 2),
                    strtoupper($item->payment_method),
                    $item->reference ?? '-',
                    $item->paidBy?->name ?? 'System/Admin',
                    $item->notes ?? '-'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        $partnerId = $this->getPartnerId();

        $baseQuery = LeadOrderPayment::query()
            ->whereHas('order', function ($oq) use ($partnerId) {
                $oq->where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } });
            })
            ->with(['order.lead', 'order.items.product', 'paidBy']);

        $user = auth()->user();

        // Access scope
        if ($user->role === 'employee'
            && $user->canAccess('recoveryhistory_viewOwn')
            && !$user->canAccess('recoveryhistory_viewAny')
            && !$user->canAccess('recoveryhistory_viewBranch')
            && !$user->canAccess('recoveryhistory_viewTeam')
        ) {
            $baseQuery->where('paid_by', $user->id);
        }

        // Search Filter
        if (!empty($this->search)) {
            $search = $this->search;
            $baseQuery->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('order.lead', function ($lq) use ($search) {
                        $lq->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('customer_mobile', 'like', "%{$search}%");
                    });
            });
        }

        // Method Filter
        if (!empty($this->paymentMethod)) {
            $baseQuery->where('payment_method', $this->paymentMethod);
        }

        // Date Range Filters
        if (!empty($this->fromDate)) {
            $baseQuery->whereDate('payment_date', '>=', $this->fromDate);
        }

        if (!empty($this->toDate)) {
            $baseQuery->whereDate('payment_date', '<=', $this->toDate);
        }

        $payments = (clone $baseQuery)->latest('payment_date')->latest('created_at')->paginate(15);

        // Stats
        $statQuery = LeadOrderPayment::query()
            ->whereHas('order', function ($oq) use ($partnerId) {
                $oq->where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } });
            });

        if ($user->role === 'employee'
            && $user->canAccess('recoveryhistory_viewOwn')
            && !$user->canAccess('recoveryhistory_viewAny')
            && !$user->canAccess('recoveryhistory_viewBranch')
            && !$user->canAccess('recoveryhistory_viewTeam')
        ) {
            $statQuery->where('paid_by', $user->id);
        }

        $totalRecovered = (clone $statQuery)->sum('amount');
        $thisMonthRecovered = (clone $statQuery)
            ->whereMonth('payment_date', Carbon::now()->month)
            ->whereYear('payment_date', Carbon::now()->year)
            ->sum('amount');
        $todayRecovered = (clone $statQuery)
            ->whereDate('payment_date', Carbon::today())
            ->sum('amount');
        $totalTransactions = (clone $statQuery)->count();

        $selectedPayment = $this->selectedPaymentId
            ? LeadOrderPayment::with(['order.lead', 'order.items.product', 'paidBy'])->find($this->selectedPaymentId)
            : null;

        return view('livewire.partner.hrms.lead-orders.recovery-history', [
            'payments' => $payments,
            'totalRecovered' => $totalRecovered,
            'thisMonthRecovered' => $thisMonthRecovered,
            'todayRecovered' => $todayRecovered,
            'totalTransactions' => $totalTransactions,
            'selectedPayment' => $selectedPayment,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Recovery History',
            'pageSubtitle' => 'Track and audit collected payments history',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
