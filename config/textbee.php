<?php

return [
    'device_id' => env('TEXTBEE_DEVICE_ID'),
    'api_key'   => env('TEXTBEE_API_KEY'),
    'base_url'  => env('TEXTBEE_BASE_URL', 'https://api.textbee.dev/api/v1/gateway/send-sms'),
];
