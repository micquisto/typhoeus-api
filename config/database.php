<?php

/**
 * Custom database configuration for Typhoeus API package (Jenssegers\Mongodb used) - this will append config to the application's config
 */

return [
    'connections' => [
        'mongodb-api' => [
            'driver'        => 'mongodb',
            'host'          => '192.168.2.233',
            'database'      => 'typhoeus'
        ]
    ]
];
