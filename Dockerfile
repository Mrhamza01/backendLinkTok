FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    supervisor \
    mariadb-client \
    mysqli \
    nginx \
    curl

WORKDIR /var/www/html

COPY package.json ./
RUN npm install

COPY composer.json ./
RUN composer install --ignore-platform-reqs

COPY . .

EXPOSE 80

RUN chmod -R 777 storage  # Grant write permissions for local storage

CMD ["supervisord", "-n"]