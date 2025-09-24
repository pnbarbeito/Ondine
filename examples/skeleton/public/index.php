<?php
require __DIR__ . '/../vendor/autoload.php';

$app = new \Ondine\App();

// Register default auth routes from the library
\Ondine\Bootstrap::registerAuthRoutes($app, ['prefix' => '/api']);

// Example custom route
$app->get('/api/hello', function ($req) {
    return ['hello' => 'skeleton'];
});

$app->run();
