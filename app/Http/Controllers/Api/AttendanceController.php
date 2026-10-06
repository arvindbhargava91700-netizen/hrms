<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Listing;
use App\Models\ListingShift;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Attendance radius gate in metres.
     */
    private const RADIUS_METRES = 100;

    // ────────────────────────────────────────────────────────────────
    // POST /api/attendance/punch-in
    // ────────────────────────────────────────────────────────────────
    public function punchIn(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'lat'        => 'required|numeric|between:-90,90',
            'lng'        => 'required|numeric|between:-180,180',
        ]);

        // ── 1. Resolve the active subscription & listing context ──
        $context = $this->resolveContext($user->id, $request->listing_id);

        if (!$context) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No active subscription found for this listing.',
            ], 403);
        }

        /** @var Listing       $listing */
        /** @var ListingShift|null $shift */
        /** @var string        $windowStart  HH:MM:SS */
        /** @var string        $windowEnd    HH:MM:SS */
        /** @var Subscription  $subscription */
        [
            'subscription' => $subscription,
            'listing'      => $listing,
            'shift'        => $shift,
            'window_start' => $windowStart,
            'window_end'   => $windowEnd,
        ] = $context;

        // ── 2. Category must have attendance enabled ──────────────
        if (!$listing->category->has_attendance) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Attendance is not enabled for this listing category.',
            ], 403);
        }

        // ── 3. GPS distance check (100 m radius) ──────────────────
        if ($listing->lat && $listing->lng) {
            $distanceMetres = $this->haversineDistance(
                (float) $request->lat, (float) $request->lng,
                (float) $listing->lat, (float) $listing->lng
            );

            if ($distanceMetres > self::RADIUS_METRES) {
                return response()->json([
                    'status'           => 'error',
                    'message'          => 'You are too far from the listing to mark attendance. You must be within ' . self::RADIUS_METRES . ' metres.',
                    'distance_metres'  => round($distanceMetres, 1),
                    'allowed_metres'   => self::RADIUS_METRES,
                ], 403);
            }
        }

        // ── 4. Time-window check ──────────────────────────────────
        if ($windowStart && $windowEnd) {
            $now          = now();
            $todayStart   = Carbon::parse(today()->toDateString() . ' ' . $windowStart);
            $todayEnd     = Carbon::parse(today()->toDateString() . ' ' . $windowEnd);

            // Handle overnight shifts (e.g. 22:00 – 06:00)
            if ($todayEnd->lessThanOrEqualTo($todayStart)) {
                $todayEnd->addDay();
            }

            if ($now->lessThan($todayStart)) {
                return response()->json([
                    'status'         => 'error',
                    'message'        => 'Attendance window has not started yet. It opens at ' . $todayStart->format('h:i A') . '.',
                    'window_start'   => $todayStart->format('H:i'),
                    'window_end'     => $todayEnd->format('H:i'),
                ], 422);
            }

            if ($now->greaterThan($todayEnd)) {
                return response()->json([
                    'status'         => 'error',
                    'message'        => 'Attendance window has closed for today. It closed at ' . $todayEnd->format('h:i A') . '.',
                    'window_start'   => $todayStart->format('H:i'),
                    'window_end'     => $todayEnd->format('H:i'),
                ], 422);
            }

            // ── 5. Late calculation ───────────────────────────────
            $isLate      = $now->greaterThan($todayStart);
            $lateMinutes = $isLate ? (int) $todayStart->diffInMinutes($now) : 0;
        } else {
            $isLate      = false;
            $lateMinutes = 0;
        }

        // ── 6. Duplicate check ────────────────────────────────────
        $existing = AttendanceLog::where('subscription_id', $subscription->id)
            ->whereDate('date', today())
            ->latest('punch_in_at')
            ->first();

        if ($existing) {
            if ($existing->isOpen()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'You have already punched in today. Please punch out first.',
                    'data'    => $this->formatLog($existing),
                ], 422);
            }

            // If it's completed, check if multiple attendances are allowed (has_rooms = true allows multiple)
            if (!$listing->category->has_rooms) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Attendance already completed for today.',
                    'data'    => $this->formatLog($existing),
                ], 422);
            }
        }

        // ── 7. Create attendance log ──────────────────────────────
        $log = AttendanceLog::create([
            'subscription_id' => $subscription->id,
            'customer_id'     => $user->id,
            'listing_id'      => $listing->id,
            'shift_id'        => $shift?->id,
            'date'            => today(),
            'punch_in_at'     => now(),
            'lat'             => $request->lat,
            'lng'             => $request->lng,
            'is_late'         => $isLate,
            'late_minutes'    => $isLate ? $lateMinutes : null,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => $isLate
                ? "Punched in successfully. You are {$lateMinutes} minute(s) late."
                : 'Punched in successfully.',
            'data'    => $this->formatLog($log->fresh(['listing', 'shift'])),
        ], 201);
    }

    // ────────────────────────────────────────────────────────────────
    // POST /api/attendance/punch-out
    // ────────────────────────────────────────────────────────────────
    public function punchOut(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'lat'        => 'required|numeric|between:-90,90',
            'lng'        => 'required|numeric|between:-180,180',
        ]);

        // ── 1. Find today's open punch-in ─────────────────────────
        $log = AttendanceLog::with(['listing'])
            ->where('customer_id', $user->id)
            ->where('listing_id', $request->listing_id)
            ->whereDate('date', today())
            ->latest('punch_in_at')
            ->first();

        if (!$log) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You have not punched in today.',
            ], 422);
        }

        if ($log->isDone()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You have already punched out today.',
                'data'    => $this->formatLog($log),
            ], 422);
        }

        // ── 2. GPS distance check ─────────────────────────────────
        $listing = $log->listing;
        if ($listing && $listing->lat && $listing->lng) {
            $distanceMetres = $this->haversineDistance(
                (float) $request->lat, (float) $request->lng,
                (float) $listing->lat, (float) $listing->lng
            );

            if ($distanceMetres > self::RADIUS_METRES) {
                return response()->json([
                    'status'          => 'error',
                    'message'         => 'You are too far from the listing to punch out. You must be within ' . self::RADIUS_METRES . ' metres.',
                    'distance_metres' => round($distanceMetres, 1),
                    'allowed_metres'  => self::RADIUS_METRES,
                ], 403);
            }
        }

        // ── 3. Save punch-out ─────────────────────────────────────
        $punchOutAt      = now();
        $durationMinutes = (int) $log->punch_in_at->diffInMinutes($punchOutAt);

        $log->update([
            'punch_out_at'     => $punchOutAt,
            'punch_out_lat'    => $request->lat,
            'punch_out_lng'    => $request->lng,
            'duration_minutes' => $durationMinutes,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Punched out successfully.',
            'data'    => $this->formatLog($log->fresh(['listing', 'shift'])),
        ]);
    }

    // ────────────────────────────────────────────────────────────────
    // GET /api/attendance/today
    // ────────────────────────────────────────────────────────────────
    public function today(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'listing_id' => 'sometimes|exists:listings,id',
        ]);

        $query = AttendanceLog::with(['listing', 'shift'])
            ->where('customer_id', $user->id)
            ->whereDate('date', today());

        if ($request->filled('listing_id')) {
            $query->where('listing_id', $request->listing_id);
        }

        $logs = $query->get()->map(fn($log) => $this->formatLog($log));

        return response()->json([
            'status' => 'success',
            'data'   => $logs,
        ]);
    }

    // ────────────────────────────────────────────────────────────────
    // GET /api/attendance
    // Customer's full attendance history (paginated)
    // ────────────────────────────────────────────────────────────────
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'listing_id' => 'sometimes|exists:listings,id',
            'from'       => 'sometimes|date',
            'to'         => 'sometimes|date|after_or_equal:from',
        ]);

        $query = AttendanceLog::with(['listing', 'shift'])
            ->where('customer_id', $user->id)
            ->when($request->filled('listing_id'), fn($q) => $q->where('listing_id', $request->listing_id))
            ->when($request->filled('from'), fn($q) => $q->whereDate('date', '>=', $request->from))
            ->when($request->filled('to'),   fn($q) => $q->whereDate('date', '<=', $request->to))
            ->orderByDesc('date');

        $logs = $query->paginate(20);
        $logs->getCollection()->transform(fn($log) => $this->formatLog($log));

        return response()->json([
            'status' => 'success',
            'data'   => $logs,
        ]);
    }

    // ────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ────────────────────────────────────────────────────────────────

    /**
     * Resolve the active subscription for a customer+listing pair.
     *
     * Handles 3 booking modes:
     *   1. Gym / shift-based  → subscription has shift_id via booking
     *   2. Package-based      → subscription → package → listing
     *   3. Room / PG          → subscription has room_id → room → floor → listing
     *
     * Returns array with keys: subscription, listing, shift, window_start, window_end
     * or null when no active subscription found.
     */
    private function resolveContext(string $customerId, string $listingId): ?array
    {
        // ── Try package-based subscription (gym, classes, etc.) ───
        $subscription = Subscription::with([
                'package.listing.category',
                'shift',
                'booking.shift',
            ])
            ->where('customer_id', $customerId)
            ->where('status', 'active')
            ->whereHas('package.listing', fn($q) => $q->where('id', $listingId))
            ->latest()
            ->first();

        if ($subscription) {
            $listing  = $subscription->package->listing;
            $category = $listing->category;

            // Determine the shift — prefer subscription.shift_id, fallback to booking.shift_id
            $shift = $subscription->shift
                ?? $subscription->booking?->shift
                ?? null;

            if ($category->has_shifts && $shift) {
                return [
                    'subscription' => $subscription,
                    'listing'      => $listing,
                    'shift'        => $shift,
                    'window_start' => $shift->start_time,
                    'window_end'   => $shift->end_time,
                ];
            }

            // No shift (e.g. has_attendance=true, has_shifts=false) → use listing opening/closing
            return [
                'subscription' => $subscription,
                'listing'      => $listing,
                'shift'        => null,
                'window_start' => $listing->opening_time,
                'window_end'   => $listing->closing_time,
            ];
        }

        // ── Try room-based subscription (PG / Room) ───────────────
        $subscription = Subscription::with(['room.floor.listing.category'])
            ->where('customer_id', $customerId)
            ->where('status', 'active')
            ->whereNotNull('room_id')
            ->whereHas('room.floor.listing', fn($q) => $q->where('id', $listingId))
            ->latest()
            ->first();

        if ($subscription) {
            $listing = $subscription->room->floor->listing;

            // Room/PG: no shifts — use listing opening/closing time
            return [
                'subscription' => $subscription,
                'listing'      => $listing,
                'shift'        => null,
                'window_start' => $listing->opening_time,
                'window_end'   => $listing->closing_time,
            ];
        }

        return null;
    }

    /**
     * Haversine formula — returns distance in metres between two GPS points.
     */
    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // metres

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Format an AttendanceLog for customer-facing API response.
     */
    private function formatLog(AttendanceLog $log): array
    {
        return [
            'id'               => $log->id,
            'date'             => $log->date->toDateString(),
            'punch_in_at'      => $log->punch_in_at?->toTimeString(),
            'punch_out_at'     => $log->punch_out_at?->toTimeString(),
            'duration_minutes' => $log->duration_minutes,
            'status'           => $log->isDone() ? 'completed' : ($log->isOpen() ? 'open' : 'absent'),
            'listing'          => $log->listing?->title,
            'listing_id'       => $log->listing_id,
            // Shift info
            'shift_id'         => $log->shift_id,
            'shift_name'       => $log->shift?->shift_name,
            'shift_start'      => $log->shift?->start_time,
            'shift_end'        => $log->shift?->end_time,
            // Late info
            'is_late'          => (bool) $log->is_late,
            'late_minutes'     => $log->late_minutes ?? 0,
            // GPS
            'punch_in_location' => ($log->lat && $log->lng) ? [
                'lat' => (float) $log->lat,
                'lng' => (float) $log->lng,
            ] : null,
            'punch_out_location' => ($log->punch_out_lat && $log->punch_out_lng) ? [
                'lat' => (float) $log->punch_out_lat,
                'lng' => (float) $log->punch_out_lng,
            ] : null,
        ];
    }
}
