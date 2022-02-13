FROM php:8.0.14

RUN apt-get update && apt-get install -y libzip-dev libpq-dev

RUN docker-php-ext-install zip
RUN docker-php-ext-install sockets
RUN docker-php-ext-install pdo_pgsql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

