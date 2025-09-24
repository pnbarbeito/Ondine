<?php
return [
    // driver: 'sqlite' or 'mariadb'
    'db' => [
        'driver' => 'sqlite',
        // sqlite uses path
        'sqlite' => [
            'path' => __DIR__ . '/../data/database.sqlite',
        ],
        // mariadb settings
        'mariadb' => [
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'newframe',
            'user' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
        ],
    ],
];
