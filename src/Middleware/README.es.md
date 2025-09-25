# Ondine - Módulo Middleware

Este README documenta el módulo Middleware de Ondine: qué es un middleware, implementaciones de middleware incluidas, cómo escribir/registrar/probar middleware, y recomendaciones de diseño.

## Propósito

El sistema de middleware proporciona una forma uniforme de interceptar y procesar solicitudes antes y/o después de que lleguen a los controladores. Permite:

- Aplicar políticas transversales (CORS, autenticación, limitación de tasa).
- Agregar registro, transformaciones de solicitud/respuesta, almacenamiento en caché.
- Componer comportamientos reutilizables en la canalización de ejecución de la aplicación.

## Middleware incluido

- `Ondine\Middleware\CorsMiddleware` — agrega encabezados CORS básicos para permitir solicitudes desde el frontend.
- `Ondine\Middleware\AuthMiddleware` — valida JWT desde el encabezado `Authorization: Bearer <token>` y establece el usuario en la solicitud/contexto.
- `Ondine\Middleware\RateLimitMiddleware` — limita la tasa de solicitudes (por IP por defecto). Devuelve una `Response` con 429 y `Retry-After` cuando se excede el límite.

## Contrato de middleware

En Ondine, un middleware es una clase con un método que recibe un `Request` y un `callable $next` (o en implementaciones simplificadas puede devolver una `Response` directamente).

Contrato mínimo (conceptual):

- Entradas: `Request $request`, `callable $next`.
- Salida: `Response` (ya sea el resultado de llamar `$next($request)` o una `Response` de cortocircuito).
- Efectos secundarios: los middlewares pueden modificar encabezados, cuerpo o establecer datos en el objeto `Request` (ej. `request->attributes['user']`).

Ejemplo conceptual:

```php
class ExampleMiddleware {
    public function handle(Request $request, callable $next): Response {
        // pre-procesamiento
        if ($somethingWrong) {
            return new Response(400, ['error' => 'bad']);
        }

        $response = $next($request);

        // post-procesamiento
        $response->setHeader('X-Hello', 'Ondine');
        return $response;
    }
}
```

## Registrando middleware en `public/index.php`

El controlador frontal construye la aplicación y registra middlewares en orden. Ejemplo:

```php
$app->addMiddleware(new CorsMiddleware());
$app->addMiddleware(new RateLimitMiddleware($options));
$app->addMiddleware(new AuthMiddleware($auth));
```

El orden importa: un middleware que cortocircuita la ejecución debe colocarse antes de cualquier middleware que espere ejecutarse para el ciclo completo de respuesta.

## Consideraciones de prueba

- Prueba unitaria de middleware instanciándolo con dependencias falsas y afirmando en la `Response` devuelta para diferentes comportamientos de `next`.
- `RateLimitMiddleware` usa un almacén de archivos en pruebas; en pruebas de integración puedes inyectar un adaptador Redis para rendimiento.

## Mejores prácticas

- No hagas echo ni llames `http_response_code()` dentro de middleware; devuelve una `Response` y deja que el controlador frontal la envíe.
- Mantén los middlewares pequeños y de responsabilidad única.
- Evita dependencias pesadas; inyecta adaptadores/servicios.
- Usa un almacén centralizado (Redis) para limitación de tasa y sesiones en producción.

## Ejemplos y fragmentos

- RateLimit: devuelve una `Response` con estado 429 y encabezado `Retry-After`.
- AuthMiddleware: si el token es válido, establece `request->attributes['user']` con la carga útil o entidad de usuario.

## Próximos pasos

- Implementar un adaptador Redis para `RateLimitMiddleware`.
- Agregar métricas y rastreo a middlewares críticos.
- Agregar pruebas que ejerciten el ordenamiento y composición de middleware.

---

Este archivo fue actualizado al inglés. Una copia en español está disponible como `README.es.md`.