<?php

// Parses JSON request bodies and enforces API content-type rules.
class JsonMiddleware
{
    // Return normalized request data used by controllers.
    public static function handle()
    {
        header("Content-Type: application/json");

        $method = $_SERVER['REQUEST_METHOD'];
         
        $uri = parse_url(
        $_SERVER['REQUEST_URI'],
        PHP_URL_PATH
        );

        $request = [];

        // Only methods that typically carry a body are validated and parsed.
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {

             // Skip body validation for refresh
            if ($uri === '/JWT_Token/api/refresh' || $uri=== '/JWT_Token/api/logout') {
                return [];
            }
            
            if (
                !isset($_SERVER['CONTENT_TYPE']) ||
                strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false
            ) {
                Response::json(415, "Content-Type must be application/json");
            }
            //Read raw input
            $input = file_get_contents("php://input");

            if (empty($input)) {
                Response::json(400, "Request body cannot be empty");
            }

            $decoded = json_decode($input, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Response::json(400, "Invalid JSON payload");
            }

            $request['body'] = $decoded;
        }

        return $request;
    }
}
