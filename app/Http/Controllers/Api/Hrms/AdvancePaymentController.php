<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\AdvancePayment;

class AdvancePaymentController extends Controller
{
    /**
     * Get Employee Advance Payments
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        
        $query = AdvancePayment::where('employee_id', $user->partner_id ?? $user->id);

        if (!$user->isPartner() && !$user->canAccess('advance_payments_viewAny')) {
            $query->where('employee_id', $user->id);
        } else if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $advances = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $advances
        ]);
    }

    /**
     * Request Advance Payment
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
            'reason' => 'nullable|string',
        ]);

        $advance = AdvancePayment::create([
            'employee_id' => $user->id,
            'partner_id' => $user->partner_id,
            'amount' => $request->amount,
            'month' => $request->month,
            'year' => $request->year,
            'reason' => $request->reason,
            'status' => 'pending',
            'request_date' => now(),
        ]);

        return response()->json(['status' => 'success', 'data' => $advance], 201);
    }
}
