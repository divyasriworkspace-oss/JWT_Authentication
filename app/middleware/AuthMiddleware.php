<?php

// Protects routes by validating Bearer JWT tokens.
class AuthMiddleware
{
    // Validate Authorization header and attach decoded user payload to request.
    public static function handle(&$request)
    {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            Response::json(401, "Authorization header missing");
        }

        if (
            !preg_match(
                '/Bearer\s(\S+)/',
                $headers['Authorization'],
                $matches
            )
        ) {
            Response::json(401, "Invalid authorization format");
        }

        $token = $matches[1];

    // Decode and validate signature + expiry.
        $decoded = JWT::verify($token, $_ENV['ACCESS_TOKEN_SECRET']);

        if (!$decoded) {
            Response::json(401, "Invalid or expired token");
            exit;
        }
        if ($decoded['type'] !== 'access') {
            Response::json(401, "Invalid token type");
            exit;
        }
        $request['user'] = $decoded;
    }
}
