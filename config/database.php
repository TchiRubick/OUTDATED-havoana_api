<?php
return [
    'connections' => [

        'default' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '192.168.56.101'),
            'port'      => env('DB_PORT', 3306),
            'database'  => env('DB_DATABASE', 'havoanac_main'),
            'username'  => env('DB_USERNAME', 'e2puser'),
            'password'  => env('DB_PASSWORD', 'password'),
            'charset'   => env('DB_CHARSET', 'utf8'),
            'collation' => env('DB_COLLATION', 'utf8_unicode_ci'),
            'prefix'    => env('DB_PREFIX', ''),
            'timezone'  => env('DB_TIMEZONE', '+03:00'),
            'strict'    => env('DB_STRICT_MODE', false),
        ],

        'client' => [
            'driver'    => 'mysql',
            'host'      => '',
            'port'      => '',
            'database'  => '',
            'username'  => '',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'timezone'  => '+03:00',
            'strict'    => false
        ],
    ]
];
