<?php

namespace app\api\controller;

use infra\annotations\Controller;
use infra\annotations\RequestMapping;
use infra\annotations\GetMapping;

#[Controller]
#[RequestMapping('/ping')]
class PingController
{
    public function __construct()
    {
    }

    #[GetMapping]
    public function ping()
    {
        return 'pong';
    }
}