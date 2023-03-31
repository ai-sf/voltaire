<?php

$db = require __DIR__.'/db.php';
$routes = require __DIR__.'/routes.php';
$app = require __DIR__.'/app.php';
$email = require __DIR__.'/email.php';

$config = [
  'app' => $app,
  'database' => $db,
  'routes' => $routes,
  'email' => $email
];

return (object) $config;
