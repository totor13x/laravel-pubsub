FROM php:7.4-fpm
MAINTAINER Superbalist <tech+docker@superbalist.com>

RUN mkdir /opt/laravel-pubsub
WORKDIR /opt/laravel-pubsub

# Packages
RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y \
        git \
        zip \
        libzip-dev \
        zlib1g-dev \
        unzip \
        python \
        && ( \
            cd /tmp \
            && mkdir librdkafka \
            && cd librdkafka \
            && git clone https://github.com/edenhill/librdkafka.git . \
            && ./configure \
            && make \
            && make install \
        ) \
    && rm -r /var/lib/apt/lists/*

# PHP Extensions
RUN pecl install rdkafka \
    && docker-php-ext-enable rdkafka \
    && rm -rf /tmp/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Composer Application Dependencies
COPY composer.json /opt/laravel-pubsub/
RUN composer install --no-autoloader --no-scripts --no-interaction
RUN composer require superbalist/php-pubsub-kafka

COPY config /opt/laravel-pubsub/config
COPY src /opt/laravel-pubsub/src
COPY tests /opt/laravel-pubsub/tests
COPY phpunit.php /opt/laravel-pubsub/
COPY phpunit.xml /opt/laravel-pubsub/

RUN composer dump-autoload --no-interaction

CMD ["/bin/bash"]
