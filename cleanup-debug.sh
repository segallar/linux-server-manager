#!/bin/bash

# Скрипт для очистки всех отладочных файлов и скриптов

echo "🧹 Очистка отладочных файлов и скриптов"
echo "======================================="

WEB_ROOT="/var/www/html/linux-server-manager"

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

cd "$WEB_ROOT"

echo "📁 Текущая директория: $(pwd)"

# Список файлов для удаления
DEBUG_FILES=(
    # Отладочные PHP файлы
    "public/debug.php"
    "public/test.php"
    "public/phpinfo.php"
    "public/test-main.php"
    "public/test-routing.php"
    "public/cache-test.php"
    
    # Отладочные shell скрипты
    "diagnose.sh"
    "fix-blank-screen.sh"
    "check-logs.sh"
    "quick-check.sh"
    "fix-nginx-config.sh"
    "fix-php-fpm.sh"
    "debug-blank-page.sh"
    "test-cache.sh"
    "check-update.sh"
    "force-update.sh"
    "performance-test.sh"
    "simple-performance-test.sh"
    "analyze-slow-pages.sh"
    "monitor-performance.sh"
    "performance-report.sh"
    
    # Временные файлы
    "VERSION"
    "update-version.sh"
    
    # Lock файлы
    ".auto-update.lock"
    
    # Временные файлы Git
    ".git/index.lock"
)

# Список директорий для очистки
DEBUG_DIRS=(
    "logs/debug"
    "tmp"
    ".debug"
)

echo "🗑️ Удаляем отладочные файлы..."

deleted_count=0
for file in "${DEBUG_FILES[@]}"; do
    if [ -f "$file" ]; then
        rm "$file"
        echo "✅ Удален: $file"
        ((deleted_count++))
    elif [ -d "$file" ]; then
        rm -rf "$file"
        echo "✅ Удалена директория: $file"
        ((deleted_count++))
    fi
done

echo ""
echo "🗂️ Очищаем отладочные директории..."

for dir in "${DEBUG_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        rm -rf "$dir"
        echo "✅ Удалена директория: $dir"
        ((deleted_count++))
    fi
done

echo ""
echo "🧹 Очищаем временные файлы..."

# Удаляем временные файлы PHP
find . -name "*.tmp" -delete 2>/dev/null
find . -name "*.cache" -not -path "./cache/*" -delete 2>/dev/null
find . -name "*.log" -not -path "./logs/*" -delete 2>/dev/null

# Удаляем файлы с отладочной информацией
find . -name "*debug*" -type f -delete 2>/dev/null
find . -name "*test*" -type f -not -path "./templates/*" -not -path "./src/*" -delete 2>/dev/null

echo ""
echo "📋 Проверяем оставшиеся файлы..."

# Показываем оставшиеся скрипты
echo "🔧 Оставшиеся скрипты:"
ls -la *.sh 2>/dev/null | grep -E "\.(sh)$" || echo "Скрипты не найдены"

echo ""
echo "📁 Оставшиеся PHP файлы в public/:"
ls -la public/*.php 2>/dev/null | grep -v "index.php" || echo "PHP файлы не найдены"

echo ""
echo "📊 Результаты очистки:"
echo "======================"
echo "🗑️ Удалено файлов/директорий: $deleted_count"

# Проверяем размер директории
TOTAL_SIZE=$(du -sh . | cut -f1)
echo "💾 Общий размер проекта: $TOTAL_SIZE"

# Проверяем Git статус
echo ""
echo "📋 Git статус:"
git status --porcelain

echo ""
echo "🎯 Очистка завершена!"
echo "===================="
echo ""
echo "✅ Все отладочные файлы и скрипты удалены"
echo "✅ Оставлены только рабочие файлы"
echo "✅ Проект готов к продакшену"
echo ""
echo "💡 Для финальной проверки выполните:"
echo "   git status"
echo "   ls -la"
