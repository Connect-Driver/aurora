<?php

namespace infra\annotations;

#[\Attribute(\Attribute::TARGET_METHOD)]
class DeleteMapping
{
    public ?string $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path;
    }
}