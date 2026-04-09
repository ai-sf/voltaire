<?php

/**
 * DO NOT MODIFY THIS FILE!
 */


require __DIR__ . "/vendor/autoload.php";


// Load environment variables from .env (if present)
if (file_exists(__DIR__ . '/.env')) {
	try {
		\Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
	} catch (Throwable $e) {
		// ignore dotenv loading errors
	}
}

global $config;

$config = require 'config/config.php';

use Lepton\Core\Application;

Application::loadErrorHandler();
Application::loadConfig($config);

Application::run();
