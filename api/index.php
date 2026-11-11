<?php

// Force SCRIPT_NAME to behave as though index.php is at the root
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Ensure HTTP_AUTHORIZATION is passed to PHP
if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                $_SERVER['HTTP_AUTHORIZATION'] = $value;
                break;
            }
        }
    }
}

// Force HTTPS behind Vercel edge reverse proxy
if ((isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || isset($_SERVER['HTTP_X_VERCEL_ID'])
    || isset($_SERVER['VERCEL'])) {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = '443';
}

// Forward Vercel serverless requests to Laravel's entry point
$dirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Clear stale compiled blade views from older builds
foreach (glob('/tmp/storage/framework/views/*.php') ?: [] as $cachedView) {
    @unlink($cachedView);
}

// Ensure SQLite database file is writable in /tmp if used
$dbSource = __DIR__ . '/../database/database.sqlite';
$dbTarget = '/tmp/database.sqlite';
if (file_exists($dbSource)) {
    if (!file_exists($dbTarget) || @filemtime($dbSource) > @filemtime($dbTarget) || filesize($dbTarget) < filesize($dbSource)) {
        @copy($dbSource, $dbTarget);
    }
}
if (file_exists($dbTarget) && (!getenv('DB_CONNECTION') || getenv('DB_CONNECTION') === 'sqlite')) {
    putenv('DB_CONNECTION=sqlite');
    $_ENV['DB_CONNECTION'] = 'sqlite';
    $_SERVER['DB_CONNECTION'] = 'sqlite';
    putenv('DB_DATABASE=' . $dbTarget);
    $_ENV['DB_DATABASE'] = $dbTarget;
    $_SERVER['DB_DATABASE'] = $dbTarget;
}

putenv('ASSET_URL=/');
$_ENV['ASSET_URL'] = '/';
$_SERVER['ASSET_URL'] = '/';

putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php');
putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');

require __DIR__ . '/../public/index.php';
