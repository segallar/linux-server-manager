FROM php:8.1-apache

# Установите системные зависимости
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Установите Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Установите рабочую директорию
WORKDIR /var/www/html

# Скопируйте файлы composer
COPY composer.json composer.lock ./

# Установите зависимости
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Скопируйте остальные файлы проекта
COPY . .

# Создайте папку для логов
RUN mkdir -p logs && chmod 777 logs

# Настройте Apache
RUN a2enmod rewrite
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Настройте права доступа
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/logs

# Создайте .env файл если его нет
RUN if [ ! -f .env ]; then \
    cp .env.example .env 2>/dev/null || \
    echo "APP_ENV=production\nAPP_DEBUG=false\nAPP_KEY=$(openssl rand -base64 32)" > .env; \
    fi

# Откройте порт
EXPOSE 80

# Запустите Apache
CMD ["apache2-foreground"]
