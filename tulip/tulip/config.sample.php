<?php

define("Config", [
    'port' => 9990,
    "database" => [
        "driver" => "pgsql",
        "host" => "localhost",
        "port" => 5432,
        "name" => "wikipali",
        "user" => "postgres",
        "password" => "123456",
    ],
    'api_server' => 'https://staging.wikipali.org/api',
]);
