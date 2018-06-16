FROM php:7.2.6-cli-alpine
RUN docker-php-ext-install mysqli
WORKDIR /app
COPY . /app
EXPOSE 80
CMD [ "php", "-S", "0.0.0.0:80", "-t", "/app" ]