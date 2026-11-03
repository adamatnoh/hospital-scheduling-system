<?php

/*
|--------------------------------------------------------------------------
| Vercel entry point
|--------------------------------------------------------------------------
|
| Vercel runs the app on a read-only filesystem where only /tmp is
| writable, so the framework caches, compiled views, logs and the
| storage directory are redirected there before Laravel boots.
|
*/

$tmp = '/tmp/hss';

$writable = [
    'LARAVEL_STORAGE_PATH' => $tmp.'/storage',
    'APP_CONFIG_CACHE' => $tmp.'/cache/config.php',
    'APP_EVENTS_CACHE' => $tmp.'/cache/events.php',
    'APP_PACKAGES_CACHE' => $tmp.'/cache/packages.php',
    'APP_ROUTES_CACHE' => $tmp.'/cache/routes.php',
    'APP_SERVICES_CACHE' => $tmp.'/cache/services.php',
    'VIEW_COMPILED_PATH' => $tmp.'/storage/framework/views',
];

foreach ($writable as $key => $value) {
    if (getenv($key) === false) {
        putenv("{$key}={$value}");
        $_ENV[$key] = $_SERVER[$key] = $value;
    }
}

foreach ([
    $tmp.'/cache',
    $tmp.'/storage/app/public',
    $tmp.'/storage/framework/cache/data',
    $tmp.'/storage/framework/sessions',
    $tmp.'/storage/framework/views',
    $tmp.'/storage/logs',
] as $directory) {
    if (! is_dir($directory)) {
        @mkdir($directory, 0755, true);
    }
}

require __DIR__.'/../public/index.php';
