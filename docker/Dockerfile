FROM php:7.4-cli

RUN apt-get update && apt-get install -y librabbitmq-dev libssh-dev git zip unzip \
    && docker-php-ext-install opcache bcmath sockets \
    && pecl install amqp mongodb \
    && docker-php-ext-enable amqp mongodb.so

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
