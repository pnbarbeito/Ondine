# Ondine - Módulo Database

Este README documenta el módulo Database de Ondine: la capa de acceso a base de datos, migraciones y convenciones para trabajar con MariaDB y SQLite.

## Resumen

Ondine utiliza una pequeña capa sobre PDO para soportar dos motores de base de datos:

- SQLite (útil para pruebas y despliegues ligeros).
- MariaDB / MySQL (para producción).

Los componentes principales son:
- `Ondine\Database\Database`: Envoltorio PDO con ayudantes
- `Ondine\Database\Migrator`: Gestión de migraciones
- `Repository/`: Implementaciones del patrón Repository para acceso a datos

## Clases clave

- `Ondine\Database\Database`
  - Envuelve `PDO` y proporciona ayudantes para `execute`, `fetch`, transacciones y configuración de driver.
  - Detecta el driver vía variables de entorno (ver Configuración abajo).

- `Ondine\Database\Migrator`
  - Ejecuta archivos del directorio `migrations/` en orden cronológico.
  - Soporta operaciones `migrate()` y `rollback()`.
  - Las migraciones son archivos PHP que exportan un callable ej. `return function(PDO $pdo){ ... };`.

## Capa Repository

El subdirectorio `Repository/` contiene clases de repositorio que implementan el patrón Repository para acceso limpio a datos:

- `UserRepository`: Operaciones CRUD para tabla users
- Excepciones personalizadas para manejo de errores específico del dominio

Consulta `Repository/README.md` para documentación detallada.
  - Ejecuta archivos del directorio `migrations/` en orden cronológico.
  - Soporta operaciones `migrate()` y `rollback()`.
  - Las migraciones son archivos PHP que exportan un callable ej. `return function(PDO $pdo){ ... };`.

## Estructura de migraciones

- Directorio: `migrations/`.
- Nomenclatura: `YYYYMMDD_HHMM_descripcion.php` para mantener orden cronológico.
- Contenido típico de migración:

```php
<?php
return function(PDO $pdo){
    $pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, password TEXT, created_at DATETIME)");
};
```

- Para rollback, el migrator llama a un callable `down()` si es proporcionado por la migración o de lo contrario confía en el seguimiento de migraciones implementado.

## Scripts útiles

- `scripts/migrate.php` — ejecuta `migrate` o `rollback` desde CLI.
- `scripts/purge_sessions.php` — script de mantenimiento para purgar sesiones expiradas.

Ejemplo de uso local (shell):

```bash
# Ejecutar todas las migraciones pendientes
php ./scripts/migrate.php migrate

# Revertir la última migración
php ./scripts/migrate.php rollback
```

## Variables de entorno

- `DB_DRIVER` — `sqlite` o `mariadb`. (por defecto `sqlite` para pruebas locales).
- Si `sqlite`:
  - `DB_SQLITE_PATH` — ruta al archivo sqlite (por ejemplo `data/database.sqlite`). Este proyecto usa `data/database.sqlite` en la raíz del proyecto por defecto.
- Si `mariadb`:
  - `MYSQL_HOST`
  - `MYSQL_PORT`
  - `MYSQL_DATABASE`
  - `MYSQL_USER`
  - `MYSQL_PASSWORD`

## Recomendaciones

- Para pruebas unitarias/integración prefiere SQLite con archivos temporales (como hace `tests/BaseTestCase.php`).
- En CI, usa un servicio MariaDB o SQLite dependiendo de tus necesidades.
- Mantén las migraciones pequeñas y atómicas. Evita dependencias implícitas entre migraciones.

## Ejemplos rápidos

- Crear tabla `users` en una migración (nombres de campos en inglés):

```php
<?php
return function(PDO $pdo){
  $pdo->exec(
    "CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, password TEXT, created_at DATETIME)"
  );
};
```

`scripts/migrate.php` carga `Ondine\Database\Migrator` y ejecuta los archivos de migración encontrados en `migrations/`.

## Pruebas y rollback

- `Migrator::migrate()` registra migraciones aplicadas (si soportado) y ejecuta solo las pendientes.
- `Migrator::rollback()` debería revertir la migración más reciente si se proporciona un callable de rollback.

---

Este archivo fue actualizado para usar documentación en inglés y para referenciar el directorio `data/` de la raíz del proyecto como ubicación sqlite por defecto.