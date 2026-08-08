# Pinned to a specific minor version rather than the floating :8 tag, so a
# rebuild does not silently pick up a different interpreter (A03:2025).
FROM docker.io/library/php:8.3-apache

LABEL org.opencontainers.image.source=https://github.com/digininja/DVWA
LABEL org.opencontainers.image.description="DVWA pre-built image."
LABEL org.opencontainers.image.licenses="gpl-3.0"

WORKDIR /var/www/html

# https://www.php.net/manual/en/image.installation.php
RUN apt-get update \
 && export DEBIAN_FRONTEND=noninteractive \
 && apt-get install -y zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev iputils-ping git zip unzip 7zip  \
 && apt-get clean -y && rm -rf /var/lib/apt/lists/* \
 && docker-php-ext-configure gd --with-jpeg --with-freetype \
 && a2enmod rewrite \
 # Use pdo_sqlite instead of pdo_mysql if you want to use sqlite
 && docker-php-ext-install gd mysqli pdo pdo_mysql

COPY --from=composer:2.7 /usr/bin/composer /usr/local/bin/composer
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data config/config.inc.php.dist config/config.inc.php

# Install the PHP hardening settings where the engine actually reads them.
# Dropping php.ini in the document root has no effect.
COPY php.ini /usr/local/etc/php/conf.d/zz-dvwa-hardening.ini

# Serve the security response headers the application relies on.
COPY dvwa-security-headers.conf /etc/apache2/conf-available/dvwa-security-headers.conf
RUN a2enmod headers && a2enconf dvwa-security-headers

# This is configuring the stuff for the API
RUN cd /var/www/html/vulnerabilities/api \
 # --no-dev keeps development-only packages out of the image and
 # --no-interaction stops the build hanging on a prompt. composer.lock is
 # honoured, so the resolved versions are the reviewed ones.
 && composer install --no-dev --no-interaction --prefer-dist
