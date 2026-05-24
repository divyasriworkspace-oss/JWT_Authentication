<?php

// Utility for generating and verifying HS256 JWT tokens.
class JWT
{
    // Build and sign a JWT from payload claims.
    public static function generate($payload)
    {
        $header = [
            "typ" => "JWT",
            "alg" => "HS256"
        ];

        $payload['iat'] = time();
        $payload['exp'] = time() + (int)$_ENV['JWT_EXPIRY'];

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            "$headerEncoded.$payloadEncoded",
            $_ENV['JWT_SECRET'],
            true
        );

        $signatureEncoded = self::base64UrlEncode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    // Validate signature and expiry, then return decoded payload.
    public static function verify($token)
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        list($header, $payload, $signature) = $parts;

        // Recompute signature and compare in constant time.
        $validSignature = self::base64UrlEncode(
            hash_hmac(
                'sha256',
                "$header.$payload",
                $_ENV['JWT_SECRET'],
                true
            )
        );

        if (!hash_equals($validSignature, $signature)) {
            return false;
        }

        $payloadData = json_decode(
            self::base64UrlDecode($payload),
            true
        );

        if (!$payloadData || $payloadData['exp'] < time()) {
            return false;
        }

        return $payloadData;
    }

    // Encode bytes into URL-safe base64 without padding.
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // Decode URL-safe base64 back into raw bytes.
    private static function base64UrlDecode($data)
    {
        return base64_decode(
            str_pad(
                strtr($data, '-_', '+/'),
                strlen($data) % 4,
                '=',
                STR_PAD_RIGHT
            )
        );
    }
}