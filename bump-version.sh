#!/bin/bash

# Простой скрипт для увеличения версии

echo "🚀 Увеличение версии"
echo "==================="

# Получаем текущую версию
CURRENT_VERSION=$(git describe --tags --abbrev=0 2>/dev/null || echo "v1.0.0")

echo "📋 Текущая версия: $CURRENT_VERSION"

# Извлекаем компоненты версии
if [[ $CURRENT_VERSION =~ v([0-9]+)\.([0-9]+)\.([0-9]+) ]]; then
    MAJOR=${BASH_REMATCH[1]}
    MINOR=${BASH_REMATCH[2]}
    PATCH=${BASH_REMATCH[3]}
    
    # Увеличиваем минорную версию
    NEW_MINOR=$((MINOR + 1))
    NEW_VERSION="v${MAJOR}.${NEW_MINOR}.0"
    
    echo "🔄 Новая версия: $NEW_VERSION"
    
    # Проверяем, существует ли тег
    if git tag -l "$NEW_VERSION" | grep -q "$NEW_VERSION"; then
        echo "ℹ️ Тег $NEW_VERSION уже существует"
        echo "📋 Текущая версия остается: $CURRENT_VERSION"
    else
        # Создаем новый тег
        echo "📝 Создаем тег: $NEW_VERSION"
        if git tag -a "$NEW_VERSION" -m "Bump version to $NEW_VERSION"; then
            echo "✅ Тег $NEW_VERSION создан"
            
            # Отправляем тег (без запуска pre-push hook)
            echo "📤 Отправляем тег..."
            if git push --no-verify origin "$NEW_VERSION"; then
                echo "✅ Тег отправлен"
                echo ""
                echo "🎉 Версия обновлена до $NEW_VERSION!"
            else
                echo "❌ Ошибка при отправке тега"
                exit 1
            fi
        else
            echo "❌ Ошибка при создании тега"
            exit 1
        fi
    fi
else
    echo "❌ Не удалось распарсить версию: $CURRENT_VERSION"
    exit 1
fi
