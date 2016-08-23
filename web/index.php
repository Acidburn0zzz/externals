<?php
declare(strict_types = 1);

// Serve static files when running with PHP's built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']))) {
    return false;
}

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../.puli/GeneratedPuliFactory.php';

$app = new Externals\Application\Application();
$app->http()->run();
