<?php

namespace infra\annotations;

use infra\http\HttpStatus;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Controller
{
}

#[\Attribute(\Attribute::TARGET_CLASS)]
class RequestMapping
{
    public string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }
}

#[\Attribute(\Attribute::TARGET_METHOD)]
class GetMapping
{
    public ?string $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path;
    }
}

#[\Attribute(\Attribute::TARGET_METHOD)]
class PostMapping
{
    public ?string $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path;
    }
}

#[\Attribute(\Attribute::TARGET_METHOD)]
class PutMapping
{
    public ?string $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path;
    }
}

#[\Attribute(\Attribute::TARGET_METHOD)]
class DeleteMapping
{
    public ?string $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path;
    }
}

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class PathVariable
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class RequestParam
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class RequestBody
{
}

#[\Attribute(\Attribute::TARGET_METHOD)]
class ResponseStatus
{
    public HttpStatus $status;

    public function __construct(HttpStatus $status)
    {
        $this->status = $status;
    }
}

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class RequestHeader
{
    public string $headerName;

    public function __construct(string $headerName)
    {
        $this->headerName = $headerName;
    }
}