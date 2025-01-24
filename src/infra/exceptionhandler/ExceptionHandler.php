<?php

namespace infra\exceptionhandler;

use infra\router\Response;
use Exception;
use infra\http\HttpStatus;

class ExceptionHandler
{
    public function handle(Exception $e): Response
    {
        $statusCode = $e->getCode() ?: HttpStatus::INTERNAL_SERVER_ERROR->value;
        return new Response($statusCode, ['error' => $e->getMessage()]);
    }
}
