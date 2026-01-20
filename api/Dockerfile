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

# Install dependencies (include dev to avoid crashes if switched to dev mode)
RUN composer install --optimize-autoloader --no-scripts --no-progress

# Copy application source
COPY . .

# Run composer post-install scripts
RUN composer dump-autoload --optimize --classmap-authoritative \
    && composer run-script post-install-cmd

# Permissions
RUN chown -R www-data:www-data var && chmod -R 777 var

# Entrypoint
COPY bin/console /var/www/symfony/bin/console
RUN chmod +x /var/www/symfony/bin/console

CMD ["php-fpm"]
