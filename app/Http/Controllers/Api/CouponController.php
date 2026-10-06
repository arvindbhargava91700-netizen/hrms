<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    // ────────────────────────────────────────────────────────────────
    // GET /api/coupons?listing_id=
    // Available coupons for a listing (global + listing-specific)
    // ────────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Coupon::where('is_active', true)
            ->where(fn($q) => $q
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>', now())
            )
            ->where(fn($q) => $q
                ->where('max_uses', 0)
                ->orWhereRaw('used_count < max_uses')
            );

        if ($request->filled('listing_id')) {
            $query->where(fn($q) => $q
                ->whereNull('listing_id')          // global coupons
                ->orWhere('listing_id', null)
            );
        } else {
            $query->whereNull('listing_id');       // only global
        }

        $coupons = $query->get()->map(fn($c) => [
            'id'           => $c->id,
            'code'         => $c->code,
            'title'        => $c->title,
            'description'  => $c->description,
            'type'         => $c->type,          // flat | percent
            'value'        => (float) $c->value,
            'min_amount'   => (float) $c->min_amount,
            'max_discount' => $c->max_discount ? (float) $c->max_discount : null,
            'expires_at'   => $c->expires_at?->toDateString(),
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => $coupons,
        ]);
    }

    // ────────────────────────────────────────────────────────────────
    // POST /api/coupons/apply
    // Apply a coupon → returns discount + updated billing summary
    // ────────────────────────────────────────────────────────────────
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'code'       => 'required|string',
            'package_id' => 'required|exists:packages,id',
        ]);

        $coupon = Coupon::where('code', strtoupper(trim($request->code)))
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Coupon not found.',
            ], 404);
        }

        if (!$coupon->isValid()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'This coupon has expired or reached its usage limit.',
            ], 422);
        }

        $package = Package::with('room')->findOrFail($request->package_id);
        $price   = (float) $package->price;

        if ($price < $coupon->min_amount) {
            return response()->json([
                'status'  => 'error',
                'message' => "This coupon requires a minimum plan amount of ₹{$coupon->min_amount}.",
            ], 422);
        }

        $discount   = $coupon->calculateDiscount($price);
        $finalTotal = round($price - $discount, 2);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'coupon'       => [
                    'code'         => $coupon->code,
                    'title'        => $coupon->title,
                    'type'         => $coupon->type,
                    'value'        => (float) $coupon->value,
                ],
                'billing'      => [
                    'original_price' => $price,
                    'discount'       => round($discount, 2),
                    'final_total'    => $finalTotal,
                    'savings'        => round($discount, 2),
                ],
            ],
        ]);
    }
}
