# Ondine - Configuration

This file documents the project's central configuration and how to adapt it to different environments.

## Main config file

- `config/config.php` — default configuration shipped in the repository. Do not store sensitive secrets here for production.

Example keys (summary):

- `db.driver` — `sqlite` or `mariadb`.
- `db.sqlite.path` — path to the sqlite file.
  - Note: by default the project uses `data/database.sqlite` in the project root. Avoid placing the DB inside `public/`.
- `db.mariadb.*` — host, port, database, user, password, charset.


## Environment variables (recommended)

In production prefer environment variables for secrets and environment-specific parameters. Important variables:

- `DB_DRIVER` — `sqlite` or `mariadb`.
- `DB_SQLITE_PATH` — when using sqlite.
- `MYSQL_HOST`, `MYSQL_PORT`, `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD` — for MariaDB.
- `JWT_SECRET` — HMAC secret for JWT.
- `REFRESH_TOKEN_SECRETS` — (optional) serialized list of secrets for rotation.
- `REFRESH_TOKEN_SECRET_VERSION` — index/current version of the secret to use.


## Development example (.env)

```env
DB_DRIVER=sqlite
DB_SQLITE_PATH=./data/dev.sqlite
JWT_SECRET=dev_jwt_secret
REFRESH_TOKEN_SECRET=dev_refresh_secret
REFRESH_TOKEN_SECRET_VERSION=1
```


## Security recommendations

- Never commit secrets to version control.
- Use a secrets manager (Vault, AWS Secrets Manager) in production.
- Rotate `REFRESH_TOKEN_SECRET` periodically and use `secret_version` to keep existing sessions verifiable.


---

This file was updated to English and to make the project-root `data/` sqlite default explicit.
