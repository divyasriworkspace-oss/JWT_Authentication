<?php

// Handles authentication endpoints: register and login.
class AuthController
{
    // User model used for persistence operations.
    private $user;

    // Inject database connection and initialize the model.
    public function __construct($db)
    {
        $this->user = new User($db);
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

        $token = JWT::generate([
            "user_id" => $user['id'],
            "email" => $user['email']
        ]);

        Response::json(200, "Login successful", [
            "token" => $token,
            "expires_in" => $_ENV['JWT_EXPIRY']
        ]);
    }
}