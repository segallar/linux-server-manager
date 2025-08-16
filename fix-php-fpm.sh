#!/bin/bash

echo "🔧 Исправление конфигурации PHP-FPM..."

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

# Проверяем статус PHP-FPM
echo "📊 Статус PHP-FPM:"
systemctl is-active php8.3-fpm

# Проверяем socket
echo ""
echo "🔌 Проверка PHP-FPM socket:"
if [ -S "/run/php/php8.3-fpm.sock" ]; then
    echo "✅ Socket существует"
    ls -la /run/php/php8.3-fpm.sock
else
    echo "❌ Socket не найден"
fi

# Проверяем права доступа к файлам
echo ""
echo "🔐 Проверка прав доступа:"
WEB_ROOT="/var/www/html/linux-server-manager"
if [ -d "$WEB_ROOT" ]; then
    echo "Владелец директории: $(ls -ld $WEB_ROOT | awk '{print $3":"$4}')"
    echo "Права на public/: $(ls -ld $WEB_ROOT/public | awk '{print $1}')"
    
    # Проверяем конкретные файлы
    FILES=(
        "public/index.php"
        "public/debug.php"
        "public/test.php"
    )
    
    for file in "${FILES[@]}"; do
        if [ -f "$WEB_ROOT/$file" ]; then
            echo "✅ $file - $(ls -l $WEB_ROOT/$file | awk '{print $1, $3":"$4}')"
        else
            echo "❌ $file не найден"
        fi
    done
else
    echo "❌ Директория $WEB_ROOT не найдена"
fi

# Исправляем права доступа
echo ""
echo "🔧 Исправляем права доступа..."
if [ -d "$WEB_ROOT" ]; then
    # Устанавливаем правильного владельца
    chown -R www-data:www-data "$WEB_ROOT"
    echo "✅ Владелец изменен на www-data:www-data"
    
    # Устанавливаем правильные права
    find "$WEB_ROOT" -type d -exec chmod 755 {} \;
    find "$WEB_ROOT" -type f -exec chmod 644 {} \;
    echo "✅ Права доступа исправлены"
    
    # Проверяем результат
    echo "Проверка после исправления:"
    ls -ld "$WEB_ROOT/public"
    ls -l "$WEB_ROOT/public/debug.php"
fi

# Проверяем конфигурацию PHP-FPM
echo ""
echo "📋 Проверка конфигурации PHP-FPM:"
PHP_FPM_CONF="/etc/php/8.3/fpm/pool.d/www.conf"
if [ -f "$PHP_FPM_CONF" ]; then
    echo "✅ Конфигурация найдена"
    
    # Проверяем настройки пользователя и группы
    echo "Пользователь PHP-FPM:"
    grep "^user\|^group" "$PHP_FPM_CONF"
    
    # Проверяем настройки socket
    echo "Настройки socket:"
    grep "listen" "$PHP_FPM_CONF"
else
    echo "❌ Конфигурация не найдена"
fi

# Перезапускаем PHP-FPM
echo ""
echo "🔄 Перезапускаем PHP-FPM..."
systemctl restart php8.3-fpm

# Проверяем статус после перезапуска
echo "📊 Статус после перезапуска:"
systemctl is-active php8.3-fpm

# Проверяем socket после перезапуска
echo ""
echo "🔌 Socket после перезапуска:"
if [ -S "/run/php/php8.3-fpm.sock" ]; then
    echo "✅ Socket существует"
    ls -la /run/php/php8.3-fpm.sock
else
    echo "❌ Socket не найден"
fi

# Тестируем PHP напрямую
echo ""
echo "🧪 Тест PHP напрямую:"
if [ -f "$WEB_ROOT/public/debug.php" ]; then
    echo "Тестируем выполнение debug.php:"
    cd "$WEB_ROOT"
    OUTPUT=$(sudo -u www-data php public/debug.php 2>&1)
    if [ $? -eq 0 ]; then
        echo "✅ debug.php выполняется без ошибок"
        echo "Первые 200 символов вывода:"
        echo "$OUTPUT" | head -c 200
    else
        echo "❌ debug.php содержит ошибки:"
        echo "$OUTPUT"
    fi
else
    echo "❌ debug.php не найден"
fi

# Тестируем HTTP запрос
echo ""
echo "🌐 Тест HTTP запроса:"
sleep 2
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:81/debug.php)
if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ debug.php работает через HTTP (код $HTTP_CODE)"
else
    echo "❌ debug.php не работает через HTTP (код $HTTP_CODE)"
fi

echo ""
echo "🎉 Исправления применены!"
echo "🌐 Теперь попробуйте:"
echo "   http://sirocco.romansegalla.online:81/debug.php"
