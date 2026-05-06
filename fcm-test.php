#!/usr/bin/env php
<?php

/**
 * FCM Testing Script
 * 
 * This script helps you test the Firebase Cloud Messaging implementation
 * Run from command line: php fcm-test.php
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

// Colors for terminal output
function colorize($text, $color = 'green') {
    $colors = [
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'reset' => "\033[0m"
    ];
    return $colors[$color] . $text . $colors['reset'];
}

function printHeader($text) {
    echo "\n" . colorize(str_repeat('=', 60), 'blue') . "\n";
    echo colorize($text, 'yellow') . "\n";
    echo colorize(str_repeat('=', 60), 'blue') . "\n\n";
}

function printSuccess($text) {
    echo colorize("✓ " . $text, 'green') . "\n";
}

function printError($text) {
    echo colorize("✗ " . $text, 'red') . "\n";
}

function printInfo($text) {
    echo colorize("ℹ " . $text, 'blue') . "\n";
}

// Main test execution
printHeader("Firebase Cloud Messaging (FCM) Test Script");

// Check if Firebase credentials exist
$credentialsPath = config('firebase.credentials');
if (!file_exists($credentialsPath)) {
    printError("Firebase credentials file not found at: {$credentialsPath}");
    printInfo("Please download your Firebase service account JSON and place it at:");
    printInfo($credentialsPath);
    exit(1);
}
printSuccess("Firebase credentials file found");

// Test 1: Check if FirebaseService can be instantiated
try {
    $firebaseService = app(\App\Services\FirebaseService::class);
    printSuccess("FirebaseService instantiated successfully");
} catch (Exception $e) {
    printError("Failed to instantiate FirebaseService: " . $e->getMessage());
    exit(1);
}

// Test 2: Check database connection
try {
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    printSuccess("Database connection successful");
} catch (Exception $e) {
    printError("Database connection failed: " . $e->getMessage());
    exit(1);
}

// Test 3: Check if tbsales table has required columns
try {
    $hasColumns = \Illuminate\Support\Facades\Schema::hasColumns('tbsales', ['fcm_token', 'sales_id']);
    if ($hasColumns) {
        printSuccess("tbsales table has required columns (fcm_token, sales_id)");
    } else {
        printError("tbsales table missing required columns");
        printInfo("Run migration to add fcm_token and sales_id columns to tbsales table");
    }
} catch (Exception $e) {
    printError("Error checking tbsales table: " . $e->getMessage());
}

// Test 4: Check if Sell observer is registered
printInfo("SellObserver should be registered in AppServiceProvider");

// Test 5: Interactive test (optional)
printHeader("Interactive Testing");

echo "Would you like to send a test notification? (requires valid FCM token)\n";
echo "Enter 'y' to continue or any other key to skip: ";
$handle = fopen("php://stdin", "r");
$input = trim(fgets($handle));

if (strtolower($input) === 'y') {
    echo "\nEnter FCM token: ";
    $token = trim(fgets($handle));
    
    if (!empty($token)) {
        try {
            printInfo("Sending test notification...");
            $result = $firebaseService->sendToToken(
                $token,
                "Test Notification from CLI",
                "This is a test message from the FCM test script",
                ['test' => 'true', 'timestamp' => now()->toDateTimeString()]
            );
            
            if ($result) {
                printSuccess("Test notification sent successfully!");
                printInfo("Check your device for the notification");
            } else {
                printError("Failed to send notification");
                printInfo("Check storage/logs/laravel.log for details");
            }
        } catch (Exception $e) {
            printError("Error: " . $e->getMessage());
        }
    } else {
        printInfo("No token provided, skipping notification test");
    }
}

fclose($handle);

// Summary
printHeader("Test Summary");
printSuccess("All basic checks completed!");
printInfo("To test the full flow:");
echo "\n1. Update a user's FCM token:\n";
echo "   POST /api/fcm-test/update-token\n";
echo "\n2. Create a sell record:\n";
echo "   POST /api/sell\n";
echo "\n3. Check logs for notification results:\n";
echo "   tail -f storage/logs/laravel.log\n";
echo "\n";

printInfo("For more details, see FCM_IMPLEMENTATION.md");
printInfo("Import postman_collection_fcm.json to Postman for API testing");

echo "\n";
