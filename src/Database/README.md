# Ondine - Database module

This README documents the Ondine Database module: the database access layer, migrations and conventions for working with MariaDB and SQLite.

## Overview

Ondine uses a small layer over PDO to support two database engines:

- SQLite (useful for tests and lightweight deployments).
- MariaDB / MySQL (for production).

The main class is `Ondine\Database\Database` and migrations are managed by `Ondine\Database\Migrator`.

## Key classes

- `Ondine\Database\Database`
  - Wraps `PDO` and provides helpers for `execute`, `fetch`, transactions and driver configuration.
  - Detects the driver via environment variables (see Configuration below).

- `Ondine\Database\Migrator`
  - Runs files from the `migrations/` directory in chronological order.
  - Supports `migrate()` and `rollback()` operations.
  - Migrations are PHP files that export a callable e.g. `return function(PDO $pdo){ ... };`.

## Migration structure

- Directory: `migrations/`.
- Naming: `YYYYMMDD_HHMM_description.php` to keep chronological order.
- Typical migration content:

```php
<?php
return function(PDO $pdo){
    $pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, password TEXT, created_at DATETIME)");
};
```

- For rollback, the migrator calls a `down()` callable if provided by the migration or otherwise relies on the implemented migration tracking.

## Useful scripts

- `scripts/migrate.php` — run `migrate` or `rollback` from CLI.
- `scripts/purge_sessions.php` — maintenance script to purge expired sessions.

Local usage example (shell):

```bash
# Run all pending migrations
php ./scripts/migrate.php migrate

# Rollback the last migration
php ./scripts/migrate.php rollback
```

## Environment variables

- `DB_DRIVER` — `sqlite` or `mariadb`. (defaults to `sqlite` for local testing).
- If `sqlite`:
  - `DB_SQLITE_PATH` — path to the sqlite file (for example `data/database.sqlite`). This project uses `data/database.sqlite` in the project root by default.
- If `mariadb`:
  - `MYSQL_HOST`
  - `MYSQL_PORT`
  - `MYSQL_DATABASE`
  - `MYSQL_USER`
  - `MYSQL_PASSWORD`

## Recommendations

- For unit/integration tests prefer SQLite with temporary files (as `tests/BaseTestCase.php` does).
- In CI, use a MariaDB service or SQLite depending on your needs.
- Keep migrations small and atomic. Avoid implicit dependencies between migrations.

## Quick examples

- Create `users` table in a migration (english field names):

```php
<?php
return function(PDO $pdo){
  $pdo->exec(
    "CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, password TEXT, created_at DATETIME)"
  );
};
```

`scripts/migrate.php` loads `Ondine\Database\Migrator` and executes the migration files found in `migrations/`.

## Testing and rollback

- `Migrator::migrate()` registers applied migrations (if supported) and runs only pending ones.
- `Migrator::rollback()` should revert the most recent migration if a rollback callable is provided.

---

This file was updated to use English documentation and to reference the project-root `data/` directory as the default sqlite location.
