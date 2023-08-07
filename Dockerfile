ARG BUILD_FROM=ghcr.io/hassio-addons/base:14.0.0
FROM $BUILD_FROM

# Set shell
SHELL ["/bin/bash", "-o", "pipefail", "-c"]

ENV LANG C.UTF-8

RUN apk add --no-cache \
        curl \
        php81-iconv \
        php81-mbstring \
        php81-openssl \
        php81-curl \
        php81-phar \
        php81

COPY rootfs /

WORKDIR /var/www/siaCidParser

RUN curl -sS https://getcomposer.org/installer | php

RUN php composer.phar install
