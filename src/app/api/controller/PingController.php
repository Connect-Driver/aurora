<?php

namespace app\api\controller;

use infra\annotations\Controller;
use infra\annotations\DeleteMapping;
use infra\annotations\RequestMapping;
use infra\annotations\GetMapping;
use infra\annotations\PathVariable;
use infra\annotations\PostMapping;
use infra\annotations\PutMapping;
use infra\annotations\RequestBody;
use infra\annotations\RequestHeader;
use infra\annotations\RequestParam;
use infra\annotations\ResponseStatus;
use infra\http\HttpStatus;

#[Controller]
#[RequestMapping('/ping')]
class PingController
{
    public function __construct()
    {
    }

    #[GetMapping('/{id}')]
    public function ping(#[PathVariable('id')] $id)
    {
        return ['nome' => 'ping', 'id' => $id];
    }

    #[GetMapping]
    public function pong(#[RequestParam('page')] $page)
    {
        return ['nome' => 'pong', 'page' => $page];
    }

    #[PostMapping('/adicionar')]
    public function pingPost(#[RequestBody] $body) {
        return ['sucesso' => true, 'data' => $body];
    }

    #[PutMapping('/{id}')]
    public function pingPut(#[PathVariable('id')] int $id, #[RequestBody] $body, #[RequestHeader('Authorization')] $token) {
        return ['sucesso' => true, 'id' => $id, 'data' => $body, 'token' => $token];
    }

    #[DeleteMapping('/{id}')]
    #[ResponseStatus(HttpStatus::NO_CONTENT)] //arrumar a lógica do ResponseStatus para a nova arquitetura do framework
    public function pingDelete(#[PathVariable('id')] $id) {
        return ['sucesso' => true, 'id' => $id];
    }

}