# Firebase Cloud Messaging (FCM) Implementation

This implementation adds FCM push notification support to the Eshia API, specifically for sending notifications when new sell records are created.

## Setup Instructions

### 1. Firebase Project Setup

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Create a new project or select an existing one
3. Navigate to Project Settings > Service Accounts
4. Click "Generate New Private Key" to download the service account JSON file
5. Rename the downloaded file to `firebase-credentials.json`
6. Place it in `storage/app/firebase-credentials.json`

### 2. Environment Configuration

Add the following to your `.env` file:

```env
FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json
FIREBASE_DATABASE_URL=https://your-project-id.firebaseio.com
FIREBASE_PROJECT_ID=your-project-id
```

### 3. Database Migration

Add the following columns to your `users` table:

```sql
ALTER TABLE users ADD COLUMN fcm_token VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN sales_id VARCHAR(20) NULL;
```

Or create a migration:

```bash
php artisan make:migration add_fcm_fields_to_users_table
```

## How It Works

### Automatic Notifications

When a new `Sell` record is created:

1. The `SellObserver` is triggered automatically
2. It finds all users with matching `sales_id` and an `fcm_token`
3. Sends push notifications to these users
4. Notification includes: sell number, sales ID, grand total, and date

### Notification Payload

```json
{
    "title": "New Sell Order",
    "body": "New sell order #SELL001 has been created",
    "data": {
        "type": "new_sell",
        "nojual": "SELL001",
        "sales_id": "SALES123",
        "grandtotal": "150000",
        "tgl": "2026-02-22"
    }
}
```

## API Endpoints

### FCM Test Endpoints (Development)

#### 1. Send Test Notification to Token

```http
POST /api/fcm-test/send
Content-Type: application/json

{
  "token": "device_fcm_token_here",
  "title": "Test Notification",
  "body": "This is a test message",
  "data": {
    "key": "value"
  }
}
```

#### 2. Send to Users by Sales ID

```http
POST /api/fcm-test/send-to-sales
Content-Type: application/json

{
  "sales_id": "SALES123",
  "title": "Sales Notification",
  "body": "Message for all users with this sales_id",
  "data": {}
}
```

#### 3. Send to Topic

```http
POST /api/fcm-test/send-to-topic
Content-Type: application/json

{
  "topic": "sales_notifications",
  "title": "Topic Message",
  "body": "Message to all subscribers",
  "data": {}
}
```

#### 4. Update User FCM Token

```http
POST /api/fcm-test/update-token
Content-Type: application/json

{
  "user_id": 1,
  "fcm_token": "new_device_token_here",
  "sales_id": "SALES123"
}
```

#### 5. Subscribe Tokens to Topic

```http
POST /api/fcm-test/subscribe-topic
Content-Type: application/json

{
  "tokens": ["token1", "token2"],
  "topic": "sales_notifications"
}
```

#### 6. Unsubscribe from Topic

```http
POST /api/fcm-test/unsubscribe-topic
Content-Type: application/json

{
  "tokens": ["token1", "token2"],
  "topic": "sales_notifications"
}
```

#### 7. Simulate New Sell (Test Notification)

```http
POST /api/fcm-test/simulate-sell
Content-Type: application/json

{
  "sales_id": "SALES123",
  "nojual": "TEST001",
  "grandtotal": 250000
}
```

This endpoint simulates a new sell creation without actually creating a database record. Perfect for testing notifications!

## Testing the Implementation

### Step 1: Setup a Test User

```http
POST /api/fcm-test/update-token
Content-Type: application/json

{
  "user_id": 1,
  "fcm_token": "your_device_fcm_token",
  "sales_id": "SALES123"
}
```

### Step 2: Test with Simulation

```http
POST /api/fcm-test/simulate-sell
Content-Type: application/json

{
  "sales_id": "SALES123",
  "nojual": "TEST001",
  "grandtotal": 150000
}
```

### Step 3: Test with Real Sell Creation

```http
POST /api/sell
Content-Type: application/json

{
  "nojual": "SELL001",
  "sales_id": "SALES123",
  "tgl": "2026-02-22",
  "grandtotal": 150000,
  "subtotal": 140000
}
```

## Mobile App Integration

### Android (Kotlin/Java)

1. Add Firebase SDK to your Android app
2. Get the FCM token:

```kotlin
FirebaseMessaging.getInstance().token.addOnCompleteListener { task ->
    if (task.isSuccessful) {
        val token = task.result
        // Send token to your API
        updateFcmToken(userId, token, salesId)
    }
}
```

3. Handle incoming notifications:

```kotlin
class MyFirebaseMessagingService : FirebaseMessagingService() {
    override fun onMessageReceived(message: RemoteMessage) {
        val type = message.data["type"]
        if (type == "new_sell") {
            val nojual = message.data["nojual"]
            val grandtotal = message.data["grandtotal"]
            // Show notification or update UI
        }
    }
}
```

### iOS (Swift)

1. Add Firebase SDK to your iOS app
2. Get the FCM token:

```swift
Messaging.messaging().token { token, error in
    if let token = token {
        // Send token to your API
        updateFcmToken(userId: userId, token: token, salesId: salesId)
    }
}
```

## Error Handling

All FCM operations are logged. Check `storage/logs/laravel.log` for:

- Successful notification sends
- Failed notification attempts
- Missing FCM tokens
- Invalid tokens

## Security Notes

1. **Never commit `firebase-credentials.json`** to version control
2. Add to `.gitignore`:

    ```
    storage/app/firebase-credentials.json
    ```

3. Store credentials securely in production
4. Consider adding authentication middleware to FCM test endpoints in production

## Troubleshooting

### Notifications Not Sending

1. Check if `firebase-credentials.json` exists and is valid
2. Verify user has `fcm_token` set
3. Verify user's `sales_id` matches the sell record's `sales_id`
4. Check logs at `storage/logs/laravel.log`

### Invalid Token Errors

- FCM tokens can expire or become invalid
- Implement token refresh logic in your mobile app
- Handle token updates when users reinstall the app

## Production Considerations

1. **Rate Limiting**: Add rate limiting to FCM test endpoints
2. **Authentication**: Protect FCM endpoints with authentication
3. **Queue**: Move notification sending to a queue for better performance:
    ```php
    dispatch(new SendFcmNotification($users, $title, $body, $data));
    ```
4. **Monitoring**: Set up alerts for failed notifications
5. **Token Management**: Implement automatic cleanup of invalid tokens
