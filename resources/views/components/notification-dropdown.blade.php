@php
    $user = Auth::guard('client')->check() ? Auth::guard('client')->user() : Auth::user();
    $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
@endphp

<div class="relative" x-data="{
        open: false, 
        notifications: [], 
        unreadCount: {{ $unreadCount }}, 
        loading: false,
        async loadNotifications() {
            this.loading = true;
            try {
                // X-Requested-With keeps this poll out of the session's previous URL,
                // so redirect()->back() never lands the user on raw JSON.
                const response = await fetch('/notifications/unread', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await response.json();
                this.notifications = data.notifications || [];
                this.unreadCount = data.unread_count || 0;
            } catch (error) {
                console.error('Error loading notifications:', error);
            } finally {
                this.loading = false;
            }
        }
    }" 
     x-init="
        loadNotifications();
        // Refresh every 30 seconds
        setInterval(() => loadNotifications(), 30000);
        
        // Listen for FCM messages to update notifications
        window.addEventListener('fcm-message', () => loadNotifications());
        
        // Listen for notifications updated event
        window.addEventListener('notifications-updated', (event) => {
            unreadCount = event.detail.unreadCount;
            if (open) {
                notifications = event.detail.notifications;
            }
        });
     ">
    <!-- Bell Icon Button -->
    <button @click="open = !open; if(open) loadNotifications()" 
            class="relative p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <!-- Unread Badge -->
        <span x-show="unreadCount > 0" 
              x-text="unreadCount > 99 ? '99+' : unreadCount"
              class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full"
              style="display: none;"></span>
    </button>

    <!-- Dropdown Panel -->
    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="absolute ltr:right-0 rtl:left-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-slate-200 z-50 max-h-96 overflow-hidden flex flex-col ltr:origin-top-right rtl:origin-top-left"
         style="display: none;">
        
        <!-- Header -->
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900">
                {{ app()->getLocale() === 'ar' ? 'الإشعارات' : 'Notifications' }}
            </h3>
            <button @click="
                fetch('/notifications/read-all', {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}})
                    .then(() => { unreadCount = 0; notifications.forEach(n => n.read = true); })
            " 
            class="text-xs text-primary hover:text-primary/80 font-medium">
                {{ app()->getLocale() === 'ar' ? 'تحديد الكل كمقروء' : 'Mark all as read' }}
            </button>
        </div>

        <!-- Notifications List -->
        <div class="overflow-y-auto flex-1">
            <template x-if="loading">
                <div class="p-4 text-center text-slate-500">
                    <svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </template>
            
            <template x-if="!loading && notifications.length === 0">
                <div class="p-4 text-center text-slate-500 text-sm">
                    {{ app()->getLocale() === 'ar' ? 'لا توجد إشعارات' : 'No notifications' }}
                </div>
            </template>

            <template x-for="notification in notifications" :key="notification.id">
                <div class="px-4 py-3 border-b border-slate-100 hover:bg-slate-50 cursor-pointer transition-colors"
                     :class="{ 'bg-blue-50': !notification.read }"
                     @click="
                        if (!notification.read) {
                            fetch(`/notifications/${notification.id}/read`, {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}})
                                .then(() => { 
                                    notification.read = true; 
                                    unreadCount = Math.max(0, unreadCount - 1);
                                    if(notification.url) window.location.href = notification.url;
                                });
                        } else if(notification.url) {
                            window.location.href = notification.url;
                        }
                     ">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 mt-0.5">
                            <div class="w-2 h-2 rounded-full bg-primary" x-show="!notification.read" style="display: none;"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900" x-text="notification.title"></p>
                            <p class="text-xs text-slate-600 mt-1" x-text="notification.message"></p>
                            <p class="text-xs text-slate-400 mt-1" x-text="notification.created_at"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <template x-if="notifications.length > 0">
            <div class="px-4 py-2 border-t border-slate-200 text-center">
                <a href="{{ route('notifications.index') }}" class="text-xs text-primary hover:text-primary/80 font-medium">
                    {{ app()->getLocale() === 'ar' ? 'عرض الكل' : 'View all' }}
                </a>
            </div>
        </template>
    </div>
</div>

