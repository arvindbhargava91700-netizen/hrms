<?php

namespace App\Livewire\Partner\Hrms\LeadOrders;

use App\Models\LeadOrder;
use App\Models\LeadOrderPayment;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Recovery extends Component
{
    use WithPagination;
    use \App\Livewire\Partner\Hrms\HasPartnerId;
    use \App\Livewire\Partner\Hrms\Traits\HasHrmsFilters;

    public $search = '';

    // Pay Amount modal properties
    public $payOrderId = null;

    public $payAmount = null;

    public $payMethod = 'cash';

    public $payDate = null;

    public $payReference = null;

    public $payNotes = '';

    protected $listeners = ['paymentRecorded' => '$refresh'];

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner()
            || auth()->user()->canAccess('recovery_viewAny')
            || auth()->user()->canAccess('recovery_viewOwn')
            || auth()->user()->canAccess('recovery_viewBranch') || auth()->user()->canAccess('recovery_viewTeam'),
            403
        );
    }

    public function canRecordPayment()
    {
        return auth()->user()->isPartner() || auth()->user()->canAccess('recovery_update');
    }

    public function openPayModal($orderId)
    {
        abort_unless($this->canRecordPayment(), 403);

        $order = LeadOrder::with(['lead', 'items.product'])->findOrFail($orderId);
        $this->payOrderId = $order->id;
        $this->payAmount = $order->remaining_balance;
        $this->payMethod = 'cash';
        $this->payDate = now()->toDateString();
        $this->payReference = null;
        $this->payNotes = '';
        $this->dispatch('open-pay-modal');
    }

    public function recordPayment()
    {
        abort_unless($this->canRecordPayment(), 403);
        $this->validate([
            'payAmount' => 'required|numeric|min:0.01',
            'payMethod' => 'required|in:cash,upi,bank,card,online,other',
            'payDate' => 'required|date',
            'payReference' => 'nullable|string|max:255',
            'payNotes' => 'nullable|string|max:1000',
        ]);

        $order = LeadOrder::with(['lead', 'items.product'])->findOrFail($this->payOrderId);

        if ($this->payAmount > $order->remaining_balance) {
            $this->addError('payAmount', 'Payment cannot exceed the remaining balance of ₹'.number_format($order->remaining_balance, 2));

            return;
        }

        try {
            DB::beginTransaction();

            LeadOrderPayment::create([
                'lead_order_id' => $order->id,
                'paid_by' => auth()->id(),
                'amount' => $this->payAmount,
                'payment_method' => $this->payMethod,
                'payment_date' => $this->payDate,
                'reference' => $this->payReference,
                'notes' => $this->payNotes,
            ]);

            $newPaid = $order->paid_amount + (float) $this->payAmount;
            $newRemaining = max(0, $order->remaining_balance - (float) $this->payAmount);

            $order->update([
                'paid_amount' => $newPaid,
                'remaining_balance' => $newRemaining,
                'payment_status' => $newRemaining > 0 ? 'partial' : 'paid',
            ]);

            DB::commit();
            session()->flash('success', 'Payment of ₹'.number_format($this->payAmount, 2).' recorded successfully.');
            $this->dispatch('close-pay-modal');
            $this->reset(['payOrderId', 'payAmount', 'payMethod', 'payDate', 'payReference', 'payNotes']);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to record payment: '.$e->getMessage());
        }
    }
/*
    public function render()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $query = LeadOrder::with(['lead', 'items.product', 'payments'])
            ->where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
            ->where('remaining_balance', '>', 0);

        $user = auth()->user();

        // recovery_viewOwn → only the user's own records, regardless of other filters
        if ($user->role === 'employee'
            && $user->canAccess('recovery_viewOwn')
            && ! $user->canAccess('recovery_viewAny')
            && ! $user->canAccess('recovery_viewBranch') || $user->canAccess('recovery_viewTeam')
        ) {
            $query->where('employee_id', $user->id);
        } else {
            // HRMS Branch/Department/Employee filters scope the orders by the
            // assigned employee. Only narrows when a value is actively selected;
            // with no selection the full permission-scoped list is shown.
            $query = $this->applyHrmsFilters($query, 'employee_id', 'recovery_viewAny');
        }

        if (! empty($this->search)) {
            $search = $this->search;
            $query->whereHas('lead', function ($lq) use ($search) {
                $lq->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_mobile', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(10);

        $totalOutstanding = LeadOrder::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
            ->where('remaining_balance', '>', 0)
            ->sum('remaining_balance');

        $payOrder = $this->payOrderId
            ? LeadOrder::with(['lead', 'items.product', 'payments'])->find($this->payOrderId)
            : null;

        $totalOutstanding = LeadOrder::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
       ->where('remaining_balance', '>', 0)
       ->sum('remaining_balance');

        return view('livewire.partner.hrms.lead-orders.recovery', [
            'orders' => $orders,
            'totalOutstanding' => $totalOutstanding,
            'payOrder' => $payOrder,
            'filterBranches' => $this->getFilterBranches(),
            'filterDepartments' => $this->getFilterDepartments(),
            'filterEmployees' => $this->getFilterEmployees('recovery_viewAny'),
        ])->layout('layouts.app', [
            'panelName' => 'HRMS Module',
            'pageTitle' => 'Recovery Amount',
            'pageSubtitle' => 'Track and collect outstanding order payments',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);

    }
    */

     public function render()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $query = LeadOrder::with(['lead', 'items.product', 'payments'])
            ->where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
            ->where('remaining_balance', '>', 0);

        $user = auth()->user();

        // recovery_viewOwn → only the user's own records, regardless of other filters
        if ($user->role === 'employee'
            && $user->canAccess('recovery_viewOwn')
            && ! $user->canAccess('recovery_viewAny')
            && ! $user->canAccess('recovery_viewBranch') || $user->canAccess('recovery_viewTeam')
        ) {
            $query->where('employee_id', $user->id);
        } else {
            // HRMS Branch/Department/Employee filters scope the orders by the
            // assigned employee. Only narrows when a value is actively selected;
            // with no selection the full permission-scoped list is shown.
            $query = $this->applyHrmsFilters($query, 'employee_id', 'recovery_viewAny');
        }

        if (! empty($this->search)) {
            $search = $this->search;
            $query->whereHas('lead', function ($lq) use ($search) {
                $lq->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_mobile', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate(10);

        $totalOutstanding = (clone $query)->sum('remaining_balance');

        $payOrder = $this->payOrderId
            ? LeadOrder::with(['lead', 'items.product', 'payments'])->find($this->payOrderId)
            : null;

        return view('livewire.partner.hrms.lead-orders.recovery', [
            'orders' => $orders,
            'totalOutstanding' => $totalOutstanding,
            'payOrder' => $payOrder,
            'filterBranches' => $this->getFilterBranches(),
            'filterDepartments' => $this->getFilterDepartments(),
            'filterEmployees' => $this->getFilterEmployees('recovery_viewAny'),
        ])->layout('layouts.app', [
            'panelName' => 'HRMS Module',
            'pageTitle' => 'Recovery Amount',
            'pageSubtitle' => 'Track and collect outstanding order payments',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
