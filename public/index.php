<?php

// Single entry point: every request goes through this file (see .htaccess).

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

// With PHP's built-in server, let it serve existing files (CSS, images) directly.
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

$config = require $root . '/config/config.php';

ini_set('display_errors', $config['debug'] ? '1' : '0');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

$pdo = App\Core\Database::connect($config['db']['dsn'], $config['db']['user'], $config['db']['password']);
$app = new App\App($pdo, App\Core\Session::start(), $config['debug']);
$app->handle(App\Core\Request::fromGlobals())->send();
