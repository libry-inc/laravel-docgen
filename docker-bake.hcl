group "default" {
  targets = ["php"]
}

target "php" {
  dockerfile-inline = <<EOF
FROM php:8-alpine

RUN apk --no-cache update && \
    apk --no-cache upgrade && \
    docker-php-ext-install pcntl pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
EOF
  tags = ["laravel-docgen/php"]
}
