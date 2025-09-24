
# Ondine — microframework PHP mínimo (REST)

Ondine es un microframework PHP muy pequeño diseñado para exponer endpoints REST e integrarse con frontends (por ejemplo, React). Este repositorio contiene la librería en `src/` y una aplicación de ejemplo en `examples/public/`.


Estructura del proyecto
- `src/` — núcleo: router, `Request`/`Response`, middleware, controllers y helpers (librería PSR-4)
- `examples/public/` — aplicación de ejemplo que consume la librería
- `migrations/` — migrations en PHP versionadas
- `data/` — almacenamiento por defecto para el fichero de la base de datos SQLite

Requisitos
- PHP 7.4+ (recomendado PHP 8+)
- Composer (recomendado para autoload PSR-4 y herramientas)

Instalación rápida

Este repositorio contiene la librería bajo `src/` y una aplicación de ejemplo en `examples/public/`.

1) Instalar dependencias (recomendado)

```bash
composer install
```

2) Usar Ondine como dependencia Composer

Si publicas el paquete en Packagist (nombre sugerido: `ondine/ondine`), puedes instalarlo desde otro proyecto con:

```bash
composer require ondine/ondine
```

Para desarrollo local usa un repositorio `path` en el `composer.json` del proyecto que consuma la librería:

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

Luego ejecuta `composer require ondine/ondine:@dev` en el proyecto consumidor.

3) Configurar variables de entorno para la aplicación de ejemplo

Copia el ejemplo y edítalo (la app de ejemplo usa `config/.env` en la raíz del repositorio):

```bash
cp config/.env.example config/.env
# Edita `config/.env` y configura DB_DRIVER y secretos según necesites
```

Por defecto Ondine usa SQLite y guarda el fichero en `data/database.sqlite`. Para usar MariaDB configura `DB_DRIVER=mariadb` y actualiza las variables `MYSQL_*` en `config/.env`.

4) Levantar la aplicación de ejemplo

Inicia un servidor PHP local (desde la raíz del repositorio):

```bash
php -S 0.0.0.0:8080 -t examples/public
```

Abre `http://localhost:8080` para acceder a la UI de ejemplo y `http://localhost:8080/docs` para la UI OpenAPI.

Ejecutar migraciones para la app de ejemplo (usar los scripts en `examples/scripts/`):

```bash
php examples/scripts/migrate.php migrate
```

Deshacer (ej: deshacer la última migration):

```bash
php examples/scripts/migrate.php rollback 1
```

Documentación API (OpenAPI / Swagger)

Abre `http://localhost:8080/docs` para acceder a la documentación interactiva. La UI captura el `token` devuelto por `POST /api/login` y lo aplica automáticamente como `Authorization: Bearer <token>` en llamadas posteriores.

Puntos principales

- Autenticación
  - `POST /api/login` — body: `{ username, password }` → devuelve `{ token, refresh_token }`
  - `POST /api/token/refresh` — body: `{ refresh_token }` → devuelve `{ token }`
  - `POST /api/logout` — body: `{ refresh_token }` → revoca la sesión
  - `GET /api/me` — requiere `Authorization: Bearer <token>`

- Usuarios
  - `GET /api/users`
  - `GET /api/users/{id}`
  - `POST /api/users`
  - `PUT /api/users/{id}`
  - `DELETE /api/users/{id}`

- Perfiles
  - `GET /api/profiles`

Pruebas

La suite de pruebas usa PHPUnit y una base SQLite efímera por cada test. Ejecuta:

```bash
composer test
# o
./vendor/bin/phpunit --colors=always
```

Variables de seed (usadas por las migrations)

- `SEED_PROFILE_NAME` — por defecto `Administrator`
- `SEED_PROFILE_PERMISSIONS` — JSON por defecto `{"admin":1,"profiles":1,"users":1}`
- `SEED_ADMIN_USERNAME` — por defecto `sysadmin`
- `SEED_ADMIN_PASSWORD` — por defecto `SysAdmin8590`
- `SEED_ADMIN_FIRSTNAME` — por defecto `Sys`
- `SEED_ADMIN_LASTNAME` — por defecto `Admin`
- `SEED_ADMIN_STATE` — por defecto `1`

Verificar seeds

- SQLite (local):

```bash
sqlite3 data/database.sqlite "SELECT id, username, first_name, last_name, state FROM users;"
```

- MariaDB/MySQL: consulta las tablas `users` y `profiles`:

```sql
SELECT id, username, first_name, last_name, state FROM users;
SELECT id, name, permissions FROM profiles;
```

Migraciones

Las migraciones están en `migrations/`. Son conscientes del driver (SQLite vs MariaDB) y el seed inicial de perfil/admin se toma desde variables de entorno para facilitar despliegues y pruebas.

Comandos comunes (fish / bash)

```bash
composer install
cp config/.env.example config/.env
php scripts/migrate.php migrate
```

Solución de problemas

- Errores por índices únicos en MariaDB: si existe una tabla previa con duplicados, `ALTER TABLE ... ADD UNIQUE INDEX` fallará; elimina duplicados o renombra la tabla antes de aplicar la migración.
- JSON de permisos: `SEED_PROFILE_PERMISSIONS` debe ser JSON válido, p. ej. `{"admin":1,"profiles":1,"users":1}`.
- Hash de contraseña: las migraciones usan `password_hash()` con `PASSWORD_ARGON2I` cuando está disponible y caen a `PASSWORD_DEFAULT` si no.

Scripts útiles

- `php scripts/migrate.php migrate` — ejecutar migrations
- `php scripts/migrate.php rollback n` — rollback n migrations
- `php scripts/purge_sessions.php` — purga sesiones caducadas

Cambios recientes importantes

- Middlewares: ahora deben devolver `\\Ondine\\Response` si cortan la ejecución (evitar `exit`/`die`).
- `CorsMiddleware` devuelve `Response(204)` en `OPTIONS` y anexa cabeceras CORS para que `App` las fusione.
- `Request` añade fallback para construir headers desde `$_SERVER` cuando `getallheaders()` no está disponible.

Seguridad y buenas prácticas

- Configura `JWT_SECRET` y `REFRESH_TOKEN_SECRET` en `config/.env` y no los incluyas en el repositorio.
- SQLite es para desarrollo/pruebas; para producción usa MariaDB/MySQL o PostgreSQL.
- Considera Redis para rate-limiting en entornos con alta concurrencia.


Contribuir
---------

Las contribuciones son bienvenidas. Añade un `CONTRIBUTING.md` o abre issues con propuestas de cambio. Para pruebas y desarrollo:

```bash
composer install
./vendor/bin/phpunit --colors=always
```

Licencia
-------

Este proyecto se provee bajo los términos indicados en el repositorio (revisa `LICENSE` si existe) o usa MIT/Apache2 según convenga.

Si quieres, puedo añadir un ejemplo de `config/.env` para MariaDB y un `CONTRIBUTING.md` corto.
