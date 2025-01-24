<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'autoload/autoload.php';

use infra\router\Router;
use infra\router\Middleware;
use infra\exceptionhandler\ExceptionHandler;

$router = new Router();
$exceptionHandler = new ExceptionHandler();
$middleware = new Middleware($router, $exceptionHandler);

$middleware->handleRequest();