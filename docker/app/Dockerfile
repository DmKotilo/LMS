FROM composer as build1
WORKDIR /app
COPY laravel/ /app
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction

FROM node:18-bookworm-slim as build2
WORKDIR /app
COPY --from=build1 /app/ /app/
RUN npm install
RUN npm run build

FROM unit:1.32.1-php8.2
WORKDIR /var/www/html/

COPY --from=build2 /app/ /var/www/html/

RUN apt-get update && apt-get install -y \
      apt-utils \
      libfreetype6-dev \
      libjpeg62-turbo-dev \
      libpq-dev \
      libpng-dev \
      libzip-dev \
      zip unzip \
      git \
      wget \
      build-essential \
      libncursesw5-dev \
      libssl-dev \
      libsqlite3-dev \
      tk-dev \
      libgdbm-dev \
      libc6-dev \
      libbz2-dev \
      libffi-dev \
      zlib1g-dev && \
      docker-php-ext-install pdo_pgsql && \
      docker-php-ext-install bcmath && \
      docker-php-ext-install gd && \
      docker-php-ext-install zip && \
      docker-php-ext-configure opcache --enable-opcache && \
      cp .env.example .env && \
      apt-get clean && \
      rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY docker/app/config.json /var/lib/unit/conf.json
COPY docker/php.ini /usr/local/etc/php/php.ini
COPY docker/app/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/app/000-default.conf /etc/apache2/sites-available/000-default.conf


# post install laravel commands
RUN php artisan key:generate && \
    php artisan storage:link && \
    php artisan route:cache && \
    chmod 777 -R /var/www/html/storage/ && \
    chown -R www-data:www-data /var/www/ 

