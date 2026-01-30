<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FcmChannel
{
    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        Log::info('FCM: Channel send() hit for notification: ' . get_class($notification));
        if (!method_exists($notification, 'toFcm')) {
            Log::warning('FCM: Notification does not have toFcm method');
            return;
        }

        $token = $notifiable->routeNotificationForFcm($notification);
        
        Log::info('FCM: Attempting to send notification to house ID: ' . $notifiable->id);
        Log::info('FCM: Token found: ' . ($token ? 'Yes (length: '.strlen($token).')' : 'No'));

        if (!$token) {
            return;
        }

        $fcmData = $notification->toFcm($notifiable);
        $accessToken = $this->getAccessToken();
        Log::info('FCM: Access Token obtained: ' . ($accessToken ? 'Yes' : 'No'));

        if (!$accessToken) {
            Log::error('FCM: Failed to obtain access token');
            return;
        }

        $projectId = config('services.firebase.project_id');
        if (!$projectId) {
            Log::error('FCM: FIREBASE_PROJECT_ID not found in config');
            return;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $fcmData['title'] ?? 'Notification',
                    'body'  => $fcmData['body'] ?? '',
                ],
                'data' => array_map('strval', $fcmData['data'] ?? []),
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => $fcmData['sound'] ?? 'default',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => $fcmData['sound'] ?? 'default',
                        ],
                    ],
                ],
            ],
        ]);

        if ($response->failed()) {
            Log::error('FCM Send Failed for house ID '.$notifiable->id.': ' . $response->body());
        } else {
            Log::info('FCM Send Success for house ID '.$notifiable->id.': ' . $response->body());
        }
    }

    /**
     * Get OAuth2 access token for FCM v1
     */
    protected function getAccessToken()
    {
        Log::info('FCM: Attempting to get Access Token');
        return Cache::remember('fcm_access_token', 3500, function () {
            $credentialsPath = base_path(config('services.firebase.credentials', 'storage/app/firebase/service-account.json'));

            if (!file_exists($credentialsPath)) {
                Log::error("FCM: Credentials file not found at {$credentialsPath}");
                return null;
            }

            $credentials = json_decode(file_get_contents($credentialsPath), true);
            if (!$credentials) {
                Log::error("FCM: Failed to parse credentials JSON");
                return null;
            }

            $now = time();
            $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $payload = base64_encode(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]));

            $unsignedToken = $header . '.' . $payload;
            $signature = '';
            if (!openssl_sign($unsignedToken, $signature, $credentials['private_key'], 'SHA256')) {
                Log::error('FCM: Failed to sign JWT. OpenSSL Error: ' . openssl_error_string());
                return null;
            }

            $signedToken = $unsignedToken . '.' . base64_encode($signature);

            $response = Http::asForm()->post($credentials['token_uri'], [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $signedToken,
            ]);

            if ($response->failed()) {
                Log::error('FCM: OAuth token request failed: ' . $response->body());
                return null;
            }

            return $response->json('access_token');
        });
    }
}
