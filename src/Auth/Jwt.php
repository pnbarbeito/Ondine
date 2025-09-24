<?php

namespace Ondine\Auth;

class Jwt
{
    public static function encode(array $payload, string $secret, int $ttl = 3600)
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $now = time();
        $payload = array_merge(['iat' => $now, 'exp' => $now + $ttl], $payload);

        $base64UrlEncode = function ($data) {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        };

        $segments = [];
        $segments[] = $base64UrlEncode(json_encode($header));
        $segments[] = $base64UrlEncode(json_encode($payload));

        $signingInput = implode('.', $segments);
        $signature = hash_hmac('sha256', $signingInput, $secret, true);
        $segments[] = $base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public static function decode(string $jwt, string $secret)
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }

        [$headb64, $bodyb64, $cryptob64] = $parts;
        $base64UrlDecode = function ($b64) {
            $b64 = strtr($b64, '-_', '+/');
            return base64_decode($b64);
        };

        $header = json_decode($base64UrlDecode($headb64), true);
        $payload = json_decode($base64UrlDecode($bodyb64), true);
        $signature = $base64UrlDecode($cryptob64);

        $signingInput = $headb64 . '.' . $bodyb64;
        $expected = hash_hmac('sha256', $signingInput, $secret, true);

        if (!hash_equals($expected, $signature)) {
            return null;
        }
        if (isset($payload['exp']) && time() > $payload['exp']) {
            return null;
        }

        return $payload;
    }
}
