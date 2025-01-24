<?php

namespace infra\annotations;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class RequestHeader
{
    public string $headerName;

    public function __construct(string $headerName)
    {
        $this->headerName = $headerName;
    }
}