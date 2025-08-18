#!/bin/bash

# Скрипт для автоматического увеличения минорной версии

echo "🏷️ Автоматическое версионирование"
echo "================================"

# Получаем текущую версию
CURRENT_VERSION=$(git describe --tags --abbrev=0 2>/dev/null || echo "v1.0.0")

echo "📋 Текущая версия: $CURRENT_VERSION"

# Извлекаем компоненты версии
if [[ $CURRENT_VERSION =~ v([0-9]+)\.([0-9]+)\.([0-9]+) ]]; then
    MAJOR=${BASH_REMATCH[1]}
    MINOR=${BASH_REMATCH[2]}
    PATCH=${BASH_REMATCH[3]}
    
    echo "🔢 Компоненты версии:"
    echo "   Major: $MAJOR"
    echo "   Minor: $MINOR"
    echo "   Patch: $PATCH"
    
    # Увеличиваем минорную версию
    NEW_MINOR=$((MINOR + 1))
    NEW_VERSION="v${MAJOR}.${NEW_MINOR}.0"
    
    echo ""
    echo "🔄 Новая версия: $NEW_VERSION"
    
    # Создаем новый тег
    echo "📝 Создаем тег: $NEW_VERSION"
    if git tag -a "$NEW_VERSION" -m "Auto-increment minor version to $NEW_VERSION"; then
        echo "✅ Тег $NEW_VERSION создан локально"
        
        # Отправляем тег в удаленный репозиторий
        echo "📤 Отправляем тег в удаленный репозиторий..."
        if git push origin "$NEW_VERSION"; then
            echo "✅ Тег $NEW_VERSION отправлен"
            echo ""
            echo "🎉 Версия успешно обновлена!"
            echo "📋 Новая версия: $NEW_VERSION"
        else
            echo "❌ Ошибка при отправке тега"
            exit 1
        fi
    else
        echo "❌ Ошибка при создании тега"
        exit 1
    fi
else
    echo "❌ Не удалось распарсить версию: $CURRENT_VERSION"
    exit 1
fi
