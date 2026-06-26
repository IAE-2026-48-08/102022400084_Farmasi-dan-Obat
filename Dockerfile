FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev \
    libonig-dev libxml2-dev libicu-dev nodejs npm \
    && docker-php-ext-install \
    pdo pdo_mysql mbstring zip gd intl bcmath sockets \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get clean

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN cp .env.example .env

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN npm install && npm run build

EXPOSE 8000

CMD php artisan key:generate --force && \
    php artisan migrate --force && \
    php artisan clear-compiled && \
    php artisan optimize:clear && \
    php artisan serve --host=0.0.0.0 --port=8000