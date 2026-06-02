<?php
declare(strict_types=1);

$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? 'index';

$routes = [
    'home' => 'HomeController',
    'login' => 'AuthController',
    'forgot-password' => 'AuthController',
    'logout' => 'AuthController',
    'change-password' => 'AuthController',
    'dashboard' => 'DashboardController',
    'memoires' => 'MemoireController',
    'admin' => 'AdminController',
    'professeur' => 'ProfesseurController',
    'etudiant' => 'EtudiantController',
];

if (!isset($routes[$page])) {
    http_response_code(404);
    echo 'Page introuvable.';
    exit;
}

$controllerName = $routes[$page];
$controller = new $controllerName();

if ($page === 'logout') {
    $controller->logout();
}

$methodMap = [
    'login' => 'login',
    'forgot-password' => 'forgotPassword',
    'change-password' => 'changePassword',
];

$method = $methodMap[$page] ?? $action;

if (!method_exists($controller, $method)) {
    http_response_code(404);
    echo 'Action introuvable.';
    exit;
}

$controller->$method();
