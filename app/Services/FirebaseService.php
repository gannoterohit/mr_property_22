<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private const FCM_V1_ENDPOINT = 'https://fcm.googleapis.com/v1/projects/{project_id}/messages:send';
    private const FCM_LEGACY_ENDPOINT = 'https://fcm.googleapis.com/fcm/send';

    /**
     * Send push notification to a user (both mobile FCM token and web push token if available).
     */
    public static function sendToUser(User $user, string $title, string $body, array $data = [], ?string $link = null, ?string $image = null): void
    {
        if (!self::isConfigured()) {
            return; // Firebase push is OFF or not configured yet — exit silently without error
        }

        $serverKey = Setting::get('firebase_server_key');

        // Send to mobile FCM token
        if ($user->fcm_token) {
            self::sendToToken($user->fcm_token, $title, $body, $data, $link, $image, $serverKey);
        }

        // Send to web push token
        if ($user->web_push_token) {
            self::sendToToken($user->web_push_token, $title, $body, $data, $link, $image, $serverKey);
        }
    }

    /**
     * Send to a single FCM token (Legacy HTTP API v1 compatible).
     */
    public static function sendToToken(string $token, string $title, string $body, array $data = [], ?string $link = null, ?string $image = null, ?string $serverKey = null): bool
    {
        $serverKey = $serverKey ?? Setting::get('firebase_server_key');
        if (!$serverKey || !$token) {
            return false;
        }

        $notificationPayload = [
            'title'        => $title,
            'body'         => $body,
            'sound'        => 'default',
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];

        if ($image) {
            $notificationPayload['image'] = $image;
        }

        $payload = [
            'to'           => $token,
            'notification' => $notificationPayload,
            'data'         => array_merge($data, [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'link'         => $link ?? '',
                'image'        => $image ?? '',
            ]),
            'priority'     => 'high',
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type'  => 'application/json',
            ])->timeout(10)->post(self::FCM_LEGACY_ENDPOINT, $payload);

            if ($response->successful()) {
                $result = $response->json();
                // FCM returns success:1 if sent
                if (($result['success'] ?? 0) === 1) {
                    return true;
                }
                // Token is invalid/expired — clean it up
                if (isset($result['results'][0]['error']) && in_array($result['results'][0]['error'], ['NotRegistered', 'InvalidRegistration'])) {
                    self::invalidateToken($token);
                }
            }

            Log::warning('FCM send failed', ['token_prefix' => substr($token, 0, 10), 'response' => $response->body()]);
            return false;

        } catch (\Exception $e) {
            Log::error('FirebaseService::sendToToken exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove invalid/expired token from users table.
     */
    private static function invalidateToken(string $token): void
    {
        try {
            \App\Models\User::where('fcm_token', $token)->update(['fcm_token' => null]);
            \App\Models\User::where('web_push_token', $token)->update(['web_push_token' => null]);
        } catch (\Exception $e) {
            Log::warning('FirebaseService: Could not invalidate token: ' . $e->getMessage());
        }
    }

    /**
     * Check if Firebase is enabled in settings and configured (server key exists).
     */
    public static function isConfigured(): bool
    {
        $enabled   = Setting::get('firebase_push_enabled', '1') === '1';
        $hasKey    = !empty(Setting::get('firebase_server_key'));
        return $enabled && $hasKey;
    }
}
