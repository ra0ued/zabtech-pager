FROM php:8.3-fpm

# Установка зависимостей и расширений
RUN apt-get update && apt-get install -y \
    libc-client-dev \
    libkrb5-dev \
    libzip-dev \
    unzip \
    cron \
    && rm -r /var/lib/apt/lists/* \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install pdo_mysql zip imap

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Рабочая директория
WORKDIR /var/www/html

# Копирование проекта
COPY ../../. /var/www/html

# Установка зависимостей Symfony
RUN if [ -f composer.json ]; then composer install --no-scripts --no-interaction; fi

# Права на файлы
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Запуск PHP-FPM
CMD ["php-fpm"]