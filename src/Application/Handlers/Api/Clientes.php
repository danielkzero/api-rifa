<?php

namespace App\Application\Handlers\Api;

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Application\Settings\SettingsInterface;
use App\Auth\TokenValidator;

class Clientes
{
    public static function registerRoutes(App $app, $validarTokenMiddleware)
    {
        $container = $app->getContainer();

        $app->group('/clientes', function ($group) use ($container, $validarTokenMiddleware) {
            // Endpoint para listar todos os clientes
            $group->get('', function (Request $request, Response $response) use ($container) {
                $pdo = $container->get(\PDO::class);
                $stmt = $pdo->query("SELECT * FROM com_cliente WHERE ativo = 1");
                $clientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return self::json($response, $clientes);
            })->add($validarTokenMiddleware);

            // Endpoint para obter um cliente específico
            $group->get('/{id}', function (Request $request, Response $response, array $args) use ($container) {
                $id = (int)$args['id'];
                $pdo = $container->get(\PDO::class);
                $stmt = $pdo->prepare("SELECT * FROM com_cliente WHERE id = ? AND ativo = 1");
                $stmt->execute([$id]);
                $cliente = $stmt->fetch(\PDO::FETCH_ASSOC);

                if (!$cliente) {
                    return self::json($response, ['error' => 'Cliente não encontrado'], 404);
                }

                return self::json($response, $cliente);
            })->add($validarTokenMiddleware);

            // Endpoint para criar um novo cliente
            $group->post('', function (Request $request, Response $response) use ($container) {
                $pdo = $container->get(\PDO::class);
                $data = $request->getParsedBody();

                // Validação básica
                if (empty($data['nome']) || empty($data['telefone'])) {
                    return self::json($response, ['error' => 'Nome e telefone são obrigatórios'], 400);
                }

                // Verifica se o email já está cadastrado
                if (!empty($data['email'])) {
                    // Verifica se o email já está cadastrado
                    $stmt = $pdo->prepare("SELECT * FROM com_cliente WHERE email = ? AND id != ?");
                    $stmt->execute([$data['email'], $id]);
                    if ($stmt->fetch()) {
                        return self::json($response, ['error' => 'Email já cadastrado'], 400);
                    }
                }
                
                // verifica se o telefone já está cadastrado
                if (!empty($data['telefone'])) {
                    $stmt = $pdo->prepare("SELECT * FROM com_cliente WHERE telefone = ? AND id != ?");
                    $stmt->execute([$data['telefone'], $id]);
                    if ($stmt->fetch()) {
                        return self::json($response, ['error' => 'Telefone já cadastrado'], 400);
                    }
                }

                // Verifica se o CPF já está cadastrado
                if (!empty($data['cpf'])) {
                    $stmt = $pdo->prepare("SELECT * FROM com_cliente WHERE cpf = ? AND id != ?");
                    $stmt->execute([$data['cpf'], $id]);
                    if ($stmt->fetch()) {
                        return self::json($response, ['error' => 'CPF já cadastrado'], 400);
                    }
                }

                // gera iv para criptografia
                $iv = openssl_random_pseudo_bytes(16);

                // Criptografa a senha
                $senhaHash = null;
                if (!empty($data['senha'])) {
                    $senhaHash = password_hash($data['senha'], PASSWORD_BCRYPT);
                }                

                $stmt = $pdo->prepare("INSERT INTO com_cliente (nome, sobrenome, telefone, email, senha, iv, cpf, cep, endereco, numero, bairro, complemento, estado, cidade, ponto_referencia, afiliado, ativo, verificado, banido) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['nome'],
                    $data['sobrenome'] ?? null,
                    $data['telefone'],
                    $data['email'] ?? null,
                    $senhaHash,
                    $iv,
                    $data['cpf'] ?? null,
                    $data['cep'] ?? null,
                    $data['endereco'] ?? null,
                    $data['numero'] ?? null,
                    $data['bairro'] ?? null,
                    $data['complemento'] ?? null,
                    $data['estado'] ?? null,
                    $data['cidade'] ?? null,
                    $data['ponto_referencia'] ?? null,
                    $data['filiado'] ?? null, 
                    $data['ativo'] ?? null, 
                    $data['verificado'] ?? null, 
                    $data['banido'] ?? null
                ]);

                return self::json($response, ['message' => 'Cliente cadastrado com sucesso']);
            })->add($validarTokenMiddleware);

            //  Endpoint para atualizar um cliente específico
            $group->put('/{id}', function (Request $request, Response $response) use ($container){
                $id = (int)$args['id'];
                $data = $request->getParsedBody();
                $pdo = $container->get(\PDO::class);


                // Validação básica
                if (empty($data['nome']) || empty($data['telefone'])) {
                    return self::json($response, ['error' => 'Nome e telefone são obrigatórios'], 400);
                }

                // Verifica se o email já está cadastrado
                if (!empty($data['email'])) {
                    // Verifica se o email já está cadastrado
                    $stmt = $pdo->prepare("SELECT * FROM com_cliente WHERE email = ?");
                    $stmt->execute([$data['email']]);
                    if ($stmt->fetch()) {
                        return self::json($response, ['error' => 'Email já cadastrado'], 400);
                    }
                }
                
                // verifica se o telefone já está cadastrado
                if (!empty($data['telefone'])) {
                    $stmt = $pdo->prepare("SELECT * FROM com_cliente WHERE telefone = ?");
                    $stmt->execute([$data['telefone']]);
                    if ($stmt->fetch()) {
                        return self::json($response, ['error' => 'Telefone já cadastrado'], 400);
                    }
                }

                // Verifica se o CPF já está cadastrado
                if (!empty($data['cpf'])) {
                    $stmt = $pdo->prepare("SELECT * FROM com_cliente WHERE cpf = ?");
                    $stmt->execute([$data['cpf']]);
                    if ($stmt->fetch()) {
                        return self::json($response, ['error' => 'CPF já cadastrado'], 400);
                    }
                }

                // gera iv para criptografia
                $iv = openssl_random_pseudo_bytes(16);

                // Criptografa a senha
                $senhaHash = null;
                if (!empty($data['senha'])) {
                    $senhaHash = password_hash($data['senha'], PASSWORD_BCRYPT);
                }


                $stmt = $pdo->prepare("UPDATE com_cliente SET nome = ?, sobrenome = ?, telefone = ?, email = ?, senha = ?, avatar = ?, cpf = ?, cep = ?, endereco = ?, numero = ?, bairro = ?, complemento = ?, estado = ?, cidade = ?, ponto_referencia = ?, afiliado = ?, ativo = ?, verificado = ?, banido = ? WHERE id = ?");
                $stmt->execute([
                    $data['nome'],
                    $data['sobrenome'] ?? null,
                    $data['telefone'],
                    $data['email'] ?? null,
                    $senhaHash,
                    $iv,
                    $data['cpf'] ?? null,
                    $data['cep'] ?? null,
                    $data['endereco'] ?? null,
                    $data['numero'] ?? null,
                    $data['bairro'] ?? null,
                    $data['complemento'] ?? null,
                    $data['estado'] ?? null,
                    $data['cidade'] ?? null,
                    $data['ponto_referencia'] ?? null
                ]);
            })->add($validarTokenMiddleware);

            // Endpoint para exclusão de um cliente específico
            $group->delete('/{id}', function (Request $request, Response $response) use ($container) {
                $id = (int)$args['id'];
                $pdo = $container(\PDO::class);
                $stmt = $pdo->prepare("DELETE FROM com_cliente WHERE id = ? AND excluido = 0");
                $stmt->execute([$id]);

                if ($stmt->rowCount() === 0) {
                    return self::json($response, ['message' => 'Cliente não encontrado ou já excluído'], 404);
                }

                return self::json($response, ['message' => 'Cliente excluído com sucesso']);
            })->add($validarTokenMiddleware);
        });
    }

    private static function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}