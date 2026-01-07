# Firebase Cloud Messaging (FCM) Service Account Setup

## Overview

This application uses **Firebase Admin SDK** with **service account credentials** to send push notifications via FCM HTTP v1 API. The legacy FCM API using server keys has been deprecated and is no longer available.

## Prerequisites

1. Install the Firebase Admin SDK PHP package:
```bash
composer require kreait/firebase-php
```

2. A Firebase project with Cloud Messaging enabled

## Setup Instructions

### Step 1: Create a Service Account

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project
3. Click on the gear icon ⚙️ next to "Project Overview"
4. Select **Project Settings**
5. Go to the **Service Accounts** tab
6. Click **Generate New Private Key**
7. A JSON file will be downloaded - this is your service account credentials file

### Step 2: Store the Service Account File

1. Place the downloaded JSON file in your Laravel project's `storage/app/` directory
2. Name it `firebase-service-account.json` (or use a different name and update the config)
3. **IMPORTANT**: Add this file to `.gitignore` to prevent committing sensitive credentials:
   ```
   /storage/app/firebase-service-account.json
   ```

### Step 3: Configure Environment Variables

Add the following to your `.env` file:

```env
# Firebase Cloud Messaging (FCM) Configuration
# Path to service account JSON file (relative to project root or absolute path)
FCM_SERVICE_ACCOUNT_PATH=storage/app/firebase-service-account.json

# Firebase Project ID (optional, but recommended)
FCM_PROJECT_ID=your-project-id

# Web App Configuration (for client-side Firebase SDK)
FCM_API_KEY=your_firebase_api_key
FCM_AUTH_DOMAIN=your-project.firebaseapp.com
FCM_STORAGE_BUCKET=your-project.appspot.com
FCM_MESSAGING_SENDER_ID=your_messaging_sender_id
FCM_APP_ID=your_firebase_app_id
FCM_VAPID_KEY=your_vapid_key
```

### Step 4: Update .env.example

Add the same variables to `.env.example` (without actual values):

```env
# Firebase Cloud Messaging (FCM) Configuration
FCM_SERVICE_ACCOUNT_PATH=storage/app/firebase-service-account.json
FCM_PROJECT_ID=
FCM_API_KEY=
FCM_AUTH_DOMAIN=
FCM_STORAGE_BUCKET=
FCM_MESSAGING_SENDER_ID=
FCM_APP_ID=
FCM_VAPID_KEY=
```

## Service Account File Location

By default, the service account file is expected at:
```
storage/app/firebase-service-account.json
```

You can change this path in your `.env` file:
```env
FCM_SERVICE_ACCOUNT_PATH=/path/to/your/service-account.json
```

Or use an absolute path:
```env
FCM_SERVICE_ACCOUNT_PATH=/var/www/html/firebase-credentials.json
```

## Security Best Practices

1. **Never commit the service account JSON file to version control**
   - Add it to `.gitignore`
   - Use environment variables for the path

2. **Restrict file permissions** (Linux/Mac):
   ```bash
   chmod 600 storage/app/firebase-service-account.json
   ```

3. **Use environment-specific files**:
   - Development: `firebase-service-account-dev.json`
   - Production: `firebase-service-account-prod.json`
   - Update `.env` accordingly

4. **Rotate credentials periodically**:
   - Generate new service account keys
   - Update the file
   - Revoke old keys in Firebase Console

## Verification

To verify the setup is working:

1. Check that the service account file exists:
   ```bash
   ls -la storage/app/firebase-service-account.json
   ```

2. Test sending a notification (you can create a test route):
   ```php
   use App\Models\User;
   use App\Notifications\ExampleNotification;
   
   $user = User::first();
   if ($user && $user->fcm_token_web) {
       $user->notify(new ExampleNotification());
   }
   ```

3. Check Laravel logs for any errors:
   ```bash
   tail -f storage/logs/laravel.log
   ```

## Troubleshooting

### Error: "Firebase service account file not found"
- Verify the file path in `.env` is correct
- Check file permissions
- Ensure the file exists at the specified location

### Error: "Failed to initialize Firebase"
- Verify the JSON file is valid
- Check that the service account has the necessary permissions
- Ensure the Firebase project is active

### Error: "FCM token not found"
- The token may be invalid or expired
- User may have uninstalled the app
- Token needs to be refreshed on the client side

### Error: "Permission denied"
- Check that the service account has "Firebase Cloud Messaging API Admin" role
- Verify the service account is enabled in Firebase Console

## Migration from Legacy API

If you were using the legacy FCM API with server keys:

1. Remove `FCM_SERVER_KEY` from `.env` (no longer needed)
2. Remove `FCM_USE_LEGACY_API` from `.env` (no longer used)
3. Follow the setup instructions above to use service account
4. The code has been updated to use Firebase Admin SDK automatically

## Additional Resources

- [Firebase Admin SDK for PHP Documentation](https://firebase-php.readthedocs.io/)
- [FCM HTTP v1 API Documentation](https://firebase.google.com/docs/cloud-messaging/migrate-v1)
- [Service Account Best Practices](https://cloud.google.com/iam/docs/best-practices-service-accounts)

