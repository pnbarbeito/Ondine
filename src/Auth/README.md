# Ondine - Auth module

Este documento describe en detalle el módulo de autenticación de Ondine: cómo funciona, las clases principales, los endpoints asociados, la configuración esperada y recomendaciones de seguridad.

## Propósito

El módulo `Auth` proporciona:

- Login with username + password credential verification and issuance of JSON Web Tokens (JWT).
- Verificación de JWT para proteger endpoints.
- Refresh tokens con persistencia en BD (revocación y expiración).
- Utilities y repositorios para gestionar sesiones y tokens.


## Componentes principales

- `Ondine\Auth\Jwt` — encode/decode JWT (HS256) con TTL configurable.
- `Ondine\Auth\Auth` — lógica de autenticación: `login($username, $password)`, `verifyToken($token)`, `getRepo()`.
- `Ondine\Auth\SessionRepository` — almacena refresh tokens (hash HMAC), revoca y purga sesiones.
- `Ondine\Controllers\AuthController` — endpoints `login`, `me`, `refresh`, `logout`.


## Flujo de autenticación (resumen)

1. El cliente realiza `POST /api/login` con `{ username, password }`.
2. `AuthController::login` valida los datos y llama a `Auth::login()`.
3. Si las credenciales son correctas, se emite un JWT (payload con `sub`, `username`, etc.) y se genera un `refresh_token` aleatorio (bin2hex(random_bytes(32))).
4. El `refresh_token` se hashea con HMAC-SHA256 usando la clave `REFRESH_TOKEN_SECRET` y se persiste en la tabla `sessions` junto a `issued_at`, `expires_at` y `secret_version`.
5. El cliente guarda en local `token` y `refresh_token` (la UI de Swagger UI en `/docs` captura automáticamente `token`).


## Endpoints relevantes

- `POST /api/login` — body: `{ username, password }` → response `{ token, refresh_token }`.
- `GET /api/me` — header `Authorization: Bearer <token>` → returns user and payload.
- `POST /api/token/refresh` — body: `{ refresh_token }` → devuelve `{ token, refresh_token }` si el refresh_token es válido y no revocado. El endpoint realiza rotación del refresh token (se genera un nuevo refresh_token y se guarda su hash en la tabla `sessions`).
- `POST /api/logout` — body: `{ refresh_token }` → revoca la sesión.


## Storage y seguridad

- Los `refresh_token` no se almacenan en texto claro: se guarda su HMAC-SHA256 con una clave secreta (env `REFRESH_TOKEN_SECRET`).
- Para permitir rotación de claves sin invalidar de inmediato todos los tokens, existe `secret_version` en la tabla `sessions` y `REFRESH_TOKEN_SECRETS` para mapear versiones a secretos.
- Recomendaciones:
  - Guarda `REFRESH_TOKEN_SECRET` en el entorno (no en el repositorio). Para rotaciones, usa `REFRESH_TOKEN_SECRETS` y sube la versión en `REFRESH_TOKEN_SECRET_VERSION`.
  - Para mayor seguridad opcional: almacena un `token_id` (UUID) y guarda el HMAC sobre `token_id`, no sobre el token completo.
  - Registra `ip` y `user_agent` en las sesiones si deseas rastrear y detectar anomalías.
  - Establece TTLs razonables: JWT corto (ej. 15 min recomendado), refresh tokens largos (ej. 14-30 días recomendado) y posibilidad de revocación inmediata.
  - Nota: al renovar el token (refresh) el servidor emite un nuevo `refresh_token` (rotación). El cliente debe sustituir el antiguo por el nuevo.

## Profile cache and permissions

Ondine utiliza una caché por perfil (`ProfileCache`) para reducir consultas a la BD al resolver permisos por request. Reglas prácticas:

- `PROFILE_CACHE_TTL` (segundos) puede configurarse en `.env` para controlar el tiempo que duran las entradas en cache (por defecto 60s).
- Cuando un perfil se actualiza (endpoint `ProfilesController::update` o `delete`), el controlador invalida la entrada de cache `profile_{id}` para que los cambios sean visibles inmediatamente.
- En despliegues multi-host, la cache por archivos es local a cada instancia; para coherencia entre instancias se recomienda usar un cache centralizado (Redis) o forzar revocación de sesiones para forzar refresh.


## API del código (clases)

### Jwt

- `Jwt::encode(array $payload, string $secret, int $ttl = 3600)` — devuelve JWT HS256.
- `Jwt::decode(string $jwt, string $secret)` — valida firma y expiración, devuelve payload o `null`.

### Auth

- `new Auth(UserRepository $repo, string $secret)` — constructor.
- `login($username, $password)` — verifica password y devuelve JWT o `null`.
- `verifyToken($token)` — valida JWT y devuelve payload o `null`.
- `getRepo()` — returns the user repository for additional operations.

### SessionRepository

- `create(int $userId, string $refreshToken, int $ttlSeconds = 2592000)` — hashes and stores the refresh token with the current `secret_version`.
- `findByToken(string $refreshToken)` — finds and returns the session row (if the hash matches).
- `revoke(string $refreshToken)` — marks the session as `revoked`.
- `purgeExpired()` — deletes sessions whose `expires_at` is in the past.


## Migraciones

- `migrations/20250922_0003_create_sessions.php` — crea la tabla `sessions`.
- `migrations/20250922_0004_add_secret_version_to_sessions.php` — agrega `secret_version`.


## Ejemplos de uso (controller)

```php
// login
$ctrl = new \Ondine\Controllers\AuthController();
$req = new \Ondine\Request();
$req->parsedBody = ['username' => 'sysadmin', 'password' => 'secret'];
$res = $ctrl->login($req, []);
// $res contains token and refresh_token

// refresh
$req2 = new \Ondine\Request();
$req2->parsedBody = ['refresh_token' => $res['refresh_token']];
$new = $ctrl->refresh($req2, []);
```


## Tests

- Hay tests unitarios e integración (`tests/SessionRepositoryTest.php`, `tests/AuthRefreshFlowTest.php`) que validan create/find/revoke/purge y el flujo login->refresh->logout.


## Notas finales

- Ondine busca ser minimal y educativo; adapta y extiende el módulo Auth para producción con logging, audit, hardening (rate limits por refresh), y políticas de rotación de claves.

---

Archivo creado automáticamente por la herramienta de desarrollo.

## Cambios recientes

Pequeñas mejoras y cambios de seguridad aplicadas recientemente:

- Fail-fast en producción: si `APP_ENV=production` y `JWT_SECRET` o `REFRESH_TOKEN_SECRET` no están configurados (o usan el valor por defecto `changeme`), `Auth` y `SessionRepository` lanzarán una excepción para evitar operar con secretos inseguros en producción.
- Compatibilidad de hashing: el proyecto usa `PASSWORD_ARGON2I` cuando está disponible y cae a `PASSWORD_DEFAULT` si no lo está, para facilitar el uso en entornos donde Argon2 no está presente.

Recomendación: asegúrate de establecer `JWT_SECRET` y `REFRESH_TOKEN_SECRET` en tus variables de entorno en despliegues reales y rotarlos periódicamente.
