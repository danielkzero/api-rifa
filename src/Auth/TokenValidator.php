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
    public static function validateToken(string $token, string $secretKey): array
    {
        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            return (array) $decoded;
        } catch (ExpiredException $e) {
            throw new Exception('Token has expired', 401);
        } catch (SignatureInvalidException $e) {
            throw new Exception('Invalid token signature', 401);
        } catch (BeforeValidException $e) {
            throw new Exception('Token is not valid yet', 401);
        } catch (Exception $e) {
            throw new Exception('Token validation failed: ' . $e->getMessage(), 401);
        }
    }
}