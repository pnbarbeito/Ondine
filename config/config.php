<?php
return [
    // driver: 'sqlite' or 'mariadb'
    'db' => [
        'driver' => 'sqlite',
        // sqlite uses path
        'sqlite' => [
            'path' => 'data/database.sqlite',
        ],
        // mariadb settings
        'mariadb' => [
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'ondine',
            'user' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
        ],
    ],
];
