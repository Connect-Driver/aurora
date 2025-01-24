<?php

namespace infra\security;

use Exception;

use infra\http\HttpStatus;

class JWT
{
    public static function encode(array $payload)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => JWT_ALGORITHM]);
        $payload['exp'] = time() + JWT_EXPIRATION;

        $header = self::base64UrlEncode($header);
        $payload = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
        $signature = self::base64UrlEncode($signature);

        return "$header.$payload.$signature";
    }

    private static function decode($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$header, $payload, $signature] = $parts;

        $header = json_decode(self::base64UrlDecode($header), true);
        $payload = json_decode(self::base64UrlDecode($payload), true);

        if (isset($payload['exp']) && time() >= $payload['exp']) {
            return false;
        }

        $validSignature = hash_hmac('sha256', "$parts[0].$parts[1]", JWT_SECRET, true);
        $validSignature = self::base64UrlEncode($validSignature);

        if (!hash_equals($validSignature, $signature)) {
            return false;
        }

        return $payload;
    }

    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private static function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function validateToken($authHeader)
    {
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            throw new Exception('Token não fornecido ou inválido', HttpStatus::UNAUTHORIZED->value);
        }

        $token = str_replace('Bearer ', '', $authHeader);
        $payload = self::decode($token);

        if (!$payload) {
            throw new Exception('Token inválido ou expirado', HttpStatus::UNAUTHORIZED->value);
        }

        return $payload;
    }
}
