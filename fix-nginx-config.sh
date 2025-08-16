#!/bin/bash

echo "🔧 Исправление конфигурации Nginx..."

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

# Проверяем текущую конфигурацию
echo "📋 Текущая конфигурация Nginx:"
if [ -f "/etc/nginx/sites-available/linux-server-manager" ]; then
    echo "✅ Конфигурация найдена в sites-available"
    CONFIG_FILE="/etc/nginx/sites-available/linux-server-manager"
elif [ -f "/etc/nginx/conf.d/linux-server-manager.conf" ]; then
    echo "✅ Конфигурация найдена в conf.d"
    CONFIG_FILE="/etc/nginx/conf.d/linux-server-manager.conf"
else
    echo "❌ Конфигурация не найдена"
    echo "Создаем новую конфигурацию..."
    CONFIG_FILE="/etc/nginx/sites-available/linux-server-manager"
fi

# Создаем правильную конфигурацию
echo "📝 Создаем правильную конфигурацию..."
cat > "$CONFIG_FILE" << 'EOF'
server {
    listen 81;
    server_name sirocco.romansegalla.online;
    root /var/www/html/linux-server-manager/public;
    index index.php index.html;

    # Увеличиваем таймауты
    proxy_connect_timeout 300s;
    proxy_send_timeout 300s;
    proxy_read_timeout 300s;
    fastcgi_send_timeout 300s;
    fastcgi_read_timeout 300s;

    # Логирование
    access_log /var/log/nginx/linux-server-manager_access.log;
    error_log /var/log/nginx/linux-server-manager_error.log;

    # Основные настройки
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Обработка PHP файлов
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Увеличиваем таймауты для PHP
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_connect_timeout 300;
        
        # Настройки буферизации
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    # Статические файлы
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Безопасность - скрываем системные файлы
    location ~ /\. {
        deny all;
    }

    location ~ /(composer\.json|composer\.lock|\.env) {
        deny all;
    }

    # Gzip сжатие
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/javascript
        application/xml+rss
        application/json;
}
EOF

# Активируем сайт если он в sites-available
if [[ "$CONFIG_FILE" == *"sites-available"* ]]; then
    echo "🔗 Активируем сайт..."
    ln -sf "$CONFIG_FILE" /etc/nginx/sites-enabled/
fi

# Проверяем конфигурацию
echo "🔍 Проверяем конфигурацию Nginx..."
if nginx -t; then
    echo "✅ Конфигурация корректна"
else
    echo "❌ Ошибка в конфигурации"
    exit 1
fi

# Перезапускаем Nginx
echo "🔄 Перезапускаем Nginx..."
systemctl reload nginx

# Проверяем статус
echo "📊 Статус Nginx:"
systemctl is-active nginx

# Тестируем доступность
echo ""
echo "🧪 Тестируем доступность..."
sleep 2

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
else
    echo "❌ Главная страница не работает (код $HTTP_CODE)"
fi

echo ""
echo "🎉 Конфигурация исправлена!"
echo "🌐 Теперь попробуйте:"
echo "   http://sirocco.romansegalla.online:81/debug.php"
echo "   http://sirocco.romansegalla.online:81/"
