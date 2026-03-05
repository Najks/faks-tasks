FROM composer:latest AS vendor
WORKDIR /app
COPY . .
RUN composer install --no-dev --prefer-dist --optimize-autoloader --ansi

FROM php:8.4-fpm-alpine
RUN apk add --no-cache bash git icu-dev libzip-dev libpng-dev libxml2-dev openssl libpq-dev \
    && docker-php-ext-install pdo_pgsql zip intl pcntl \
    && rm -rf /var/cache/apk/*
COPY --from=vendor /app/vendor /var/www/html/vendor
COPY . /var/www/html/
# copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
WORKDIR /var/www/html
RUN chown -R www-data:www-data storage bootstrap/cache
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV APP_KEY=""
ENV APP_URL=http://localhost
ENV LOG_CHANNEL=stack
ENV LOG_LEVEL=error
ENV DB_CONNECTION=pgsql
ENV DB_HOST=db
ENV DB_PORT=5432
ENV DB_DATABASE=laravel
ENV DB_USERNAME=postgres
ENV DB_PASSWORD=""
ENV SESSION_DRIVER=file
ENV QUEUE_CONNECTION=sync
ENV CACHE_DRIVER=file
ENV BROADCAST_DRIVER=log
EXPOSE 9000
# use entrypoint script that runs migrations/seeds once then starts php-fpm
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]
