FROM php:8.2-apache

ARG WWWGROUP

ENV WWWUSER 1337
ENV ENABLE_SSL false

# Install packages
RUN apt-get update && apt-get install -y sudo git curl zip unzip supervisor sqlite3 libcap2-bin libpng-dev libpq-dev libzip-dev vim

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && mkdir -p /etc/apt/keyrings/ \
    && apt-get update \
    && apt-get install -y default-mysql-client

# Install imagick
RUN apt-get update && apt-get install -y libmagickwand-dev --no-install-recommends
RUN printf "\n" | pecl install imagick
RUN docker-php-ext-enable imagick

RUN apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql pcntl mysqli gd zip

COPY apache/default.apache.conf /etc/apache2/sites-available/000-default.conf

RUN mkdir -p /var/run/apache2/

# Enable headers/rewrite module for Apache
RUN a2enmod headers rewrite

# Set document root for Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

RUN groupadd --force -g $WWWGROUP www-dev
RUN useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u $WWWUSER www-dev \
    && echo "www-dev:123456" | chpasswd && adduser www-dev sudo

COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /usr/local/etc/php/

RUN chmod +x /usr/local/bin/start-container
RUN chown -R www-dev:dialout /var/www/html
RUN chmod -R 777 /var/www/html

EXPOSE 80 443

ENTRYPOINT ["start-container"]
