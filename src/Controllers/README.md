# Ondine - Controllers

This README describes conventions for creating and using controllers in Ondine, practical examples and testing/error-handling best practices.

## General convention

- Controllers live under `Ondine\Controllers`.
- Each controller is a class with methods representing actions (endpoints). For example `AuthController::login(Request $req, array $args)`.
- Methods receive a `Request` (an object containing method, path, headers, parsedBody and attributes) and an array of route `args`.
- Methods should return either a `Response` instance or a value (array/object) which the front controller will serialize to JSON.

## Recommended signature

```php
public function actionName(Request $request, array $args): Response
```

If a method returns an array or object, the front controller serializes it to JSON with `Content-Type: application/json`.

## Error handling

- For validation errors return a `Response` with status `422` and a body describing the errors.
- For conflicts (e.g. duplicate username) return status `409`.
- Use `401` for authentication errors and `403` for insufficient permissions.

Example:

```php
public function createUser(Request $request, array $args): Response {
    $data = $request->parsedBody;
    try {
        // validate and create
        return new Response(201, ['id' => $id]);
    } catch (DuplicateUsernameException $e) {
        return new Response(409, ['error' => 'Duplicate username']);
    } catch (ValidationException $e) {
        return new Response(422, ['errors' => $e->getErrors()]);
    }
}
```

## Existing controllers

- `AuthController` — login, refresh, logout, me.
- `UsersController` — users CRUD (includes validation and 409 handling).

## Route registration

Routes are registered in `public/index.php`. Example:

```php
$router->post('/api/login', [AuthController::class, 'login']);
```

The front controller instantiates the controller and calls the method passing `$request` and `$args`.

## Testing

- Test controllers at two levels:
  - Unit: instantiate the controller with mocked dependencies (repositories, services) and call the method directly.
  - Integration: run the front controller or simulate an HTTP request to validate full serialization and pipeline behavior.

## Best practices

- Keep business logic in services or repositories; controllers should orchestrate and validate.
- Use specific exceptions to map to HTTP codes (ValidationException → 422, NotFoundException → 404).
- Do not use `exit`/`die` or directly send headers from controllers. Return a `Response`.

---

This file was updated to English. A Spanish copy is available as `README.es.md`.
