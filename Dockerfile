FROM php:8.1-apache

RUN docker-php-ext-install mysqli

RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y git

# Install composer (php's package manager)
RUN php  -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && php -r "unlink('composer-setup.php');" \
 && rm -rf /var/lib/apt/lists/*
ENV COMPOSER_HOME /usr/local/bin/

# Install php backend dependencies using PHP Composer package specification (composer.json)
WORKDIR /var/www
RUN composer require --no-update lcobucci/jwt:4.1.5
RUN composer install --prefer-dist --no-dev --profile

WORKDIR /var/www/html

COPY config.json ./
COPY *.html ./
COPY *.php ./
COPY *.ico ./
COPY *.png ./

WORKDIR /var/www/html