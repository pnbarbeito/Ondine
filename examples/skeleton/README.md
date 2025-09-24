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
