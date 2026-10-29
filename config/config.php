<?php

/*
 * Default settings. Environment variables win, then config/config.local.php
 * (not committed) can override anything, for example the database password.
 */
$config = [
    'debug' => (bool) (getenv('APP_DEBUG') ?: false),
    'db' => [
        'dsn' => getenv('DB_DSN') ?: 'mysql:host=127.0.0.1;port=3306;dbname=escales;charset=utf8mb4',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
    ],
];

$local = __DIR__ . '/config.local.php';
if (is_file($local)) {
    $config = array_replace_recursive($config, require $local);
}

return $config;
