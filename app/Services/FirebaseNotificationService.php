<?php

namespace App\Services;

use GuzzleHttp\Client;

class FirebaseNotificationService
{
    protected $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function sendNotification($deviceToken, $message)
    {
        $serverKey = 'AAAAZdGAKEY:APA91bFfN-7xQiGT3dUt_W70OegbEdDV2GCKVDzhBwPLjxL0kdopb6AIworabPa35A4I0sbEtAPmW6lF3-NUoD5dYQIuOIapQV1RONUvk2Cr5a-FEH3A3REJ0JCA1E7qOXa9kv8lGxXR';
        $url = 'https://fcm.googleapis.com/fcm/send';

        $headers = [
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ];

        $notification = [
            'to' => $deviceToken,
            'notification' => [
                'title' => 'New User',
                'body' => $message,
            ],
        ];

        $response = $this->httpClient->post($url, [
            'headers' => $headers,
            'json' => $notification,
        ]);

        return $response->getBody();
    }
}
