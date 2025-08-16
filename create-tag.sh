#!/bin/bash

# Скрипт для создания Git тегов версий

echo "🏷️ Создание Git тега для Linux Server Manager"

# Проверяем, что мы в Git репозитории
if [ ! -d ".git" ]; then
    echo "❌ Это не Git репозиторий"
    exit 1
fi

# Проверяем, что нет несохраненных изменений
if [ -n "$(git status --porcelain)" ]; then
    echo "❌ Есть несохраненные изменения. Сначала закоммитьте их."
    git status --short
    exit 1
fi

# Получаем последний тег
LAST_TAG=$(git describe --tags --abbrev=0 2>/dev/null)

if [ -n "$LAST_TAG" ]; then
    echo "📋 Последний тег: $LAST_TAG"
    
    # Парсим версию
    IFS='.' read -ra VERSION_PARTS <<< "${LAST_TAG#v}"
    MAJOR=${VERSION_PARTS[0]}
    MINOR=${VERSION_PARTS[1]}
    PATCH=${VERSION_PARTS[2]}
    
    echo ""
    echo "🎯 Выберите тип обновления:"
    echo "1) Patch (исправления) - v$MAJOR.$MINOR.$((PATCH + 1))"
    echo "2) Minor (новые функции) - v$MAJOR.$((MINOR + 1)).0"
    echo "3) Major (критические изменения) - v$((MAJOR + 1)).0.0"
    echo "4) Ввести версию вручную"
    echo "5) Отмена"
    
    read -p "Выберите вариант (1-5): " choice
    
    case $choice in
        1)
            NEW_TAG="v$MAJOR.$MINOR.$((PATCH + 1))"
            ;;
        2)
            NEW_TAG="v$MAJOR.$((MINOR + 1)).0"
            ;;
        3)
            NEW_TAG="v$((MAJOR + 1)).0.0"
            ;;
        4)
            read -p "Введите новую версию (например, v1.2.3): " NEW_TAG
            ;;
        5)
            echo "❌ Создание тега отменено"
            exit 0
            ;;
        *)
            echo "❌ Неверный выбор"
            exit 1
            ;;
    esac
else
    echo "📋 Тегов еще нет"
    echo ""
    echo "🎯 Выберите начальную версию:"
    echo "1) v0.1.0 (альфа версия)"
    echo "2) v1.0.0 (первый релиз)"
    echo "3) Ввести версию вручную"
    echo "4) Отмена"
    
    read -p "Выберите вариант (1-4): " choice
    
    case $choice in
        1)
            NEW_TAG="v0.1.0"
            ;;
        2)
            NEW_TAG="v1.0.0"
            ;;
        3)
            read -p "Введите версию (например, v1.0.0): " NEW_TAG
            ;;
        4)
            echo "❌ Создание тега отменено"
            exit 0
            ;;
        *)
            echo "❌ Неверный выбор"
            exit 1
            ;;
    esac
fi

echo ""
echo "📝 Создаем тег: $NEW_TAG"

# Запрашиваем сообщение для тега
read -p "Введите сообщение для тега (или нажмите Enter для использования версии): " TAG_MESSAGE
if [ -z "$TAG_MESSAGE" ]; then
    TAG_MESSAGE="Release $NEW_TAG"
fi

# Создаем тег
git tag -a "$NEW_TAG" -m "$TAG_MESSAGE"

if [ $? -eq 0 ]; then
    echo "✅ Тег $NEW_TAG создан локально"
    
    # Спрашиваем о push
    read -p "Отправить тег в удаленный репозиторий? (y/n): " push_choice
    if [[ $push_choice =~ ^[Yy]$ ]]; then
        git push origin "$NEW_TAG"
        if [ $? -eq 0 ]; then
            echo "✅ Тег отправлен в удаленный репозиторий"
        else
            echo "❌ Ошибка отправки тега"
        fi
    fi
    
    echo ""
    echo "🎉 Тег $NEW_TAG успешно создан!"
    echo "📋 Информация о теге:"
    echo "   Версия: $NEW_TAG"
    echo "   Сообщение: $TAG_MESSAGE"
    echo "   Коммит: $(git rev-parse --short HEAD)"
    echo ""
    echo "🌐 Версия будет отображаться в подвале всех страниц приложения"
else
    echo "❌ Ошибка создания тега"
    exit 1
fi
