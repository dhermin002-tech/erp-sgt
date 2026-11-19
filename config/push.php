<?php

return [
    'file' => env('SGT_PUSH_QUEUE_CONNECTION', 'database'),
    'fcm' => [
        'projet' => env('SGT_FCM_PROJECT'),
        'identifiants' => env('GOOGLE_APPLICATION_CREDENTIALS'),
    ],
    'vapid' => [
        'subject' => env('SGT_VAPID_SUBJECT'),
        'publicKey' => env('SGT_VAPID_PUBLIC_KEY'),
        'privateKey' => env('SGT_VAPID_PRIVATE_KEY'),
    ],
];
