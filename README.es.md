
# Ondine — microframework PHP mínimo (REST)

Ondine es un microframework PHP muy pequeño diseñado para exponer endpoints REST e integrarse con frontends (por ejemplo, React). Este repositorio contiene la librería en `src/` y una aplicación de ejemplo en `examples/public/`.

## Estructura del proyecto
- `src/` — librería principal (PSR-4, exportable como paquete Composer)
- `examples/public/` — aplicación de ejemplo e interfaz de prueba que consume la librería
- `migrations/` — migraciones PHP versionadas
- `data/` — almacenamiento por defecto para el archivo de base de datos SQLite

## Requisitos
- PHP 7.4+ (PHP 8+ recomendado)
- Composer (recomendado para autoload PSR-4 y herramientas)

## Inicio rápido

Este repositorio contiene la librería en `src/` y una aplicación de ejemplo en `examples/public/`.

1) **Instalar dependencias (recomendado)**

```bash
composer install
```

2) **Usar Ondine como dependencia Composer**

Si publicas el paquete en Packagist (nombre recomendado: `ondine/ondine`), puedes instalarlo en otro proyecto usando:

```bash
composer require ondine/ondine
```

Para desarrollo local puedes usar un repositorio `path` en el `composer.json` de tu proyecto:

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

3) **Configurar variables de entorno para la aplicación de ejemplo**

Copia el ejemplo y edítalo (la aplicación de ejemplo usa `config/.env` en la raíz del repositorio):

```bash
cp config/.env.example config/.env
# Edita `config/.env` y configura DB_DRIVER y secretos según necesites
```

Por defecto Ondine usa SQLite y guarda el archivo en `data/database.sqlite` en la raíz del proyecto. Para usar MariaDB configura `DB_DRIVER=mariadb` y actualiza las variables `MYSQL_*` en el archivo `.env`.

4) **Ejecutar la aplicación de ejemplo**

Inicia un servidor PHP local (desde la raíz del repositorio):

```bash
php -S 0.0.0.0:8080 -t examples/public
```

Abre `http://localhost:8080` para acceder a la interfaz de ejemplo y `http://localhost:8080/docs` para la interfaz OpenAPI.

Ejecutar migraciones para la aplicación de ejemplo (los ejemplos usan los scripts en `examples/scripts/`):

```bash
php examples/scripts/migrate.php migrate
```

Rollback (ejemplo: rollback de la última migración):

```bash
php examples/scripts/migrate.php rollback 1
```

## Documentación de API (OpenAPI / Swagger)

Abre `http://localhost:8080/docs` para acceder a la documentación interactiva de la API. La interfaz captura el `token` devuelto por `POST /api/login` y lo aplica automáticamente como `Authorization: Bearer <token>` para solicitudes posteriores.

## Endpoints principales

- **Autenticación**
  - `POST /api/login` — body: `{ username, password }` → devuelve `{ token, refresh_token }`
  - `POST /api/token/refresh` — body: `{ refresh_token }` → devuelve `{ token }`
  - `POST /api/logout` — body: `{ refresh_token }` → revoca la sesión
  - `GET /api/me` — requiere `Authorization: Bearer <token>`

- **Usuarios**
  - `GET /api/users`
  - `GET /api/users/{id}`
  - `POST /api/users`
  - `PUT /api/users/{id}`
  - `PUT /api/users/{id}/change-password` — body: `{ new_password }` → requiere `Authorization: Bearer <token>`
  - `DELETE /api/users/{id}`

- **Perfiles**
  - `GET /api/profiles`
  - `GET /api/profiles/{id}`
  - `GET /api/profiles/distinct-permissions` — requiere `Authorization: Bearer <token>`
  - `POST /api/profiles`
  - `PUT /api/profiles/{id}`
  - `DELETE /api/profiles/{id}`

- **Usuario (operaciones del usuario autenticado)**
  - `PUT /api/user/theme` — body: `{ theme }` → requiere `Authorization: Bearer <token>`
  - `PUT /api/user/profile` — body: `{ first_name?, last_name? }` → requiere `Authorization: Bearer <token>`
  - `PUT /api/user/password` — body: `{ current_password, new_password }` → requiere `Authorization: Bearer <token>`

## Pruebas

La suite de pruebas usa PHPUnit y una base de datos SQLite efímera para cada prueba. Ejecuta las pruebas con:

```bash
composer test
# o
./vendor/bin/phpunit --colors=always
```

## Variables de seed (usadas por las migraciones)

- `SEED_PROFILE_NAME` — por defecto: `Administrator`
- `SEED_PROFILE_PERMISSIONS` — cadena JSON, por defecto: `{"admin":1,"profiles":1,"users":1}`
- `SEED_ADMIN_USERNAME` — por defecto: `sysadmin`
- `SEED_ADMIN_PASSWORD` — por defecto: `SysAdmin8590`
- `SEED_ADMIN_FIRSTNAME` — por defecto: `Sys`
- `SEED_ADMIN_LASTNAME` — por defecto: `Admin`
- `SEED_ADMIN_STATE` — por defecto: `1`

## Migraciones

Las migraciones son archivos PHP simples ubicados en `migrations/`. Son conscientes del driver (SQLite vs MariaDB) y crean el perfil inicial y usuario administrador usando variables de entorno para que puedas personalizarlos en despliegues y pruebas.

### Comandos comunes (compatibles con fish / bash)

- **Asegurar dependencias y archivo de entorno:**

```bash
composer install
cp config/.env.example config/.env
# Edita `config/.env` con tu `DB_DRIVER` y detalles de conexión si es necesario
```

- **Ejecutar todas las migraciones:**

```bash
php scripts/migrate.php migrate
```

- **Rollback de las últimas N migraciones (ejemplo: rollback 1):**

```bash
php scripts/migrate.php rollback 1
```

- **Configurar variables de seed (ejemplo):**

```bash
env SEED_ADMIN_USERNAME=adminpersonalizado SEED_ADMIN_PASSWORD=secreto php scripts/migrate.php migrate
```

## Proyecto Skeleton

Este repositorio incluye una aplicación skeleton mínima que sirve como punto de partida para construir aplicaciones basadas en Ondine. El skeleton está disponible como un repositorio separado en [`pnbarbeito/ondine-skeleton`](https://github.com/pnbarbeito/ondine-skeleton).

### Crear un proyecto desde el skeleton

**Opción A — Usando Composer create-project (recomendado):**

```bash
# Instalar versión estable
composer create-project pnbarbeito/ondine-skeleton my-app

# O instalar versión de desarrollo
composer create-project pnbarbeito/ondine-skeleton my-app dev-main

# O con estabilidad dev
composer create-project pnbarbeito/ondine-skeleton my-app --stability dev
```

**Opción B — Copia manual (para desarrollo):**

```bash
# Copiar el skeleton a una nueva carpeta
cp -R examples/skeleton my-app
cd my-app
composer install
cp config/.env.example config/.env
# Editar config/.env si es necesario
php scripts/migrate.php
php -S 0.0.0.0:8000 -t public
```

El skeleton incluye:
- Estructura completa del proyecto con autoload PSR-4
- Autenticación JWT con tokens de refresco
- Gestión de usuarios con perfiles y permisos
- Documentación interactiva de API (Swagger UI)
- Configuración Docker para despliegue en producción
- Suite de pruebas PHPUnit
- Controladores y middleware de ejemplo

## Seguridad y notas

- Configura `JWT_SECRET` y `REFRESH_TOKEN_SECRET` en `config/.env` y no los incluyas en el control de versiones.
- SQLite está destinado para desarrollo y pruebas. Para producción usa MariaDB/MySQL o PostgreSQL.
- Considera Redis para rate-limiting en entornos de alta concurrencia.
- El framework soporta permisos basados en perfiles para control de acceso granular.

## Contribuyendo

Las contribuciones son bienvenidas. Por favor abre issues con propuestas de cambios o envía pull requests. Para desarrollo:

```bash
composer install
./vendor/bin/phpunit --colors=always
```

## Licencia

Este proyecto está licenciado bajo la Licencia MIT - consulta el archivo [LICENSE](LICENSE) para más detalles.
