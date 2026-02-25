<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    /**
     * The notification service instance.
     *
     * @var \App\Services\NotificationService
     */
    protected $notificationService;
    
    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\NotificationService  $notificationService
     * @return void
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }
    
    /**
     * Display a listing of the user's notifications.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notifications = UserNotification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('notifications.index', compact('notifications'));
    }
    
    /**
     * Mark a notification as read.
     *
     * @param  \App\Models\UserNotification  $notification
     * @return \Illuminate\Http\Response
     */
    public function markAsRead(UserNotification $notification)
    {
        // Check if the notification belongs to the authenticated user
        if ($notification->user_id !== Auth::id()) {
            return redirect()->route('notifications.index')
                ->with('error', __('messages.unauthorized'));
        }
        
        $notification->markAsRead();
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back()
            ->with('success', __('messages.notification_marked_as_read'));
    }
    

    
    /**
     * Mark all notifications as read.
     *
     * @return \Illuminate\Http\Response
     */
    public function markAllAsRead()
    {
        $this->notificationService->markAllAsRead(Auth::user());
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back()
            ->with('success', __('messages.all_notifications_marked_as_read'));
    }
    
    /**
     * Delete a notification.
     *
     * @param  \App\Models\UserNotification  $notification
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserNotification $notification)
    {
        // Check if the notification belongs to the authenticated user
        if ($notification->user_id !== Auth::id()) {
            return redirect()->route('notifications.index')
                ->with('error', __('messages.unauthorized'));
        }
        
        $notification->delete();
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->route('notifications.index')
            ->with('success', __('messages.notification_deleted'));
    }
    
    /**
     * Get the latest notifications for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getLatest(Request $request)
    {
        $limit = $request->input('limit', 5);
        $notifications = $this->notificationService->getRecentNotifications($limit);
        $unreadCount = $this->notificationService->getUnreadCount();
        
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }
}
