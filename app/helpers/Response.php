<?php

// Standardized JSON response helper.
class Response
{
    // Send JSON payload with HTTP status, then terminate request execution.
    public static function json($status, $message, $data = [])
    {
        http_response_code($status);

        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ]);

        exit;
    }
}