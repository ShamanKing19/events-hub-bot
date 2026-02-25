FROM php:8.4-fpm-alpine

ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} ehub \
    && adduser -D -u ${UID} -G ehub ehub

# Устанавливаем зависимости
RUN apk add --no-cache \
    bash \
    curl

RUN curl -sSLf \
    -o /usr/local/bin/install-php-extensions \
    https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions @composer pdo pdo_mysql mbstring

WORKDIR /var/www

USER ehub

CMD ["php-fpm"]