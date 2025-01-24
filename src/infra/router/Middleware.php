<?php

namespace infra\router;

use infra\exceptionhandler\ExceptionHandler;
use Exception;
use infra\http\HttpStatus;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
header('Access-Control-Max-Age: 3600');

class Middleware
{
    private Router $router;
    private ExceptionHandler $exceptionHandler;

    public function __construct(Router $router, ExceptionHandler $exceptionHandler)
    {
        $this->router = $router;
        $this->exceptionHandler = $exceptionHandler;
    }

    public function handleRequest(): void
    {
        try {
            $response = $this->router->start();
            if ($response instanceof Response) {
                $response->send();
            } else {
                (new Response(HttpStatus::OK->value, $response))->send();
            }
        } catch (Exception $e) {
            $errorResponse = $this->exceptionHandler->handle($e);
            $errorResponse->send();
        }
    }
}