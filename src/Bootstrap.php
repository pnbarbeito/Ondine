<?php

namespace Ondine;

class Bootstrap
{
    /**
     * Register auth and profile routes on an App or Router instance.
     *
     * @param mixed $appOrRouter Ondine\App or Ondine\Router
     * @param array $options Options: prefix (string) to prefix routes
     */
    public static function registerAuthRoutes($appOrRouter, array $options = [])
    {
        $prefix = rtrim($options['prefix'] ?? '', '/');

        $add = function ($method, $path, $handler) use ($appOrRouter, $prefix) {
            $fullPath = ($prefix === '') ? $path : ($prefix . $path);
            if ($appOrRouter instanceof App) {
                switch (strtoupper($method)) {
                    case 'GET':
                        $appOrRouter->get($fullPath, $handler);
                        break;
                    case 'POST':
                        $appOrRouter->post($fullPath, $handler);
                        break;
                    case 'PUT':
                        $appOrRouter->put($fullPath, $handler);
                        break;
                    case 'DELETE':
                        $appOrRouter->delete($fullPath, $handler);
                        break;
                }
            } elseif ($appOrRouter instanceof Router) {
                $appOrRouter->add(strtoupper($method), $fullPath, $handler);
            } else {
                throw new \InvalidArgumentException('Unsupported router/app instance');
            }
        };

        // Auth routes
        $add('POST', '/login', [\Ondine\Controllers\AuthController::class, 'login']);
        $add('POST', '/refresh', [\Ondine\Controllers\AuthController::class, 'refresh']);
        $add('POST', '/logout', [\Ondine\Controllers\AuthController::class, 'logout']);
        $add('GET', '/me', [\Ondine\Controllers\AuthController::class, 'me']);

        // Profiles routes
        $add('GET', '/profiles', [\Ondine\Controllers\ProfilesController::class, 'index']);
        $add('GET', '/profiles/{id}', [\Ondine\Controllers\ProfilesController::class, 'show']);
        $add('GET', '/profiles/distinct-permissions', [\Ondine\Controllers\ProfilesController::class, 'distinctPermissions']);

    // Users routes (CRUD)
        $add('GET', '/users', [\Ondine\Controllers\UsersController::class, 'index']);
        $add('GET', '/users/{id}', [\Ondine\Controllers\UsersController::class, 'show']);
        $add('POST', '/users', [\Ondine\Controllers\UsersController::class, 'store']);
        $add('PUT', '/users/{id}', [\Ondine\Controllers\UsersController::class, 'update']);
        $add('DELETE', '/users/{id}', [\Ondine\Controllers\UsersController::class, 'delete']);
    }
}
