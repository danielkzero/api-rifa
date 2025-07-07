<?php

namespace App\Application\Handlers\Api;

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Online
{
    public static function registerRoutes(App $app)
    {
        $container = $app->getContainer();

        $app->get('', function (Request $request, Response $response) use ($container) {
            $response->getBody()->write(self::apresentacao());
            return $response->withHeader('Content-Type', 'text/html');
        });
        $app->get('/', function (Request $request, Response $response) use ($container) {
            $response->getBody()->write(self::apresentacao());
            return $response->withHeader('Content-Type', 'text/html');
        });
    }

    private static function apresentacao()
    {
        return <<<HTML
        <h1>API Rifa</h1>
        <p>Bem-vindo à API Rifa! Esta API permite que você interaja com o sistema de rifas.</p>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                color: #333;
                padding: 20px;
            }
            h1 {
                color: #2c3e50;
            }
            p {
                font-size: 16px;
            }
        </style>
        HTML;
    }
}