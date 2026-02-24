# Syntax for Dokploy/Production
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    acl \
    file \
    gettext \
    git \
    mariadb-client \
    python3 \
    py3-pip \
    icu-dev \
    zlib-dev \
    libzip-dev \
    ffmpeg \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-configure intl \
    && docker-php-ext-install \
    intl \
    opcache \
    pdo_mysql \
    zip

# Install yt-dlp via pip (apk package might be outdated or missing)
# Create a virtual environment for python tools to avoid breaking system packages
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"
RUN pip3 install --upgrade pip \
    && pip3 install yt-dlp


# JWT key paths (static filesystem paths, not secrets)
ENV JWT_SECRET_KEY="%kernel.project_dir%/config/jwt/private.pem"
ENV JWT_PUBLIC_KEY="%kernel.project_dir%/config/jwt/public.pem"
# All other env vars (DATABASE_URL, CORS_ALLOW_ORIGIN, DEFAULT_URI,
# JWT_PASSPHRASE, APP_SECRET) are provided at runtime by docker-compose.
# Never bake secrets into the image as build args.

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/symfony

# Set recommended PHP.ini settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Define build arguments
ARG APP_ENV=prod

# Copy composer files
COPY composer.json composer.lock symfony.lock ./

# Create empty .env file to avoid errors if not provided
RUN touch .env

# Install dependencies (with dev dependencies for development)
RUN composer install --optimize-autoloader --no-scripts --no-progress

# Copy application source
COPY . .


COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]

