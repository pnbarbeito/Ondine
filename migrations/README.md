# Ondine - Migrations

This directory contains database migration files for the Ondine framework. Migrations are used to manage database schema changes in a version-controlled and repeatable way.

## Migration Structure

Each migration file is a PHP file that returns an array with migration operations:

```php
<?php
return [
  'up' => function ($pdo) {
    // Code to apply the migration
    $pdo->exec("CREATE TABLE example (id INTEGER PRIMARY KEY)");
  },
  'down' => function ($pdo) {
    // Code to rollback the migration (optional)
    $pdo->exec("DROP TABLE example");
  }
];
```

## Naming Convention

Migration files follow the format: `YYYYMMDD_HHMM_description.php`

- `YYYYMMDD`: Date in year-month-day format
- `HHMM`: Time in hour-minute format
- `description`: Brief description of what the migration does

Example: `20250922_0001_create_profiles_users.php`

## How Migrations Work

- Migrations are executed in chronological order based on filename
- The `Ondine\Database\Migrator` class manages migration execution
- Each migration's `up` function is called to apply changes
- The `down` function (if provided) can be used to rollback changes

## Database Compatibility

Migrations should be written to work with both SQLite and MariaDB/MySQL:

- Use `INTEGER PRIMARY KEY AUTOINCREMENT` for auto-incrementing IDs (works in both)
- Use `TEXT` for string fields
- Use `DATETIME` for timestamps
- Check the `DB_DRIVER` environment variable if needed for driver-specific code

## Running Migrations

Use the migration script from the project root:

```bash
# Apply all pending migrations
php scripts/migrate.php migrate

# Rollback the last migration
php scripts/migrate.php rollback
```

## Best Practices

- Keep migrations small and focused on a single change
- Test migrations on both SQLite and MariaDB
- Include rollback functionality when possible
- Use descriptive names for migration files
- Avoid modifying existing migrations after they've been applied in production

## Current Migrations

- `20250922_0001_create_profiles_users.php`: Creates profiles and users tables
- `20250922_0002_unique_user.php`: Adds unique constraints
- `20250922_0003_create_sessions.php`: Creates sessions table

---

For more information about the database module, see `src/Database/README.md`.