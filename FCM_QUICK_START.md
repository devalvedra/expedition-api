# FCM Quick Start Guide

## Prerequisites

- Laravel application running
- Firebase project created
- Mobile app with Firebase SDK integrated

## Setup Steps (5 Minutes)

### 1. Download Firebase Credentials

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project
3. Click ⚙️ > Project Settings > Service Accounts
4. Click "Generate New Private Key"
5. Save as `firebase-credentials.json` in `storage/app/`

### 2. Update Environment

Add to `.env`:

```env
FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json
FIREBASE_PROJECT_ID=your-project-id
```

### 3. Run Migration

```bash
php artisan migrate
```

This adds `fcm_token` and `sales_id` columns to users table.

### 4. Test the Installation

```bash
php fcm-test.php
```

## Quick Test (2 Minutes)

### Step 1: Get your FCM token from mobile app

In your mobile app, get the device FCM token and copy it.

### Step 2: Update user with FCM token

```bash
curl -X POST http://localhost:8000/api/fcm-test/update-token \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "fcm_token": "YOUR_FCM_TOKEN_HERE",
    "sales_id": "SALES123"
  }'
```

### Step 3: Test notification

```bash
curl -X POST http://localhost:8000/api/fcm-test/simulate-sell \
  -H "Content-Type: application/json" \
  -d '{
    "sales_id": "SALES123",
    "nojual": "TEST001",
    "grandtotal": 100000
  }'
```

✅ You should receive a notification on your device!

### Step 4: Test with real sell creation

```bash
curl -X POST http://localhost:8000/api/sell \
  -H "Content-Type: application/json" \
  -d '{
    "nojual": "SELL001",
    "sales_id": "SALES123",
    "tgl": "2026-02-22",
    "grandtotal": 150000,
    "subtotal": 140000
  }'
```

✅ Notification is sent automatically via observer!

## Mobile App Integration

### Get FCM Token (React Native - Firebase)

```javascript
import messaging from "@react-native-firebase/messaging";

const getFCMToken = async () => {
    const token = await messaging().getToken();
    // Send to API
    await updateUserToken(userId, token, salesId);
};
```

### Get FCM Token (Flutter)

```dart
import 'package:firebase_messaging/firebase_messaging.dart';

Future<void> getFCMToken() async {
  String? token = await FirebaseMessaging.instance.getToken();
  // Send to API
  await updateUserToken(userId, token, salesId);
}
```

### Handle Notifications (React Native)

```javascript
messaging().onMessage(async (remoteMessage) => {
    const { type, nojual, grandtotal } = remoteMessage.data;
    if (type === "new_sell") {
        showAlert(`New order #${nojual} - Rp ${grandtotal}`);
    }
});
```

## Troubleshooting

### No notification received?

1. Check logs: `tail -f storage/logs/laravel.log`
2. Verify FCM token is set: `SELECT fcm_token FROM users WHERE id = 1;`
3. Verify sales_id matches: User's sales_id must match Sell's sales_id
4. Check Firebase credentials exist: `ls storage/app/firebase-credentials.json`

### Invalid token error?

- FCM tokens expire - update token periodically from mobile app
- Token becomes invalid after app reinstall

## API Endpoints

| Endpoint                           | Purpose                                  |
| ---------------------------------- | ---------------------------------------- |
| `POST /api/fcm-test/update-token`  | Update user's FCM token                  |
| `POST /api/fcm-test/send`          | Send test notification to token          |
| `POST /api/fcm-test/simulate-sell` | Simulate sell creation (test)            |
| `POST /api/sell`                   | Create sell (triggers auto notification) |

## Next Steps

1. **Production**: Add authentication to FCM test endpoints
2. **Performance**: Move notifications to queue
3. **Monitoring**: Set up alerts for failed notifications
4. **Token Management**: Implement periodic token cleanup

## Need Help?

- Read full documentation: `FCM_IMPLEMENTATION.md`
- Use Postman collection: Import `postman_collection_fcm.json`
- Check Laravel logs: `storage/logs/laravel.log`
- Test script: `php fcm-test.php`
