<?php

namespace infra\router;

use infra\http\HttpStatus;

class Response
{
    private int $status;
    private array|string|null $data;

    public function __construct(int $status = HttpStatus::OK->value, array|string|null $data = null)
    {
        $this->status = $status;
        $this->data = $data;
    }

    public function send(): void
    {
        http_response_code($this->status);
        if ($this->data !== null) {
            header('Content-Type: application/json');
            echo json_encode($this->data);
        }
    }
}
