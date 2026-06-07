FROM php:8.2-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    TZ=America/Mexico_City

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        zip \
        libcurl4-openssl-dev \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        curl \
        gd \
        intl \
        mbstring \
        mysqli \
        pdo_mysql \
        zip \
    && a2enmod rewrite headers expires \
    && rm -rf /var/lib/apt/lists/*

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-admin-sgl.ini
COPY docker/entrypoint.sh /usr/local/bin/admin-sgl-entrypoint

RUN chmod +x /usr/local/bin/admin-sgl-entrypoint

WORKDIR /var/www/html

COPY --chown=www-data:www-data . /var/www/html

RUN mkdir -p writable/cache writable/debugbar writable/logs writable/session writable/uploads \
    && chown -R www-data:www-data /var/www/html/writable

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD php -r "exit(@file_get_contents('http://127.0.0.1/') === false ? 1 : 0);"

ENTRYPOINT ["admin-sgl-entrypoint"]
CMD ["apache2-foreground"]
