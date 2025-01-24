<?php

namespace infra\annotations;

#[\Attribute(\Attribute::TARGET_METHOD)]
class GetMapping
{
    public ?string $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path;
    }
}