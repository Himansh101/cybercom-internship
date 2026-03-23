# FROM php:8.2-cli

# RUN apt-get update && apt-get install -y \
#     git curl zip unzip libzip-dev \
#     && docker-php-ext-install zip \
#     && apt-get clean && rm -rf /var/lib/apt/lists/*

# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# WORKDIR /app

# COPY . .

# CMD ["sh", "-c", "composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader && php KafkaConsumer.php"]

FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    librdkafka-dev \
    git \
    unzip \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka \
    && docker-php-ext-install bcmath \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

CMD ["sh", "-c", "composer install --no-dev --prefer-dist --no-interaction --ignore-platform-reqs && php KafkaConsumer.php"]
