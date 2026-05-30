<?php

// Handles authentication endpoints: register and login.
class AuthController
{
    // User model used for persistence operations.
    private $user;
    private $refreshToken; 
    // Inject database connection and initialize the model.
    public function __construct($db)
    {
        $this->user = new User($db);
        $this->refreshToken = new RefreshToken($db);
    }

    // Register a new user after validating required input fields. 
    public function register($request)
    {
        $body = $request['body'];

        if (
            empty($body['name']) ||
            empty($body['email']) ||
            empty($body['password'])
        ) {
            Response::json(400, "All fields are required");
        }

        if ($this->user->findByEmail($body['email'])) {
            Response::json(409, "Email already exists");
        }

        $hashedPassword = password_hash(
            $body['password'],
            PASSWORD_DEFAULT
        );

        $this->user->create(
            $body['name'],
            $body['email'],
            $hashedPassword
        );

        Response::json(201, "Registration successful");
    }

    // Validate credentials and return a signed JWT token.
    public function login($request)
    {
        $body = $request['body'];

        $user = $this->user->findByEmail($body['email']);

        if (!$user) {
            Response::json(401, "Invalid credentials");
        }

        if (
            !password_verify(
                $body['password'],
                $user['password']
            )
        ) {
            Response::json(401, "Invalid credentials");
        }

         $accessToken = JWT::generateAccessToken($user);

        // Check existing refresh token
        $existingToken = $this->refreshToken->getUserToken($user['id']);

        // If token exists and not expired
        if (
            $existingToken &&
            strtotime($existingToken['expires_at']) > time()
        ) {
            $refreshToken = $existingToken['token'];
        } else {

            // Generate new refresh token
            $refreshToken = JWT::generateRefreshToken($user);
            $expiresAt = date(
                'Y-m-d H:i:s',
                time() + $_ENV['REFRESH_TOKEN_EXPIRY']
            );

            // Update existing row
            if ($existingToken) {
                $this->refreshToken->updateToken(
                    $user['id'],
                    $refreshToken,
                    $expiresAt
                );
            } else {
                // First login
                $this->refreshToken->store(
                    $user['id'],
                    $refreshToken,
                    $expiresAt
                );
            }
        }
        setcookie(
            "refresh_token",
            $refreshToken,
            [
                "expires" => time() + $_ENV['REFRESH_TOKEN_EXPIRY'],
                "path" => "/",
                "secure" => false,
                "httponly" => true,
                "samesite" => "Lax"
            ]
        );

        Response::json(200, "Logged in successfully", [
            "access_token" => $accessToken
        ]);
    }
    public function refresh($request)
    {
        // Get refresh token from cookie
        $refreshToken = $_COOKIE['refresh_token'] ?? null;

        // Check token exists
        if (!$refreshToken) {

            Response::json(
                400,
                "Refresh token required"
            );

            exit;
        }

        // Verify refresh token
        $decoded = JWT::verify(
            $refreshToken,
            $_ENV['REFRESH_TOKEN_SECRET']
        );

        // Invalid token
        if (!$decoded) {

            Response::json(
                401,
                "Invalid refresh token"
            );

            exit;
        }

        // Check token type
        if ($decoded['type'] !== 'refresh') {

            Response::json(
                401,
                "Invalid token type"
            );

            exit;
        }

        // Check token exists in DB
        $storedToken =
            $this->refreshToken
            ->findValidToken($refreshToken);

        if (!$storedToken) {

            Response::json(
                401,
                "Token revoked or expired"
            );

            exit;
        }

        // Generate new access token
        $user =
            $this->user->findById(
                $decoded['user_id']
            );
        $newAccessToken =
            JWT::generateAccessToken($user);

        // Return new access token
        Response::json(
            200,
            "Access token refreshed",
            [
                "access_token" => $newAccessToken
            ]
        );
    }
    public function logout($request)
    {
        $refreshToken = $_COOKIE['refresh_token'] ?? null;

        if ($refreshToken) {
            $this->refreshToken->revoke($refreshToken);
        }

        setcookie(
            "refresh_token",
            "",
            [
                "expires" => time() - 3600,
                "path" => "/",
                "httponly" => true,
                "samesite" => "Strict"
            ]
        );

        Response::json(200, "Logged out");
    }
}
