// Firebase Service Worker for background message handling
// This file is required for FCM to work properly

importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

// Listen for config from main page
let firebaseConfig = null;
let messagingInitialized = false;

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'FIREBASE_CONFIG') {
        firebaseConfig = event.data.config;
        initializeFirebase();
    }
});

function initializeFirebase() {
    if (!firebaseConfig) {
        // Wait for config from main page
        return;
    }

    try {
        // Check if app already exists
        let app;
        try {
            app = firebase.app();
        } catch (e) {
            // App doesn't exist, initialize it
            app = firebase.initializeApp(firebaseConfig);
        }

        setupMessaging();
    } catch (error) {
        console.error('[firebase-messaging-sw.js] Error initializing:', error);
    }
}

function setupMessaging() {
    if (messagingInitialized) {
        return;
    }

    try {
        const messaging = firebase.messaging();
        messagingInitialized = true;

        // Handle background messages
        messaging.onBackgroundMessage((payload) => {
            console.log('[firebase-messaging-sw.js] Received background message ', payload);

            const data = payload.data || {};
            // Determine language (heuristic: if browser is likely Arabic)
            const isAr = self.navigator.language.startsWith('ar');

            // Fallbacks from generic data keys
            let notificationTitle = data.title || 'New Notification';
            let notificationBody = data.body || data.message || '';

            // Prioritize translated versions if available in data
            if (isAr && data.title_ar) {
                notificationTitle = data.title_ar;
                notificationBody = data.message_ar || notificationBody;
            } else if (!isAr && data.title_en) {
                notificationTitle = data.title_en;
                notificationBody = data.message_en || notificationBody;
            }

            const notificationOptions = {
                body: notificationBody,
                icon: data.icon || '/images/mo-logo.png',
                badge: '/images/mo-logo.png',
                data: data,
                vibrate: [200, 100, 200],
                tag: data.type || 'generic' // Prevent duplicate alerts for same event
            };

            self.registration.showNotification(notificationTitle, notificationOptions);
        });

        console.log('[firebase-messaging-sw.js] Messaging initialized successfully');
    } catch (error) {
        console.error('[firebase-messaging-sw.js] Error setting up messaging:', error);
    }
}

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    // Check various locations for the URL (FCM sometimes nests it)
    const notificationData = event.notification.data || {};
    const url = notificationData.url || (notificationData.data ? notificationData.data.url : null);

    console.log('[SW] Notification Clicked. URL found:', url, 'Full Data:', notificationData);

    if (url) {
        event.waitUntil(
            clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
                // 1. Try to find an existing window already at this URL and focus it
                for (const client of clientList) {
                    if (client.url === url && 'focus' in client) {
                        return client.focus();
                    }
                }

                // 2. If no window is at this URL, but we have a window open, 
                // we could navigate it, but usually opening a new one is safer
                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
        );
    }
});

// Try to initialize if Firebase is available (will wait for config)
if (typeof firebase !== 'undefined') {
    // Don't initialize yet, wait for config from main page
    console.log('[firebase-messaging-sw.js] Waiting for Firebase config from main page...');
}
