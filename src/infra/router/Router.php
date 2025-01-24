<?php

namespace infra\router;

use ReflectionClass;
use ReflectionMethod;
use Exception;

use infra\http\HttpStatus;

use infra\annotations\Controller;
use infra\annotations\RequestMapping;
use infra\annotations\GetMapping;
use infra\annotations\PostMapping;
use infra\annotations\PutMapping;
use infra\annotations\DeleteMapping;
use infra\annotations\ResponseStatus;
use infra\annotations\PathVariable;
use infra\annotations\RequestParam;
use infra\annotations\RequestBody;
use infra\annotations\RequestHeader;

class Router
{
    public function start(): Response|string|array|null
    {
        return $this->processUrl();
    }

    private function processUrl()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestData = explode("/", trim($uri, '/'));

        $controller = $this->getControllerByRequestMapping($requestData[0]);

        if (!$controller) {
            throw new Exception('Controlador não encontrado.', HttpStatus::NOT_FOUND->value);
        }

        $refClass = new ReflectionClass($controller);

        $controllerAttributes = $refClass->getAttributes(Controller::class);
        if (empty($controllerAttributes)) {
            throw new Exception('Controlador não possui o atributo necessário: Controller.', HttpStatus::NOT_FOUND->value);
        }

        $httpMethod = $_SERVER['REQUEST_METHOD'];

        foreach ($refClass->getMethods() as $method) {
            $attributes = $method->getAttributes();

            foreach ($attributes as $attribute) {
                $mapping = $this->getMappingForMethod($attribute, $httpMethod);

                if ($mapping) {
                    $fullPath = $mapping->path ?? '/';

                    $matches = [];

                    if ($this->matchPath($fullPath, $requestData, $matches)) {
                        $params = $this->resolveMethodParams($method, $matches);

                        $response = $method->invoke($controller, ...$params);

                        $responseStatusAttribute = $method->getAttributes(ResponseStatus::class);
                        if (!empty($responseStatusAttribute)) {
                            $statusCode = $responseStatusAttribute[0]->newInstance()->status->value;
                            http_response_code($statusCode);
                        } else {
                            http_response_code(HttpStatus::OK->value);
                        }

                        return $response;
                    }
                }
            }
        }

        throw new Exception('Método HTTP não permitido para esta rota.', HttpStatus::METHOD_NOT_ALLOWED->value);
    }

    private function getMappingForMethod($attribute, $httpMethod)
    {
        $attributeName = $attribute->getName();

        switch ($httpMethod) {
            case 'GET':
                if ($attributeName == GetMapping::class) {
                    return $attribute->newInstance();
                }
                break;
            case 'POST':
                if ($attributeName == PostMapping::class) {
                    return $attribute->newInstance();
                }
                break;
            case 'PUT':
                if ($attributeName == PutMapping::class) {
                    return $attribute->newInstance();
                }
                break;
            case 'DELETE':
                if ($attributeName == DeleteMapping::class) {
                    return $attribute->newInstance();
                }
                break;
            case 'OPTIONS':
                http_response_code(HttpStatus::OK->value);
                (new Response())->send('');
                break;
            default:
                throw new Exception('Método HTTP não suportado.', HttpStatus::METHOD_NOT_ALLOWED->value);
        }

        return null;
    }

    private function getControllerByRequestMapping($firstSegment)
    {
        try {
            $controllers = glob(__DIR__ . '/../../app/api/controller/*.php');
            
            foreach ($controllers as $controllerFile) {
                include_once $controllerFile;
                $controllerClass = basename($controllerFile, '.php');
                $controllerClass = 'app\api\controller\\' . ucfirst($controllerClass);
                $refClass = new ReflectionClass($controllerClass);
    
                $controllerAttributes = $refClass->getAttributes(Controller::class);
                if (empty($controllerAttributes)) {
                    continue;
                }
    
                $attributes = $refClass->getAttributes(RequestMapping::class);
                if (!empty($attributes)) {
                    $basePath = $attributes[0]->newInstance()->basePath;
                    if (trim($basePath, '/') === $firstSegment) {
                        return $refClass->newInstance();
                    }
                }
            }
    
            return null;
        }
        catch(Exception $e) {
            throw new Exception($e->getMessage(), HttpStatus::BAD_REQUEST->value);
        }
    }

    private function matchPath($fullPath, $requestData, &$matches)
    {
        $regexPath = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_]+)', $fullPath);
        $currentPath = '/' . implode('/', array_slice($requestData, 1));
        return preg_match("#^$regexPath$#", $currentPath, $matches);
    }

    private function resolveMethodParams(ReflectionMethod $method, array $matches)
    {
        $params = [];
        $queryParams = $_GET;

        foreach ($method->getParameters() as $param) {
            $paramAttributes = $param->getAttributes();

            foreach ($paramAttributes as $attribute) {
                // PathVariable
                $_ENV['PATH'] = $matches;
                if ($attribute->getName() === PathVariable::class) {
                    $pathVarName = $attribute->newInstance()->name;
                    $params[] = $matches[$pathVarName] ?? null;
                }
                // RequestParam
                elseif ($attribute->getName() === RequestParam::class) {
                    $queryParamName = $attribute->newInstance()->name;
                    $params[] = $queryParams[$queryParamName] ?? null;
                }
                // RequestBody
                elseif ($attribute->getName() === RequestBody::class) {
                    $body = json_decode(file_get_contents('php://input'), true);
                    $params[] = $body;
                }
                // RequestHeader
                elseif ($attribute->getName() === RequestHeader::class) {
                    $headerName = $attribute->newInstance()->headerName;
                    $params[] = $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $headerName))] ?? null;
                }
            }
        }

        return $params;
    }
}