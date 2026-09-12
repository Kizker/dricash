# ==============================================================================
# Multi-Stage Dockerfile for Dricash - Smart Financial & Wealth Planner
# Production-ready: Laravel 11 + Vue 3 (Inertia.js) + Vite + Nginx + PHP-FPM 8.2
# ==============================================================================

# ------------------------------------------------------------------------------
# Stage 1: Build Frontend Assets (Vite & Inertia.js)
# ------------------------------------------------------------------------------
FROM node:20-alpine AS frontend_builder

WORKDIR /app

# Copy dependency definitions
COPY package.json bun.lock* package-lock.json* ./

# Install frontend dependencies
RUN npm install

# Copy application resources required for Vite build
COPY resources ./resources
COPY vite.config.js ./
COPY public ./public

# Compile production assets to /app/public/build
RUN npm run build

# ------------------------------------------------------------------------------
# Stage 2: Install PHP & Composer Dependencies
# ------------------------------------------------------------------------------
FROM composer:2 AS composer_builder

WORKDIR /app

# Copy composer manifests
COPY composer.json composer.lock ./

# Install production dependencies only (no dev packages)
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-scripts \
    --no-progress \
    --no-interaction \
    --optimize-autoloader

# ------------------------------------------------------------------------------
# Stage 3: Production Runtime (Nginx + PHP 8.2 FPM Alpine)
# ------------------------------------------------------------------------------
FROM php:8.2-fpm-alpine AS runner

# Install system dependencies and build libraries
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    bash \
    tzdata \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Configure PHP for production
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.revalidate_freq=2'; \
        echo 'opcache.fast_shutdown=1'; \
        echo 'memory_limit=256M'; \
        echo 'upload_max_filesize=50M'; \
        echo 'post_max_size=50M'; \
        echo 'max_execution_time=60'; \
    } > /usr/local/etc/php/conf.d/dricash-production.ini

WORKDIR /var/www/html

# Copy application source code
COPY . /var/www/html

# Copy compiled vendor from composer_builder
COPY --from=composer_builder /app/vendor /var/www/html/vendor

# Copy compiled frontend assets from frontend_builder
COPY --from=frontend_builder /app/public/build /var/www/html/public/build

# Copy server configuration files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Ensure entrypoint is executable and storage permissions are set
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose web server port
EXPOSE 80

# Healthcheck via Laravel 11 native /up route
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://127.0.0.1/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
