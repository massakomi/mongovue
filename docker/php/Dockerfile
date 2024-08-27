FROM php:8.3-fpm-alpine

WORKDIR /var/www/

ARG TZ='UTC'

RUN echo "${TZ}" && apk --update add tzdata && \
    cp /usr/share/zoneinfo/$TZ /etc/localtime && \
    echo $TZ > /etc/timezone && \
    apk del tzdata

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# весь список здесь https://github.com/mlocati/docker-php-extension-installer
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions mongodb

RUN cp /usr/local/lib/php/extensions/no-debug-non-zts-*/* /usr/local/lib/php/extensions
