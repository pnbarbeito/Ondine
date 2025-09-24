# Ondine Skeleton

This is a minimal application skeleton that uses the `pnbarbeito/ondine` library.

Quick start

1. Install dependencies:

```bash
composer install
cp config/.env.example config/.env
# edit config/.env if needed
```

2. Run migrations:

```bash
php scripts/migrate.php
```

3. Start a local server:

```bash
php -S 0.0.0.0:8000 -t public
```

Open `http://localhost:8000` and test the API endpoints under `/api`.

API Endpoints provided by the library
------------------------------------

The skeleton registers all default endpoints provided by the `pnbarbeito/ondine` library under the `/api` prefix (this can be changed in `public/index.php` when calling `\Ondine\Bootstrap::registerAuthRoutes`). Below is a concise reference for each endpoint, expected request fields and example `curl` commands.

Auth
----

- POST /api/login
	- Description: Authenticate a user and receive a short-lived JWT plus a refresh token.
	- Body (application/json): { "username": "user", "password": "secret" }
	- Success (200): { "token": "<jwt>", "refresh_token": "<refresh>" }
	- Errors: 400 (validation), 401 (invalid credentials), 403 (user blocked)

	Example:

	```bash
	curl -s -X POST http://localhost:8000/api/login \
		-H "Content-Type: application/json" \
		-d '{"username":"admin","password":"secret"}' | jq
	```

- POST /api/refresh
	- Description: Exchange a refresh token for a new JWT.
	- Body (application/json): { "refresh_token": "<refresh>" }
	- Success (200): { "token": "<jwt>" }
	- Errors: 400 (missing refresh_token), 401 (invalid/expired)

	Example:

	```bash
	curl -s -X POST http://localhost:8000/api/refresh \
		-H "Content-Type: application/json" \
		-d '{"refresh_token":"REPLACE_REFRESH_TOKEN"}' | jq
	```

- POST /api/logout
	- Description: Revoke a refresh token (log out).
	- Body (application/json): { "refresh_token": "<refresh>" }
	- Success (200): { "ok": true }

	Example:

	```bash
	curl -s -X POST http://localhost:8000/api/logout \
		-H "Content-Type: application/json" \
		-d '{"refresh_token":"REPLACE_REFRESH_TOKEN"}' | jq
	```

- GET /api/me
	- Description: Return information about the authenticated user. Requires `Authorization: Bearer <token>` header.
	- Success (200): { "user": { ... }, "token_payload": { ... } }
	- Errors: 401 (missing/invalid token)

	Example:

	```bash
	curl -s http://localhost:8000/api/me -H "Authorization: Bearer REPLACE_JWT" | jq
	```

Profiles
--------

- GET /api/profiles
	- Description: List available profiles.
	- Success (200): { "data": [ { "id": 1, "name": "admin", "permissions": {...} }, ... ] }

	Example:

	```bash
	curl -s http://localhost:8000/api/profiles | jq
	```

- GET /api/profiles/{id}
	- Description: Get a profile by id.
	- Success (200): { "data": { "id": 1, "name": "admin", "permissions": {...} } }
	- Errors: 400 (missing id), 404 (not found)

	Example:

	```bash
	curl -s http://localhost:8000/api/profiles/1 | jq
	```

Users
-----

The library exposes basic CRUD endpoints for `users`.

- GET /api/users
	- Description: List users.
	- Success (200): { "data": [ ... ] }

	Example:

	```bash
	curl -s http://localhost:8000/api/users | jq
	```

- GET /api/users/{id}
	- Description: Get a user by id.
	- Errors: 400 (missing id), 404 (not found)

	Example:

	```bash
	curl -s http://localhost:8000/api/users/1 | jq
	```

- POST /api/users
	- Description: Create a new user.
	- Body (application/json): fields like `first_name`, `last_name`, `username`, `password`, `profile_id`, `theme`, `state` (see validation rules in the controller).
	- Success (201): { "id": <new id> }
	- Errors: 400 (validation), 409 (duplicate username)

	Example (minimal):

	```bash
	curl -s -X POST http://localhost:8000/api/users \
		-H "Content-Type: application/json" \
		-d '{"first_name":"John","last_name":"Doe","username":"jdoe","password":"secret"}' | jq
	```

- PUT /api/users/{id}
	- Description: Update an existing user. Provide any of the updatable fields in the body.
	- Success (200): { "updated": <count> }

	Example:

	```bash
	curl -s -X PUT http://localhost:8000/api/users/1 \
		-H "Content-Type: application/json" \
		-d '{"theme":"light"}' | jq
	```

- DELETE /api/users/{id}
	- Description: Delete a user.
	- Success (200): { "deleted": <count> }

	Example:

	```bash
	curl -s -X DELETE http://localhost:8000/api/users/1 | jq
	```

Notes
-----

- Prefix: The skeleton registers routes with `['prefix' => '/api']`. Change that in `public/index.php` if you want another root path.
- Authentication: `POST /api/login` issues a JWT and a refresh token. Use the JWT in `Authorization: Bearer <token>` to access `GET /api/me` and other protected endpoints as implemented by the library.
- Configuration: Copy `config/.env.example` to `config/.env` and update DB and `JWT_SECRET` values before running migrations.

Troubleshooting
---------------

- If you get permission or file-lock errors when running migrations inside Docker, use a named volume for persistent storage or run migrations locally against the bundled SQLite DB (`data/database.sqlite`).

API documentation (Swagger UI)
-----------------------------

This skeleton includes a static Swagger UI at `/docs` that loads the OpenAPI spec from `/openapi.yaml` (the main project `openapi.yaml` is served by the root `public/index.php`). Start the skeleton server and open:

```
http://localhost:8000/docs
```

How it works:
- The UI is at `examples/skeleton/public/docs/index.html` and it points to `/openapi.yaml`.
- The Swagger UI in this skeleton attempts to automatically capture a JWT from the response of `/api/login` or `/api/refresh` and apply it as `Authorization: Bearer <token>` for subsequent requests in the UI.

Usage tips:
- First create or login a user using `POST /api/login` (use the curl example above). The UI will capture the `token` field in the JSON response and pre-authorize requests.
- If you prefer to manually set the Bearer token in the UI, use the "Authorize" button in Swagger UI and paste `Bearer <token>`.


