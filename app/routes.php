<?php

declare(strict_types=1);


use Slim\App;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\Exception\HttpUnauthorizedException;

use App\Application\Handlers\Api\Online;
use App\Application\Handlers\Api\Usuarios;
use App\Application\Handlers\Api\Campanhas;
use App\Auth\TokenValidator;


$app->add(function ($request, $handler) {
    // Middleware to handle CORS
    $response = $handler->handle($request);
    return $response->withHeader('Access-Control-Allow-Origin', '*')
                         ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                         ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

$validerTokenMiddleware = function ($request, $handler) {
    try {
        TokenValidator::validateToken($request);
    } catch (Exception $e) {
        throw new HttpUnauthorizedException($request, $e->getMessage());
    }
    return $handler->handle($request);
};

return function (App $app) use ($validerTokenMiddleware) {
    // Registrando as rotas da API

    // Rota de apresentação
    Online::registerRoutes($app);

    // Rotas de usuários
    Usuarios::registerRoutes($app, $validerTokenMiddleware);

    // Rotas de campanhas
    Campanhas::registerRoutes($app, $validerTokenMiddleware);

    // Middleware para CORS Pre-Flight OPTIONS Request
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });
};
