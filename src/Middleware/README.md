# Ondine - Middleware module

This README documents the Middleware module of Ondine: what a middleware is, included middleware implementations, how to write/register/test middleware, and design recommendations.

## Purpose

The middleware system provides a uniform way to intercept and process requests before and/or after they reach controllers. It enables:

- Applying cross-cutting policies (CORS, authentication, rate-limiting).
- Adding logging, transformations of request/response, caching.
- Composing reusable behaviors in the application's execution pipeline.

## Included middleware

- `Ondine\Middleware\CorsMiddleware` — adds basic CORS headers to allow requests from the frontend.
- `Ondine\Middleware\AuthMiddleware` — validates JWT from the `Authorization: Bearer <token>` header and sets the user on the request/context.
- `Ondine\Middleware\RateLimitMiddleware` — rate-limits requests (by IP by default). Returns a `Response` with 429 and `Retry-After` when the limit is exceeded.

## Middleware contract

In Ondine a middleware is a class with a method that receives a `Request` and a `callable $next` (or in simplified implementations it can return a `Response` directly).

Minimum contract (conceptual):

- Inputs: `Request $request`, `callable $next`.
- Output: `Response` (either the result of calling `$next($request)` or a short-circuit `Response`).
- Side-effects: middlewares can modify headers, body or set data on the `Request` object (e.g. `request->attributes['user']`).

Conceptual example:

```php
class ExampleMiddleware {
    public function handle(Request $request, callable $next): Response {
        // pre-processing
        if ($somethingWrong) {
            return new Response(400, ['error' => 'bad']);
        }

        $response = $next($request);

        // post-processing
        $response->setHeader('X-Hello', 'Ondine');
        return $response;
    }
}
```

## Registering middleware in `public/index.php`

The front controller builds the application and registers middlewares in order. Example:

```php
$app->addMiddleware(new CorsMiddleware());
$app->addMiddleware(new RateLimitMiddleware($options));
$app->addMiddleware(new AuthMiddleware($auth));
```

Order matters: a middleware that short-circuits execution should be placed before any middleware that expects to run for the full response cycle.

## Testing considerations

- Unit test middleware by instantiating it with fake dependencies and asserting on the returned `Response` for different `next` behaviors.
- `RateLimitMiddleware` uses a file-store in tests; in integration tests you can inject a Redis adapter for performance.

## Best practices

- Do not echo or call `http_response_code()` inside middleware; return a `Response` and let the front controller send it.
- Keep middlewares small and single-responsibility.
- Avoid heavy dependencies; inject adapters/services.
- Use a centralized store (Redis) for rate-limiting and sessions in production.

## Examples and snippets

- RateLimit: return a `Response` with status 429 and `Retry-After` header.
- AuthMiddleware: if token is valid, set `request->attributes['user']` with the payload or user entity.

## Next steps

- Implement a Redis adapter for `RateLimitMiddleware`.
- Add metrics and tracing to critical middlewares.
- Add tests that exercise middleware ordering and composition.

---

This file was updated to English. A Spanish copy is available as `README.es.md`.
