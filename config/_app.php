<?php
return (object) [
    "name" => "Voltaire",
    "static_files_dir" => "resources",
    "static_files_extensions" => ["png", "jpg", "jpeg", "gif", "css", "js", "pdf", "ico"],
    "base_url" => "",
    "middlewares" => [
        // If you want to use RBAC
        /*\Lepton\Middleware\RBACMiddleware::class => [
      "rbac_class" => \App\RBAC\RBAC::class,
      "user_class" => \App\Models\User::class
    ],*/

        // If you want to use ACF

        \Lepton\Middleware\ACFMiddleware::class => [
            "level_field" => "level"
        ],


        // If you want to use base control (only logged in/not logged in)
        /*
     * \Lepton\Middleware\BaseAccessControlMiddleware::class => []
     */
    ],
];
?>