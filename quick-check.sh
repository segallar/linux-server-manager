#!/bin/bash

echo "⚡ Быстрая проверка состояния приложения..."

# Проверяем статус сервисов
echo "📊 Сервисы:"
echo "Nginx: $(systemctl is-active nginx)"
echo "PHP-FPM: $(systemctl is-active php8.3-fpm)"

# Проверяем основные файлы
echo ""
echo "📁 Файлы приложения:"
WEB_ROOT="/var/www/html/linux-server-manager"

if [ -d "$WEB_ROOT" ]; then
    echo "✅ Директория приложения найдена"
    
    # Проверяем ключевые файлы
    FILES=(
        "public/index.php"
        "public/debug.php"
        "public/test.php"
        "vendor/autoload.php"
        ".env"
        "composer.json"
    )
    
    for file in "${FILES[@]}"; do
        if [ -f "$WEB_ROOT/$file" ]; then
            echo "✅ $file"
        else
            echo "❌ $file"
        fi
    done
else
    echo "❌ Директория приложения не найдена"
fi

# Тестируем HTTP запросы
echo ""
echo "🌐 HTTP тесты:"
echo "Тест debug.php:"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:81/debug.php)
if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ debug.php работает (код $HTTP_CODE)"
else
    echo "❌ debug.php не работает (код $HTTP_CODE)"
fi

echo "Тест главной страницы:"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:81/)
if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ Главная страница работает (код $HTTP_CODE)"
    
    # Проверяем контент
    CONTENT=$(curl -s http://localhost:81/ | head -c 100)
    if [ -n "$CONTENT" ]; then
        echo "✅ Страница содержит контент"
    else
        echo "⚠️  Страница пустая"
    fi
else
    echo "❌ Главная страница не работает (код $HTTP_CODE)"
fi

# Проверяем последние ошибки
echo ""
echo "⚠️  Последние ошибки:"
if [ -f "/var/log/nginx/error.log" ]; then
    echo "Nginx ошибки:"
    tail -3 /var/log/nginx/error.log
fi

if [ -f "/var/log/php8.3-fpm.log" ]; then
    echo "PHP-FPM ошибки:"
    tail -3 /var/log/php8.3-fpm.log
fi

echo ""
echo "🎯 Следующие шаги:"
echo "1. Откройте http://sirocco.romansegalla.online:81/debug.php"
echo "2. Если debug.php не работает, запустите: sudo ./check-logs.sh"
echo "3. Если debug.php работает, но главная страница пустая, запустите: sudo ./fix-blank-screen.sh"
