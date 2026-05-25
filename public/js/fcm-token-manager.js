/**
 * FCM Token Manager
 * Handles Firebase Cloud Messaging token registration and updates
 */

class FcmTokenManager {
    constructor() {
        this.messaging = null;
        this.currentToken = null;
        this.platform = this.detectPlatform();
        this.init();
    }

    /**
     * Platform for web push tokens registered from JavaScript.
     * Always "web" — native Android/iOS FCM tokens are sent via POST /api/fcm-token with platform=mobile.
     */
    detectPlatform() {
        return 'web';
    }

    /**
     * Initialize Firebase Messaging
     */
    async init() {
        // Flutter WebView sets this before page scripts; native FCM handles push — do not register web push here
        if (typeof window !== 'undefined' && window.MostashfaOnFlutterWebView === true) {
            console.log('FCM: Skipping web FCM inside Flutter WebView (native app handles notifications)');
            return;
        }

        // Check if Firebase is available
        if (typeof firebase === 'undefined') {
            console.warn('FCM: Firebase is not available. Make sure Firebase SDK is loaded and FCM_API_KEY is set in .env');
            return;
        }

        // Wait a bit for Firebase to fully initialize
        await new Promise(resolve => setTimeout(resolve, 100));

        // Check if messaging is available
        if (!firebase.messaging) {
            console.warn('FCM: Firebase Messaging is not available. Make sure firebase-messaging-compat.js is loaded.');
            return;
        }

        try {
            console.log('FCM: Initializing Firebase Messaging...');

            // Check if we're in a browser environment
            if (typeof window === 'undefined') {
                console.warn('FCM: Window object not available');
                return;
            }

            // Register service worker first (required for FCM)
            let serviceWorkerRegistration = null;
            if ('serviceWorker' in navigator) {
                try {
                    // Build registration URL with config for immediate SW initialization
                    let swUrl = '/firebase-messaging-sw.js?v=1.0.1';
                    if (window.firebaseConfig) {
                        const params = new URLSearchParams(window.firebaseConfig);
                        swUrl += '&' + params.toString();
                    }

                    // Try to register service worker with config in URL
                    const registration = await navigator.serviceWorker.register(swUrl, {
                        scope: '/'
                    }).catch(err => {
                        console.warn('FCM: Service worker registration failed:', err);
                        return null;
                    });

                    if (registration) {
                        // Wait for service worker to be ready
                        serviceWorkerRegistration = await navigator.serviceWorker.ready;
                        console.log('FCM: Service worker registered and ready');

                        // Send Firebase config to service worker
                        if (serviceWorkerRegistration.active && window.firebaseConfig) {
                            serviceWorkerRegistration.active.postMessage({
                                type: 'FIREBASE_CONFIG',
                                config: window.firebaseConfig
                            });
                            // Wait a bit for service worker to process the config
                            await new Promise(resolve => setTimeout(resolve, 200));
                        }
                    }
                } catch (swError) {
                    console.warn('FCM: Service worker error, continuing without it:', swError);
                }
            }

            // Wait a bit more to ensure everything is ready
            await new Promise(resolve => setTimeout(resolve, 200));

            // Initialize Firebase Messaging (using compat version)
            // This requires the service worker to be ready
            try {
                this.messaging = firebase.messaging();

                // Verify messaging instance is valid
                if (!this.messaging || typeof this.messaging.getToken !== 'function') {
                    throw new Error('Messaging instance is not valid');
                }

                console.log('FCM: Messaging instance created successfully');
            } catch (msgError) {
                console.error('FCM: Failed to get messaging instance:', msgError);
                // If messaging fails, we can't use FCM but the app should continue
                return;
            }

            // Request notification permission
            console.log('FCM: Requesting notification permission...');
            const permission = await Notification.requestPermission();

            if (permission === 'granted') {
                console.log('FCM: Notification permission granted');
                await this.getToken();

                // Only setup handlers if messaging is valid
                if (this.messaging) {
                    this.setupTokenRefresh();
                    this.setupMessageHandler();
                }
            } else if (permission === 'denied') {
                console.warn('FCM: Notification permission denied by user');
            } else {
                console.warn('FCM: Notification permission default (not granted or denied)');
            }
        } catch (error) {
            console.error('FCM: Error initializing:', error);
            // Don't throw, just log the error so the page can continue
        }
    }

    /**
     * Get FCM token
     */
    async getToken() {
        try {
            if (!this.messaging) {
                console.warn('Messaging not initialized');
                return null;
            }

            // Register service worker for background messages (if not already registered)
            let serviceWorkerRegistration = null;
            if ('serviceWorker' in navigator) {
                try {
                    serviceWorkerRegistration = await navigator.serviceWorker.ready;
                } catch (error) {
                    console.warn('Service Worker not ready:', error);
                }
            }

            this.currentToken = await this.messaging.getToken({
                vapidKey: window.FCM_VAPID_KEY || null,
                serviceWorkerRegistration: serviceWorkerRegistration
            });

            if (this.currentToken) {
                await this.sendTokenToServer(this.currentToken);
            } else {
                console.warn('No FCM token available');
            }

            return this.currentToken;
        } catch (error) {
            console.error('Error getting FCM token:', error);
            return null;
        }
    }

    /**
     * Send token to server
     */
    async sendTokenToServer(token) {
        try {
            console.log('FCM: Sending token to server...', { platform: this.platform, tokenLength: token.length });

            const response = await fetch('/fcm-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    token: token,
                    platform: this.platform
                })
            });

            const data = await response.json();

            if (data.success) {
                console.log('FCM: Token updated successfully on server');
                // Store token in localStorage for reference
                localStorage.setItem('fcm_token', token);
                localStorage.setItem('fcm_platform', this.platform);
            } else {
                console.error('FCM: Failed to update token on server:', data.message, data);
            }
        } catch (error) {
            console.error('FCM: Error sending token to server:', error);
        }
    }

    /**
     * Setup token refresh handler
     */
    setupTokenRefresh() {
        if (!this.messaging) return;

        try {
            // Check if onTokenRefresh method exists (compat version)
            if (typeof this.messaging.onTokenRefresh === 'function') {
                this.messaging.onTokenRefresh(async () => {
                    console.log('FCM token refreshed');
                    await this.getToken();
                });
            } else {
                // For compat version, we might need to use a different approach
                // Listen for token refresh events manually
                console.log('FCM: onTokenRefresh not available, will handle token refresh manually');
            }
        } catch (error) {
            console.error('FCM: Error setting up token refresh:', error);
        }
    }

    /**
     * Setup message handler
     */
    setupMessageHandler() {
        if (!this.messaging) return;

        try {
            // Check if onMessage method exists
            if (typeof this.messaging.onMessage === 'function') {
                this.messaging.onMessage((payload) => {
                    console.log('FCM: Message received:', payload);

                    // Show browser notification
                    if (Notification.permission === 'granted') {
                        const data = payload.data || {};
                        const isAr = (document.documentElement.lang || '').startsWith('ar') || document.documentElement.dir === 'rtl';

                        // Pick localized title/body from data if available
                        let title = isAr ? (data.title_ar || data.title) : (data.title_en || data.title);
                        let body = isAr ? (data.message_ar || data.message || data.body) : (data.message_en || data.message || data.body);

                        // Fallback to legacy notification object
                        if (!title && payload.notification) title = payload.notification.title;
                        if (!body && payload.notification) body = payload.notification.body;

                        const notificationOptions = {
                            body: body || '',
                            icon: data.icon || '/images/mo-logo.png',
                            badge: '/images/mo-logo.png',
                            data: data
                        };

                        new Notification(title || 'New Notification', notificationOptions);
                    }

                    // Trigger custom event for handling in the app
                    window.dispatchEvent(new CustomEvent('fcm-message', { detail: payload }));
                });
            } else {
                console.warn('FCM: onMessage method not available');
            }
        } catch (error) {
            console.error('FCM: Error setting up message handler:', error);
        }
    }

    /**
     * Remove token (on logout)
     */
    async removeToken() {
        try {
            const response = await fetch('/fcm-token', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    platform: this.platform
                })
            });

            const data = await response.json();

            if (data.success) {
                console.log('FCM token removed successfully');
                localStorage.removeItem('fcm_token');
                localStorage.removeItem('fcm_platform');
            }
        } catch (error) {
            console.error('Error removing FCM token:', error);
        }
    }

    /**
     * Re-initialize (useful after login)
     */
    async reinitialize() {
        // Check if we already have a token
        const storedToken = localStorage.getItem('fcm_token');
        const storedPlatform = localStorage.getItem('fcm_platform');

        // If token exists and platform matches, verify it's still valid
        if (storedToken && storedPlatform === this.platform) {
            // Try to get current token and compare
            const currentToken = await this.getToken();
            if (currentToken !== storedToken) {
                // Token changed, update server
                await this.sendTokenToServer(currentToken);
            }
        } else {
            // No token or platform changed, get new token
            await this.getToken();
        }
    }
}

// Initialize on page load
let fcmManager = null;

function initializeFcm() {
    if (typeof window !== 'undefined' && window.MostashfaOnFlutterWebView === true) {
        return false;
    }
    // Only initialize if Firebase is loaded
    if (typeof firebase !== 'undefined' && firebase.messaging) {
        try {
            fcmManager = new FcmTokenManager();
            console.log('FCM Token Manager initialized successfully');
            return true;
        } catch (error) {
            console.error('Error initializing FCM Token Manager:', error);
            return false;
        }
    }
    return false;
}

// Wait for Firebase to be available
function waitForFirebase(maxAttempts = 15, attempt = 0) {
    if (attempt >= maxAttempts) {
        console.error('FCM: Firebase SDK failed to load after multiple attempts. Check Firebase configuration in .env');
        return;
    }

    // Check if Firebase is loaded and messaging is available
    if (typeof firebase !== 'undefined' && typeof firebase.messaging === 'function') {
        // Wait a bit more to ensure Firebase is fully initialized
        setTimeout(() => {
            if (!fcmManager) {
                initializeFcm();
            }
        }, 300);
    } else {
        setTimeout(() => waitForFirebase(maxAttempts, attempt + 1), 500);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    console.log('FCM: Starting initialization...');

    // Start waiting for Firebase
    waitForFirebase();

    // Check if we need to refresh token after login
    const needsRefresh = sessionStorage.getItem('fcm_token_refresh');
    if (needsRefresh) {
        console.log('FCM: Token refresh flag detected');
        setTimeout(() => {
            if (fcmManager) {
                fcmManager.reinitialize();
            } else if (typeof firebase !== 'undefined' && firebase.messaging) {
                fcmManager = new FcmTokenManager();
            }
            sessionStorage.removeItem('fcm_token_refresh');
        }, 2000);
    }
});

// Re-initialize after login (triggered by login success or page load)
window.addEventListener('fcm-reinitialize', function () {
    if (fcmManager) {
        fcmManager.reinitialize();
    } else if (typeof firebase !== 'undefined' && firebase.messaging) {
        fcmManager = new FcmTokenManager();
    }
});

// Handle logout - remove token
document.addEventListener('click', function (e) {
    // Check if clicking logout button/link
    const logoutElement = e.target.closest('form[action*="logout"], a[href*="logout"], button[onclick*="logout"]');
    if (logoutElement && fcmManager) {
        fcmManager.removeToken();
    }
});

// Periodic token refresh (every 30 minutes to ensure token is always valid)
setInterval(() => {
    if (fcmManager && typeof firebase !== 'undefined' && firebase.messaging) {
        fcmManager.reinitialize();
    }
}, 30 * 60 * 1000); // 30 minutes

// Export for use in other scripts
window.FcmTokenManager = FcmTokenManager;
window.fcmManager = fcmManager;

