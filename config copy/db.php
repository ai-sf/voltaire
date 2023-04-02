<?php

return (object) [
  "host" => "62.149.150.166",
  "user" => "Sql795758",
  "password" => "e51qcv483z",
  "dbname" => "Sql795758_1",
  "authentication" => (object) [
    "auth_model" => App\Models\User::class,
    "username_field" => "email",
    "password_field" => "token",
    "max_login_attempts_per_hour" => 10,
    "level_field" => "level",
    "hash_field" => "hash"
  ]
];
