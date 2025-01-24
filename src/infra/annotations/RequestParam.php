<?php

namespace infra\annotations;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class RequestParam
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}