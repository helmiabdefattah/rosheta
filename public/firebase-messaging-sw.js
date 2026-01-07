// Firebase Service Worker for background message handling
// This file is required for FCM to work properly

importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

// Listen for config from main page
let firebaseConfig = null;
let messagingInitialized = false;

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
            
            const notificationTitle = payload.notification?.title || 'New Notification';
            const notificationOptions = {
                body: payload.notification?.body || '',
                icon: payload.notification?.icon || '/images/logo.png',
                badge: '/images/logo.png',
                data: payload.data || {}
            };

            self.registration.showNotification(notificationTitle, notificationOptions);
        });
        
        console.log('[firebase-messaging-sw.js] Messaging initialized successfully');
    } catch (error) {
        console.error('[firebase-messaging-sw.js] Error setting up messaging:', error);
    }
}

// Try to initialize if Firebase is available (will wait for config)
if (typeof firebase !== 'undefined') {
    // Don't initialize yet, wait for config from main page
    console.log('[firebase-messaging-sw.js] Waiting for Firebase config from main page...');
}
