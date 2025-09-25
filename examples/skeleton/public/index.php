<?php
require __DIR__ . '/../vendor/autoload.php';

$app = new \Ondine\App();

// load .env if present (useful so Auth middleware has JWT secret)
// load project .env if present (use Composer autoloaded Env class)
if (file_exists(__DIR__ . '/../config/.env') && class_exists('\Ondine\\Env')) {
    \Ondine\Env::load(__DIR__ . '/../config/.env');
}

// Register middlewares (CORS, rate limit, auth)
$app->addMiddleware(new \Ondine\Middleware\CorsMiddleware());
$app->addMiddleware(new \Ondine\Middleware\RateLimitMiddleware(['limit' => 10, 'window' => 60]));
$app->addMiddleware(new \Ondine\Middleware\AuthMiddleware(['except' => ['/login']]));

// Register all default endpoints provided by the library (auth, profiles, users)
\Ondine\Bootstrap::registerAuthRoutes($app, ['prefix' => '/api']);

// Example custom route
$app->get('/api/hello', function ($req) {
    return ['hello' => 'skeleton'];
});

$app->get('/api/items', [\App\Controllers\ExampleController::class, 'index']);
$app->get('/api/items/{id}', [\App\Controllers\ExampleController::class, 'show']);

$app->run();
