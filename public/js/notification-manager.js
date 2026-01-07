/**
 * Notification Manager
 * Handles fetching and displaying notifications using Toastr
 */

class NotificationManager {
    constructor() {
        this.shownNotificationIds = new Set();
        this.isChecking = false;
        this.init();
    }

    /**
     * Initialize the notification manager
     */
    init() {
        // Wait for toastr to be available
        if (typeof toastr === 'undefined') {
            console.warn('Toastr not loaded, notification manager will retry');
            setTimeout(() => this.init(), 1000);
            return;
        }

        // Check for notifications on page load (only once to get any missed notifications)
        setTimeout(() => {
            this.checkNotifications();
        }, 2000);

        // Listen for FCM messages (primary method for receiving notifications)
        window.addEventListener('fcm-message', (event) => {
            const payload = event.detail;
            if (payload.notification && typeof toastr !== 'undefined') {
                this.showNotification({
                    title: payload.notification.title || 'Notification',
                    message: payload.notification.body || '',
                    data: payload.data || {},
                });

                // Update unread count after receiving FCM notification
                this.updateUnreadCountFromServer();
            }
        });

        // Also listen for FCM data messages (when notification is sent as data only)
        // Note: We don't call firebase.messaging() here because it requires service worker
        // The fcm-token-manager.js will handle setting up the message handler
        // We'll rely on the 'fcm-message' event which is dispatched by fcm-token-manager
    }

    /**
     * Check for new unread notifications (only used on page load to catch missed notifications)
     * Primary method for receiving notifications is via FCM push messages
     */
    async checkNotifications() {
        if (this.isChecking) return;

        this.isChecking = true;

        try {
            const response = await fetch('/notifications/unread', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch notifications');
            }

            const data = await response.json();

            // Show new notifications (only on initial load, FCM handles real-time)
            // if (data.notifications && data.notifications.length > 0) {
            //     data.notifications.forEach(notification => {
            //         // Only show if it's a new notification (not shown before)
            //         if (!this.shownNotificationIds.has(notification.id)) {
            //             this.showNotification(notification);
            //             this.shownNotificationIds.add(notification.id);

            //             // Keep only last 50 IDs to prevent memory issues
            //             if (this.shownNotificationIds.size > 50) {
            //                 const firstId = Array.from(this.shownNotificationIds)[0];
            //                 this.shownNotificationIds.delete(firstId);
            //             }
            //         }
            //     });
            // }

            // Update unread count badge if exists
            this.updateUnreadCount(data.unread_count || 0);

            // Trigger custom event for dropdown to update
            window.dispatchEvent(new CustomEvent('notifications-updated', {
                detail: { unreadCount: data.unread_count || 0, notifications: data.notifications || [] }
            }));

        } catch (error) {
            console.error('Error checking notifications:', error);
        } finally {
            this.isChecking = false;
        }
    }

    /**
     * Update unread count from server (called after FCM notification)
     */
    async updateUnreadCountFromServer() {
        try {
            const response = await fetch('/notifications/unread', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
            });

            if (response.ok) {
                const data = await response.json();
                this.updateUnreadCount(data.unread_count || 0);
            }
        } catch (error) {
            console.error('Error updating unread count:', error);
        }
    }

    /**
     * Show notification using Toastr
     */
    showNotification(notification) {
        if (typeof toastr === 'undefined') {
            console.warn('Toastr is not loaded');
            return;
        }

        const title = notification.title || 'Notification';
        const message = notification.message || '';
        const data = notification.data || {};

        // Determine notification type based on data.type or default to info
        let type = 'info';
        if (data.type) {
            const typeMap = {
                'offer_created': 'success',
                'offer_accepted': 'success',
                'order_shipped': 'info',
                'error': 'error',
                'warning': 'warning',
            };
            type = typeMap[data.type] || 'info';
        }

        // Show toastr notification
        toastr[type](message, title, {
            timeOut: 5000,
            extendedTimeOut: 10000,
            progressBar: true,
            closeButton: true,
            onclick: function () {
                // Handle click - could navigate to relevant page
                if (data.action && data.url) {
                    window.location.href = data.url;
                }
            }
        });
    }

    /**
     * Update unread count badge
     */
    updateUnreadCount(count) {
        // Find and update notification badge in the UI
        const badgeElements = document.querySelectorAll('.notification-badge, [data-notification-count]');
        badgeElements.forEach(badge => {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        });
    }

    /**
     * Mark notification as read
     */
    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });

            if (response.ok) {
                // Refresh notification count
                this.checkNotifications();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    /**
     * Mark all notifications as read
     */
    async markAllAsRead() {
        try {
            const response = await fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });

            if (response.ok) {
                this.updateUnreadCount(0);
                this.checkNotifications();
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }
}

// Initialize on page load
let notificationManager = null;

document.addEventListener('DOMContentLoaded', function () {
    // Wait for toastr to be loaded
    if (typeof toastr !== 'undefined') {
        notificationManager = new NotificationManager();
    } else {
        // Try again after a delay
        setTimeout(() => {
            if (typeof toastr !== 'undefined') {
                notificationManager = new NotificationManager();
            }
        }, 1000);
    }
});

// Export for use in other scripts
window.NotificationManager = NotificationManager;
window.notificationManager = notificationManager;

