// Firebase Service Worker for background message handling
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

// 1. Extract config from registration URL (Synchronous)
const swUrl = new URL(self.location);
const firebaseConfig = {
    apiKey: swUrl.searchParams.get('apiKey'),
    authDomain: swUrl.searchParams.get('authDomain'),
    projectId: swUrl.searchParams.get('projectId'),
    storageBucket: swUrl.searchParams.get('storageBucket'),
    messagingSenderId: swUrl.searchParams.get('messagingSenderId'),
    appId: swUrl.searchParams.get('appId')
};

// 2. Initialize Firebase immediately (Synchronous evaluation)
if (firebaseConfig.apiKey) {
    try {
        firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();

        // Handle background messages
        // CRITICAL: This MUST be called on initial evaluation
        messaging.onBackgroundMessage((payload) => {
            console.log('[firebase-messaging-sw.js] Received background message ', payload);

            const data = payload.data || {};
            const isAr = self.navigator.language.startsWith('ar');

            let notificationTitle = data.title || 'New Notification';
            let notificationBody = data.body || data.message || '';

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
                tag: data.type || 'generic'
            };

            self.registration.showNotification(notificationTitle, notificationOptions);
        });

        console.log('[firebase-messaging-sw.js] Messaging initialized synchronously');
    } catch (error) {
        console.error('[firebase-messaging-sw.js] Error during sync init:', error);
    }
}

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const notificationData = event.notification.data || {};
    const url = notificationData.url || (notificationData.data ? notificationData.data.url : null);

    if (url) {
        event.waitUntil(
            clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
                for (const client of clientList) {
                    if (client.url === url && 'focus' in client) {
                        return client.focus();
                    }
                }
                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
        );
    }
});

// Legacy support for dynamic updates (though sync init is primary now)
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'FIREBASE_CONFIG') {
        console.log('[firebase-messaging-sw.js] Received config update via message');
        // If it wasn't initialized (e.g. refresh), this might help, 
        // but push events still require the sync init above to work.
    }
});
