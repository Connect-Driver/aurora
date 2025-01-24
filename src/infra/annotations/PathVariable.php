<?php

namespace infra\annotations;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class PathVariable
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}