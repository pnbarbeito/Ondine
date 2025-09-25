# Ondine - Auth Module

This document describes in detail the Ondine authentication module: how it works, the main classes, associated endpoints, expected configuration, and security recommendations.

## Purpose

The `Auth` module provides:

- Login with username + password credential verification and issuance of JSON Web Tokens (JWT).
- JWT verification to protect endpoints.
- Refresh tokens with database persistence (revocation and expiration).
- Utilities and repositories for managing sessions and tokens.

## Main Components

- `Ondine\Auth\Jwt` — encode/decode JWT (HS256) with configurable TTL.
- `Ondine\Auth\Auth` — authentication logic: `login($username, $password)`, `verifyToken($token)`, `getRepo()`.
- `Ondine\Auth\SessionRepository` — stores refresh tokens (HMAC hash), revokes and purges sessions.
- `Ondine\Controllers\AuthController` — endpoints `login`, `me`, `refresh`, `logout`.

## Authentication Flow (Summary)

1. Client makes `POST /api/login` with `{ username, password }`.
2. `AuthController::login` validates data and calls `Auth::login()`.
3. If credentials are correct, a JWT is issued (payload with `sub`, `username`, etc.) and a random `refresh_token` is generated (bin2hex(random_bytes(32))).
4. The `refresh_token` is hashed with HMAC-SHA256 using the `REFRESH_TOKEN_SECRET` key and persisted in the `sessions` table along with `issued_at`, `expires_at`, and `secret_version`.
5. Client stores `token` and `refresh_token` locally (Swagger UI in `/docs` automatically captures `token`).

## Relevant Endpoints

- `POST /api/login` — body: `{ username, password }` → response `{ token, refresh_token }`.
- `GET /api/me` — header `Authorization: Bearer <token>` → returns user and payload.
- `POST /api/token/refresh` — body: `{ refresh_token }` → returns `{ token, refresh_token }` if the refresh_token is valid and not revoked. The endpoint performs refresh token rotation (generates a new refresh_token and saves its hash in the `sessions` table).
- `POST /api/logout` — body: `{ refresh_token }` → revokes the session.

## Storage and Security

- `refresh_token`s are not stored in plain text: their HMAC-SHA256 with a secret key (env `REFRESH_TOKEN_SECRET`) is saved.
- To allow key rotation without immediately invalidating all tokens, there is `secret_version` in the `sessions` table and `REFRESH_TOKEN_SECRETS` to map versions to secrets.
- Recommendations:
  - Store `REFRESH_TOKEN_SECRET` in the environment (not in the repository). For rotations, use `REFRESH_TOKEN_SECRETS` and increase the version in `REFRESH_TOKEN_SECRET_VERSION`.
  - For optional additional security: store a `token_id` (UUID) and save the HMAC over `token_id`, not over the complete token.
  - Record `ip` and `user_agent` in sessions if you want to track and detect anomalies.
  - Set reasonable TTLs: short JWT (e.g. 15 min recommended), long refresh tokens (e.g. 14-30 days recommended) with immediate revocation possibility.
  - Note: when renewing the token (refresh), the server issues a new `refresh_token` (rotation). The client must replace the old one with the new one.

## Profile Cache and Permissions

Ondine uses a per-profile cache (`ProfileCache`) to reduce database queries when resolving permissions per request. Practical rules:

- `PROFILE_CACHE_TTL` (seconds) can be configured in `.env` to control how long cache entries last (default 60s).
- When a profile is updated (endpoint `ProfilesController::update` or `delete`), the controller invalidates the cache entry `profile_{id}` so changes are visible immediately.
- In multi-host deployments, file-based cache is local to each instance; for consistency between instances, centralized cache (Redis) is recommended or force session revocation to force refresh.

## Code API (Classes)

### Jwt

- `Jwt::encode(array $payload, string $secret, int $ttl = 3600)` — returns HS256 JWT.
- `Jwt::decode(string $jwt, string $secret)` — validates signature and expiration, returns payload or `null`.

### Auth

- `new Auth(UserRepository $repo, string $secret)` — constructor.
- `login($username, $password)` — verifies password and returns JWT or `null`.
- `verifyToken($token)` — validates JWT and returns payload or `null`.
- `getRepo()` — returns the user repository for additional operations.

### SessionRepository

- `create(int $userId, string $refreshToken, int $ttlSeconds = 2592000)` — hashes and stores the refresh token with the current `secret_version`.
- `findByToken(string $refreshToken)` — finds and returns the session row (if the hash matches).
- `revoke(string $refreshToken)` — marks the session as `revoked`.
- `purgeExpired()` — deletes sessions whose `expires_at` is in the past.

## Migrations

- `migrations/20250922_0003_create_sessions.php` — creates the `sessions` table.
- `migrations/20250922_0004_add_secret_version_to_sessions.php` — adds `secret_version`.

## Usage Examples (Controller)

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

- There are unit and integration tests (`tests/SessionRepositoryTest.php`, `tests/AuthRefreshFlowTest.php`) that validate create/find/revoke/purge and the login->refresh->logout flow.

## Final Notes

- Ondine aims to be minimal and educational; adapt and extend the Auth module for production with logging, audit, hardening (rate limits per refresh), and key rotation policies.

---

File created automatically by the development tool.

## Recent Changes

Small improvements and security changes applied recently:

- Fail-fast in production: if `APP_ENV=production` and `JWT_SECRET` or `REFRESH_TOKEN_SECRET` are not configured (or use the default value `changeme`), `Auth` and `SessionRepository` will throw an exception to avoid operating with insecure secrets in production.
- Hashing compatibility: the project uses `PASSWORD_ARGON2I` when available and falls back to `PASSWORD_DEFAULT` if not present, to facilitate use in environments where Argon2 is not present.

Recommendation: make sure to set `JWT_SECRET` and `REFRESH_TOKEN_SECRET` in your environment variables in real deployments and rotate them periodically.
