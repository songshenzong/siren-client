<?php

return [

    'token' => env('SIREN_TOKEN', ''),

    'protocol' => env('SIREN_PROTOCOL', 'udp'),

    'udp' => [
        'host' => env('SIREN_UDP_HOST', '127.0.0.1'),
        'port' => env('SIREN_UDP_PORT', 55656),
    ],

    'tcp' => [
        'host' => env('SIREN_TCP_HOST', '127.0.0.1'),
        'port' => env('SIREN_TCP_PORT', 55657),
    ],

    'http' => [
        'host' => env('SIREN_HTTP_HOST', '127.0.0.1'),
        'port' => env('SIREN_HTTP_PORT', 55658),
    ],

];
