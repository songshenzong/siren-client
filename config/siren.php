<?php

return [

    'token' => env('SIREN_TOKEN', ''),

    'protocol' => env('SIREN_PROTOCOL', 'udp'),

    'servers' => [

        'udp' => env('SIREN_UDP_SERVER', '127.0.0.1:55656'),

        'tcp' => env('SIREN_TCP_SERVER', '127.0.0.1:55657'),

        'http' => env('SIREN_HTTPl_HOST', '127.0.0.1:55658'),

    ]

];
