#!/bin/bash

echo "🔧 Быстрое исправление синтаксической ошибки..."

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

# Проверяем синтаксис PHP файлов
echo ""
echo "🧪 Проверяем синтаксис PHP файлов..."
WEB_ROOT="/var/www/html/linux-server-manager"

echo "Проверка index.php:"
if php -l $WEB_ROOT/public/index.php; then
    echo "✅ index.php синтаксис корректен"
else
    echo "❌ index.php содержит синтаксические ошибки"
fi

echo "Проверка test.php:"
if php -l $WEB_ROOT/public/test.php; then
    echo "✅ test.php синтаксис корректен"
else
    echo "❌ test.php содержит синтаксические ошибки"
fi

# Тестируем файлы через HTTP
echo ""
echo "🌐 Тестируем файлы через HTTP..."

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

echo "Тест test.php:"
if curl -s -o /dev/null -w "%{http_code}" http://localhost:81/test.php | grep -q "200"; then
    echo "✅ test.php работает"
else
    echo "❌ test.php не работает"
fi

# Проверяем логи на ошибки
echo ""
echo "📋 Проверяем логи на ошибки..."
if [ -f "/var/log/nginx/linux-server-manager_error.log" ]; then
    echo "Последние ошибки Nginx:"
    tail -5 /var/log/nginx/linux-server-manager_error.log
fi

echo ""
echo "🎉 Исправления применены!"
echo "🌐 Теперь попробуйте:"
echo "   1. http://sirocco.romansegalla.online:81/ (главная страница)"
echo "   2. http://sirocco.romansegalla.online:81/test.php (тест контроллеров)"
echo ""
echo "📝 Если все еще есть проблемы, проверьте логи выше"
