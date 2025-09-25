# Ondine - Configuración

Este archivo documenta la configuración central del proyecto y cómo adaptarla a diferentes entornos.

## Archivo de configuración principal

- `config/config.php` — configuración por defecto incluida en el repositorio. No almacenes secretos sensibles aquí para producción.

Ejemplo de claves (resumen):

- `db.driver` — `sqlite` o `mariadb`.
- `db.sqlite.path` — ruta al archivo sqlite.
  - Nota: por defecto el proyecto usa `data/database.sqlite` en la raíz del proyecto. Evita colocar la BD dentro de `public/`.
- `db.mariadb.*` — host, port, database, user, password, charset.

## Variables de entorno (recomendado)

En producción prefiere variables de entorno para secretos y parámetros específicos del entorno. Variables importantes:

- `DB_DRIVER` — `sqlite` o `mariadb`.
- `DB_SQLITE_PATH` — cuando uses sqlite.
- `MYSQL_HOST`, `MYSQL_PORT`, `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD` — para MariaDB.
- `JWT_SECRET` — secreto HMAC para JWT.
- `REFRESH_TOKEN_SECRETS` — (opcional) lista serializada de secretos para rotación.
- `REFRESH_TOKEN_SECRET_VERSION` — índice/versión actual del secreto a usar.

## Ejemplo de desarrollo (.env)

```env
DB_DRIVER=sqlite
DB_SQLITE_PATH=./data/dev.sqlite
JWT_SECRET=dev_jwt_secret
REFRESH_TOKEN_SECRET=dev_refresh_secret
REFRESH_TOKEN_SECRET_VERSION=1
```

## Recomendaciones de seguridad

- Nunca commits secretos al control de versiones.
- Usa un gestor de secretos (Vault, AWS Secrets Manager) en producción.
- Rota `REFRESH_TOKEN_SECRET` periódicamente y usa `secret_version` para mantener sesiones existentes verificables.

---

Este archivo fue actualizado al inglés y para hacer explícita la ubicación por defecto de sqlite en `data/` de la raíz del proyecto.