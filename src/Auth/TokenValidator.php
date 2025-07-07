<?php

namespace App\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Exception;

class TokenValidator
{
    public static function validateToken($request): array
    {
        global $container;

        if (!$request->hasHeader('Authorization')) {
            throw new Exception('Token não fornecido', 401);
        }

        $token = $request->getHeaderLine('Authorization');
        $jwt_token = str_replace('Bearer ', '', $token);
        $settings = $container->get(\App\Application\Settings\SettingsInterface::class);
        $secretKey = $settings->get('secret_key');

        $decoded = self::validateJwtToken($jwt_token, $secretKey);
        return $decoded;
    }

    public static function generateJwtToken(array $usuario): string
    {
        global $container;

        $settings = $container->get(\App\Application\Settings\SettingsInterface::class);
        $secretKey = $settings->get('secret_key');

        if (empty($secretKey)) {
            throw new Exception('Chave secreta não configurada', 500);
        }

        $iat = time(); // Data de emissão
        $exp = $iat + 3600; // Expira em 1 hora

        $payload = [
            'iat' => $iat,
            'exp' => $exp,
            'id' => $usuario['id'],
            'email' => $usuario['email'],
            'nome' => $usuario['nome'],
            'cliente_id' => $usuario['cliente_id'],
            'cliente_nome' => $usuario['cliente_nome'],
            'nivel_acesso' => $usuario['nivel_acesso']
        ];

        return JWT::encode($payload, $secretKey, 'HS256');
    }

    private static function validateJwtToken(string $jwt_token, string $secretKey): array
    {
        try {
            $decoded = JWT::decode($jwt_token, new Key($secretKey, 'HS256'));
            return (array) $decoded;
        } catch (ExpiredException $e) {
            throw new Exception('Token expirado', 401);
        } catch (SignatureInvalidException $e) {
            throw new Exception('Assinatura inválida do token', 401);
        } catch (BeforeValidException $e) {
            throw new Exception('Token não está pronto para ser usado', 401);
        } catch (Exception $e) {
            throw new Exception('Erro ao decodificar o token: ' . $e->getMessage(), 401);
        }
    }
}