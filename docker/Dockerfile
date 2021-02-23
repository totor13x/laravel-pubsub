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
