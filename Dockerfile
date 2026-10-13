FROM php:8.5-cli

RUN docker-php-ext-install pdo_mysql

WORKDIR /app
COPY app/ app/
COPY bin/ bin/
COPY migrations/ migrations/
COPY public/ public/
COPY autoload.php bootstrap.php ./
COPY --chmod=755 docker-entrypoint-test.sh ./docker-entrypoint-test.sh

EXPOSE 8080
CMD ["./docker-entrypoint-test.sh"]
