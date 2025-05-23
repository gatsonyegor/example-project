FROM php:8.4-fpm as base

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    wget \
    curl \
    libzip-dev \
    zip \
    libpq-dev \
    librabbitmq-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && pecl install amqp \
    && docker-php-ext-enable amqp

# RUN rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

CMD ["php-fpm"]
