FROM php:8.5.2-fpm-alpine

RUN set -eux; \
  apk add --no-cache bash coreutils curl \
    postgresql-libs icu-libs \
  ; \
  apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS linux-headers \
    postgresql-dev icu-dev \
  ; \
  docker-php-ext-install -j"$(nproc)" intl pdo_pgsql; \
  pecl install redis; \
  docker-php-ext-enable redis; \
  pecl install xdebug; \
  docker-php-ext-enable xdebug; \
  rm -rf /tmp/pear; \
  apk del .build-deps

COPY conf.d/custom.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY conf.d/xdebug.ini /usr/local/etc/php/conf.d/99-xdebug.ini

WORKDIR /app
CMD ["php-fpm"]