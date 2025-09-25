# Ondine Skeleton

[![PHP Version](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Un esqueleto de aplicación minimalista para iniciar rápidamente nuevos proyectos con el microframework PHP Ondine. Obtén una API REST completamente funcional con autenticación en funcionamiento en minutos.

## ✨ Características

- 🚀 **Configuración Rápida**: API funcional en menos de 5 minutos
- 🔐 **Autenticación JWT**: Inicio de sesión seguro con tokens de refresco
- 👥 **Gestión de Usuarios**: CRUD completo para usuarios y perfiles
- 📚 **Documentación Interactiva**: Interfaz Swagger UI integrada en `/docs`
- 🗄️ **Base de Datos Lista**: SQLite (desarrollo) o MariaDB/MySQL (producción)
- 🧩 **Extensible**: Fácil agregar controladores, rutas y middleware

## 📦 Instalación

### Via Composer Create-Project

```bash
# Recomendado: Instalar versión estable (cuando esté disponible)
composer create-project pnbarbeito/ondine-skeleton my-app

# Alternativa: Instalar versión de desarrollo
composer create-project pnbarbeito/ondine-skeleton my-app dev-main

# O con estabilidad dev
composer create-project pnbarbeito/ondine-skeleton my-app --stability dev
```

## 🚀 Inicio Rápido

1. **Instalar dependencias:**
   ```bash
   composer install
   ```

2. **Configurar entorno:**
   ```bash
   cp config/.env.example config/.env
   # Editar config/.env con tus configuraciones
   ```

3. **Ejecutar migraciones:**
   ```bash
   php scripts/migrate.php
   ```

4. **Iniciar servidor:**
   ```bash
   php -S 0.0.0.0:8000 -t public
   ```

5. **Probarlo:**
   - Frontend: http://localhost:8000
   - Documentación API: http://localhost:8000/docs

## 🚀 Despliegue en Producción

El esqueleto incluye una configuración completa basada en Docker para producción en la carpeta `docker/`:

- **`docker-compose.prod.yml`**: Orquestación de contenedores lista para producción con Nginx, PHP-FPM y MariaDB
- **`Dockerfile`**: Construcción multi-etapa para contenedor PHP optimizado
- **`nginx.conf`**: Configuración de Nginx con proxy FastCGI y servicio de archivos estáticos
- **Configuración de entorno**: Pre-configurada para producción con configuraciones de seguridad apropiadas

### Configuración Rápida de Producción

1. **Navegar a la carpeta docker:**
   ```bash
   cd docker
   ```

2. **Configurar entorno:**
   ```bash
   cp .env.example .env
   # Editar .env con tus configuraciones de producción (base de datos, secretos, etc.)
   ```

3. **Desplegar:**
   ```bash
   docker compose -f docker-compose.prod.yml up -d
   ```

4. **Ejecutar migraciones:**
   ```bash
   docker compose -f docker-compose.prod.yml exec app php scripts/migrate.php
   ```

La aplicación estará disponible en `http://localhost` (o tu dominio configurado).

> 📖 **Documentación completa**: Ver `docker/README.md` y `docker/README.es.md` para guías detalladas de despliegue en producción en inglés y español.

## 📋 Endpoints de API

El esqueleto proporciona endpoints REST listos para usar bajo `/api`:

### Autenticación
- `POST /api/login` - Inicio de sesión de usuario
- `POST /api/refresh` - Refrescar token JWT
- `POST /api/logout` - Cerrar sesión de usuario
- `GET /api/me` - Obtener información del usuario actual

### Usuarios (CRUD)
- `GET /api/users` - Listar usuarios
- `GET /api/users/{id}` - Obtener usuario por ID
- `POST /api/users` - Crear usuario
- `PUT /api/users/{id}` - Actualizar usuario
- `DELETE /api/users/{id}` - Eliminar usuario

### Perfiles
- `GET /api/profiles` - Listar perfiles
- `GET /api/profiles/{id}` - Obtener perfil por ID

### Ejemplos de Uso

Iniciar sesión y obtener un token:
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"sysadmin","password":"SecureAdmin2025"}'
```

Usar el token para solicitudes autenticadas:
```bash
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  http://localhost:8000/api/me
```

## 📁 Estructura del Proyecto

```
my-app/
├── config/          # Configuración de entorno (.env)
├── data/            # Archivos de base de datos (SQLite)
├── migrations/      # Migraciones de base de datos
├── public/          # Raíz web (index.php, docs/)
├── scripts/         # Scripts de utilidad (migrate.php)
├── src/             # Código de aplicación
│   ├── Controllers/ # Controladores de API
│   ├── Middleware/  # Middleware personalizado
│   └── ...          # Modelos, servicios, etc.
├── tests/           # Tests de PHPUnit
└── vendor/          # Dependencias de Composer
```

## ⚙️ Configuración

### Variables de Entorno

Copiar `config/.env.example` a `config/.env` y configurar:

```bash
# Base de datos
DB_DRIVER=sqlite  # o mariadb
DB_SQLITE_PATH=./data/database.sqlite

# Secreto JWT (¡CAMBIAR EN PRODUCCIÓN!)
JWT_SECRET=your_secure_jwt_secret_here

# Seeds de Usuario Admin
SEED_ADMIN_USERNAME=sysadmin
SEED_ADMIN_PASSWORD=SecureAdmin2025
```

### Base de Datos

**SQLite (Por defecto - Desarrollo):**
- Archivo: `data/database.sqlite`
- No requiere configuración adicional

**MariaDB/MySQL (Producción):**
```bash
DB_DRIVER=mariadb
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_DATABASE=ondine
MYSQL_USER=your_user
MYSQL_PASSWORD=your_password
```

## 🛠️ Desarrollo

### Ejecutar Tests

```bash
composer install --dev  # Instalar dependencias de desarrollo
composer test
# o
./vendor/bin/phpunit --colors=always
```

### Cobertura de Tests

```bash
composer test-coverage
# Ver reporte de cobertura en coverage/index.html
```

### Migraciones

```bash
# Ejecutar todas las migraciones
php scripts/migrate.php migrate

# Revertir última migración
php scripts/migrate.php rollback 1

# Valores de seed personalizados
env SEED_ADMIN_USERNAME=custom php scripts/migrate.php migrate
```

### Agregar Nuevas Funcionalidades

1. **Controladores**: Agregar en `src/Controllers/`
2. **Rutas**: Registrar en `public/index.php`
3. **Middleware**: Agregar en `src/Middleware/`
4. **Migraciones**: Crear en `migrations/`

## 🔧 Documentación de API

Interfaz Swagger UI interactiva disponible en `http://localhost:8000/docs`

- Captura automáticamente tokens JWT de respuestas de login
- Probar todos los endpoints directamente desde el navegador
- Ver esquemas de solicitud/respuesta

## 🐛 Solución de Problemas

### Errores de Permisos en Docker
Usar volúmenes nombrados para almacenamiento persistente o ejecutar migraciones localmente.

### Problemas de Conexión a Base de Datos
- Verificar configuración de `.env`
- Asegurar que el servidor de base de datos esté ejecutándose
- Verificar permisos de usuario

### Conflictos de Puerto
Cambiar el puerto en el comando del servidor PHP:
```bash
php -S 0.0.0.0:8081 -t public
```

## 🤝 Contribuyendo

1. Hacer fork del repositorio
2. Crear una rama de funcionalidad
3. Agregar tests para nueva funcionalidad
4. Asegurar que todos los tests pasen
5. Enviar pull request

## 📄 Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

## 🔗 Enlaces

- [Ondine Framework](https://github.com/pnbarbeito/ondine) - Repositorio principal del framework
- [Packagist](https://packagist.org/packages/pnbarbeito/ondine-skeleton) - Paquete de Composer
- [Documentación](https://github.com/pnbarbeito/ondine/wiki) - Documentación completa

---

Construido con ❤️ usando el microframework [Ondine](https://github.com/pnbarbeito/ondine).