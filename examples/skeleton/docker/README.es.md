# Despliegue en Producción con Docker

Este directorio contiene la configuración completa para desplegar Ondine en un entorno de producción usando Docker y Docker Compose.

## Archivos Incluidos

- `docker-compose.yml`: Configuración de servicios (Nginx, PHP-FPM, MariaDB)
- `nginx.conf`: Configuración de Nginx para servir el frontend y proxy a la API
- `Dockerfile`: Imagen personalizada de PHP-FPM con Ondine

## Prerrequisitos

- Docker y Docker Compose instalados
- Puerto 8080 disponible (o cambiar en docker-compose.yml)
- Puerto 13306 disponible para MariaDB (opcional, para acceso directo)

## Pasos de Despliegue

### 1. Preparar el Frontend

Si tienes un frontend React/Vite/etc.:

```bash
# En el directorio del frontend
npm run build
# Copiar el build a docker/frontend/build
cp -r build ../docker/frontend/build
```

### 2. Preparar la Documentación

```bash
# Copiar docs de Ondine
cp -r ../public/docs docker/public/docs
```

### 3. Configurar Variables de Entorno

Edita las variables en `docker-compose.yml`:

- `JWT_SECRET`: Cambia a un secreto seguro en producción
- `MYSQL_*`: Configura credenciales de DB seguras
- `SEED_*`: Configura usuario admin inicial

### 4. Crear la Red Docker (si no existe)

```bash
docker network create container-network
```

### 5. Iniciar los Servicios

```bash
docker compose --project-name ondine up -d
```

### 6. Ejecutar Migraciones

```bash
docker exec ondine php /var/www/html/scripts/migrate.php
```

### 7. Verificar

- Frontend: http://localhost:8080
- API Docs: http://localhost:8080/docs
- Base de datos: localhost:13306 (con usuario/password de docker-compose.yml)

## Configuración de Producción

### SSL/HTTPS

Para producción, agrega SSL:

1. Obtén certificados SSL
2. Monta los certificados en Nginx
3. Actualiza `nginx.conf` para escuchar en 443 y redirigir 80 a 443

### Variables de Entorno

Crea un archivo `.env` y móntalo en el contenedor `ondine`:

```yaml
volumes:
  - ./config/.env:/var/www/html/config/.env:ro
```

### Backup de Base de Datos

```bash
docker exec ondine-db mysqldump -u ondine -pDBManagerOndine2025 ondine > backup.sql
```

### Logs

```bash
docker logs ondine-nginx
docker logs ondine
docker logs ondine-db
```

## Solución de Problemas

- **502 Bad Gateway**: Verifica que `ondine` esté corriendo y accesible
- **404 en API**: Revisa logs de PHP y configuración de rutas
- **Error de DB**: Ejecuta migraciones y verifica credenciales

## Seguridad

- Cambia todas las contraseñas por defecto
- No expongas puertos innecesarios en producción
- Usa secrets de Docker para credenciales sensibles
- Mantén las imágenes actualizadas