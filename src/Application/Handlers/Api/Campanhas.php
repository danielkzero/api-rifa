<?php

namespace App\Application\Handlers\Api;

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Application\Settings\SettingsInterface;
use App\Auth\TokenValidator;

class Campanhas
{
    public static function registerRoutes(App $app, $validarTokenMiddleware)
    {
        $container = $app->getContainer();
        
        $app->group('/campanhas', function ($group) use ($container) {

            // Endpoint para listar todas as campanhas
            $group->get('', function (Request $request, Response $response) use ($container) {
                $pdo = $container->get(\PDO::class);
                $stmt = $pdo->query("SELECT * FROM com_campanha WHERE excluido = 0");
                $campanhas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                return self::json($response, $campanhas);
            });

            // Endpoint para obter uma campanha específica
            $group->get('/{id}', function (Request $request, Response $response, array $args) use ($container) {
                $id = (int)$args['id'];
                $pdo = $container->get(\PDO::class);
                $stmt = $pdo->prepare("SELECT * FROM com_campanha WHERE id = ? AND excluido = 0");
                $stmt->execute([$id]);
                $campanha = $stmt->fetch(\PDO::FETCH_ASSOC);

                if (!$campanha) {
                    return self::json($response, ['error' => 'Campanha não encontrada'], 404);
                }

                return self::json($response, $campanha);
            });

            // Endpoint para criar uma nova campanha
            $group->post('', function (Request $request, Response $response) use ($container) {
                $pdo = $container->get(\PDO::class);
                $data = $request->getParsedBody();

                // Validação básica
                if (empty($data['nome']) || empty($data['preco'])) {
                    return self::json($response, ['error' => 'Nome e preço são obrigatórios'], 400);
                }

                $stmt = $pdo->prepare("INSERT INTO com_campanha (nome, preco, slug, descricao, dados_visuais, dados_comerciais, dados_sorteio, dados_premiacao, dados_ranking, dados_meta, dados_extra) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['nome'],
                    $data['preco'],
                    $data['slug'] ?? null,
                    $data['descricao'] ?? null,
                    json_encode($data['dados_visuais'] ?? []),
                    json_encode($data['dados_comerciais'] ?? []),
                    json_encode($data['dados_sorteio'] ?? []),
                    json_encode($data['dados_premiacao'] ?? []),
                    json_encode($data['dados_ranking'] ?? []),
                    json_encode($data['dados_meta'] ?? []),
                    json_encode($data['dados_extra'] ?? [])
                ]);

                $campanhaId = $pdo->lastInsertId();
                return self::json($response, ['id' => $campanhaId], 201);
            });

            // Endpoint para atualizar uma campanha
            $group->put('/{id}', function (Request $request, Response $response, array $args) use ($container) {
                $id = (int)$args['id'];
                $pdo = $container->get(\PDO::class);
                $data = $request->getParsedBody();

                // Validação básica
                if (empty($data['nome']) || empty($data['preco'])) {
                    return self::json($response, ['error' => 'Nome e preço são obrigatórios'], 400);
                }

                $stmt = $pdo->prepare("UPDATE com_campanha SET nome = ?, preco = ?, slug = ?, descricao = ?, dados_visuais = ?, dados_comerciais = ?, dados_sorteio = ?, dados_premiacao = ?, dados_ranking = ?, dados_meta = ?, dados_extra = ? WHERE id = ? AND excluido = 0");
                $stmt->execute([
                    $data['nome'],
                    $data['preco'],
                    $data['slug'] ?? null,
                    $data['descricao'] ?? null,
                    json_encode($data['dados_visuais'] ?? []),
                    json_encode($data['dados_comerciais'] ?? []),
                    json_encode($data['dados_sorteio'] ?? []),
                    json_encode($data['dados_premiacao'] ?? []),
                    json_encode($data['dados_ranking'] ?? []),
                    json_encode($data['dados_meta'] ?? []),
                    json_encode($data['dados_extra'] ?? []),
                    $id
                ]);

                return self::json($response, ['message' => 'Campanha atualizada com sucesso']);
            });

            // Endpoint para deletar uma campanha (soft delete)
            $group->delete('/{id}', function (Request $request, Response $response, array $args) use ($container) {
                $id = (int)$args['id'];
                $pdo = $container->get(\PDO::class);
                $stmt = $pdo->prepare("UPDATE com_campanha SET excluido = 1 WHERE id = ? AND excluido = 0");
                $stmt->execute([$id]);

                if ($stmt->rowCount() === 0) {
                    return self::json($response, ['error' => 'Campanha não encontrada ou já excluída'], 404);
                }

                return self::json($response, ['message' => 'Campanha excluída com sucesso']);
            });
        });

    }

    private static function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}