<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toFcm')) {
             return;
        }

        // Get the device token from the notifiable model
        // You should add a column 'fcm_token' to your 'houses' table
        // or a separate method to retrieve it.
        $token = $notifiable->routeNotificationForFcm($notification);

        if (!$token) {
            // Log::info('FCM: No token found for user ' . $notifiable->id);
            return;
        }

        $fcmData = $notification->toFcm($notifiable);

        $serverKey = env('FIREBASE_SERVER_KEY'); // You must add this to your .env file

        if (!$serverKey) {
            Log::warning('FCM: FIREBASE_SERVER_KEY not found in .env');
            return;
        }

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type'  => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $token,
            'notification' => [
                'title' => $fcmData['title'] ?? 'Notification',
                'body'  => $fcmData['body'] ?? '',
                'sound' => $fcmData['sound'] ?? 'default',
            ],
            'data' => $fcmData['data'] ?? [],
            'priority' => 'high',
        ]);

        if ($response->failed()) {
            Log::error('FCM Send Failed: ' . $response->body());
        }
    }
}
