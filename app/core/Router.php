<?php

// Minimal router supporting static and {id}-style dynamic routes.
class Router
{
    // Registered route definitions.
    private $routes = [];

    // Register a route with HTTP method, URI pattern, and callback.
    public function add($method, $route, $callback)
    {
        $this->routes[] = [
            "method" => $method,
            "route" => $route,
            "callback" => $callback
        ];
    }

    // Match incoming request and execute corresponding callback.
    public function dispatch($method, $uri)
    {
        foreach ($this->routes as $route) {

            // Convert placeholders like /patients/{id} to a numeric capture group.
            $pattern = preg_replace(
                '#\{[a-zA-Z]+\}#',
                '([0-9]+)',
                $route['route']
            );

            // Anchor the regex to enforce full-path matching.
            $pattern = "#^$pattern$#";

            if (
                $method === $route['method'] &&
                preg_match($pattern, $uri, $matches)
            ) {

                // Remove full match and keep only captured parameters.
                array_shift($matches);

                call_user_func_array(
                    $route['callback'],
                    $matches
                );

                return;
            }
        }

        Response::json(404, "Route not found");
    }
}