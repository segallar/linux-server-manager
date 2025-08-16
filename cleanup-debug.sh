#!/bin/bash

echo "🧹 Очистка отладочных файлов..."

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

WEB_ROOT="/var/www/html/linux-server-manager"

echo "📁 Удаляем отладочные файлы из public/:"
cd "$WEB_ROOT/public"

# Удаляем отладочные PHP файлы
FILES_TO_REMOVE=(
    "debug.php"
    "test.php"
    "test-main.php"
    "test-routing.php"
    "phpinfo.php"
)

for file in "${FILES_TO_REMOVE[@]}"; do
    if [ -f "$file" ]; then
        rm "$file"
        echo "✅ Удален: $file"
    else
        echo "⚠️ Не найден: $file"
    fi
done

echo ""
echo "📁 Удаляем отладочные скрипты:"
cd "$WEB_ROOT"

SCRIPTS_TO_REMOVE=(
    "debug-blank-page.sh"
    "check-logs.sh"
    "quick-check.sh"
    "fix-nginx-config.sh"
    "fix-php-fpm.sh"
)

for script in "${SCRIPTS_TO_REMOVE[@]}"; do
    if [ -f "$script" ]; then
        rm "$script"
        echo "✅ Удален: $script"
    else
        echo "⚠️ Не найден: $script"
    fi
done

echo ""
echo "🔧 Проверяем права доступа:"
chown -R www-data:www-data "$WEB_ROOT"
find "$WEB_ROOT" -type d -exec chmod 755 {} \;
find "$WEB_ROOT" -type f -exec chmod 644 {} \;
echo "✅ Права доступа исправлены"

echo ""
echo "🧪 Тестируем главную страницу:"
sleep 2
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:81/)
if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ Главная страница работает (код $HTTP_CODE)"
else
    echo "❌ Главная страница не работает (код $HTTP_CODE)"
fi

echo ""
echo "🎉 Очистка завершена!"
echo "🌐 Приложение готово к продакшену: http://sirocco.romansegalla.online:81/"
