FROM alpine:3.9

ADD https://dl.bintray.com/php-alpine/key/php-alpine.rsa.pub /etc/apk/keys/php-alpine.rsa.pub

RUN apk --update add ca-certificates

RUN echo "https://dl.bintray.com/php-alpine/v3.9/php-7.3" >> /etc/apk/repositories

RUN apk add --update \
    php \
    php-fpm \
    php-apcu \
    php-ctype \
    php-curl \
    php-dom \
    php-gd \
    php-iconv \
    php-imagick \
    php-json \
    php-intl \
    php-mbstring \
    php-opcache \
    php-openssl \
    php-pdo \
    php-pdo_mysql \
    php-mysqli \
    php-xml \
    php-zlib \
    php-phar \
    php-session \
    php-xdebug \
    php-zip \
    php-sodium \
    openssl \
    make \
    curl

RUN apk add --update \
        python \
        python-dev \
        py-pip \
        build-base \
    && pip install dump-env

RUN rm -rf /var/cache/apk/* && \
    curl --insecure https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer

ADD docker/php-fpm/symfony.ini /etc/php7/conf.d/
ADD docker/php-fpm/symfony.ini /etc/php7/cli/conf.d/

ADD docker/php-fpm/symfony.pool.conf /etc/php7/php-fpm.d/
RUN ln -s /usr/bin/php7 /usr/bin/php
RUN addgroup -g 1000 www-data && adduser -u 1000 -D -g '' -G www-data www-data
RUN mkdir /code && chown www-data:www-data /code
WORKDIR /code
COPY --chown=www-data:www-data composer.json composer.json
COPY --chown=www-data:www-data composer.lock composer.lock
USER www-data
RUN composer install --no-interaction --prefer-dist --no-scripts
COPY --chown=www-data:www-data . .
USER root
CMD ["php-fpm7", "-F"]
EXPOSE 9001
