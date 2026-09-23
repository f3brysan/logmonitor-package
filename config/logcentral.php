<?php

return [

    'enabled' => env('LOGCENTRAL_ENABLED', true),

    'endpoint' => env('LOGCENTRAL_ENDPOINT', 'http://localhost:8088/api/error-log-transactions'),

    'api_key' => env('LOGCENTRAL_API_KEY'),

    'timeout' => (int) env('LOGCENTRAL_TIMEOUT', 3),

    'dont_report' => [
        //
    ],

];
