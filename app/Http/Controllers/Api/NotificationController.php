<?php



namespace App\Http\Controllers\Api;



use App\Http\Controllers\Controller;

use App\Models\AppNotification;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;



class NotificationController extends Controller

{

    /**

     * Get user's notifications.

     */

    public function index(Request $request): JsonResponse

    {

        $notifications = AppNotification::where('user_id', $request->user()->id)

            ->latest()

            ->paginate(20);



        return response()->json([

            'status' => 'success',

            'data'   => $notifications,

        ]);

    }



    /**

     * Mark a specific notification as read.

     */

    public function markAsRead(Request $request, $id): JsonResponse

    {

        $notification = AppNotification::where('user_id', $request->user()->id)

            ->findOrFail($id);



        if (!$notification->read_at) {

            $notification->update(['read_at' => now()]);

        }



        return response()->json([

            'status'  => 'success',

            'message' => 'Notification marked as read',

            'data'    => $notification

        ]);

    }



    /**

     * Mark all notifications as read.

     */

    public function markAllAsRead(Request $request): JsonResponse

    {

        AppNotification::where('user_id', $request->user()->id)

            ->whereNull('read_at')

            ->update(['read_at' => now()]);



        return response()->json([

            'status'  => 'success',

            'message' => 'All notifications marked as read',

        ]);

    }



    /**

     * Delete a notification.

     */

    public function destroy(Request $request, $id): JsonResponse
    {

        $notification = AppNotification::where('user_id', $request->user()->id)

            ->findOrFail($id);

            

        $notification->delete();



        return response()->json([

            'status'  => 'success',

            'message' => 'Notification deleted'

        ]);

    }

    /**
     * Unread notifications count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = AppNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    /**
     * Delete all notifications.
     */
    public function destroyAll(Request $request): JsonResponse
    {
        AppNotification::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'All notifications deleted'
        ]);
    }

}

