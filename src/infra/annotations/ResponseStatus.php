<?php

namespace infra\annotations;

use infra\http\HttpStatus;

#[\Attribute(\Attribute::TARGET_METHOD)]
class ResponseStatus
{
    public HttpStatus $status;

    public function __construct(HttpStatus $status)
    {
        $this->status = $status;
    }
}