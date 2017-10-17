FROM php:7.1-alpine
MAINTAINER Martin Halamicek <martin@keboola.com>

RUN apk add --no-cache wget git unzip gzip

COPY . /code/
WORKDIR /code/

RUN ./composer.sh \
  && rm composer.sh \
  && mv composer.phar /usr/local/bin/composer \
  && composer install --no-interaction \
  && apk del wget git unzip
ADD . /code

CMD php ./src/run.php --data=/data