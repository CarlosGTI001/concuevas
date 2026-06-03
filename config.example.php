<?php
declare(strict_types=1);

return [
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'database_name',
        'user' => 'database_user',
        'pass' => 'change_me',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url' => 'https://example.com/',
        'debug' => false,
        'error_log' => __DIR__ . '/error_log',
    ],
    'mail' => [
        'from_email' => 'noreply@example.com',
        'from_name' => 'Construcciones Cuevas',
    ],
    'smtp' => [
        'enabled' => false,
        'host' => 'smtp.example.com',
        'port' => 587,
        'username' => 'user@example.com',
        'password' => 'change_me',
        'encryption' => 'tls', // tls, ssl, or none
        'timeout' => 15,
    ],
];
