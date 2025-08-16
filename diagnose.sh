#!/bin/bash

echo "🔍 Диагностика проблемы с пустым экраном..."

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

# Проверяем статус сервисов
echo "📊 Статус сервисов:"
echo "Nginx: $(systemctl is-active nginx)"
echo "PHP-FPM: $(systemctl is-active php8.3-fpm)"

# Проверяем логи PHP-FPM
echo ""
echo "📋 Логи PHP-FPM:"
if [ -f "/var/log/php8.3-fpm.log" ]; then
    echo "Последние 10 строк:"
    tail -10 /var/log/php8.3-fpm.log
else
    echo "❌ Файл логов PHP-FPM не найден"
fi

# Проверяем логи Nginx
echo ""
echo "📋 Логи Nginx:"
if [ -f "/var/log/nginx/linux-server-manager_error.log" ]; then
    echo "Последние 10 строк:"
    tail -10 /var/log/nginx/linux-server-manager_error.log
else
    echo "❌ Файл логов Nginx не найден"
fi

# Проверяем права доступа к файлам
echo ""
echo "🔐 Проверка прав доступа:"
WEB_ROOT="/var/www/html/linux-server-manager"
if [ -d "$WEB_ROOT" ]; then
    echo "Владелец директории: $(ls -ld $WEB_ROOT | awk '{print $3":"$4}')"
    echo "Права на public/: $(ls -ld $WEB_ROOT/public | awk '{print $1}')"
    echo "Права на src/: $(ls -ld $WEB_ROOT/src | awk '{print $1}')"
    echo "Права на templates/: $(ls -ld $WEB_ROOT/templates | awk '{print $1}')"
else
    echo "❌ Директория $WEB_ROOT не найдена"
fi

# Проверяем composer
echo ""
echo "📦 Проверка Composer:"
if [ -f "$WEB_ROOT/vendor/autoload.php" ]; then
    echo "✅ Composer autoload найден"
else
    echo "❌ Composer autoload не найден"
fi

# Проверяем .env файл
echo ""
echo "🌍 Проверка .env файла:"
if [ -f "$WEB_ROOT/.env" ]; then
    echo "✅ .env файл найден"
    echo "Размер: $(ls -lh $WEB_ROOT/.env | awk '{print $5}')"
else
    echo "❌ .env файл не найден"
fi

# Тестируем PHP напрямую
echo ""
echo "🧪 Тест PHP:"
if php -v > /dev/null 2>&1; then
    echo "✅ PHP работает"
    echo "Версия: $(php -v | head -1)"
else
    echo "❌ PHP не работает"
fi

# Тестируем отладочный файл
echo ""
echo "🔍 Тест отладочного файла:"
if [ -f "$WEB_ROOT/public/debug.php" ]; then
    echo "✅ debug.php найден"
    echo "Тестируем выполнение..."
    OUTPUT=$(php $WEB_ROOT/public/debug.php 2>&1)
    if [ $? -eq 0 ]; then
        echo "✅ debug.php выполняется без ошибок"
    else
        echo "❌ debug.php содержит ошибки:"
        echo "$OUTPUT"
    fi
else
    echo "❌ debug.php не найден"
fi

# Проверяем конфигурацию Nginx
echo ""
echo "🌐 Проверка конфигурации Nginx:"
if nginx -t > /dev/null 2>&1; then
    echo "✅ Конфигурация Nginx корректна"
else
    echo "❌ Ошибка в конфигурации Nginx:"
    nginx -t
fi

# Проверяем socket PHP-FPM
echo ""
echo "🔌 Проверка PHP-FPM socket:"
if [ -S "/run/php/php8.3-fpm.sock" ]; then
    echo "✅ PHP-FPM socket существует"
    echo "Права: $(ls -la /run/php/php8.3-fpm.sock)"
else
    echo "❌ PHP-FPM socket не найден"
fi

# Тестируем HTTP запрос
echo ""
echo "🌐 Тест HTTP запроса:"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:81/debug.php)
if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ HTTP запрос к debug.php успешен (код $HTTP_CODE)"
elif [ "$HTTP_CODE" = "000" ]; then
    echo "❌ HTTP запрос не удался (нет соединения)"
else
    echo "⚠️ HTTP запрос вернул код $HTTP_CODE"
fi

echo ""
echo "🎯 Рекомендации:"
echo "1. Откройте http://sirocco.romansegalla.online:81/debug.php в браузере"
echo "2. Проверьте вывод отладочной информации"
echo "3. Если debug.php не работает, проверьте права доступа"
echo "4. Перезапустите сервисы: systemctl restart nginx php8.3-fpm"
