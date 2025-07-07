<?php

namespace App\Application\Handlers\Api;

use Slim\App;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Application\Settings\SettingsInterface;
use App\Auth\TokenValidator;

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
                return self::json($response, $usuarios);
            });

            $group->get('/{id}', function (Request $request, Response $response, array $args) use ($container) {
                $id = (int)$args['id'];
                $pdo = $container->get(PDO::class);
                $stmt = $pdo->prepare("SELECT * FROM com_usuario WHERE id = ?");
                $stmt->execute([$id]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$usuario) {
                    return self::json($response, ['error' => 'Usuário não encontrado'], 404);
                }

                return self::json($response, $usuario);
            })->add($validarTokenMiddleware);

            $group->post('/auth', function (Request $request, Response $response) use ($container) 
            {
                
                $pdo = $container->get(PDO::class);

                $data = $request->getParsedBody();
                $email = $data['email'] ?? null;
                $senha = $data['senha'] ?? null;
                $cliente_id = $data['cliente_id'] ?? null;

                if (!$email || !$senha) {
                    return self::json($response, ['error' => 'Email e senha são obrigatórios'], 400);
                }

                $stmt = $pdo->prepare("SELECT * FROM com_usuario WHERE email = ? AND ativo = 1");
                $stmt->execute([$email]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$usuario || !password_verify($senha, $usuario['senha'])) {
                    return self::json($response, ['error' => 'Email ou senha inválidos'], 401);
                }

                if ($cliente_id && $usuario['cliente_id'] != $cliente_id) {
                    return self::json($response, ['error' => 'Usuário não pertence ao cliente especificado'], 403);
                }

                $stmt = $pdo->prepare("SELECT 
                    c.id,
                    c.nome AS cliente_nome,
                    cu.nivel_acesso 
                FROM com_empresa c
                JOIN com_usuario_empresa cu ON c.id = cu.empresa_id  
                WHERE cu.usuario_id = ? AND c.ativo = 1");

                $stmt->execute([$usuario['id']]);
                $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$cliente) {
                    return self::json($response, ['error' => 'Usuário não associado a nenhum cliente ativo'], 403);
                }

                $userload = [
                    'id' => $usuario['id'],
                    'email' => $usuario['email'],
                    'nome' => $usuario['nome'],
                    'cliente_id' => $cliente['id'],
                    'cliente_nome' => $cliente['cliente_nome'],
                    'nivel_acesso' => $cliente['nivel_acesso']
                ];

                $token = TokenValidator::generateJwtToken($userload);
                
                return self::json($response, [
                    'token' => $token,
                    'usuario' => $userload
                ]);
            });
        });
    }

    private static function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}