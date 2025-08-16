#!/bin/bash

echo "🔧 Быстрое исправление проблемы с пустым экраном..."

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

# Перезапускаем PHP-FPM для применения изменений в коде
echo "🔄 Перезапускаем PHP-FPM..."
if systemctl is-active --quiet php8.3-fpm; then
    systemctl restart php8.3-fpm
    echo "✅ PHP-FPM перезапущен"
else
    echo "⚠️  PHP-FPM не запущен"
fi

# Проверяем статус
echo "📊 Статус сервисов:"
echo "PHP-FPM: $(systemctl is-active php8.3-fpm)"

# Тестируем основные файлы
echo ""
echo "🧪 Тестируем файлы..."
WEB_ROOT="/var/www/html/linux-server-manager"

# Тестируем debug.php
echo "Тест debug.php:"
if curl -s -o /dev/null -w "%{http_code}" http://localhost:81/debug.php | grep -q "200"; then
    echo "✅ debug.php работает"
else
    echo "❌ debug.php не работает"
fi

# Тестируем test.php
echo "Тест test.php:"
if curl -s -o /dev/null -w "%{http_code}" http://localhost:81/test.php | grep -q "200"; then
    echo "✅ test.php работает"
else
    echo "❌ test.php не работает"
fi

# Тестируем главную страницу
echo "Тест главной страницы:"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:81/)
if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ Главная страница работает (код $HTTP_CODE)"
    
    # Проверяем, что страница не пустая
    CONTENT_LENGTH=$(curl -s -I http://localhost:81/ | grep -i "content-length" | awk '{print $2}' | tr -d '\r')
    if [ -n "$CONTENT_LENGTH" ] && [ "$CONTENT_LENGTH" -gt 0 ]; then
        echo "✅ Страница содержит контент ($CONTENT_LENGTH байт)"
    else
        echo "⚠️  Страница может быть пустой"
    fi
else
    echo "❌ Главная страница не работает (код $HTTP_CODE)"
fi

# Проверяем логи на ошибки
echo ""
echo "📋 Проверяем логи на ошибки..."
if [ -f "/var/log/nginx/linux-server-manager_error.log" ]; then
    echo "Последние ошибки Nginx:"
    tail -5 /var/log/nginx/linux-server-manager_error.log
fi

if [ -f "/var/log/php8.3-fpm.log" ]; then
    echo "Последние ошибки PHP-FPM:"
    tail -5 /var/log/php8.3-fpm.log
fi

echo ""
echo "🎉 Исправления применены!"
echo "🌐 Теперь попробуйте:"
echo "   1. http://sirocco.romansegalla.online:81/ (главная страница)"
echo "   2. http://sirocco.romansegalla.online:81/debug.php (отладка)"
echo "   3. http://sirocco.romansegalla.online:81/test.php (тест контроллеров)"
echo ""
echo "📝 Если главная страница все еще пустая, проверьте test.php для диагностики"
