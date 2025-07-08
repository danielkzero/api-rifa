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
            
            // Endpoint para listar todos os usuários
            $group->get('', function (Request $request, Response $response) use ($container) {
                $pdo = $container->get(PDO::class);
                $stmt = $pdo->query("SELECT 
                    id, 
                    nome, 
                    email, 
                    tipo,
                    config,
                    ativo
                FROM com_usuario");
                $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);                
                return self::json($response, $usuarios);
            })->add($validarTokenMiddleware);

            // Endpoint para obter um usuário específico
            $group->get('/{id}', function (Request $request, Response $response, array $args) use ($container) {
                $id = (int)$args['id'];
                $pdo = $container->get(PDO::class);
                $stmt = $pdo->prepare("SELECT 
                    id, 
                    nome, 
                    email, 
                    tipo,
                    config 
                FROM com_usuario WHERE id = ? AND ativo = 1");
                $stmt->execute([$id]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$usuario) {
                    return self::json($response, ['error' => 'Usuário não encontrado'], 404);
                }

                return self::json($response, $usuario);
            })->add($validarTokenMiddleware);

            // Endpoint para autenticação de usuário
            $group->post('/auth', function (Request $request, Response $response) use ($container) {
                
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
                    'nivel_acesso' => $cliente['nivel_acesso'],
                    'iv' => $usuario['iv'],
                ];

                $token = TokenValidator::generateJwtToken($userload);
                
                return self::json($response, [
                    'token' => $token,
                    'usuario' => $userload
                ]);
            });

            // Endpoint para criar um novo usuário
            $group->post('', function (Request $request, Response $response) use ($container) {
                $data = $request->getParsedBody();
                $nome = $data['nome'] ?? null;
                $email = $data['email'] ?? null;
                $senha = $data['senha'] ?? null;
                $tipo = $data['tipo'] ?? 'cliente';
                $config = json_encode($data['config'] ?? []);
                $ativo = isset($data['ativo']) ? (bool)$data['ativo'] : true;

                if (!$nome || !$email || !$senha) {
                    return self::json($response, ['error' => 'Nome, email e senha são obrigatórios'], 400);
                }

                $pdo = $container->get(PDO::class);
                $stmt = $pdo->prepare("INSERT INTO com_usuario (nome, email, senha, tipo, config, ativo) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT), $tipo, $config, $ativo]);

                return self::json($response, ['message' => 'Usuário criado com sucesso'], 201);
            })->add($validarTokenMiddleware);

            // Endpoint para atualizar um usuário
            $group->put('/{id}', function (Request $request, Response $response, array $args) use ($container) {
                $id = (int)$args['id'];
                $data = $request->getParsedBody();
                $nome = $data['nome'] ?? null;
                $email = $data['email'] ?? null;
                $senha = $data['senha'] ?? null;
                $tipo = $data['tipo'] ?? null;
                $config = json_encode($data['config'] ?? []);
                $ativo = isset($data['ativo']) ? (bool)$data['ativo'] : true;

                if (!$nome || !$email) {
                    return self::json($response, ['error' => 'Nome e email são obrigatórios'], 400);
                }

                $pdo = $container->get(PDO::class);
                $stmt = $pdo->prepare("UPDATE com_usuario SET nome = ?, email = ?, tipo = ?, config = ?, ativo = ? WHERE id = ?");
                $params = [$nome, $email, $tipo, $config, $ativo, $id];

                if ($senha) {
                    $params[2] = password_hash($senha, PASSWORD_DEFAULT); // Atualiza a senha
                }

                if ($stmt->execute($params)) {
                    return self::json($response, ['message' => 'Usuário atualizado com sucesso']);
                } else {
                    return self::json($response, ['error' => 'Erro ao atualizar usuário'], 500);
                }
            })->add($validarTokenMiddleware);

            // Endpoint para deletar um usuário
            $group->delete('/{id}', function (Request $request, Response $response, array $args) use ($container) {
                $id = (int)$args['id'];
                $pdo = $container->get(PDO::class);
                $stmt = $pdo->prepare("UPDATE com_usuario SET ativo = 0 WHERE id = ?");
                if ($stmt->execute([$id])) {
                    return self::json($response, ['message' => 'Usuário deletado com sucesso']);
                } else {
                    return self::json($response, ['error' => 'Erro ao deletar usuário'], 500);
                }
            })->add($validarTokenMiddleware);
        });
    }

    private static function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}