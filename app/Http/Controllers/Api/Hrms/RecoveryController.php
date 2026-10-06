<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\LeadOrder;
use App\Models\LeadOrderPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecoveryController extends Controller
{
    /**
     * Get pending recovery orders list and total outstanding summary
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        if (!$user->isPartner()
            && !$user->canAccess('recovery_viewAny')
            && !$user->canAccess('recovery_viewOwn')
            && !$user->canAccess('recovery_viewBranch')
            && !$user->canAccess('recovery_viewTeam')
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.'
            ], 403);
        }

        $query = LeadOrder::with(['lead', 'items.product', 'payments', 'employee.department', 'employee.branch'])
            ->where('partner_id', $partnerId)
            ->where('remaining_balance', '>', 0);

        // Permission scoping
        if ($user->role === 'employee'
            && $user->canAccess('recovery_viewOwn')
            && !$user->canAccess('recovery_viewAny')
            && !$user->canAccess('recovery_viewBranch')
            && !$user->canAccess('recovery_viewTeam')
        ) {
            $query->where('employee_id', $user->id);
        } elseif (!$user->isPartner() && !$user->canAccess('recovery_viewAny')) {
            if ($user->canAccess('recovery_viewBranch') || $user->canAccess('recovery_viewTeam')) {
                $query->whereIn('employee_id', $user->getTeamIds());
            } else {
                $query->where('employee_id', $user->id);
            }
        }

        // Search Filter (by customer name or mobile)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('lead', function ($lq) use ($search) {
                $lq->where('customer_name', 'like', "%{$search}%")
                   ->orWhere('customer_mobile', 'like', "%{$search}%");
            });
        }

        // Branch filter
        if ($request->filled('branch_id')) {
            $branchId = $request->branch_id;
            $query->whereHas('employee', function ($eq) use ($branchId) {
                $eq->where('branch_id', $branchId);
            });
        }

        // Department filter
        if ($request->filled('department_id')) {
            $deptId = $request->department_id;
            $query->whereHas('employee', function ($eq) use ($deptId) {
                $eq->where('department_id', $deptId);
            });
        }

        // Employee filter
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Calculate Summary
        $totalOutstanding = (clone $query)->sum('remaining_balance');
        $totalPendingOrders = (clone $query)->count();

        // Paginate
        $perPage = (int) $request->input('per_page', 10);
        $orders = $query->latest()->paginate($perPage);

        $data = $orders->getCollection()->map(function ($order) {
            $lastPayment = $order->payments->first();

            return [
                'order_id' => $order->id,
                'order_number' => '#' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'customer' => $order->lead ? [
                    'id' => $order->lead->id,
                    'name' => $order->lead->customer_name ?? 'Unknown Lead',
                    'mobile' => $order->lead->customer_mobile,
                    'email' => $order->lead->customer_email,
                ] : null,
                'employee' => $order->employee ? [
                    'id' => $order->employee->id,
                    'name' => $order->employee->name,
                    'branch' => $order->employee->branch?->name,
                    'department' => $order->employee->department?->name,
                ] : null,
                'items_count' => $order->items->count(),
                'financials' => [
                    'final_amount' => round((float)$order->final_amount, 2),
                    'paid_amount' => round((float)$order->paid_amount, 2),
                    'remaining_balance' => round((float)$order->remaining_balance, 2),
                    'payment_status' => $order->payment_status,
                ],
                'last_payment' => $lastPayment ? [
                    'id' => $lastPayment->id,
                    'amount' => round((float)$lastPayment->amount, 2),
                    'payment_date' => $lastPayment->payment_date,
                    'payment_method' => $lastPayment->payment_method,
                    'reference' => $lastPayment->reference,
                ] : null,
                'created_at' => $order->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'summary' => [
                'total_outstanding' => round((float)$totalOutstanding, 2),
                'total_pending_orders' => $totalPendingOrders,
            ],
            'data' => $data,
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ]);
    }

    /**
     * Get single recovery order details with full payment history
     */
    public function show(Request $request, $orderId): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        if (!$user->isPartner()
            && !$user->canAccess('recovery_viewAny')
            && !$user->canAccess('recovery_viewOwn')
            && !$user->canAccess('recovery_viewBranch')
            && !$user->canAccess('recovery_viewTeam')
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.'
            ], 403);
        }

        $order = LeadOrder::with(['lead', 'items.product', 'payments.paidBy', 'employee.department', 'employee.branch'])
            ->where('partner_id', $partnerId)
            ->where('id', $orderId)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found.'
            ], 404);
        }

        // Employee scope check
        if ($user->role === 'employee'
            && $user->canAccess('recovery_viewOwn')
            && !$user->canAccess('recovery_viewAny')
            && !$user->canAccess('recovery_viewBranch')
            && !$user->canAccess('recovery_viewTeam')
        ) {
            if ($order->employee_id !== $user->id) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
            }
        } elseif (!$user->isPartner() && !$user->canAccess('recovery_viewAny')) {
            $teamIds = $user->getTeamIds();
            if (!in_array($order->employee_id, $teamIds) && $order->employee_id !== $user->id) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'order_id' => $order->id,
                'order_number' => '#' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'customer' => $order->lead ? [
                    'id' => $order->lead->id,
                    'name' => $order->lead->customer_name ?? 'Unknown Lead',
                    'mobile' => $order->lead->customer_mobile,
                    'email' => $order->lead->customer_email,
                    'address' => $order->lead->address ?? null,
                ] : null,
                'employee' => $order->employee ? [
                    'id' => $order->employee->id,
                    'name' => $order->employee->name,
                    'branch' => $order->employee->branch?->name,
                    'department' => $order->employee->department?->name,
                ] : null,
                'financials' => [
                    'base_amount' => round((float)$order->base_amount, 2),
                    'gst_amount' => round((float)$order->gst_amount, 2),
                    'discount' => round((float)$order->discount, 2),
                    'final_amount' => round((float)$order->final_amount, 2),
                    'paid_amount' => round((float)$order->paid_amount, 2),
                    'remaining_balance' => round((float)$order->remaining_balance, 2),
                    'payment_status' => $order->payment_status,
                ],
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product?->name ?? 'Custom Product',
                        'quantity' => $item->quantity,
                        'base_price' => round((float)$item->base_price, 2),
                        'price' => round((float)$item->price, 2),
                    ];
                }),
                'payments_history' => $order->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => round((float)$payment->amount, 2),
                        'payment_method' => $payment->payment_method,
                        'payment_date' => $payment->payment_date,
                        'reference' => $payment->reference,
                        'notes' => $payment->notes,
                        'collected_by' => $payment->paidBy?->name ?? 'System/Admin',
                        'created_at' => $payment->created_at?->toIso8601String(),
                    ];
                }),
                'created_at' => $order->created_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Record recovery payment against an order
     */
    public function recordPayment(Request $request, $orderId): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        if (!$user->isPartner() && !$user->canAccess('recovery_update')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to record payment.'
            ], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,upi,bank,card,online,other',
            'payment_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $order = LeadOrder::where('partner_id', $partnerId)
            ->where('id', $orderId)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found.'
            ], 404);
        }

        $payAmount = (float) $request->input('amount');

        if ($payAmount > (float) $order->remaining_balance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment amount cannot exceed the remaining balance of ₹' . number_format($order->remaining_balance, 2)
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payment = LeadOrderPayment::create([
                'lead_order_id' => $order->id,
                'paid_by' => $user->id,
                'amount' => $payAmount,
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'reference' => $request->reference,
                'notes' => $request->notes,
            ]);

            $newPaid = (float) $order->paid_amount + $payAmount;
            $newRemaining = max(0, (float) $order->remaining_balance - $payAmount);

            $order->update([
                'paid_amount' => $newPaid,
                'remaining_balance' => $newRemaining,
                'payment_status' => $newRemaining > 0 ? 'partial' : 'paid',
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment of ₹' . number_format($payAmount, 2) . ' recorded successfully.',
                'data' => [
                    'payment_id' => $payment->id,
                    'order_id' => $order->id,
                    'paid_amount' => round($newPaid, 2),
                    'remaining_balance' => round($newRemaining, 2),
                    'payment_status' => $order->payment_status,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recovery payments history / audit list
     */
    public function history(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        if (!$user->isPartner()
            && !$user->canAccess('recoveryhistory_viewAny')
            && !$user->canAccess('recoveryhistory_viewOwn')
            && !$user->canAccess('recoveryhistory_viewBranch')
            && !$user->canAccess('recoveryhistory_viewTeam')
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.'
            ], 403);
        }

        $query = LeadOrderPayment::query()
            ->whereHas('order', function ($oq) use ($partnerId) {
                $oq->where('partner_id', $partnerId);
            })
            ->with(['order.lead', 'order.items.product', 'paidBy']);

        // Scope check
        if ($user->role === 'employee'
            && $user->canAccess('recoveryhistory_viewOwn')
            && !$user->canAccess('recoveryhistory_viewAny')
            && !$user->canAccess('recoveryhistory_viewBranch')
            && !$user->canAccess('recoveryhistory_viewTeam')
        ) {
            $query->where('paid_by', $user->id);
        } elseif (!$user->isPartner() && !$user->canAccess('recoveryhistory_viewAny')) {
            if ($user->canAccess('recoveryhistory_viewBranch') || $user->canAccess('recoveryhistory_viewTeam')) {
                $query->whereIn('paid_by', $user->getTeamIds());
            } else {
                $query->where('paid_by', $user->id);
            }
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('order.lead', function ($lq) use ($search) {
                      $lq->where('customer_name', 'like', "%{$search}%")
                         ->orWhere('customer_mobile', 'like', "%{$search}%");
                  });
            });
        }

        // Payment method filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Date Range
        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        // Stats
        $statQuery = clone $query;
        $totalRecovered = (clone $statQuery)->sum('amount');
        $thisMonthRecovered = (clone $statQuery)
            ->whereMonth('payment_date', Carbon::now()->month)
            ->whereYear('payment_date', Carbon::now()->year)
            ->sum('amount');
        $todayRecovered = (clone $statQuery)
            ->whereDate('payment_date', Carbon::today())
            ->sum('amount');
        $totalTransactions = (clone $statQuery)->count();

        // Paginate
        $perPage = (int) $request->input('per_page', 15);
        $payments = $query->latest('payment_date')->latest('created_at')->paginate($perPage);

        $data = $payments->getCollection()->map(function ($payment) {
            return [
                'id' => $payment->id,
                'order_id' => $payment->lead_order_id,
                'order_number' => '#' . str_pad($payment->lead_order_id, 5, '0', STR_PAD_LEFT),
                'amount' => round((float)$payment->amount, 2),
                'payment_method' => $payment->payment_method,
                'payment_date' => $payment->payment_date,
                'reference' => $payment->reference,
                'notes' => $payment->notes,
                'customer' => $payment->order?->lead ? [
                    'id' => $payment->order->lead->id,
                    'name' => $payment->order->lead->customer_name,
                    'mobile' => $payment->order->lead->customer_mobile,
                ] : null,
                'order_financials' => $payment->order ? [
                    'final_amount' => round((float)$payment->order->final_amount, 2),
                    'paid_amount' => round((float)$payment->order->paid_amount, 2),
                    'remaining_balance' => round((float)$payment->order->remaining_balance, 2),
                ] : null,
                'collected_by' => $payment->paidBy ? [
                    'id' => $payment->paidBy->id,
                    'name' => $payment->paidBy->name,
                ] : null,
                'created_at' => $payment->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'summary' => [
                'total_recovered' => round((float)$totalRecovered, 2),
                'this_month_recovered' => round((float)$thisMonthRecovered, 2),
                'today_recovered' => round((float)$todayRecovered, 2),
                'total_transactions' => $totalTransactions,
            ],
            'data' => $data,
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ]
        ]);
    }

    /**
     * Get single recovery payment receipt details (for View modal popup)
     */
    public function showPayment(Request $request, $paymentId): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;

        if (!$user->isPartner()
            && !$user->canAccess('recoveryhistory_viewAny')
            && !$user->canAccess('recoveryhistory_viewOwn')
            && !$user->canAccess('recoveryhistory_viewBranch')
            && !$user->canAccess('recoveryhistory_viewTeam')
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.'
            ], 403);
        }

        $payment = LeadOrderPayment::where('id', $paymentId)
            ->whereHas('order', function ($oq) use ($partnerId) {
                $oq->where('partner_id', $partnerId);
            })
            ->with(['order.lead', 'order.items.product', 'paidBy'])
            ->first();

        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment receipt not found.'
            ], 404);
        }

        // Scope check
        if ($user->role === 'employee'
            && $user->canAccess('recoveryhistory_viewOwn')
            && !$user->canAccess('recoveryhistory_viewAny')
            && !$user->canAccess('recoveryhistory_viewBranch')
            && !$user->canAccess('recoveryhistory_viewTeam')
        ) {
            if ($payment->paid_by !== $user->id) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
            }
        } elseif (!$user->isPartner() && !$user->canAccess('recoveryhistory_viewAny')) {
            $teamIds = $user->getTeamIds();
            if (!in_array($payment->paid_by, $teamIds) && $payment->paid_by !== $user->id) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $payment->id,
                'receipt_number' => '#' . substr($payment->id, 0, 8),
                'amount' => round((float)$payment->amount, 2),
                'payment_date' => $payment->payment_date,
                'payment_date_formatted' => Carbon::parse($payment->payment_date)->format('d M Y'),
                'payment_method' => $payment->payment_method,
                'reference' => $payment->reference ?: null,
                'notes' => $payment->notes ?: null,
                'customer' => $payment->order?->lead ? [
                    'id' => $payment->order->lead->id,
                    'name' => $payment->order->lead->customer_name ?? 'N/A',
                    'mobile' => $payment->order->lead->customer_mobile ?? 'N/A',
                    'email' => $payment->order->lead->customer_email,
                ] : null,
                'order' => $payment->order ? [
                    'id' => $payment->order->id,
                    'order_number' => '#' . substr($payment->order->id, 0, 8),
                    'final_amount' => round((float)$payment->order->final_amount, 2),
                    'paid_amount' => round((float)$payment->order->paid_amount, 2),
                    'remaining_balance' => round((float)$payment->order->remaining_balance, 2),
                    'payment_status' => $payment->order->payment_status,
                ] : null,
                'collected_by' => $payment->paidBy ? [
                    'id' => $payment->paidBy->id,
                    'name' => $payment->paidBy->name,
                    'email' => $payment->paidBy->email,
                ] : [
                    'id' => null,
                    'name' => 'System/Admin',
                ],
                'created_at' => $payment->created_at?->toIso8601String(),
                'created_at_human' => $payment->created_at?->diffForHumans(),
            ]
        ]);
    }
}
