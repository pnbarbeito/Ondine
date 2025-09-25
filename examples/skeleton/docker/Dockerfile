# Dockerfile for Ondine - Custom PHP-FPM image
# Base: PHP 8.4 with FPM on Alpine Linux
FROM php:8.4-fpm-alpine  

ARG USER_ID=1000  # UID for www-data (configurable)
ARG GROUP_ID=1000  # GID for www-data (configurable)

# Install system dependencies and PHP extensions
RUN set -eux; \
	apk add --no-cache --update shadow git unzip libzip-dev oniguruma-dev icu-dev zlib-dev curl; \
	# Install build dependencies for PHP extensions
	apk add --no-cache --virtual .build-deps $PHPIZE_DEPS autoconf g++ make sqlite-dev; \
	# Install common PHP extensions (PDO, MySQL, SQLite, etc.)
	docker-php-ext-install pdo pdo_mysql pdo_sqlite zip intl opcache; \
	# Clean up build dependencies
	apk del .build-deps; \
	rm -rf /var/cache/apk/*

# Install Composer for PHP dependency management
ENV COMPOSER_HOME=/tmp
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html  # Working directory

# Create www-data user with predictable UID/GID
RUN usermod -u ${USER_ID} www-data || useradd -u ${USER_ID} -G www-data -s /sbin/nologin www-data

# Copy Composer files first (for Docker cache)
COPY composer.json composer.lock* /var/www/html/

# Install PHP dependencies (production by default)
RUN composer install --no-dev --prefer-dist --no-scripts --no-interaction --no-progress --optimize-autoloader || \
	composer install --no-dev --prefer-dist --no-scripts --no-interaction --no-progress --optimize-autoloader

# Copy the rest of the application files
COPY . /var/www/html/

# Remove docker directory to avoid recursion
RUN rm -rf /var/www/html/docker

# Ensure correct permissions for mounted files at runtime
RUN chown -R www-data:www-data /var/www/html

# Port for PHP-FPM
EXPOSE 9000  

# Default command: start PHP-FPM
CMD ["php-fpm"]  