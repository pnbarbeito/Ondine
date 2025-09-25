# Production Deployment with Docker

This directory contains the complete configuration to deploy Ondine in a production environment using Docker and Docker Compose.

## Included Files

- `docker-compose.yml`: Service configuration (Nginx, PHP-FPM, MariaDB)
- `nginx.conf`: Nginx configuration to serve the frontend and proxy to the API
- `Dockerfile`: Custom PHP-FPM image with Ondine

## Prerequisites

- Docker and Docker Compose installed
- Port 8080 available (or change in docker-compose.yml)
- Port 13306 available for MariaDB (optional, for direct access)

## Deployment Steps

### 1. Prepare the Frontend

If you have a React/Vite/etc. frontend:

```bash
# In the frontend directory
npm run build
# Copy the build to docker/frontend/build
cp -r build ../docker/frontend/build
```

### 2. Prepare the Documentation

```bash
# Copy Ondine docs
cp -r ../public/docs docker/public/docs
```

### 3. Configure Environment Variables

Edit the variables in `docker-compose.yml`:

- `JWT_SECRET`: Change to a secure secret in production
- `MYSQL_*`: Configure secure DB credentials
- `SEED_*`: Configure initial admin user

### 4. Create the Docker Network (if it doesn't exist)

```bash
docker network create container-network
```

### 5. Start the Services

```bash
docker compose --project-name ondine up -d
```

### 6. Run Migrations

```bash
docker exec ondine php /var/www/html/scripts/migrate.php
```

### 7. Verify

- Frontend: http://localhost:8080
- API Docs: http://localhost:8080/docs
- Database: localhost:13306 (with user/password from docker-compose.yml)

## Production Configuration

### SSL/HTTPS

For production, add SSL:

1. Obtain SSL certificates
2. Mount certificates in Nginx
3. Update `nginx.conf` to listen on 443 and redirect 80 to 443

### Environment Variables

Create a `.env` file and mount it in the `ondine` container:

```yaml
volumes:
  - ./config/.env:/var/www/html/config/.env:ro
```

### Database Backup

```bash
docker exec ondine-db mysqldump -u ondine -pDBManagerOndine2025 ondine > backup.sql
```

### Logs

```bash
docker logs ondine-nginx
docker logs ondine
docker logs ondine-db
```

## Troubleshooting

- **502 Bad Gateway**: Check that `ondine` is running and accessible
- **404 on API**: Check PHP logs and route configuration
- **DB Error**: Run migrations and verify credentials

## Security

- Change all default passwords
- Don't expose unnecessary ports in production
- Use Docker secrets for sensitive credentials
- Keep images updated</content>
<parameter name="filePath">/home/pbarbeito/Dev/Ondine/examples/docker/README.md