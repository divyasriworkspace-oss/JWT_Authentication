<?php

// Utility for generating and verifying HS256 JWT tokens.
class JWT
{
     public static function generateAccessToken($user)
    {
        $payload = [
            "user_id" => $user['id'],
            "email" => $user['email'],
            "type" => "access",
            "exp" => time() + $_ENV['ACCESS_TOKEN_EXPIRY']
        ];

        return self::generateToken(
            $payload,
            $_ENV['ACCESS_TOKEN_SECRET']
        );
    }

    public static function generateRefreshToken($user)
    {
        $payload = [
            "user_id" => $user['id'],
            "type" => "refresh",
            "exp" => time() + $_ENV['REFRESH_TOKEN_EXPIRY']
        ];

        return self::generateToken(
            $payload,
            $_ENV['REFRESH_TOKEN_SECRET']
        );
    }

    private static function generateToken($payload, $secret)
    {
        $header = [
            "alg" => "HS256",
            "typ" => "JWT"
        ];

        $headerEncoded = self::base64UrlEncode(json_encode($header));

        $payloadEncoded =self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            "$headerEncoded.$payloadEncoded",
            $secret,
            true
        );

        $signatureEncoded =self::base64UrlEncode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    // Validate signature and expiry, then return decoded payload.
    public static function verify($token, $secret)
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
                $secret,
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
