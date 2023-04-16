FROM composer:latest AS deps

WORKDIR /composer
COPY composer.* .
RUN composer install

FROM php:8.1-cli-alpine AS app

WORKDIR /app
COPY --from=deps /composer/vendor vendor
COPY ./resources resources
COPY ./src src
ENTRYPOINT ["tail", "-f", "/dev/null"]