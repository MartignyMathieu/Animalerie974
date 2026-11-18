# syntax=docker/dockerfile:1
# Image de production pour Fly.io : PHP 8.4 + Apache, base SQLite sur le volume /data.
FROM php:8.4-apache-bookworm

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev unzip git \
    && docker-php-ext-install -j"$(nproc)" intl bcmath opcache \
    && a2enmod rewrite headers \
    && sed -ri 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf \
    && sed -ri 's#<VirtualHost \*:80>#<VirtualHost *:8080>#' /etc/apache2/sites-available/000-default.conf \
    && sed -ri 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#' /etc/apache2/sites-available/000-default.conf \
    && rm -rf /var/lib/apt/lists/*

COPY docker/apache-symfony.conf /etc/apache2/conf-available/symfony.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
RUN a2enconf symfony

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dépendances d'abord (meilleur cache). Les dépendances de dev restent installées :
# doctrine-fixtures-bundle sert à charger les données de démonstration au premier démarrage.
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative

# Compilation des assets et du cache (valeurs factices : seules les variables d'exécution comptent).
RUN APP_SECRET=build-only DATABASE_URL=sqlite:////tmp/build.db \
    php bin/console importmap:install \
    && APP_SECRET=build-only DATABASE_URL=sqlite:////tmp/build.db \
    php bin/console asset-map:compile \
    && APP_SECRET=build-only DATABASE_URL=sqlite:////tmp/build.db \
    php bin/console cache:warmup \
    && chown -R www-data:www-data var

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["entrypoint.sh"]
