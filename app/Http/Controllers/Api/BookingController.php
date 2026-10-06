<?php



namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\ListingShift;
use App\Models\ListingTrainer;
use App\Services\BookingService;
use App\Services\OtpService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;



class BookingController extends Controller
{
    public function __construct(
        protected OtpService $otpService,
        protected BookingService $bookingService,
    ) {}

    // ─── Helper: Build billing summary ──────────────────────────────
    private function billingData(Package $pkg, float $discount = 0, ?ListingShift $shift = null, int $bedsBooked = 1, array $trainerIds = [], ?\App\Models\Room $room = null): array
    {

        $basePrice = (float) $pkg->price;

        // If package pricing is per-bed, multiply by beds booked

        $subtotal = in_array($pkg->occupancy_type, ['per_bed', 'per_site']) ? round($basePrice * max(1, $bedsBooked), 2) : $basePrice;



        // Shift fee (if selected)

        $shiftFee = $shift?->fee ? (float) $shift->fee : 0.00;



        // Trainer fees: currently not modeled; default to 0. Reserved for future per-trainer pricing.

        $trainerFee = 0.00;

        $pkg->loadMissing('listing.category');
        
        $securityDeposit = 0.00;
        
        if ($room) {
            $baseDeposit = (float) $room->security_deposit;
            
            if ($pkg->listing && $pkg->listing->category && $pkg->listing->category->has_rooms) {
                if ($pkg->occupancy_type === 'full_room') {
                    $securityDeposit = $baseDeposit * max(1, $room->available_beds);
                } elseif ($pkg->occupancy_type === 'per_bed') {
                    $securityDeposit = $baseDeposit * max(1, $bedsBooked);
                } else {
                    $securityDeposit = $baseDeposit;
                }
            } else {
                $securityDeposit = $baseDeposit;
            }
        } elseif ($pkg->listing && $pkg->listing->security_deposit) {
            $securityDeposit = (float) $pkg->listing->security_deposit;
        }



        $subtotal = round($subtotal + $shiftFee + $trainerFee, 2);

        $taxRate      = 0.00;  // adjust if GST needed

        $tax          = round($subtotal * $taxRate, 2);

        $discounted   = min($discount, $subtotal);

        $total        = round($subtotal + $tax - $discounted + $securityDeposit, 2);





        return [
            'plan_name'       => $pkg->name,
            'duration'        => $pkg->duration_label,
            'duration_days'   => $pkg->duration_days,
            'subtotal'        => $subtotal,
            'tax'             => $tax,
            'discount'        => $discounted,
            'security_deposit'=> $securityDeposit,
            'total'           => $total,
            'room'            => $room ? [
                'room_number'      => $room->room_number,
                'room_type'        => $room->room_type,
                'security_deposit' => (float) $room->security_deposit,
            ] : null,
            'shift'           => $shift ? [
                'id'         => $shift->id,
                'shift_name' => $shift->shift_name,
                'start_time' => $shift->start_time,
                'end_time'   => $shift->end_time,
                'fee'        => (float) $shift->fee,
            ] : null,
            'trainers'        => !empty($trainerIds) ? \App\Models\ListingTrainer::whereIn('id', $trainerIds)->get()->map(fn($t) => [
                'id'             => $t->id,
                'name'           => $t->name,
                'specialization' => $t->specialization,
                'photo'          => $t->photo_url,
            ]) : null,
            'shift_fee'       => $shiftFee,
            'trainer_fee'     => $trainerFee,
            'beds_booked'     => (int) $bedsBooked,
        ];

    }

    // ────────────────────────────────────────────────────────────────
    // GET /api/bookings/billing-preview?package_id=&coupon_code=
    // Billing summary before creating booking
    // ────────────────────────────────────────────────────────────────
    public function billingPreview(Request $request): JsonResponse
    {

        $request->validate([

            'package_id'  => 'required|exists:packages,id',

            'room_id'     => 'nullable|exists:rooms,id',

            'shift_id'    => 'nullable|exists:listing_shifts,id',

            'beds_booked' => 'nullable|integer|min:1',

            'trainer_ids' => 'nullable|array',

            'trainer_ids.*' => 'nullable|exists:listing_trainers,id',

        ]);



        $pkg      = Package::with('listing.category')->findOrFail($request->package_id);
        $room     = $request->filled('room_id') ? \App\Models\Room::find($request->room_id) : null;

        $shift    = $request->filled('shift_id') ? ListingShift::find($request->shift_id) : null;

        $beds     = (int) ($request->beds_booked ?? 1);

        $trainerIds = $request->input('trainer_ids', []);

        if ($pkg->listing && $pkg->listing->category) {
            $category = $pkg->listing->category;

            if ($category->has_shifts && $shift) {
                if ($shift->max_members > 0) {
                    $activeMembers = \App\Models\Subscription::where('status', 'active')
                        ->whereHas('booking', function ($q) use ($shift) {
                            $q->where('shift_id', $shift->id);
                        })->sum('beds_booked');
                        
                    $pendingBookings = \App\Models\Booking::where('shift_id', $shift->id)
                        ->whereIn('status', ['pending_otp', 'confirmed'])
                        ->sum('beds_booked');
                        
                    if (($activeMembers + $pendingBookings + $beds) > $shift->max_members) {
                        return response()->json(['status' => 'error', 'message' => 'Not enough slots available in the selected shift.'], 422);
                    }
                }
            }

            if ($category->has_rooms && $room) {
                if ($pkg->occupancy_type === 'full_room') {
                    if ($room->available_beds < $room->capacity) {
                        return response()->json(['status' => 'error', 'message' => 'Cannot book full room. Some beds are already occupied.'], 422);
                    }
                    $beds = $room->capacity;
                } elseif ($pkg->occupancy_type === 'per_bed') {
                    if ($beds > $room->available_beds) {
                        return response()->json(['status' => 'error', 'message' => 'Not enough beds available in this room.'], 422);
                    }
                }
            }
        }

        $discount = 0;

        $coupon   = null;



        if ($request->filled('coupon_code')) {

            $coupon = Coupon::where('code', strtoupper($request->coupon_code))

                ->where('is_active', true)->first();



            if ($coupon && $coupon->isValid()) {

                // calculate discount against the subtotal including shift/trainer fees

                $subtotalEstimate = in_array($pkg->occupancy_type, ['per_bed', 'per_site']) ? $pkg->price * max(1, $beds) : $pkg->price;

                $subtotalEstimate += $shift?->fee ?? 0;

                $discount = $coupon->calculateDiscount((float) $subtotalEstimate);

            }

        }



        $billingData = $this->billingData($pkg, $discount, $shift, $beds, $trainerIds, $room);

        $walletDeduction = 0;
        $payableAmount = $billingData['total'];

        if ($request->boolean('use_wallet') && $request->user()->wallet_balance > 0) {
            $walletDeduction = min((float) $request->user()->wallet_balance, $billingData['total']);
            $payableAmount = $billingData['total'] - $walletDeduction;
        }

        $billingData['wallet_deduction'] = $walletDeduction;
        $billingData['payable_amount'] = $payableAmount;

        return response()->json([
            'status' => 'success',
            'data'   => $billingData,
        ]);

    }

    // ────────────────────────────────────────────────────────────────
    // POST /api/bookings
    // Customer creates a booking (pending_otp state)
    // ────────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {

        $request->validate([
            'package_id'     => 'required|exists:packages,id',
            'room_id'        => 'nullable|exists:rooms,id',
            'payment_method' => 'required|in:cash,online,auto_pay',
            'coupon_code'    => 'nullable|string',
            'shift_id'       => 'nullable|exists:listing_shifts,id',
            'beds_booked'    => 'nullable|integer|min:1',
            'trainer_ids'    => 'nullable|array',
            'trainer_ids.*'  => 'nullable|exists:listing_trainers,id',
            'use_wallet'     => 'nullable|boolean',
        ]);



        $user = $request->user();

        $pkg  = Package::with(['listing.category'])->findOrFail($request->package_id);

        $room = $request->filled('room_id') ? \App\Models\Room::find($request->room_id) : null;

        $shift = $request->filled('shift_id') ? ListingShift::find($request->shift_id) : null;

        $beds  = (int) ($request->beds_booked ?? 1);

        $trainerIds = $request->input('trainer_ids', []);



        if (!$pkg->listing || $pkg->listing->status !== 'approved') {
            return response()->json(['status' => 'error', 'message' => 'Listing not available.'], 422);
        }

        $partnerId = $pkg->listing?->partner_id;
        if (!$partnerId || !\App\Services\PackageService::getActiveSubscription($partnerId)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Partner subscription has expired. New bookings cannot be accepted for this listing at this time.'
            ], 422);
        }

        if ($pkg->listing->category) {
            $category = $pkg->listing->category;

            if ($category->has_shifts && $shift) {
                if ($shift->max_members > 0) {
                    $activeMembers = \App\Models\Subscription::where('status', 'active')
                        ->whereHas('booking', function ($q) use ($shift) {
                            $q->where('shift_id', $shift->id);
                        })->sum('beds_booked');
                        
                    $pendingBookings = \App\Models\Booking::where('shift_id', $shift->id)
                        ->whereIn('status', ['pending_otp', 'confirmed'])
                        ->sum('beds_booked');
                        
                    if (($activeMembers + $pendingBookings + $beds) > $shift->max_members) {
                        return response()->json(['status' => 'error', 'message' => 'Not enough slots available in the selected shift.'], 422);
                    }
                }
            }

            if ($category->has_rooms && $room) {
                if ($pkg->occupancy_type === 'full_room') {
                    if ($room->available_beds < $room->capacity) {
                        return response()->json(['status' => 'error', 'message' => 'Cannot book full room. Some beds are already occupied.'], 422);
                    }
                    $beds = $room->capacity;
                } elseif ($pkg->occupancy_type === 'per_bed') {
                    if ($beds > $room->available_beds) {
                        return response()->json(['status' => 'error', 'message' => 'Not enough beds available in this room.'], 422);
                    }
                }
            }
        }

        // Duplicate booking check (still checking Subscriptions for active ones, but also pending bookings)

        if (\App\Models\Booking::where('customer_id', $user->id)

            ->where('package_id', $pkg->id)

            ->whereIn('status', ['pending_otp', 'confirmed'])

            ->exists() ||

            Subscription::where('customer_id', $user->id)

            ->where('package_id', $pkg->id)

            ->whereIn('status', ['active'])

            ->exists()

        ) {

            return response()->json(['status' => 'error', 'message' => 'You already have an active booking or subscription for this plan.'], 422);

        }



        // Coupon

        $couponId     = null;

        $discountAmt  = 0;



        if ($request->filled('coupon_code')) {

            $coupon = Coupon::where('code', strtoupper($request->coupon_code))

                ->where('is_active', true)->first();



            if (!$coupon || !$coupon->isValid()) {

                return response()->json(['status' => 'error', 'message' => 'Invalid or expired coupon.'], 422);

            }



            // calculate discount against subtotal incl. shift/trainer fees

            $subtotalEstimate = in_array($pkg->occupancy_type, ['per_bed', 'per_site']) ? $pkg->price * max(1, $beds) : $pkg->price;

            $subtotalEstimate += $shift?->fee ?? 0;

            $discountAmt = $coupon->calculateDiscount((float) $subtotalEstimate);

            $couponId    = $coupon->id;

        }



        // Compute final amount using billingData to include shift/trainer fees

        $billing = $this->billingData($pkg, $discountAmt, $shift, $beds, $trainerIds, $room);

        $finalAmt = (float) $billing['total'];



        $payableAmt = $finalAmt;
        $walletDeduction = 0;
        if ($request->boolean('use_wallet') && $user->wallet_balance > 0) {
            $walletDeduction = min((float)$user->wallet_balance, $finalAmt);
            $payableAmt = $finalAmt - $walletDeduction;
        }

        DB::beginTransaction();

        try {

            $booking = \App\Models\Booking::create([

                'customer_id'     => $user->id,

                'package_id'      => $pkg->id,

                'room_id'         => $room?->id,

                'occupancy_type'  => $pkg->occupancy_type,

                'beds_booked'     => $beds,
                'shift_id'        => $shift?->id,

                'trainer_ids'     => $trainerIds ?: null,

                'status'          => (in_array($request->payment_method, ['online', 'auto_pay']) && $payableAmt > 0) ? 'pending_payment' : 'pending_otp',

                'payment_method'  => $request->payment_method,

                'coupon_id'       => $couponId,

                'coupon_type'     => $couponId ? Coupon::class : null,

                'discount_amount' => $discountAmt,

                'security_deposit'=> $billing['security_deposit'] ?? 0,

                'final_amount'    => $finalAmt,

            ]);



            // Increment coupon usage

            if ($couponId) {

                Coupon::where('id', $couponId)->increment('used_count');

            }



            // Create invoice linked to booking for cash or if fully paid by wallet
            $invoice = null;
            if (!in_array($request->payment_method, ['online', 'auto_pay']) || $payableAmt <= 0) {
                $invoice = Invoice::create([
                    'booking_id'      => $booking->id,
                    'amount'          => (float) $billing['subtotal'],
                    'tax'             => (float) $billing['tax'],
                    'total'           => $finalAmt,
                    'due_date'        => now()->toDateString(),
                    'status'          => 'sent',
                ]);
            }

            if ($walletDeduction > 0) {
                $user->decrement('wallet_balance', $walletDeduction);
                \App\Models\WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $walletDeduction,
                    'description' => 'Payment for booking',
                    'reference_type' => \App\Models\Booking::class,
                    'reference_id' => $booking->id,
                ]);

                \App\Models\Payment::create([
                    'booking_id'  => $booking->id,
                    'gateway'     => 'wallet',
                    'gateway_ref' => 'WALLET_' . time() . '_' . rand(100, 999),
                    'amount'      => $walletDeduction,
                    'status'      => 'paid',
                    'paid_at'     => now(),
                ]);
            }

            DB::commit();
            
            // Auto generate and send OTP for new booking
            if (!in_array($request->payment_method, ['online', 'auto_pay']) || $payableAmt <= 0) {
                try {
                    $this->bookingService->sendOtp($booking);
                } catch (\Exception $e) {
                    Log::error('Auto OTP generation failed: ' . $e->getMessage());
                }
            }



        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Booking failed: ' . $e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Booking failed. Please try again.'], 500);

        }



        $booking->refresh();



        $paymentInfo = null;
        if (in_array($booking->payment_method, ['online', 'auto_pay']) && $payableAmt > 0) {
            $merchant = $pkg->listing->partner->tpiMerchant ?? null;
            $merchantId = $merchant->tpi_merchant_id ?? null;
            if (!$merchantId) {
                $admin = \App\Models\User::whereIn('role', ['super_admin'])->first();
                $merchantId = $admin ? ($admin->tpiMerchant->tpi_merchant_id ?? null) : null;
            }
            if (!$merchantId) {
                $merchantId = \App\Models\SystemSetting::getSetting('default_merchant_id');
            }

            if ($merchantId) {
                try {
                    $tpiService = app(\App\Services\TpiPaymentService::class);
                    $resp = $tpiService->createPayment([
                        'merchantId' => $merchantId,
                        'orderId' => 'BKG-' . substr($booking->id, 0, 8) . '-' . time(),
                        'amount' => $payableAmt,
                        'customerName' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->mobile ?? '9999999999',
                        'surl' => url('/api/payment/tpi/success?bkg=' . $booking->id),
                        'furl' => url('/api/payment/tpi/failure?bkg=' . $booking->id),
                        'productInfo' => substr($pkg->name, 0, 100),
                    ]);
                    
                    $paymentId = $resp['paymentId'] ?? null;
                    
                    if ($paymentId) {
                        \App\Models\Payment::create([
                            'booking_id'  => $booking->id,
                            'gateway'     => 'tpipay',
                            'gateway_ref' => $paymentId,
                            'amount'      => $payableAmt,
                            'status'      => 'pending'
                        ]);
                    }

                    $paymentInfo = [
                        'order_id' => $paymentId,
                        'accessKey' => $resp['accessKey'] ?? null,
                        'payment_link' => $resp['paymentLink'] ?? null,
                        'amount' => $payableAmt,
                        'currency' => 'INR',
                        'gateway' => 'tpipay'
                    ];
                } catch (\Exception $e) {
                    Log::error('TPI Pay error in store: ' . $e->getMessage());
                }
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => in_array($booking->payment_method, ['online', 'auto_pay']) ? 'Booking created. Proceed to payment.' : 'Booking created. Waiting for partner OTP confirmation.',
            'data'    => [
                'booking_id'      => $booking->id,
                'status'          => $booking->status, // pending_otp or pending_payment
                'otp'             => !in_array($booking->payment_method, ['online', 'auto_pay']) ? $booking->otp : null,
                'payment_method'  => $booking->payment_method,
                'billing'         => $billing,
                'invoice_id'      => $invoice ? $invoice->id : null,
                'invoice_number'  => $invoice ? $invoice->invoice_number : null,
                'payment_info'    => $paymentInfo,
            ],
        ], 201);

    }

    // ────────────────────────────────────────────────────────────────
    // GET /api/bookings
    // Customer's booking list
    // ────────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {

        $bookings = \App\Models\Booking::with([

            'package.listing.category',

            'package.listing.images',

            'room.floor',

            'invoices',

            'coupon',

        ])

        ->where('customer_id', $request->user()->id)

        ->latest()

        ->paginate(15);



        $bookings->getCollection()->transform(fn($b) => $this->formatBooking($b));



        return response()->json(['status' => 'success', 'data' => $bookings]);

    }

    // ────────────────────────────────────────────────────────────────
    // GET /api/bookings/{id}
    // Booking detail + full billing summary
    // ────────────────────────────────────────────────────────────────

    public function pay(Request $request, $id)
    {
   
        $booking = \App\Models\Booking::with('package.listing.partner')->findOrFail($id);
        $user = $request->user();

        if ($booking->customer_id !== $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        if ($booking->status !== 'pending_payment') {
            return response()->json(['status' => 'error', 'message' => 'Booking is not pending payment.'], 400);
        }

        $merchantId = $booking->package->listing->partner->tpiMerchant->tpi_merchant_id ?? null;
        if (!$merchantId) {
            $admin = \App\Models\User::whereIn('role', ['super_admin'])->first();
            $merchantId = $admin ? ($admin->tpiMerchant->tpi_merchant_id ?? null) : null;
        }
        if (!$merchantId) {
            $merchantId = \App\Models\SystemSetting::getSetting('default_merchant_id');
        }

        if (!$merchantId) {
            return response()->json(['status' => 'error', 'message' => 'Payment gateway not configured.'], 500);
        }


        try {
            $tpiService = app(\App\Services\TpiPaymentService::class);
            $resp = $tpiService->createPayment([
                'merchantId' => $merchantId,
                'orderId' => 'BKG-' . substr($booking->id, 0, 8) . '-' . time(),
                'amount' => (float) $booking->final_amount,
                'customerName' => $user->name,
                'email' => $user->email,
                'phone' => $user->mobile ?? '9999999999',
                'surl' => url('/api/payment/tpi/success?bkg=' . $booking->id),
                'furl' => url('/api/payment/tpi/failure?bkg=' . $booking->id),
                'productInfo' => substr($booking->package->name, 0, 100),
            ]);
            
            $paymentId = $resp['paymentId'] ?? null;
            
            if ($paymentId) {
                // Find existing pending payment or create new
                $payment = \App\Models\Payment::firstOrNew([
                    'booking_id' => $booking->id,
                    'status' => 'pending'
                ]);
                $payment->gateway = 'tpipay';
                $payment->gateway_ref = $paymentId;
                $payment->amount = $booking->final_amount;
                $payment->save();
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Payment link generated successfully.',
                'data'    => [
                    'order_id' => $paymentId,
                    'accessKey' => $resp['accessKey'] ?? null,
                    'payment_link' => $resp['paymentLink'] ?? null,
                    'amount' => $booking->final_amount,
                    'currency' => 'INR',
                    'gateway' => 'tpipay'
                ]
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
            \Illuminate\Support\Facades\Log::error('TPI Pay error in pay(): ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to generate payment link.'], 500);
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {

        $booking = \App\Models\Booking::with([
            'package.listing.category',
            'package.listing.images',
            'room.floor',
            'invoices',
            'coupon',
            'payments',
        ])

        ->where('customer_id', $request->user()->id)

        ->findOrFail($id);



        return response()->json([

            'status' => 'success',

            'data'   => $this->formatBooking($booking, detailed: true),

        ]);

    }

    // ────────────────────────────────────────────────────────────────
    // POST /api/bookings/{id}/verify-otp
    // ────────────────────────────────────────────────────────────────
    public function verifyOtp(Request $request, string $id): JsonResponse
    {
        $request->validate(['otp' => 'required|string']);

        $booking = \App\Models\Booking::with(['invoices', 'package.listing.partner.tpiMerchant', 'customer'])
            ->where('customer_id', $request->user()->id)
            ->findOrFail($id);

        if ($booking->status !== 'pending_otp') {
            return response()->json(['status' => 'error', 'message' => 'Booking is not pending OTP.'], 422);
        }

        if (!$this->otpService->verifyOtp('booking_' . $booking->id, $request->otp)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or expired OTP.'], 422);
        }

        $booking->update(['status' => 'completed']);
        $invoice = $booking->invoices()->first();

        // Create subscription since OTP is verified and payment is done (for online) or cash is collected
        $subscription = \App\Models\Subscription::create([
            'customer_id'     => $booking->customer_id,
            'package_id'      => $booking->package_id,
            'room_id'         => $booking->room_id,
            'occupancy_type'  => $booking->occupancy_type,
            'beds_booked'     => $booking->beds_booked,
            'starts_at'       => now()->toDateString(),
            'expires_at'      => now()->addDays((int) $booking->package->duration_days)->toDateString(),
            'status'          => 'active',
            'auto_renew'      => $booking->payment_method === 'auto_pay',
            'booking_id'      => $booking->id,
        ]);

        if ($invoice) {
            $invoice->update(['subscription_id' => $subscription->id]);
            // If payment was cash, we mark invoice as paid now since OTP is verified
            if ($booking->payment_method === 'cash') {
                $invoice->update(['status' => 'paid']);
                \App\Models\Payment::updateOrCreate(
                    ['booking_id' => $booking->id, 'invoice_id' => $invoice->id, 'status' => 'pending'],
                    [
                        'subscription_id' => $subscription->id,
                        'gateway'         => 'cash',
                        'gateway_ref'     => 'cash_' . \Illuminate\Support\Str::random(10),
                        'amount'          => $invoice->total,
                        'status'          => 'paid',
                        'paid_at'         => now(),
                    ]
                );
            }
        }

        // Process Partner & Admin Commission Wallet distribution
        app(\App\Http\Controllers\Api\PaymentController::class)->processCommission($booking);

        if ($subscription && $subscription->auto_renew && !$subscription->gateway_subscription_id) {
            $subscription->update(['gateway_subscription_id' => 'sub_' . \Illuminate\Support\Str::random(14)]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'OTP verified successfully. Booking is complete!',
            'data' => [
                'booking_id' => $booking->id,
                'status' => 'completed',
                'invoice_id' => $invoice ? $invoice->id : null,
                'subscription_id' => $subscription->id
            ]
        ]);
    }

    // ────────────────────────────────────────────────────────────────
    // POST /api/bookings/{id}/resend-otp
    // ────────────────────────────────────────────────────────────────
    public function resendOtp(Request $request, string $id): JsonResponse
    {
        $booking = \App\Models\Booking::where('customer_id', $request->user()->id)->findOrFail($id);

        if ($booking->status !== 'pending_otp') {
            return response()->json(['status' => 'error', 'message' => 'Booking is not pending OTP.'], 422);
        }

        try {
            $this->bookingService->sendOtp($booking);
            return response()->json(['status' => 'success', 'message' => 'OTP resent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to resend OTP.'], 500);
        }
    }

    // ────────────────────────────────────────────────────────────────
    // POST /api/bookings/{id}/cancel
    // Customer cancels their booking before payment
    // ────────────────────────────────────────────────────────────────
    public function cancel(Request $request, string $id): JsonResponse
    {
        $booking = \App\Models\Booking::where('customer_id', $request->user()->id)->findOrFail($id);
        if (in_array($booking->status, ['completed', 'cancelled'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot cancel a booking that is already ' . $booking->status . '.',
            ], 422);

        }
        DB::beginTransaction();
        try {
            $booking->update(['status' => 'cancelled']);
            // Update associated invoices to cancelled if they are not paid
            $booking->invoices()->where('status', '!=', 'paid')->update(['status' => 'cancelled']);

            // Refund logic
            $totalPaid = \App\Models\Payment::where('booking_id', $booking->id)
                            ->where('status', 'paid')
                            ->sum('amount');
                            
            if ($totalPaid > 0) {
                $user = $request->user();
                $user->increment('wallet_balance', $totalPaid);
                
                \App\Models\WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'credit',
                    'amount' => $totalPaid,
                    'description' => 'Refund for cancelled booking #' . $booking->booking_number,
                    'reference_type' => \App\Models\Booking::class,
                    'reference_id' => $booking->id,
                ]);
            }

            DB::commit();
            $title = 'Booking Cancelled';
            $message = "Your booking for {$booking->listing->title} has been cancelled successfully.";
            \App\Models\AppNotification::create([
                'user_id' => $request->user()->id,
                'title' => $title,
                'message' => $message,
            ]);

            if ($request->user()->fcm_token) {
                try {
                    app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                        $request->user()->fcm_token,
                        $title,
                        $message,
                        ['booking_id' => $booking->id],
                        null,
                        false
                    );
                } catch (\Exception $e) {}

            }



            return response()->json([

                'status'  => 'success',

                'message' => 'Booking has been cancelled successfully.',

                'data'    => ['booking_id' => $booking->id, 'status' => 'cancelled'],

            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Booking cancellation failed: ' . $e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Failed to cancel booking.'], 500);

        }

    }

    // ─── Format booking for response ────────────────────────────────
    private function formatBooking(\App\Models\Booking $b, bool $detailed = false): array
    {
        $listing = $b->package?->listing;
        $category = $listing?->category;
        $base = [
            'booking_id'      => $b->id,
            'booking_date'    => $b->created_at,
            'status'          => $b->status,
            'otp'             => $b->otp,
            'payment_method'  => $b->payment_method,
            'listing'         => $listing ? [
                'id'           => $listing->id,
                'title'        => $listing->title,
                'category'     => $category?->name,
                'description'  => $listing->description,
                'address'      => $listing->address,
                'city'         => $listing->city,
                'state'        => $listing->state,
                'pincode'      => $listing->pincode,
                'landmark'     => $listing->landmark,
                'lat'          => $listing->lat,
                'lng'          => $listing->lng,
                'phone'        => $listing->phone,
                'opening_time' => $listing->opening_time,
                'closing_time' => $listing->closing_time,
                'image'        => $listing->images->first()
                    ? asset('storage/' . $listing->images->first()->image_path)
                    : null,
                'partner'      => $listing->partner ? [
                    'id'    => $listing->partner->id,
                    'name'  => $listing->partner->name,
                    'mobile'=> $listing->partner->mobile,
                    'email' => $listing->partner->email,
                ] : null,

            ] : null,

            'plan'   => $b->package ? [
                'id'            => $b->package->id,
                'name'          => $b->package->name,
                'duration'      => $b->package->duration_label,
                'price'         => (float) $b->package->price,
            ] : null,

            'customer'        => $b->customer ? [
                'id'     => $b->customer->id,
                'name'   => $b->customer->name,
                'mobile' => $b->customer->mobile,
                'email'  => $b->customer->email,
            ] : null,

        ];



        if ($category && $category->has_rooms && $b->room) {

            $base['room'] = [

                'id' => $b->room->id,

                'room_number' => $b->room->room_number,

                'room_type' => $b->room->room_type,

                'security_deposit' => (float) $b->room->security_deposit,

                'floor' => $b->room->floor ? [

                    'id' => $b->room->floor->id,

                    'floor_number' => $b->room->floor->floor_number,

                    'name' => $b->room->floor->name,

                ] : null,

            ];

        } else {
            $base['room'] = null;
        }



        if ($category && $category->has_shifts && $b->shift) {

            $base['shift'] = [

                'id' => $b->shift->id,

                'shift_name' => $b->shift->shift_name,

                'start_time' => $b->shift->start_time,

                'end_time' => $b->shift->end_time,

                'fee' => (float) $b->shift->fee,

            ];

        } else {
            $base['shift'] = null;
        }



        if ($category && $category->has_trainers && $b->trainer_ids) {

            $base['trainers'] = \App\Models\ListingTrainer::whereIn('id', $b->trainer_ids)->get()->map(fn($t) => [

                'id' => $t->id,

                'name' => $t->name,

                'specialization' => $t->specialization,

                'photo' => $t->photo_url,

            ]);

        } else {

            $base['trainers'] = null;

        }



        $basePrice = (float) ($b->package->price ?? 0);
        $subtotal = in_array($b->occupancy_type, ['per_bed', 'per_site']) ? round($basePrice * max(1, $b->beds_booked), 2) : $basePrice;
        $shiftFee = $b->shift ? (float) $b->shift->fee : 0.00;
        $trainerFee = 0.00;
        $calculatedSubtotal = round($subtotal + $shiftFee + $trainerFee, 2);

        $securityDeposit = (float) $b->security_deposit;

        $base['billing'] = [
            'subtotal'        => $calculatedSubtotal,
            'discount'        => (float) $b->discount_amount,
            'security_deposit'=> $securityDeposit,
            'reserve_amount'  => $securityDeposit > 0 ? (float) $b->final_amount : 0,
            'shift_fee'       => $shiftFee,
            'trainer_fee'     => $trainerFee,
            'beds_booked'     => (int) $b->beds_booked,
            'final_amount'    => (float) $b->final_amount,
            'coupon'          => $b->coupon ? [
                'code'  => $b->coupon->code,
                'title' => $b->coupon->title,
            ] : null,

        ];



        if ($detailed) {

            $base['invoices'] = $b->invoices->map(fn($inv) => [
                'id'             => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'amount'         => (float) $inv->amount,
                'total'          => (float) $inv->total,
                'status'         => $inv->status,
                'due_date'       => $inv->due_date,
            ]);

            $base['payments'] = $b->payments->map(fn($p) => [
                'id'             => $p->id,
                'gateway'        => $p->gateway,
                'receipt_no'     => $p->receipt_number,
                'transaction_id' => $p->gateway_ref,
                'amount'         => (float) $p->amount,
                'status'         => $p->status,
                'paid_at'        => $p->paid_at,
            ]);

        }



        return $base;

    }


}

