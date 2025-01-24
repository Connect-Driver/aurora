<?php

namespace infra\annotations;

#[\Attribute(\Attribute::TARGET_CLASS)]
class RequestMapping
{
    public string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }
}