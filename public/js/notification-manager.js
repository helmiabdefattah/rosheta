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
            const payload = event.detail || {};
            const data = payload.data || {};

            // Check if we have either a notification object OR sufficient data in the payload
            if ((payload.notification || data.title || data.title_ar || data.title_en) && typeof toastr !== 'undefined') {
                this.showNotification({
                    title: payload.notification?.title || '',
                    message: payload.notification?.body || '',
                    title_ar: data.title_ar,
                    title_en: data.title_en,
                    message_ar: data.message_ar,
                    message_en: data.message_en,
                    data: data,
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
     * Show notification using Toastr with premium card UI
     */
    showNotification(notification) {
        if (typeof toastr === 'undefined') {
            console.warn('Toastr is not loaded');
            return;
        }

        const isAr = (document.documentElement.lang || '').startsWith('ar') || document.documentElement.getAttribute('dir') === 'rtl';
        const title = isAr ? (notification.title_ar || notification.title) : (notification.title_en || notification.title);
        const message = isAr ? (notification.message_ar || notification.message || notification.body) : (notification.message_en || notification.message || notification.body);
        const data = notification.data || {};

        // Play sound if enabled in UserConfig
        if (window.UserConfig && window.UserConfig.notificationSound) {
            this.playNotificationSound();
        }

        // Determine notification type and icon
        let type = 'info';
        let icon = '🔔';

        if (data.type) {
            const typeMap = {
                'offer_created': { type: 'success', icon: '💰' },
                'offer_accepted': { type: 'success', icon: '✅' },
                'order_shipped': { type: 'info', icon: '🚚' },
                'test_notification': { type: 'info', icon: '🧪' },
                'error': { type: 'error', icon: '❌' },
                'warning': { type: 'warning', icon: '⚠️' },
            };
            const config = typeMap[data.type] || { type: 'info', icon: '🔔' };
            type = config.type;
            icon = config.icon;
        }

        // Custom HTML for Toastr to look like a card
        const toastContent = `
            <div class="toastr-card">
                <div class="toastr-icon">${icon}</div>
                <div class="toastr-body">
                    <div class="toastr-title">${title}</div>
                    <div class="toastr-message">${message}</div>
                </div>
            </div>
        `;

        // Configure premium toastr options
        toastr.options = {
            timeOut: 8000,
            extendedTimeOut: 15000,
            progressBar: true,
            closeButton: false, // Cleaner without close button
            positionClass: "toast-top-right",
            preventDuplicates: false,
            showDuration: "300",
            hideDuration: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut",
            onShown: function () {
                // Style specific tweaks if needed
            },
            onclick: () => {
                const notificationId = data.id || data.notification_id;
                const redirectUrl = data.url || notification.url;

                if (notificationId && window.notificationManager) {
                    window.notificationManager.markAsRead(notificationId).then(() => {
                        if (redirectUrl) window.location.href = redirectUrl;
                    });
                } else if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            }
        };

        // Show toastr
        toastr[type](toastContent);

        // Inject custom CSS if not already present
        if (!document.getElementById('toastr-custom-styles')) {
            const style = document.createElement('style');
            style.id = 'toastr-custom-styles';
            style.innerHTML = `
                #toast-container > div {
                    opacity: 1;
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                    border-radius: 12px;
                    padding: 0;
                    background-image: none !important;
                    background-color: white !important;
                    width: 350px;
                    overflow: hidden;
                    border: 1px solid #f1f5f9;
                }
                .toastr-card {
                    display: flex;
                    align-items: center;
                    padding: 16px;
                    gap: 12px;
                }
                .toastr-icon {
                    font-size: 24px;
                    flex-shrink: 0;
                    width: 48px;
                    height: 48px;
                    background: #f8fafc;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 10px;
                }
                .toastr-body {
                    flex-grow: 1;
                    min-width: 0;
                }
                .toastr-title {
                    font-weight: 700;
                    font-size: 14px;
                    color: #1e293b;
                    margin-bottom: 2px;
                }
                .toastr-message {
                    font-size: 13px;
                    color: #64748b;
                    line-height: 1.4;
                }
                .toast-progress {
                    background-color: var(--primary-color, #2dd4bf);
                    opacity: 0.3;
                    height: 3px;
                }
                #toast-container > .toast-success .toast-progress { background-color: #10b981; }
                #toast-container > .toast-info .toast-progress { background-color: #3b82f6; }
                #toast-container > .toast-warning .toast-progress { background-color: #f59e0b; }
                #toast-container > .toast-error .toast-progress { background-color: #ef4444; }
                
                /* RTL support */
                [dir="rtl"] .toastr-card { flex-direction: row; }
                [dir="rtl"] #toast-container > div { text-align: right; }
            `;
            document.head.appendChild(style);
        }
    }

    /**
     * Play notification sound
     */
    playNotificationSound() {
        try {
            // Using a high-quality notification sound URL
            const soundUrl = 'https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3';
            const audio = new Audio(soundUrl);
            audio.play().catch(e => console.warn('Audio play failed:', e));
        } catch (error) {
            console.warn('Error playing sound:', error);
        }
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
            return response;
        } catch (error) {
            console.error('Error marking notification as read:', error);
            throw error;
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

