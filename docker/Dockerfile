# php/Dockerfile
FROM php:8.4-fpm-alpine

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN set -eux; \
	apk add --no-cache --update shadow git unzip libzip-dev oniguruma-dev icu-dev zlib-dev curl; \
	# Install build deps for extensions (include sqlite-dev so pdo_sqlite can be built)
	apk add --no-cache --virtual .build-deps $PHPIZE_DEPS autoconf g++ make sqlite-dev; \
	# Install common PHP extensions
	docker-php-ext-install pdo pdo_mysql pdo_sqlite zip intl opcache; \
	# Cleanup build deps
	apk del .build-deps; \
	rm -rf /var/cache/apk/*

# Install Composer
ENV COMPOSER_HOME=/tmp
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Create www-data with predictable UID/GID
RUN usermod -u ${USER_ID} www-data || useradd -u ${USER_ID} -G www-data -s /sbin/nologin www-data

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock* /var/www/html/

# Install PHP dependencies (production by default)
RUN composer install --no-dev --prefer-dist --no-scripts --no-interaction --no-progress --optimize-autoloader || \
	composer install --no-dev --prefer-dist --no-scripts --no-interaction --no-progress --optimize-autoloader

# Copy application files (entire project structure)
COPY . /var/www/html/

# Remove docker directory from the container to avoid recursion
RUN rm -rf /var/www/html/docker

# Ensure correct permissions for mounted files at runtime
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

CMD ["php-fpm"]