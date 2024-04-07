<?php


return (object) [
    "use_auth" => true,
    "auth_model" => App\Models\User::class,
    "username_field" => "email",
    "password_field" => "token",
    "login_url" => "login",
    "max_login_attempts_per_hour" => 10,
    "login_use_unique_hash" => false,
    // if login_use_unique_hash is true
    //    "hash_field" => "hash"
    "access_control" => "acf", // "rbac" for RBAC, "acf" for ACF
    // if ACF, select which field of auth_model contains the user privilege level
    //"level_field" => "level",
    // if RBAC, select the class that inherits from BaseRBAC
    //"rbac_class" => App\RBAC::class,

];
