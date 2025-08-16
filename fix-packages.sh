#!/bin/bash

echo "🔧 Быстрое исправление управления пакетами..."

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

# Создаем директорию для кэша если её нет
mkdir -p /tmp
chmod 777 /tmp

# Проверяем и очищаем возможные блокировки apt
echo "🔍 Проверяем блокировки apt..."
if [ -f "/var/lib/apt/lists/lock" ]; then
    echo "⚠️  Найдена блокировка apt/lists/lock, удаляем..."
    rm -f /var/lib/apt/lists/lock
fi

if [ -f "/var/cache/apt/archives/lock" ]; then
    echo "⚠️  Найдена блокировка apt/archives/lock, удаляем..."
    rm -f /var/cache/apt/archives/lock
fi

if [ -f "/var/lib/dpkg/lock" ]; then
    echo "⚠️  Найдена блокировка dpkg/lock, удаляем..."
    rm -f /var/lib/dpkg/lock
fi

# Очищаем кэш приложения
echo "🧹 Очищаем кэш приложения..."
if [ -f "/tmp/package_cache.json" ]; then
    rm -f /tmp/package_cache.json
    echo "✅ Кэш пакетов очищен"
fi

# Проверяем права на выполнение команд
echo "🔐 Проверяем права sudo..."
if ! sudo -n true 2>/dev/null; then
    echo "⚠️  Требуется ввод пароля для sudo"
fi

# Проверяем доступность команд
echo "🔍 Проверяем доступность команд..."
if [ ! -f "/usr/bin/apt" ]; then
    echo "❌ Команда apt не найдена"
    exit 1
fi

if [ ! -f "/usr/bin/dpkg" ]; then
    echo "❌ Команда dpkg не найдена"
    exit 1
fi

echo "✅ Все команды доступны"

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

# Тестируем команды apt
echo "🧪 Тестируем команды apt..."
echo "Проверка apt list --upgradable (с таймаутом 5 сек):"
timeout 5 apt list --upgradable 2>/dev/null | head -5
if [ $? -eq 124 ]; then
    echo "⚠️  Команда apt зависла (таймаут)"
else
    echo "✅ Команда apt работает нормально"
fi

echo ""
echo "🎉 Исправления применены!"
echo "🌐 Теперь попробуйте открыть страницу управления пакетами"
echo "📝 Если проблемы остаются, проверьте:"
echo "   - Логи PHP: /var/log/php8.3-fpm.log"
echo "   - Логи Nginx: /var/log/nginx/linux-server-manager_error.log"
echo "   - Блокировки apt: ls -la /var/lib/apt/lists/lock /var/cache/apt/archives/lock /var/lib/dpkg/lock"
