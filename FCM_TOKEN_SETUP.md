# FCM Token Management Setup Guide

## Overview

The FCM token management system automatically handles Firebase Cloud Messaging token registration and updates for all user types (Admin, Laboratory, Nurse, and Client) across all dashboards.

## Features

- ✅ Automatic token registration on page load
- ✅ Token refresh on login
- ✅ Periodic token validation (every 30 minutes)
- ✅ Support for both web and mobile platforms
- ✅ Automatic token removal on logout
- ✅ Token refresh on browser token change
- ✅ Works for all user types (Admin, Lab, Nurse, Client)

## Setup Instructions

### 1. Install Firebase Admin SDK

```bash
composer require kreait/firebase-php
```

### 2. Firebase Configuration

Add the following to your `.env` file:

```env
# Firebase Cloud Messaging (FCM) Configuration
# Path to service account JSON file (for sending notifications)
FCM_SERVICE_ACCOUNT_PATH=storage/app/firebase-service-account.json
FCM_PROJECT_ID=your-project-id

# Firebase Web App Configuration (for browser push notifications)
FCM_API_KEY=your_firebase_api_key
FCM_AUTH_DOMAIN=your-project.firebaseapp.com
FCM_STORAGE_BUCKET=your-project.appspot.com
FCM_MESSAGING_SENDER_ID=your_messaging_sender_id
FCM_APP_ID=your_firebase_app_id
FCM_VAPID_KEY=your_vapid_key
```

### 3. Get Firebase Credentials

#### Service Account (for sending notifications):

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project
3. Click on the gear icon ⚙️ next to "Project Overview"
4. Select **Project Settings**
5. Go to the **Service Accounts** tab
6. Click **Generate New Private Key**
7. Save the downloaded JSON file to `storage/app/firebase-service-account.json`
8. **IMPORTANT**: Add this file to `.gitignore`

#### Web App Configuration (for client-side):

1. In Firebase Console, go to Project Settings > General
2. Scroll down to "Your apps" section
3. Click on the Web app icon (</>) to get web app credentials
4. Copy the config values to your `.env` file
5. For VAPID key: Go to Project Settings > Cloud Messaging > Web Push certificates
6. Generate a key pair if you don't have one and copy the key

### 3. How It Works

#### Automatic Token Registration

1. **On Page Load**: When a user visits any dashboard, the FCM token manager:
   - Requests notification permission
   - Gets the FCM token from Firebase
   - Sends the token to the server
   - Stores it in the database (web or mobile based on platform)

2. **On Login**: After successful login:
   - A session flag is set
   - The token manager detects the flag
   - Re-initializes and updates the token
   - Ensures the token is current

3. **Token Refresh**: 
   - Automatically refreshes when Firebase detects token change
   - Periodic validation every 30 minutes
   - Compares stored token with current token
   - Updates server if token changed

4. **On Logout**:
   - Token is removed from the database
   - Local storage is cleared

#### Platform Detection

- **Web**: Detected when running in a regular browser
- **Mobile**: Detected when running in a mobile app or PWA

## API Endpoints

### Web Routes (for dashboards)

- `POST /fcm-token` - Update FCM token
  - Body: `{ "token": "fcm_token_here", "platform": "web|mobile" }`
  
- `DELETE /fcm-token` - Remove FCM token
  - Body: `{ "platform": "web|mobile" }`

### API Routes (for mobile apps)

- `POST /api/fcm-token` - Update FCM token (requires Sanctum auth)
  - Body: `{ "token": "fcm_token_here", "platform": "web|mobile" }`
  
- `DELETE /api/fcm-token` - Remove FCM token (requires Sanctum auth)
  - Body: `{ "platform": "web|mobile" }`

### Login with FCM Token (API)

When logging in via API, you can include FCM token:

```json
POST /api/login
{
  "phone_number": "1234567890",
  "password": "password",
  "fcm_token": "fcm_token_here",
  "platform": "mobile"
}
```

## Best Practices Implemented

1. **Token Validation**: 
   - Compares stored token with current token
   - Updates server only if token changed
   - Prevents unnecessary database writes

2. **Error Handling**:
   - Graceful fallback if Firebase is not loaded
   - Retry mechanism with delays
   - Console logging for debugging

3. **Security**:
   - Tokens only updated for authenticated users
   - CSRF protection for web routes
   - Sanctum authentication for API routes

4. **Performance**:
   - Tokens stored in localStorage for quick access
   - Periodic refresh to ensure validity
   - Non-blocking initialization

5. **User Experience**:
   - Automatic permission request
   - Background message handling
   - In-app notification display

## Testing

1. **Check Token Registration**:
   - Open browser console
   - Look for "FCM token updated successfully" message
   - Check database: `users.fcm_token_web` or `clients.fcm_token_web`

2. **Test Token Refresh**:
   - Login to dashboard
   - Check console for token refresh messages
   - Verify token in database is updated

3. **Test Logout**:
   - Click logout
   - Check console for "FCM token removed successfully"
   - Verify token is null in database

## Troubleshooting

### Token not updating
- Check Firebase configuration in `.env`
- Verify Firebase SDK is loaded (check browser console)
- Check network tab for API calls to `/fcm-token`
- Verify user is authenticated

### Permission denied
- User must grant notification permission
- Check browser notification settings
- Some browsers require HTTPS for notifications

### Service Worker issues
- Service worker is optional for foreground messages
- Background messages require service worker
- Check browser console for service worker errors

## Files Modified/Created

- `app/Http/Controllers/FcmTokenController.php` - Web token management
- `app/Http/Controllers/Api/FcmTokenController.php` - API token management
- `app/Http/Controllers/Auth/LoginController.php` - Login token refresh trigger
- `app/Http/Controllers/Api/AuthController.php` - API login with token support
- `public/js/fcm-token-manager.js` - Client-side token management
- `config/services.php` - FCM configuration
- Dashboard layouts - Firebase SDK integration
- Routes - Token management endpoints

