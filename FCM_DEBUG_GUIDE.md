# FCM Token Debugging Guide

If `fcm_token_web` is null after login, follow these steps to debug:

## Step 1: Check Browser Console

Open your browser's developer console (F12) and look for FCM-related messages:

1. **Firebase SDK Loading:**
   - Should see: `FCM: Firebase SDK loaded successfully`
   - If you see: `FCM: Firebase API key not configured` → Check `.env` file

2. **FCM Initialization:**
   - Should see: `FCM: Starting initialization...`
   - Should see: `FCM: Initializing Firebase Messaging...`
   - Should see: `FCM: Requesting notification permission...`

3. **Permission Status:**
   - Should see: `FCM: Notification permission granted`
   - If denied: User needs to allow notifications in browser settings

4. **Token Generation:**
   - Should see: `FCM: Sending token to server...`
   - Should see: `FCM: Token updated successfully on server`

## Step 2: Check Environment Variables

Make sure these are set in your `.env` file:

```env
FCM_API_KEY=your_api_key
FCM_AUTH_DOMAIN=your-project.firebaseapp.com
FCM_PROJECT_ID=your-project-id
FCM_STORAGE_BUCKET=your-project.appspot.com
FCM_MESSAGING_SENDER_ID=your_sender_id
FCM_APP_ID=your_app_id
FCM_VAPID_KEY=your_vapid_key
```

Then run:
```bash
php artisan config:clear
php artisan cache:clear
```

## Step 3: Check Network Tab

1. Open browser DevTools → Network tab
2. Filter by "fcm-token"
3. After page load, you should see a POST request to `/fcm-token`
4. Check the response:
   - Status should be 200
   - Response should contain `{"success": true, ...}`

## Step 4: Check Authentication

The `/fcm-token` endpoint requires authentication. Make sure:
- You are logged in
- Session is valid
- CSRF token is present

## Step 5: Check Notification Permission

1. Click the lock/info icon in the browser address bar
2. Check "Notifications" permission
3. Should be set to "Allow"
4. If blocked, click and change to "Allow"

## Step 6: Manual Test

Open browser console and run:

```javascript
// Check if Firebase is loaded
console.log('Firebase:', typeof firebase !== 'undefined' ? 'Loaded' : 'Not loaded');
console.log('FCM Manager:', window.fcmManager ? 'Initialized' : 'Not initialized');

// Check token in localStorage
console.log('Stored token:', localStorage.getItem('fcm_token'));

// Try to get token manually
if (window.fcmManager) {
    window.fcmManager.getToken().then(token => {
        console.log('Current token:', token);
    });
}
```

## Common Issues

### Issue 1: "Firebase API key not configured"
**Solution:** Add `FCM_API_KEY` to `.env` and clear config cache

### Issue 2: "Notification permission denied"
**Solution:** 
- Click browser address bar icon
- Allow notifications
- Refresh page

### Issue 3: "Unauthorized" error when sending token
**Solution:**
- Make sure you're logged in
- Check session is valid
- Verify CSRF token is present

### Issue 4: Token generated but not saved
**Solution:**
- Check browser console for errors
- Check network tab for failed requests
- Verify route `/fcm-token` exists and is accessible

### Issue 5: Firebase SDK not loading
**Solution:**
- Check internet connection
- Check browser console for CORS errors
- Verify Firebase config values are correct

## Testing the Token Endpoint

You can test the endpoint directly:

```bash
# Get your session cookie and CSRF token from browser
curl -X POST http://your-domain/fcm-token \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  -H "Cookie: your-session-cookie" \
  -d '{"token": "test-token", "platform": "web"}'
```

## Verify Token is Saved

Check database directly:

```sql
SELECT id, name, email, fcm_token_web, fcm_token_mobile 
FROM users 
WHERE id = YOUR_USER_ID;
```

Or use Laravel Tinker:

```bash
php artisan tinker
>>> $user = App\Models\User::find(YOUR_USER_ID);
>>> $user->fcm_token_web;
```

