#!/bin/bash

echo "🔍 Проверка логов на сервере..."

# Проверяем статус сервисов
echo "📊 Статус сервисов:"
echo "Nginx: $(systemctl is-active nginx)"
echo "PHP-FPM: $(systemctl is-active php8.3-fpm)"

# Проверяем различные места логов Nginx
echo ""
echo "📋 Поиск логов Nginx:"
NGINX_LOG_PATHS=(
    "/var/log/nginx/linux-server-manager_error.log"
    "/var/log/nginx/error.log"
    "/var/log/nginx/access.log"
    "/var/log/nginx/nginx_error.log"
    "/var/log/nginx/nginx_access.log"
)

for log_path in "${NGINX_LOG_PATHS[@]}"; do
    if [ -f "$log_path" ]; then
        echo "✅ Найден: $log_path"
        echo "   Размер: $(ls -lh $log_path | awk '{print $5}')"
        echo "   Последние 5 строк:"
        tail -5 "$log_path"
        echo ""
    else
        echo "❌ Не найден: $log_path"
    fi
done

# Проверяем логи PHP-FPM
echo ""
echo "📋 Поиск логов PHP-FPM:"
PHP_LOG_PATHS=(
    "/var/log/php8.3-fpm.log"
    "/var/log/php-fpm.log"
    "/var/log/php8.3-fpm/error.log"
    "/var/log/php-fpm/error.log"
    "/var/log/php_errors.log"
    "/var/log/php/error.log"
)

for log_path in "${PHP_LOG_PATHS[@]}"; do
    if [ -f "$log_path" ]; then
        echo "✅ Найден: $log_path"
        echo "   Размер: $(ls -lh $log_path | awk '{print $5}')"
        echo "   Последние 5 строк:"
        tail -5 "$log_path"
        echo ""
    else
        echo "❌ Не найден: $log_path"
    fi
done

# Проверяем системные логи
echo ""
echo "📋 Системные логи:"
if [ -f "/var/log/syslog" ]; then
    echo "✅ /var/log/syslog найден"
    echo "   Последние ошибки PHP/Nginx:"
    grep -i "php\|nginx" /var/log/syslog | tail -5
else
    echo "❌ /var/log/syslog не найден"
fi

# Проверяем journalctl
echo ""
echo "📋 Логи systemd:"
echo "Последние логи Nginx:"
journalctl -u nginx --no-pager -n 10

echo ""
echo "Последние логи PHP-FPM:"
journalctl -u php8.3-fpm --no-pager -n 10

# Проверяем конфигурацию Nginx
echo ""
echo "🌐 Проверка конфигурации Nginx:"
if nginx -t > /dev/null 2>&1; then
    echo "✅ Конфигурация Nginx корректна"
    echo "Файл конфигурации:"
    nginx -T 2>/dev/null | grep "server_name sirocco.romansegalla.online" -A 10 -B 5
else
    echo "❌ Ошибка в конфигурации Nginx:"
    nginx -t
fi

echo ""
echo "🎯 Рекомендации:"
echo "1. Если логи не найдены, проверьте права доступа"
echo "2. Перезапустите сервисы: sudo systemctl restart nginx php8.3-fpm"
echo "3. Проверьте отладочные страницы в браузере"
