#!/bin/bash

# Скрипт для принудительного обновления

echo "🚀 Принудительное обновление Linux Server Manager"
echo "================================================"

WEB_ROOT="/var/www/html/linux-server-manager"

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

echo "📁 Переходим в директорию приложения..."
if [ ! -d "$WEB_ROOT" ]; then
    echo "❌ Директория $WEB_ROOT не найдена"
    exit 1
fi

cd "$WEB_ROOT"

echo "📋 Текущий статус Git:"
git status --porcelain

echo ""
echo "🏷️ Текущая версия:"
git describe --tags --always

echo ""
echo "🔄 Начинаем принудительное обновление..."

# Сохраняем текущие изменения (если есть)
if [ -n "$(git status --porcelain)" ]; then
    echo "💾 Сохраняем текущие изменения..."
    git stash push -m "Auto-stash before force update $(date)"
fi

# Получаем последние изменения
echo "📥 Получаем последние изменения..."
git fetch origin

# Принудительно обновляем до последней версии
echo "🔄 Принудительное обновление до origin/main..."
git reset --hard origin/main

# Обновляем Composer
echo "📦 Обновляем Composer..."
if [ -f "composer.json" ]; then
    composer dump-autoload --no-dev
else
    echo "⚠️ composer.json не найден"
fi

# Создаем директорию кэша
echo "🗄️ Создаем директорию кэша..."
mkdir -p cache
chown www-data:www-data cache
chmod 755 cache

# Делаем скрипты исполняемыми
echo "🔧 Делаем скрипты исполняемыми..."
chmod +x *.sh

# Проверяем результат
echo ""
echo "✅ Обновление завершено!"
echo "=========================="

echo "🏷️ Новая версия:"
git describe --tags --always

echo ""
echo "📋 Статус после обновления:"
git status --porcelain

echo ""
echo "🌐 Проверяем доступность приложения..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:81/)
if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ Приложение доступно (HTTP $HTTP_CODE)"
else
    echo "❌ Приложение недоступно (HTTP $HTTP_CODE)"
fi

echo ""
echo "🔍 Проверяем версию в приложении..."
VERSION_IN_APP=$(curl -s http://localhost:81/ | grep -o "v[0-9]\+\.[0-9]\+\.[0-9]\+" | head -1)
if [ -n "$VERSION_IN_APP" ]; then
    echo "✅ Версия в приложении: $VERSION_IN_APP"
else
    echo "⚠️ Версия в приложении не найдена"
fi

echo ""
echo "🎯 Обновление завершено!"
echo "💡 Для проверки работы кэша выполните: sudo ./test-cache.sh"
