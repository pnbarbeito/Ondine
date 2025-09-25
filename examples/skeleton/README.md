# Ondine Skeleton

[![PHP Version](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

A minimal application skeleton for quickly bootstrapping new projects with the Ondine PHP microframework. Get a fully functional REST API with authentication up and running in minutes.

## ✨ Features

- 🚀 **Rapid Setup**: Working API in under 5 minutes
- 🔐 **JWT Authentication**: Secure login with refresh tokens
- 👥 **User Management**: Full CRUD for users and profiles
- 📚 **Interactive Docs**: Built-in Swagger UI at `/docs`
- 🗄️ **Database Ready**: SQLite (dev) or MariaDB/MySQL (prod)
- 🧩 **Extensible**: Easy to add controllers, routes, and middleware

## 📦 Installation

### Via Composer Create-Project

```bash
# Recommended: Install stable version (when available)
composer create-project pnbarbeito/ondine-skeleton my-app

# Alternative: Install development version
composer create-project pnbarbeito/ondine-skeleton my-app dev-main

# Or with dev stability
composer create-project pnbarbeito/ondine-skeleton my-app --stability dev
```

## 🚀 Quick Start

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Configure environment:**
   ```bash
   cp config/.env.example config/.env
   # Edit config/.env with your settings
   ```

3. **Run migrations:**
   ```bash
   php scripts/migrate.php
   ```

4. **Start the server:**
   ```bash
   php -S 0.0.0.0:8000 -t public
   ```

5. **Test it:**
   - Frontend: http://localhost:8000
   - API Docs: http://localhost:8000/docs

## � Production Deployment

The skeleton includes a complete Docker-based production setup in the `docker/` folder:

- **`docker-compose.prod.yml`**: Production-ready container orchestration with Nginx, PHP-FPM, and MariaDB
- **`Dockerfile`**: Multi-stage build for optimized PHP container
- **`nginx.conf`**: Nginx configuration with FastCGI proxy and static file serving
- **Environment setup**: Pre-configured for production with proper security settings

### Quick Production Setup

1. **Navigate to docker folder:**
   ```bash
   cd docker
   ```

2. **Configure environment:**
   ```bash
   cp .env.example .env
   # Edit .env with your production settings (database, secrets, etc.)
   ```

3. **Deploy:**
   ```bash
   docker compose -f docker-compose.prod.yml up -d
   ```

4. **Run migrations:**
   ```bash
   docker compose -f docker-compose.prod.yml exec app php scripts/migrate.php
   ```

The application will be available at `http://localhost` (or your configured domain).

> 📖 **Full documentation**: See `docker/README.md` and `docker/README.es.md` for detailed production deployment guides in English and Spanish.

## �📋 API Endpoints

The skeleton provides ready-to-use REST endpoints under `/api`:

### Authentication
- `POST /api/login` - User login
- `POST /api/refresh` - Refresh JWT token
- `POST /api/logout` - Logout user
- `GET /api/me` - Get current user info

### Users (CRUD)
- `GET /api/users` - List users
- `GET /api/users/{id}` - Get user by ID
- `POST /api/users` - Create user
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user

### Profiles
- `GET /api/profiles` - List profiles
- `GET /api/profiles/{id}` - Get profile by ID

### Example Usage

Login and get a token:
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"sysadmin","password":"SecureAdmin2025"}'
```

Use the token for authenticated requests:
```bash
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  http://localhost:8000/api/me
```

## 📁 Project Structure

```
my-app/
├── config/          # Environment configuration (.env)
├── data/            # Database files (SQLite)
├── migrations/      # Database migrations
├── public/          # Web root (index.php, docs/)
├── scripts/         # Utility scripts (migrate.php)
├── src/             # Application code
│   ├── Controllers/ # API controllers
│   ├── Middleware/  # Custom middleware
│   └── ...          # Models, services, etc.
├── tests/           # PHPUnit tests
└── vendor/          # Composer dependencies
```

## ⚙️ Configuration

### Environment Variables

Copy `config/.env.example` to `config/.env` and configure:

```bash
# Database
DB_DRIVER=sqlite  # or mariadb
DB_SQLITE_PATH=./data/database.sqlite

# JWT Secret (CHANGE IN PRODUCTION!)
JWT_SECRET=your_secure_jwt_secret_here

# Admin User Seeds
SEED_ADMIN_USERNAME=sysadmin
SEED_ADMIN_PASSWORD=SecureAdmin2025
```

### Database

**SQLite (Default - Development):**
- File: `data/database.sqlite`
- No additional setup required

**MariaDB/MySQL (Production):**
```bash
DB_DRIVER=mariadb
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_DATABASE=ondine
MYSQL_USER=your_user
MYSQL_PASSWORD=your_password
```

## 🛠️ Development

### Running Tests

```bash
composer install --dev  # Install development dependencies
composer test
# or
./vendor/bin/phpunit --colors=always
```

### Test Coverage

```bash
composer test-coverage
# View coverage report in coverage/index.html
```

### Migrations

```bash
# Run all migrations
php scripts/migrate.php migrate

# Rollback last migration
php scripts/migrate.php rollback 1

# Custom seed values
env SEED_ADMIN_USERNAME=custom php scripts/migrate.php migrate
```

### Adding New Features

1. **Controllers**: Add to `src/Controllers/`
2. **Routes**: Register in `public/index.php`
3. **Middleware**: Add to `src/Middleware/`
4. **Migrations**: Create in `migrations/`

## 🔧 API Documentation

Interactive Swagger UI available at `http://localhost:8000/docs`

- Automatically captures JWT tokens from login responses
- Test all endpoints directly from the browser
- View request/response schemas

## 🐛 Troubleshooting

### Permission Errors in Docker
Use named volumes for persistent storage or run migrations locally.

### Database Connection Issues
- Verify `.env` configuration
- Ensure database server is running
- Check user permissions

### Port Conflicts
Change the port in the PHP server command:
```bash
php -S 0.0.0.0:8081 -t public
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🔗 Links

- [Ondine Framework](https://github.com/pnbarbeito/ondine) - Main framework repository
- [Packagist](https://packagist.org/packages/pnbarbeito/ondine-skeleton) - Composer package
- [Documentation](https://github.com/pnbarbeito/ondine/wiki) - Full documentation

---

Built with ❤️ using the [Ondine](https://github.com/pnbarbeito/ondine) microframework.


# Ondine-skeleton
