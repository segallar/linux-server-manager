#!/bin/bash

echo "🔧 Быстрое исправление ошибки Application..."

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

# Тестируем приложение
echo "🧪 Тестируем приложение..."
curl -s -o /dev/null -w "%{http_code}" http://localhost:81/ | grep -q "200"
if [ $? -eq 0 ]; then
    echo "✅ Приложение отвечает (HTTP 200)"
else
    echo "⚠️  Приложение не отвечает или возвращает ошибку"
fi

echo ""
echo "🎉 Исправления применены!"
echo "🌐 Теперь попробуйте открыть приложение в браузере"
echo "📝 Если проблемы остаются, проверьте:"
echo "   - Логи PHP: /var/log/php8.3-fpm.log"
echo "   - Логи Nginx: /var/log/nginx/linux-server-manager_error.log"
echo "   - Статус PHP-FPM: systemctl status php8.3-fpm"
