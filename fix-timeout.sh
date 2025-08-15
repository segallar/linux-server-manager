#!/bin/bash

echo "🔧 Исправление проблем с таймаутами..."

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

# Создаем директорию для логов если её нет
mkdir -p /var/log/nginx
mkdir -p /var/log/php8.3-fpm

# Применяем конфигурацию Nginx
if [ -f "nginx.conf" ]; then
    echo "📝 Применяем конфигурацию Nginx..."
    cp nginx.conf /etc/nginx/sites-available/linux-server-manager
    ln -sf /etc/nginx/sites-available/linux-server-manager /etc/nginx/sites-enabled/
    
    # Удаляем дефолтный сайт если он существует
    if [ -f "/etc/nginx/sites-enabled/default" ]; then
        rm /etc/nginx/sites-enabled/default
    fi
    
    # Проверяем конфигурацию
    if nginx -t; then
        echo "✅ Конфигурация Nginx корректна"
    else
        echo "❌ Ошибка в конфигурации Nginx"
        exit 1
    fi
fi

# Применяем конфигурацию PHP-FPM
if [ -f "php-fpm.conf" ]; then
    echo "📝 Применяем конфигурацию PHP-FPM..."
    cp php-fpm.conf /etc/php/8.3/fpm/pool.d/www.conf
    
    # Проверяем конфигурацию
    if php-fpm8.3 -t; then
        echo "✅ Конфигурация PHP-FPM корректна"
    else
        echo "❌ Ошибка в конфигурации PHP-FPM"
        exit 1
    fi
fi

# Создаем директорию для сессий PHP
mkdir -p /var/lib/php/sessions
chown www-data:www-data /var/lib/php/sessions
chmod 755 /var/lib/php/sessions

# Создаем директорию для кэша приложения
mkdir -p /tmp
chmod 777 /tmp

# Перезапускаем сервисы
echo "🔄 Перезапускаем сервисы..."

# Перезапускаем PHP-FPM
systemctl restart php8.3-fpm
if [ $? -eq 0 ]; then
    echo "✅ PHP-FPM перезапущен"
else
    echo "❌ Ошибка перезапуска PHP-FPM"
    systemctl status php8.3-fpm
fi

# Перезапускаем Nginx
systemctl restart nginx
if [ $? -eq 0 ]; then
    echo "✅ Nginx перезапущен"
else
    echo "❌ Ошибка перезапуска Nginx"
    systemctl status nginx
fi

# Проверяем статус сервисов
echo "📊 Статус сервисов:"
echo "PHP-FPM: $(systemctl is-active php8.3-fpm)"
echo "Nginx: $(systemctl is-active nginx)"

# Проверяем права на socket
if [ -S "/run/php/php8.3-fpm.sock" ]; then
    echo "✅ PHP-FPM socket существует"
    ls -la /run/php/php8.3-fpm.sock
else
    echo "❌ PHP-FPM socket не найден"
fi

# Проверяем логи на ошибки
echo "📋 Проверяем логи на ошибки..."
if [ -f "/var/log/nginx/linux-server-manager_error.log" ]; then
    echo "Последние ошибки Nginx:"
    tail -5 /var/log/nginx/linux-server-manager_error.log
fi

if [ -f "/var/log/php8.3-fpm.log" ]; then
    echo "Последние ошибки PHP-FPM:"
    tail -5 /var/log/php8.3-fpm.log
fi

echo "🎉 Исправления применены!"
echo "🌐 Приложение должно быть доступно по адресу: http://sirocco.romansegalla.online:81"
echo "📝 Если проблемы остаются, проверьте логи:"
echo "   - Nginx: /var/log/nginx/linux-server-manager_error.log"
echo "   - PHP-FPM: /var/log/php8.3-fpm.log"
