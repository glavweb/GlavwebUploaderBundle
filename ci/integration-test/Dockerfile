FROM php:7.2.5-cli

ARG BUNDLE_VERSION
ARG PACKAGE_NAME

RUN apt-get update && \
    apt-get install -y \
      unzip \
      libzip-dev \
      libpq-dev \
    && docker-php-ext-install zip pdo_pgsql 

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /usr/src/

RUN composer create-project symfony/skeleton:"^5.3" app

COPY scripts/ scripts/
RUN chmod +x scripts/*

WORKDIR /usr/src/app

RUN composer require --dev phpunit/phpunit symfony/test-pack

COPY environment .

RUN composer config repositories.glavweb "{\"type\": \"path\", \"url\": \"../bundle\", \"options\": {\"versions\": { \"$PACKAGE_NAME\": \"$BUNDLE_VERSION\" }}}"