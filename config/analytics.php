<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Yandex.Metrika
    |--------------------------------------------------------------------------
    */

    'metrika' => [
        'enabled' => env('METRIKA_ENABLED', false),
        'counter_id' => env('METRIKA_COUNTER_ID', ''),
        'webvisor' => env('METRIKA_WEBVISOR', false),
        'clickmap' => env('METRIKA_CLICKMAP', true),
        'accurate_track_bounce' => env('METRIKA_ACCURATE_BOUNCE', true),
        'ecommerce' => env('METRIKA_ECOMMERCE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Analytics (GA4)
    |--------------------------------------------------------------------------
    */

    'ga4' => [
        'enabled' => env('GA4_ENABLED', false),
        'measurement_id' => env('GA4_MEASUREMENT_ID', ''),
    ],

];
