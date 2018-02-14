FROM php:7.1

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN apt-get update -q \
  && apt-get install wget unzip git zlib1g-dev -y --no-install-recommends \
  && rm -rf /var/lib/apt/lists/* \
  && docker-php-ext-install zip

COPY ./docker/php/php-prod.ini /usr/local/etc/php/php.ini
COPY . /code/
WORKDIR /code/

RUN ./composer.sh \
  && rm composer.sh \
  && mv composer.phar /usr/local/bin/composer \
  && composer install --no-interaction

CMD php ./src/run.php --data=/data
