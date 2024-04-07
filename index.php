<?php

/**
 * DO NOT MODIFY THIS FILE!
 */


require __DIR__ . "/vendor/autoload.php";


global $config;

$config = require 'config/config.php';

use Lepton\Core\Application;

Application::loadErrorHandler();
Application::loadConfig($config);

Application::run();
