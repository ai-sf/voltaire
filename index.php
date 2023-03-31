<?php

  global $config;
  require __DIR__."/vendor/autoload.php";

  $config = require 'config/config.php';

  use Lepton\Base\Database;
  use Lepton\Base\Application;

  session_start();

  Application::loadErrorHandler();
  Application::loadConfig($config);

  $dbconfig = Application::getDbConfig();
  Application::loadDb(new Database($dbconfig->host, $dbconfig->user, $dbconfig->password, $dbconfig->dbname));
  Application::matchRoutes();
  Application::unloadDb();
