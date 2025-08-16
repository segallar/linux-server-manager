#!/bin/bash

echo "🔍 Диагностика пустой страницы..."

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

WEB_ROOT="/var/www/html/linux-server-manager"

echo "📋 Проверка основных компонентов:"

# 1. Проверяем .env файл
echo ""
echo "1. Проверка .env файла:"
if [ -f "$WEB_ROOT/.env" ]; then
    echo "✅ .env файл найден"
    echo "   Размер: $(ls -lh $WEB_ROOT/.env | awk '{print $5}')"
    echo "   Содержимое (первые 5 строк):"
    head -5 "$WEB_ROOT/.env"
else
    echo "❌ .env файл не найден"
    echo "   Создаем .env файл из примера..."
    if [ -f "$WEB_ROOT/env.example" ]; then
        cp "$WEB_ROOT/env.example" "$WEB_ROOT/.env"
        chown www-data:www-data "$WEB_ROOT/.env"
        echo "✅ .env файл создан"
    else
        echo "❌ env.example не найден"
    fi
fi

# 2. Проверяем Composer зависимости
echo ""
echo "2. Проверка Composer зависимостей:"
if [ -f "$WEB_ROOT/vendor/autoload.php" ]; then
    echo "✅ Composer autoload найден"
    echo "   Размер vendor/: $(du -sh $WEB_ROOT/vendor | awk '{print $1}')"
else
    echo "❌ Composer autoload не найден"
    echo "   Устанавливаем зависимости..."
    cd "$WEB_ROOT"
    composer install --no-dev
    if [ $? -eq 0 ]; then
        echo "✅ Зависимости установлены"
    else
        echo "❌ Ошибка установки зависимостей"
    fi
fi

# 3. Проверяем права доступа
echo ""
echo "3. Проверка прав доступа:"
chown -R www-data:www-data "$WEB_ROOT"
find "$WEB_ROOT" -type d -exec chmod 755 {} \;
find "$WEB_ROOT" -type f -exec chmod 644 {} \;
echo "✅ Права доступа исправлены"

# 4. Тестируем PHP файлы напрямую
echo ""
echo "4. Тестируем PHP файлы напрямую:"
cd "$WEB_ROOT"

echo "Тест debug.php:"
OUTPUT=$(sudo -u www-data php public/debug.php 2>&1)
if [ $? -eq 0 ]; then
    echo "✅ debug.php работает"
    echo "   Длина вывода: $(echo "$OUTPUT" | wc -c) символов"
else
    echo "❌ debug.php не работает:"
    echo "$OUTPUT"
fi

echo "Тест test.php:"
if [ -f "public/test.php" ]; then
    OUTPUT=$(sudo -u www-data php public/test.php 2>&1)
    if [ $? -eq 0 ]; then
        echo "✅ test.php работает"
        echo "   Длина вывода: $(echo "$OUTPUT" | wc -c) символов"
    else
        echo "❌ test.php не работает:"
        echo "$OUTPUT"
    fi
else
    echo "❌ test.php не найден"
fi

# 5. Тестируем главную страницу напрямую
echo ""
echo "5. Тестируем главную страницу напрямую:"
OUTPUT=$(sudo -u www-data php public/index.php 2>&1)
if [ $? -eq 0 ]; then
    echo "✅ index.php выполняется без ошибок"
    echo "   Длина вывода: $(echo "$OUTPUT" | wc -c) символов"
    
    if [ $(echo "$OUTPUT" | wc -c) -gt 10 ]; then
        echo "   Первые 200 символов:"
        echo "$OUTPUT" | head -c 200
    else
        echo "   ⚠️ Вывод очень короткий (возможно, пустая страница)"
    fi
else
    echo "❌ index.php содержит ошибки:"
    echo "$OUTPUT"
fi

# 6. Проверяем логи на ошибки
echo ""
echo "6. Проверяем логи на ошибки:"
if [ -f "/var/log/nginx/linux-server-manager_error.log" ]; then
    echo "Последние ошибки Nginx:"
    tail -3 /var/log/nginx/linux-server-manager_error.log
fi

if [ -f "/var/log/php8.3-fpm.log" ]; then
    echo "Последние ошибки PHP-FPM:"
    tail -3 /var/log/php8.3-fpm.log
fi

# 7. Тестируем HTTP запросы
echo ""
echo "7. Тестируем HTTP запросы:"
sleep 2

echo "Тест debug.php через HTTP:"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:81/debug.php)
if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ debug.php работает через HTTP (код $HTTP_CODE)"
else
    echo "❌ debug.php не работает через HTTP (код $HTTP_CODE)"
fi

echo "Тест главной страницы через HTTP:"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:81/)
if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ Главная страница работает через HTTP (код $HTTP_CODE)"
    
    # Проверяем контент
    CONTENT=$(curl -s http://localhost:81/ | head -c 100)
    if [ -n "$CONTENT" ]; then
        echo "✅ Страница содержит контент"
        echo "   Первые 100 символов: $CONTENT"
    else
        echo "⚠️ Страница пустая"
    fi
else
    echo "❌ Главная страница не работает через HTTP (код $HTTP_CODE)"
fi

echo ""
echo "🎯 Рекомендации:"
echo "1. Проверьте вывод debug.php в браузере: http://sirocco.romansegalla.online:81/debug.php"
echo "2. Если debug.php показывает ошибки, исправьте их"
echo "3. Если debug.php работает, но главная страница пустая, проверьте контроллеры и шаблоны"
echo "4. Проверьте test.php: http://sirocco.romansegalla.online:81/test.php"
