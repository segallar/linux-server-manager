#!/bin/bash

# Скрипт для диагностики проблем с обновлением

echo "🔍 Диагностика проблем с обновлением"
echo "===================================="

WEB_ROOT="/var/www/html/linux-server-manager"

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

echo "📁 Проверяем директорию приложения..."
if [ ! -d "$WEB_ROOT" ]; then
    echo "❌ Директория $WEB_ROOT не найдена"
    exit 1
fi

cd "$WEB_ROOT"

echo "📋 Проверяем Git статус..."
echo "Текущая директория: $(pwd)"
echo "Git статус:"
git status --porcelain

echo ""
echo "🏷️ Проверяем теги..."
echo "Последние теги:"
git tag --sort=-version:refname | head -5

echo ""
echo "📝 Последние коммиты:"
git log --oneline -5

echo ""
echo "🔗 Проверяем удаленный репозиторий..."
echo "Удаленные ветки:"
git branch -r

echo ""
echo "🔄 Проверяем возможность обновления..."
echo "Изменения в удаленном репозитории:"
git fetch origin
git log HEAD..origin/main --oneline

echo ""
echo "📦 Проверяем Composer..."
if [ -f "composer.json" ]; then
    echo "✅ composer.json найден"
    if [ -d "vendor" ]; then
        echo "✅ vendor директория найдена"
    else
        echo "❌ vendor директория не найдена"
    fi
else
    echo "❌ composer.json не найден"
fi

echo ""
echo "🗄️ Проверяем кэш..."
if [ -d "cache" ]; then
    echo "✅ Директория кэша найдена"
    echo "Права доступа:"
    ls -la cache/
else
    echo "❌ Директория кэша не найдена"
fi

echo ""
echo "🌐 Проверяем веб-сервер..."
echo "Статус Nginx:"
systemctl status nginx --no-pager -l

echo ""
echo "Статус PHP-FPM:"
systemctl status php8.1-fpm --no-pager -l

echo ""
echo "📄 Проверяем доступность приложения..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:81/)
echo "HTTP код главной страницы: $HTTP_CODE"

echo ""
echo "🔧 Рекомендации по обновлению:"
echo "=============================="

if [ "$(git log HEAD..origin/main --oneline | wc -l)" -gt 0 ]; then
    echo "✅ Есть обновления для загрузки"
    echo "💡 Выполните: git pull origin main"
else
    echo "ℹ️ Обновлений нет"
fi

if [ ! -d "vendor" ]; then
    echo "💡 Выполните: composer install"
fi

if [ ! -d "cache" ]; then
    echo "💡 Выполните: mkdir -p cache && chown www-data:www-data cache"
fi

echo ""
echo "🎯 Для принудительного обновления выполните:"
echo "git fetch origin"
echo "git reset --hard origin/main"
echo "composer dump-autoload"
