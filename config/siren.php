<?php

return [

    'token' => env('SIREN_TOKEN'),

    'protocol' => env('SIREN_PROTOCOL'),

    'udp' => [
        'host' => env('SIREN_UDP_HOST'),
        'port' => env('SIREN_UDP_PORT'),
    ],

    'http' => [
        'host' => env('SIREN_HTTP_HOST'),
        'port' => env('SIREN_HTTP_PORT'),
    ],

    'tcp' => [
        'host' => env('SIREN_TCP_HOST'),
        'port' => env('SIREN_TCP_PORT'),
    ],

];
