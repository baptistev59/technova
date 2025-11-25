<?php

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__).'/vendor/autoload.php';

$env = $_SERVER['APP_ENV'] ?? 'prod';
$debug = (bool) ($_SERVER['APP_DEBUG'] ?? false);

if ($debug) {
    umask(0000);

    Symfony\Component\ErrorHandler\Debug::enable();
}

$kernel = new Kernel($env, $debug);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
