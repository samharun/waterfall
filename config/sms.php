<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS Driver
    |--------------------------------------------------------------------------
    | Supported: "log", "ssl_wireless", "twilio"
    | Set SMS_DRIVER in your .env file.
    */

    'driver' => env('SMS_DRIVER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | SSL Wireless (Bangladesh)
    | https://sslwireless.com/
    |--------------------------------------------------------------------------
    */
    'ssl_wireless' => [
        'url'       => env('SSL_WIRELESS_URL', 'https://sms.sslwireless.com/pushapi/dynamic/server.php'),
        'api_token' => env('SSL_WIRELESS_API_TOKEN', ''),
        'sid'       => env('SSL_WIRELESS_SID', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Twilio
    |--------------------------------------------------------------------------
    */
    'twilio' => [
        'sid'   => env('TWILIO_SID', ''),
        'token' => env('TWILIO_TOKEN', ''),
        'from'  => env('TWILIO_FROM', ''),
    ],

];
