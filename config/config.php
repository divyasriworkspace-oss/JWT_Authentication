<?php

// Load environment variables from a .env file into $_ENV.
function loadEnv($path)
{
    if (!file_exists($path)) {
        die(".env file missing");
    }
    //read full .env file lines
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {

        // Skip comment lines.
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);

        $_ENV[trim($key)] = trim($value);
    }
}

// Resolve the project root .env file from config directory.
loadEnv(__DIR__ . '/../.env');