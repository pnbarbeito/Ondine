<?php

namespace Ondine;

class App
{
    protected $router;
    protected $middleware = [];

    public function __construct()
    {
        $this->router = new Router();
    }

    public function addMiddleware($middleware)
    {
        $this->middleware[] = $middleware;
    }

    public function get($path, $handler)
    {
        $this->router->add('GET', $path, $handler);
    }

    public function post($path, $handler)
    {
        $this->router->add('POST', $path, $handler);
    }

    public function put($path, $handler)
    {
        $this->router->add('PUT', $path, $handler);
    }

    public function delete($path, $handler)
    {
        $this->router->add('DELETE', $path, $handler);
    }

    public function run()
    {
        $request = Request::fromGlobals();

        // run middleware (simple sequential). If a middleware returns a Response, send and exit.
        foreach ($this->middleware as $m) {
            $out = null;
            if (is_callable($m)) {
                $out = $m($request);
            } elseif (is_object($m) && method_exists($m, 'handle')) {
                $out = $m->handle($request);
            }

            if ($out instanceof Response) {
                // merge any headers set by previous middleware (e.g. CORS) stored on the request
                if (isset($request->attributes['cors_headers']) && is_array($request->attributes['cors_headers'])) {
                    foreach ($request->attributes['cors_headers'] as $k => $v) {
                        $out->setHeader($k, $v);
                    }
                }
                $out->send();
                return; // stop processing
            }
        }

        try {
            $route = $this->router->match($request->method, $request->path);

            if (!$route) {
                $response = new Response(404, ['error' => true, 'message' => 'Not Found']);
                $response->send();
                return;
            }

            $handler = $route['handler'];

            if (is_array($handler) && count($handler) === 2 && is_string($handler[0]) && class_exists($handler[0])) {
                $controllerClass = $handler[0];
                $method = $handler[1];
                $controllerInstance = new $controllerClass();
                $result = call_user_func([$controllerInstance, $method], $request, $route['params']);
            } elseif (is_callable($handler)) {
                // closure or other callable (including [object, method])
                $result = call_user_func($handler, $request, $route['params']);
            } else {
                throw new \RuntimeException('Invalid handler');
            }

            if ($result instanceof Response) {
                $respToSend = $result;
            } else {
                $respToSend = new Response(200, $result);
            }

            // apply any headers set by middleware (e.g. CORS) attached to the request
            if (isset($request->attributes['cors_headers']) && is_array($request->attributes['cors_headers'])) {
                foreach ($request->attributes['cors_headers'] as $k => $v) {
                    $respToSend->setHeader($k, $v);
                }
            }

            $respToSend->send();
        } catch (\Throwable $e) {
            $status = $e instanceof \InvalidArgumentException ? 400 : 500;
            $resp = new Response($status, ['error' => true, 'message' => $e->getMessage()]);
            $resp->send();
        }
    }
}
