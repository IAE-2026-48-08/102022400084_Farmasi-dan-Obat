FROM php:8.4-cli

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apt-get update && apt-get install -y git curl zip unzip nodejs npm && apt-get clean

RUN install-php-extensions pdo pdo_mysql mbstring zip gd intl bcmath sockets redis

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