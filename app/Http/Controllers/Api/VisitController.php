<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisitBooking;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VisitController extends Controller
{
    // Customer: create visit request
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'visit_date' => 'required|date',
            'visit_time' => 'required|date_format:H:i',
            'note'       => 'nullable|string',
        ]);

        $listing = Listing::findOrFail($data['listing_id']);

        $visit = VisitBooking::create([
            'customer_id' => $request->user()->id,
            'listing_id'  => $listing->id,
            'partner_id'  => $listing->partner_id,
            'visit_date'  => $data['visit_date'],
            'visit_time'  => $data['visit_time'],
            'note'        => $data['note'] ?? null,
            'status'      => 'pending',
        ]);

        // Notify partner if possible
        try {
            $partner = $listing->partner;
            if ($partner?->fcm_token) {
                app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                    $partner->fcm_token,
                    'New Visit Request',
                    "A customer requested a visit on {$visit->visit_date} at {$visit->visit_time}.",
                    ['visit_id' => $visit->id, 'listing_id' => $listing->id]
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Visit notification failed: ' . $e->getMessage());
        }

        app(\App\Services\WhatsAppNotificationService::class)->sendVisitRequested($visit);

        return response()->json(['status' => 'success', 'data' => $visit], 201);
    }

    // Customer: update visit details
    public function update(Request $request, string $id): JsonResponse
    {
        $visit = VisitBooking::where('customer_id', $request->user()->id)->findOrFail($id);
        
        if (!in_array($visit->status, ['pending', 'accepted'])) {
            return response()->json(['status' => 'error', 'message' => 'Cannot update at this stage'], 422);
        }

        $data = $request->validate([
            'visit_date' => 'sometimes|date',
            'visit_time' => 'sometimes|date_format:H:i',
            'note'       => 'nullable|string',
        ]);

        $visit->update($data);

        return response()->json(['status' => 'success', 'message' => 'Visit updated successfully', 'data' => $visit]);
    }

    // Customer: list my visits
    public function index(Request $request): JsonResponse
    {
        $visits = VisitBooking::with('listing.images')->where('customer_id', $request->user()->id)->latest()->paginate(20);
        return response()->json(['status' => 'success', 'data' => $visits]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $visit = VisitBooking::with(['listing.images','partner','customer'])->where(function($q) use ($request, $id) {
            // owner or partner or admin can view
            $q->where('id', $id);
        })->firstOrFail();

        // Authorization: only customer, partner or admin
        $user = $request->user();
        if ($user->id !== $visit->customer_id && $user->id !== $visit->partner_id && $user->role !== 'super_admin') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        return response()->json(['status' => 'success', 'data' => $visit]);
    }

    // Customer: cancel
    public function cancel(Request $request, string $id): JsonResponse
    {
        $visit = VisitBooking::where('customer_id', $request->user()->id)->findOrFail($id);
        if (!in_array($visit->status, ['pending'])) {
            return response()->json(['status' => 'error', 'message' => 'Cannot cancel at this stage'], 422);
        }
        $visit->update(['status' => 'cancelled']);

        try {
            $partner = $visit->partner;
            $title = 'Visit Cancelled';
            $message = "A customer cancelled their visit request on {$visit->visit_date}.";
            
            \App\Models\AppNotification::create([
                'user_id' => $partner->id,
                'title' => $title,
                'message' => $message,
            ]);

            \App\Models\AppNotification::create([
                'user_id' => $request->user()->id,
                'title' => 'Visit Cancelled',
                'message' => "You have successfully cancelled your visit request on {$visit->visit_date}.",
            ]);

            if ($partner?->fcm_token) {
                app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                    $partner->fcm_token,
                    $title,
                    $message,
                    ['visit_id' => $visit->id, 'type' => 'visit_cancelled'],
                    null,
                    false
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Visit cancel notify failed: ' . $e->getMessage());
        }

        return response()->json(['status' => 'success', 'message' => 'Visit cancelled']);
    }

    // Partner: list incoming visits
    public function partnerIndex(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'partner') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        $visits = VisitBooking::with(['customer','listing.images'])->where('partner_id', $request->user()->id)->latest()->paginate(30);
        return response()->json(['status' => 'success', 'data' => $visits]);
    }

    // Partner: approve
    public function partnerApprove(Request $request, string $id): JsonResponse
    {
        if ($request->user()->role !== 'partner') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        $visit = VisitBooking::where('partner_id', $request->user()->id)->where('id', $id)->where('status', 'pending')->firstOrFail();
        $visit->update(['status' => 'accepted']);

        // notify customer
        try {
            $customer = $visit->customer;
            $title = 'Visit Accepted';
            $message = "Your visit request on {$visit->visit_date} at {$visit->visit_time} has been accepted.";
            
            \App\Models\AppNotification::create([
                'user_id' => $customer->id,
                'title' => $title,
                'message' => $message,
            ]);

            if ($customer?->fcm_token) {
                app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                    $customer->fcm_token,
                    $title,
                    $message,
                    ['visit_id' => $visit->id],
                    null,
                    false
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Visit accept notify failed: ' . $e->getMessage());
        }

        return response()->json(['status' => 'success', 'message' => 'Visit accepted', 'data' => $visit]);
    }

    public function partnerReject(Request $request, string $id): JsonResponse
    {
        if ($request->user()->role !== 'partner') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        $visit = VisitBooking::where('partner_id', $request->user()->id)->where('id', $id)->where('status', 'pending')->firstOrFail();
        $visit->update(['status' => 'rejected']);

        // notify customer
        try {
            $customer = $visit->customer;
            $title = 'Visit Rejected';
            $message = "Your visit request on {$visit->visit_date} at {$visit->visit_time} has been rejected.";

            \App\Models\AppNotification::create([
                'user_id' => $customer->id,
                'title' => $title,
                'message' => $message,
            ]);

            if ($customer?->fcm_token) {
                app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                    $customer->fcm_token,
                    $title,
                    $message,
                    ['visit_id' => $visit->id],
                    null,
                    false
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Visit reject notify failed: ' . $e->getMessage());
        }

        return response()->json(['status' => 'success', 'message' => 'Visit rejected', 'data' => $visit]);
    }

    // Admin: list all
    public function adminIndex(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'super_admin') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        $visits = VisitBooking::with(['customer','partner','listing.images'])->latest()->paginate(50);
        return response()->json(['status' => 'success', 'data' => $visits]);
    }

    // Admin: update status (accept/reject/cancel)
    public function adminUpdateStatus(Request $request, string $id): JsonResponse
    {
        if ($request->user()->role !== 'super_admin') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        $data = $request->validate(['status' => 'required|in:pending,accepted,rejected,cancelled']);
        $visit = VisitBooking::findOrFail($id);
        $visit->update(['status' => $data['status']]);
        return response()->json(['status' => 'success', 'data' => $visit]);
    }
}
