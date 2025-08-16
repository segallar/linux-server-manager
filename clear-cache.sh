#!/bin/bash

# Скрипт для быстрой очистки кэша

echo "🧹 Быстрая очистка кэша Linux Server Manager"
echo "============================================"

WEB_ROOT="/var/www/html/linux-server-manager"
CACHE_DIR="$WEB_ROOT/cache"

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

# Проверяем существование директории кэша
if [ ! -d "$CACHE_DIR" ]; then
    echo "📁 Создаем директорию кэша..."
    mkdir -p "$CACHE_DIR"
    chown www-data:www-data "$CACHE_DIR"
    chmod 755 "$CACHE_DIR"
fi

# Очищаем кэш
echo "🗑️ Очищаем кэш..."
deleted_count=$(find "$CACHE_DIR" -name "*.cache" -delete 2>/dev/null | wc -l)

if [ "$deleted_count" -gt 0 ]; then
    echo "✅ Удалено файлов кэша: $deleted_count"
else
    echo "📭 Кэш уже пуст"
fi

# Показываем статистику
echo ""
echo "📊 Статистика после очистки:"
echo "----------------------------"
total_files=$(find "$CACHE_DIR" -name "*.cache" 2>/dev/null | wc -l)
total_size=$(du -sh "$CACHE_DIR" 2>/dev/null | cut -f1)

echo "📁 Файлов в кэше: $total_files"
echo "💾 Размер кэша: $total_size"

echo ""
echo "🎯 Кэш очищен! При следующем посещении медленных страниц данные будут загружены заново."
