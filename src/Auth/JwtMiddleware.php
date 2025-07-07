<?php

namespace App\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;
use App\Auth\TokenValidator;

class JwtMiddleware
{
    private string $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);

        if (!$token) {
            return $this->unauthorized();
        }

        try {
            $decoded = TokenValidor::validateToken($token, $this->secretKey);
            $request = $request->withAttribute('user', $decoded);
            return $handler->handle($request);
        } catch (\Exception $e) {
            return $this->unauthorized($e->getMessage());
        }
    }

    private function unauthorized(string $message = 'Token inválido'): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response->withStatus(401)
                        ->withHeader('Content-Type', 'application/json');
    }
}