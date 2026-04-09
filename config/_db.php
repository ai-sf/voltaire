<?php

return (object) [
  "use_db" => getenv('DB_USE') !== false ? filter_var(getenv('DB_USE'), FILTER_VALIDATE_BOOLEAN) : true,
  "host" => getenv('DB_HOST') ?: "localhost",
  "user" => getenv('DB_USER') ?: "voltaire",
  "password" => getenv('DB_PASS') ?: "voltairepwd",
  "dbname" => getenv('DB_NAME') ?: "voltaire_local",
];
