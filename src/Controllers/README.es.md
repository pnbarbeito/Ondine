# Ondine - Controllers

Este README describe convenciones para crear y usar controladores en Ondine, ejemplos prácticos y mejores prácticas de testing/manejo de errores.

## Convención general

- Los controladores viven bajo `Ondine\Controllers`.
- Cada controlador es una clase con métodos representando acciones (endpoints). Por ejemplo `AuthController::login(Request $req, array $args)`.
- Los métodos reciben un `Request` (un objeto conteniendo method, path, headers, parsedBody y attributes) y un array de `args` de ruta.
- Los métodos deberían retornar ya sea una instancia de `Response` o un valor (array/object) que el controlador frontal serializará a JSON.

## Firma recomendada

```php
public function actionName(Request $request, array $args): Response
```

Si un método retorna un array u object, el controlador frontal lo serializa a JSON con `Content-Type: application/json`.

## Manejo de errores

- Para errores de validación retornar un `Response` con status `422` y un body describiendo los errores.
- Para conflictos (ej. nombre de usuario duplicado) retornar status `409`.
- Usar `401` para errores de autenticación y `403` para permisos insuficientes.

Ejemplo:

```php
public function createUser(Request $request, array $args): Response {
    $data = $request->parsedBody;
    try {
        // validar y crear
        return new Response(201, ['id' => $id]);
    } catch (DuplicateUsernameException $e) {
        return new Response(409, ['error' => 'Nombre de usuario duplicado']);
    } catch (ValidationException $e) {
        return new Response(422, ['errors' => $e->getErrors()]);
    }
}
```

## Controladores existentes

- `AuthController` — login, refresh, logout, me.
- `UsersController` — CRUD de usuarios (incluye validación y manejo de 409).

## Registro de rutas

Las rutas se registran en `public/index.php`. Ejemplo:

```php
$router->post('/api/login', [AuthController::class, 'login']);
```

El controlador frontal instancia el controlador y llama al método pasando `$request` y `$args`.

## Testing

- Testear controladores a dos niveles:
  - Unit: instanciar el controlador con dependencias mockeadas (repositorios, servicios) y llamar el método directamente.
  - Integration: ejecutar el controlador frontal o simular una petición HTTP para validar serialización completa y comportamiento del pipeline.

## Mejores prácticas

- Mantener lógica de negocio en servicios o repositorios; los controladores deberían orquestar y validar.
- Usar excepciones específicas para mapear a códigos HTTP (ValidationException → 422, NotFoundException → 404).
- No usar `exit`/`die` o enviar headers directamente desde controladores. Retornar un `Response`.

---

Este archivo fue actualizado al inglés. Una copia en español está disponible como `README.es.md`.