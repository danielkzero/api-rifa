<?php

namespace App\Application\Handlers\Api;

use Slim\App;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Application\Settings\SettingsInterface;

class Usuarios
{
    public static function registerRoutes(App $app, $validarTokenMiddleware)
    {
        $container = $app->getContainer();

        $app->group('/usuarios', function ($group) use ($container, $validarTokenMiddleware) {
            $group->get('', function (Request $request, Response $response) use ($container) {
                $pdo = $container->get(PDO::class);
                $stmt = $pdo->query("SELECT * FROM com_usuario");
                $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response->getBody()->write(json_encode($usuarios));
                return $response->withHeader('Content-Type', 'application/json');
            });

            $group->get('/{id}', function (Request $request, Response $response, array $args) use ($container) {
                $id = (int)$args['id'];
                $pdo = $container->get();
                $stmt = $pdo->prepare("SELECT * FROM com_usuario WHERE id = ?");
                $stmt->execute([$id]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$usuario) {
                    return $response->withStatus(404)->write('Usuário não encontrado');
                }

                $response->getBody()->write(json_encode($usuario));
                return $response->withHeader('Content-Type', 'application/json');
            })->add($validarTokenMiddleware);
        });
    }
}