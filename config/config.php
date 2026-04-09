<?php

// Load environment variables from project root .env (if present)
if (file_exists(dirname(__DIR__) . '/.env')) {
    try {
        \Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
    } catch (Throwable $e) {
        // ignore dotenv loading errors
    }
}

$db = require __DIR__ . '/_db.php';
$routes = require __DIR__ . '/_routes.php';
$app = require __DIR__ . '/_app.php';
$email = require __DIR__ . '/_email.php';
$security = require __DIR__ . '/_authentication.php';

$config = [
    'app' => $app,
    'database' => $db,
    'auth' => $security,
    'routes' => $routes,
    'email' => $email
];

return (object) $config;
