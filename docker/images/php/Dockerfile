FROM php:8.1-fpm

RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y bash-completion git libpq-dev libzip-dev zip

RUN docker-php-ext-install -j$(getconf _NPROCESSORS_ONLN) zip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
COPY conf.d                        $PHP_INI_DIR/conf.d/
RUN usermod -u 1000 www-data
RUN mkdir -p /var/www/.composer && chown www-data:www-data /var/www/.composer