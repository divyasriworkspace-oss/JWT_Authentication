<?php

// Load environment variables.
require_once "../config/config.php";

// Load helper utilities.
require_once "../app/helpers/Response.php";
require_once "../app/helpers/JWT.php";

// Load framework/core components.
require_once "../app/core/Database.php";
require_once "../app/core/Router.php";

// Load data models.
require_once "../app/models/User.php";
require_once "../app/models/Patient.php";
require_once "../app/models/RefreshToken.php";


// Load controllers.
require_once "../app/controllers/AuthController.php";
require_once "../app/controllers/PatientController.php";

// Load middleware.
require_once "../app/middleware/JsonMiddleware.php";
require_once "../app/middleware/AuthMiddleware.php";

// Bootstrap shared services and request context.
$db = (new Database())->connect();

$request = JsonMiddleware::handle();

$router = new Router();

$authController = new AuthController($db);
$patientController = new PatientController($db);

// Public auth routes.
$router->add('POST', '/api/register', function () use ($authController,$request)
{
    $authController->register($request);
});

$router->add('POST', '/api/login', function () use ($authController,$request)
{
    $authController->login($request);
});

// Refresh Access Token
$router->add( 'POST','/api/refresh',function () use ($authController, $request)
{
    $authController->refresh($request);
});

// Logout
$router->add('POST','/api/logout',function () use ($authController, $request)
{
    $authController->logout($request);
});

// Protected patient routes.
$router->add('GET', '/api/patients', function () use ($patientController, &$request)
{
    AuthMiddleware::handle($request);
    $patientController->index($request);
});

$router->add('POST', '/api/patients', function () use ( $patientController, &$request)
{
    AuthMiddleware::handle($request);
    $patientController->store($request);
});

$router->add('PUT', '/api/patients/{id}', function ($id) use ($patientController, &$request) 
{
    AuthMiddleware::handle($request);
    $patientController->update($id, $request);
});

$router->add('DELETE', '/api/patients/{id}', function ($id) use ($patientController, &$request) 
{
    AuthMiddleware::handle($request);
    $patientController->delete($id,$request);
});

// Normalize URI by removing project base path.
$uri = parse_url(
    $_SERVER['REQUEST_URI'],
    PHP_URL_PATH
);

$basePath = '/JWT_Token';

$uri = str_replace($basePath, '', $uri);

// Dispatch incoming request.
$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $uri
);
