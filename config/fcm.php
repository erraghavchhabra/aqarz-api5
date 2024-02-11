<?php

return [
    'driver' => env('FCM_PROTOCOL', 'http'),
    'log_enabled' => false,

    'http' => [
        'server_key' => 'AAAA31wc8-o:APA91bFwf5UreKU9xaWTA59DZLZlzL_E1Ju_r2pV3e6K0NclfvaJQOfjCEQusLzfEpcXj_7BPA1ZXFZiHk7Db-GsBQtpv7ZA_62SM-_b3bjhEYrUvFZ0T3gG_P7qUTmQZdnjmPPfT0KS',
        'sender_id' => '959323108330',
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30.0, // in second

    ],
];
