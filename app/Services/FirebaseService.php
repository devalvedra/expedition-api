<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Exception;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        try {
            // Initialize Firebase with service account
            $serviceAccountPath = config('firebase.credentials');
            
            if (!$serviceAccountPath || !file_exists($serviceAccountPath)) {
                Log::warning('Firebase service account file not found. FCM notifications will not work.');
                return;
            }

            $factory = (new Factory)->withServiceAccount($serviceAccountPath);
            $this->messaging = $factory->createMessaging();
        } catch (Exception $e) {
            Log::error('Firebase initialization error: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to a specific device token
     *
     * @param string $token
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        if (!$this->messaging) {
            Log::warning('Firebase messaging not initialized');
            return false;
        }

        try {
            $message = CloudMessage::fromArray([
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
            ]);

            $this->messaging->send($message);
            
            Log::info("FCM notification sent to token: {$token}");
            return true;
        } catch (Exception $e) {
            Log::error('FCM send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to multiple device tokens
     *
     * @param array $tokens
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array
     */
    public function sendToMultipleTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        if (!$this->messaging) {
            Log::warning('Firebase messaging not initialized');
            return ['success' => 0, 'failure' => count($tokens)];
        }

        $results = ['success' => 0, 'failure' => 0];

        foreach ($tokens as $token) {
            if ($this->sendToToken($token, $title, $body, $data)) {
                $results['success']++;
            } else {
                $results['failure']++;
            }
        }

        return $results;
    }

    /**
     * Send notification to a topic
     *
     * @param string $topic
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        if (!$this->messaging) {
            Log::warning('Firebase messaging not initialized');
            return false;
        }

        try {
            $message = CloudMessage::fromArray([
                'topic' => $topic,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
            ]);

            $this->messaging->send($message);
            
            Log::info("FCM notification sent to topic: {$topic}");
            return true;
        } catch (Exception $e) {
            Log::error('FCM topic send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Subscribe tokens to a topic
     *
     * @param array $tokens
     * @param string $topic
     * @return bool
     */
    public function subscribeToTopic(array $tokens, string $topic): bool
    {
        if (!$this->messaging) {
            Log::warning('Firebase messaging not initialized');
            return false;
        }

        try {
            $this->messaging->subscribeToTopic($topic, $tokens);
            Log::info("Tokens subscribed to topic: {$topic}");
            return true;
        } catch (Exception $e) {
            Log::error('FCM topic subscription error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Unsubscribe tokens from a topic
     *
     * @param array $tokens
     * @param string $topic
     * @return bool
     */
    public function unsubscribeFromTopic(array $tokens, string $topic): bool
    {
        if (!$this->messaging) {
            Log::warning('Firebase messaging not initialized');
            return false;
        }

        try {
            $this->messaging->unsubscribeFromTopic($topic, $tokens);
            Log::info("Tokens unsubscribed from topic: {$topic}");
            return true;
        } catch (Exception $e) {
            Log::error('FCM topic unsubscription error: ' . $e->getMessage());
            return false;
        }
    }
}
