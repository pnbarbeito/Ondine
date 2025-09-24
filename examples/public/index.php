<?php
// Prefer composer autoload if available
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../src/autoload.php';
}

use Ondine\App;

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$pathOnly = parse_url($requestUri, PHP_URL_PATH);
if ($pathOnly === '/' || $pathOnly === '/index.html') {
    $file = __DIR__ . '/index.html';
    if (file_exists($file)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($file);
        exit;
    }
}

$app = new App();

// Register CORS middleware
$app->addMiddleware(new Ondine\Middleware\CorsMiddleware());

// load .env if present (do this before middleware that needs JWT_SECRET)
if (file_exists(__DIR__ . '/../../config/.env')) {
    require_once __DIR__ . '/../../src/Env.php';
    \Env::load(__DIR__ . '/../../config/.env');
}

// Rate limit middleware (simple file-based limiter) - applies to /api/login
$app->addMiddleware(new Ondine\Middleware\RateLimitMiddleware(['limit' => 10, 'window' => 60]));

// Auth middleware (protect all endpoints except /login)
$app->addMiddleware(new Ondine\Middleware\AuthMiddleware(['except' => ['/login']]));

// API routes
$app->get('/api/users', [\Ondine\Controllers\UsersController::class, 'index']);
$app->get('/api/users/{id}', [\Ondine\Controllers\UsersController::class, 'show']);
$app->post('/api/users', [\Ondine\Controllers\UsersController::class, 'store']);
$app->put('/api/users/{id}', [\Ondine\Controllers\UsersController::class, 'update']);
$app->delete('/api/users/{id}', [\Ondine\Controllers\UsersController::class, 'delete']);

$app->get('/api/profiles', [\Ondine\Controllers\ProfilesController::class, 'index']);
$app->get('/api/profiles/{id}', [\Ondine\Controllers\ProfilesController::class, 'show']);

$app->post('/api/login', [\Ondine\Controllers\AuthController::class, 'login']);
$app->get('/api/me', [\Ondine\Controllers\AuthController::class, 'me']);
$app->post('/api/token/refresh', [\Ondine\Controllers\AuthController::class, 'refresh']);
$app->post('/api/logout', [\Ondine\Controllers\AuthController::class, 'logout']);

$app->run();
