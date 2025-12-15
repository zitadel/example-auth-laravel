<?php

use Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (class_exists(Dotenv::class)) {
    $dotenv = Dotenv::createImmutable(dirname(__DIR__), '.env.test');
    $dotenv->load();
}

if ($_SERVER['APP_DEBUG'] ?? false) {
    umask(0000);
}
