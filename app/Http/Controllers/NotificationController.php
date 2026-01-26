<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get unread notifications for the authenticated user
     */
    public function unread(Request $request)
    {
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            return response()->json([
                'notifications' => [],
                'unread_count' => 0,
            ]);
        }

        $notifications = $user->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title_' . app()->getLocale()] ?? $notification->data['title'] ?? '',
                    'message' => $notification->data['message_' . app()->getLocale()] ?? $notification->data['message'] ?? '',
                    'title_ar' => $notification->data['title_ar'] ?? '',
                    'title_en' => $notification->data['title_en'] ?? '',
                    'message_ar' => $notification->data['message_ar'] ?? '',
                    'message_en' => $notification->data['message_en'] ?? '',
                    'url' => $notification->data['url'] ?? null,
                    'data' => $notification->data['data'] ?? [],
                    'read' => $notification->read_at !== null,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'created_at_raw' => $notification->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Show all notifications in a paginated view
     */
    public function index()
    {
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            return redirect()->route('login');
        }

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Determine which layout to use based on guard and user attributes
        $layout = 'admin.layouts.admin';
        
        if (Auth::guard('client')->check()) {
            $layout = 'client.layouts.dashboard';
        } elseif (method_exists($user, 'doctor') && $user->doctor()->exists()) {
            $layout = 'doctor.layouts.dashboard';
        } elseif ($user->laboratory_id) {
            $layout = 'laboratories.layouts.dashboard';
        } elseif ($user->pharmacy_id) {
            $layout = 'pharmacies.layouts.dashboard';
        } elseif ($user->nurse_id || (method_exists($user, 'nurse') && $user->nurse()->exists())) {
            $layout = 'nurse.layouts.dashboard';
        }

        return view('notifications.index', compact('notifications', 'layout'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $notification = $user->notifications()->find($id);
        
        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $user->unreadNotifications->markAsRead();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
            ]);
        }

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم تحديد الكل كمقروء' : 'All notifications marked as read');
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $notification = $user->notifications()->find($id);
        if ($notification) {
            $notification->delete();
        }

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم حذف الإشعار' : 'Notification deleted');
    }

    /**
     * Delete all notifications
     */
    public function destroyAll()
    {
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $user->notifications()->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', app()->getLocale() === 'ar' ? 'تم حذف جميع الإشعارات' : 'All notifications deleted');
    }

    /**
     * Helper to get authenticated user across guards
     */
    private function getAuthenticatedUser()
    {
        if (Auth::guard('client')->check()) {
            return Auth::guard('client')->user();
        }
        return Auth::user();
    }
}
