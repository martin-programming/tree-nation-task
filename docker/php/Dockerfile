FROM php:8.4-cli-alpine

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libpq-dev \
    libxml2-dev \
    libzip-dev \
    oniguruma-dev \
    unzip

RUN docker-php-ext-install \
    bcmath \
    intl \
    mbstring \
    pcntl \
    pdo \
    pdo_pgsql \
    pgsql \
    xml \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
