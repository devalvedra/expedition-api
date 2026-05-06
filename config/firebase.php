<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Service Account
    |--------------------------------------------------------------------------
    |
    | Path to your Firebase service account JSON file.
    | You can download this from Firebase Console > Project Settings > Service Accounts
    | Place the file in storage/app/ directory
    |
    */
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),

    /*
    |--------------------------------------------------------------------------
    | Firebase Database URL
    |--------------------------------------------------------------------------
    |
    | Your Firebase Realtime Database URL (optional)
    |
    */
    'database_url' => env('FIREBASE_DATABASE_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Firebase Project ID
    |--------------------------------------------------------------------------
    |
    | Your Firebase Project ID
    |
    */
    'project_id' => env('FIREBASE_PROJECT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Firebase Web SDK Configuration
    |--------------------------------------------------------------------------
    |
    | These values are used by the Firebase JavaScript SDK in the browser.
    | Obtain them from Firebase Console > Project Settings > Your apps (Web).
    |
    */
    'web_api_key'          => env('FIREBASE_WEB_API_KEY', ''),
    'auth_domain'          => env('FIREBASE_AUTH_DOMAIN', ''),
    'storage_bucket'       => env('FIREBASE_STORAGE_BUCKET', ''),
    'messaging_sender_id'  => env('FIREBASE_MESSAGING_SENDER_ID', ''),
    'app_id'               => env('FIREBASE_APP_ID', ''),
];
