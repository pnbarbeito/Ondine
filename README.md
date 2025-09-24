# Ondine — minimal PHP microframework (REST)

Ondine is a tiny PHP microframework designed to expose REST endpoints and integrate with frontends (for example, React). This repository now provides the library in `src/` and an example application under `examples/public/`.

Project layout
- `src/` — core library (PSR-4, exportable as a Composer package)
- `examples/public/` — example application and test UI that consumes the library
- `migrations/` — versioned PHP migrations
- `data/` — default storage for the SQLite database file

Requirements
- PHP 7.4+ (PHP 8+ recommended)
- Composer (recommended for PSR-4 autoload and tooling)

Quickstart

This repository contains the library under `src/` and an example application under `examples/public/`.

1) Install dependencies (recommended)

```bash
composer install
```

2) Using Ondine as a Composer dependency

If you publish the package to Packagist (recommended name: `ondine/ondine`), you can install it in another project using:

```bash
composer require ondine/ondine
```

For local development you can use a `path` repository in your project's `composer.json`:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../path/to/Ondine",
      "options": { "symlink": true }
    }
  ]
}
```

Then run `composer require ondine/ondine:@dev` in the consuming project.

3) Configure environment variables for the example app

Copy the example and edit it (example uses `config/.env` in the repository root):

```bash
cp config/.env.example config/.env
# Edit `config/.env` and set DB_DRIVER and secrets as needed
```

By default Ondine uses SQLite and stores the file at `data/database.sqlite` in the project root. To use MariaDB set `DB_DRIVER=mariadb` and update the `MYSQL_*` variables in the `.env` file.

4) Run the example application

Start a local PHP server (from repository root):

```bash
php -S 0.0.0.0:8080 -t examples/public
```

Open `http://localhost:8080` to access the example UI and `http://localhost:8080/docs` for the OpenAPI UI.

Running migrations for the example app (examples use the scripts in `examples/scripts/`):

```bash
php examples/scripts/migrate.php migrate
```

Rollback (e.g. rollback last migration):

```bash
php examples/scripts/migrate.php rollback 1
```

API documentation (OpenAPI / Swagger)

Open `http://localhost:8080/docs` to access the interactive API docs. The UI captures the `token` returned by `POST /api/login` and applies it automatically as `Authorization: Bearer <token>` for subsequent requests.

Main endpoints

- Authentication
  - `POST /api/login` — body: `{ username, password }` → returns `{ token, refresh_token }`
  - `POST /api/token/refresh` — body: `{ refresh_token }` → returns `{ token }`
  - `POST /api/logout` — body: `{ refresh_token }` → revokes the session
  - `GET /api/me` — requires `Authorization: Bearer <token>`

- Users
  - `GET /api/users`
  - `GET /api/users/{id}`
  - `POST /api/users`
  - `PUT /api/users/{id}`
  - `DELETE /api/users/{id}`

- Profiles
  - `GET /api/profiles`

Testing

The test suite uses PHPUnit and an ephemeral SQLite database for each test. Run the tests with:

```bash
composer test
# or
./vendor/bin/phpunit --colors=always
```

Seed variables (used by migrations)

- `SEED_PROFILE_NAME` — default: `Administrator`
- `SEED_PROFILE_PERMISSIONS` — JSON string, default: `{"admin":1,"profiles":1,"users":1}`
- `SEED_ADMIN_USERNAME` — default: `sysadmin`
- `SEED_ADMIN_PASSWORD` — default: `SysAdmin8590`
- `SEED_ADMIN_FIRSTNAME` — default: `Sys`
- `SEED_ADMIN_LASTNAME` — default: `Admin`
- `SEED_ADMIN_STATE` — default: `1`

Migrations
---------

Migrations are simple PHP files under `migrations/`. They are driver-aware (SQLite vs MariaDB) and seed the initial profile and admin user using environment variables so you can customize them at deploy or test time.

Common commands (fish / bash compatible)

- Ensure dependencies and environment file:

```bash
composer install
cp config/.env.example config/.env
# Edit `config/.env` with your `DB_DRIVER` and connection details if needed
```

- Run all migrations:

```bash
php scripts/migrate.php migrate
```

- Rollback the last N migrations (example: rollback 1):

```bash
php scripts/migrate.php rollback 1
```

- Set seed variables (example):

```bash
env SEED_ADMIN_USERNAME=customadmin SEED_ADMIN_PASSWORD=s3cret php scripts/migrate.php migrate
```

Security & notes

- Configure `JWT_SECRET` and `REFRESH_TOKEN_SECRET` in `config/.env` and do not commit them to source control.
- SQLite is intended for development and testing. For production use MariaDB/MySQL or PostgreSQL.
- Consider Redis for rate-limiting in high-concurrency environments.

Contributing
------------

Contributions are welcome. If you'd like, add a short contributing guide or open issues with proposed changes. For tests and development:

```bash
composer install
./vendor/bin/phpunit --colors=always
```

License
-------

This project is provided under the terms stated in the repository (check `LICENSE` if present) or use MIT/Apache2 as appropriate for your project.

If you want, I can also add a sample `config/.env` template for MariaDB and a short `CONTRIBUTING.md`.

