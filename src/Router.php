<?php

namespace Ondine;

class Router
{
    protected $routes = [];

    public function add($method, $path, $handler)
    {
        $this->routes[] = compact('method', 'path', 'handler');
    }

    public function match($method, $path)
    {
        $path = rtrim($path, '/');
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $routePath = rtrim($route['path'], '/');
            $pattern = preg_replace('#\{([^}]+)\}#', '([^/]+)', $routePath);
            $pattern = '/^' . str_replace('/', '\\/', $pattern) . '$/';

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                preg_match_all('#\{([^}]+)\}#', $route['path'], $keys);
                $params = [];
                if (!empty($keys[1])) {
                    foreach ($keys[1] as $i => $key) {
                        $params[$key] = $matches[$i] ?? null;
                    }
                }

                // normalize handler
                $handler = $route['handler'];
                if (is_array($handler) && count($handler) === 2) {
                    $handler = [$handler[0], $handler[1]];
                }

                return [
                    'handler' => $handler,
                    'params' => $params,
                ];
            }
        }

        return null;
    }
}
