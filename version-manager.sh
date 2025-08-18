#!/bin/bash

# Улучшенный менеджер версий для Linux Server Manager

set -e

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🏷️ Менеджер версий Linux Server Manager${NC}"
echo "=============================================="

# Проверяем, что мы в Git репозитории
if [ ! -d ".git" ]; then
    echo -e "${RED}❌ Это не Git репозиторий${NC}"
    exit 1
fi

# Проверяем, что нет несохраненных изменений
if [ -n "$(git status --porcelain)" ]; then
    echo -e "${RED}❌ Есть несохраненные изменения. Сначала закоммитьте их.${NC}"
    git status --short
    exit 1
fi

# Получаем текущую версию
CURRENT_VERSION=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")

echo -e "${YELLOW}📋 Текущая версия: $CURRENT_VERSION${NC}"

# Проверяем, что текущий тег связан с HEAD
CURRENT_TAG_COMMIT=$(git rev-parse "$CURRENT_VERSION" 2>/dev/null || echo "")
HEAD_COMMIT=$(git rev-parse HEAD)

if [ "$CURRENT_TAG_COMMIT" != "$HEAD_COMMIT" ]; then
    echo -e "${YELLOW}⚠️ Текущий тег $CURRENT_VERSION не связан с HEAD${NC}"
    echo -e "${YELLOW}   Тег указывает на: $(git show --oneline -s "$CURRENT_TAG_COMMIT" 2>/dev/null || echo "неизвестный коммит")${NC}"
    echo -e "${YELLOW}   HEAD указывает на: $(git show --oneline -s HEAD)${NC}"
    echo ""
    echo -e "${BLUE}🎯 Выберите действие:${NC}"
    echo "1) Создать новый тег для текущего коммита"
    echo "2) Переместить существующий тег на текущий коммит"
    echo "3) Отмена"
    
    read -p "Выберите вариант (1-3): " choice
    
    case $choice in
        1)
            # Создаем новый тег
            if [[ $CURRENT_VERSION =~ v([0-9]+)\.([0-9]+)\.([0-9]+) ]]; then
                MAJOR=${BASH_REMATCH[1]}
                MINOR=${BASH_REMATCH[2]}
                PATCH=${BASH_REMATCH[3]}
                
                echo ""
                echo -e "${BLUE}🎯 Выберите тип обновления:${NC}"
                echo "1) Patch (исправления) - v$MAJOR.$MINOR.$((PATCH + 1))"
                echo "2) Minor (новые функции) - v$MAJOR.$((MINOR + 1)).0"
                echo "3) Major (критические изменения) - v$((MAJOR + 1)).0.0"
                echo "4) Ввести версию вручную"
                echo "5) Отмена"
                
                read -p "Выберите вариант (1-5): " version_choice
                
                case $version_choice in
                    1)
                        NEW_VERSION="v$MAJOR.$MINOR.$((PATCH + 1))"
                        ;;
                    2)
                        NEW_VERSION="v$MAJOR.$((MINOR + 1)).0"
                        ;;
                    3)
                        NEW_VERSION="v$((MAJOR + 1)).0.0"
                        ;;
                    4)
                        read -p "Введите новую версию (например, v1.2.3): " NEW_VERSION
                        ;;
                    5)
                        echo -e "${YELLOW}❌ Создание тега отменено${NC}"
                        exit 0
                        ;;
                    *)
                        echo -e "${RED}❌ Неверный выбор${NC}"
                        exit 1
                        ;;
                esac
            else
                echo -e "${RED}❌ Не удалось распарсить версию: $CURRENT_VERSION${NC}"
                exit 1
            fi
            ;;
        2)
            # Перемещаем существующий тег
            NEW_VERSION=$CURRENT_VERSION
            ;;
        3)
            echo -e "${YELLOW}❌ Операция отменена${NC}"
            exit 0
            ;;
        *)
            echo -e "${RED}❌ Неверный выбор${NC}"
            exit 1
            ;;
    esac
else
    echo -e "${GREEN}✅ Текущий тег $CURRENT_VERSION связан с HEAD${NC}"
    echo ""
    echo -e "${BLUE}🎯 Выберите действие:${NC}"
    echo "1) Создать новую версию"
    echo "2) Показать информацию о текущей версии"
    echo "3) Отмена"
    
    read -p "Выберите вариант (1-3): " choice
    
    case $choice in
        1)
            # Создаем новую версию
            if [[ $CURRENT_VERSION =~ v([0-9]+)\.([0-9]+)\.([0-9]+) ]]; then
                MAJOR=${BASH_REMATCH[1]}
                MINOR=${BASH_REMATCH[2]}
                PATCH=${BASH_REMATCH[3]}
                
                echo ""
                echo -e "${BLUE}🎯 Выберите тип обновления:${NC}"
                echo "1) Patch (исправления) - v$MAJOR.$MINOR.$((PATCH + 1))"
                echo "2) Minor (новые функции) - v$MAJOR.$((MINOR + 1)).0"
                echo "3) Major (критические изменения) - v$((MAJOR + 1)).0.0"
                echo "4) Ввести версию вручную"
                echo "5) Отмена"
                
                read -p "Выберите вариант (1-5): " version_choice
                
                case $version_choice in
                    1)
                        NEW_VERSION="v$MAJOR.$MINOR.$((PATCH + 1))"
                        ;;
                    2)
                        NEW_VERSION="v$MAJOR.$((MINOR + 1)).0"
                        ;;
                    3)
                        NEW_VERSION="v$((MAJOR + 1)).0.0"
                        ;;
                    4)
                        read -p "Введите новую версию (например, v1.2.3): " NEW_VERSION
                        ;;
                    5)
                        echo -e "${YELLOW}❌ Создание тега отменено${NC}"
                        exit 0
                        ;;
                    *)
                        echo -e "${RED}❌ Неверный выбор${NC}"
                        exit 1
                        ;;
                esac
            else
                echo -e "${RED}❌ Не удалось распарсить версию: $CURRENT_VERSION${NC}"
                exit 1
            fi
            ;;
        2)
            # Показываем информацию о текущей версии
            echo ""
            echo -e "${GREEN}📊 Информация о версии $CURRENT_VERSION:${NC}"
            echo "   Коммит: $(git rev-parse --short HEAD)"
            echo "   Дата: $(git log -1 --format=%cd --date=short)"
            echo "   Сообщение: $(git log -1 --format=%s)"
            echo "   Автор: $(git log -1 --format=%an)"
            echo ""
            echo -e "${GREEN}✅ Версия корректна${NC}"
            exit 0
            ;;
        3)
            echo -e "${YELLOW}❌ Операция отменена${NC}"
            exit 0
            ;;
        *)
            echo -e "${RED}❌ Неверный выбор${NC}"
            exit 1
            ;;
    esac
fi

# Проверяем, что новая версия не существует
if git tag -l "$NEW_VERSION" | grep -q "$NEW_VERSION"; then
    echo -e "${RED}❌ Тег $NEW_VERSION уже существует${NC}"
    exit 1
fi

echo ""
echo -e "${BLUE}📝 Создаем тег: $NEW_VERSION${NC}"

# Запрашиваем сообщение для тега
read -p "Введите сообщение для тега (или нажмите Enter для использования версии): " TAG_MESSAGE
if [ -z "$TAG_MESSAGE" ]; then
    TAG_MESSAGE="Release $NEW_VERSION"
fi

# Создаем тег
if git tag -a "$NEW_VERSION" -m "$TAG_MESSAGE"; then
    echo -e "${GREEN}✅ Тег $NEW_VERSION создан локально${NC}"
    
    # Спрашиваем о push
    read -p "Отправить тег в удаленный репозиторий? (y/n): " push_choice
    if [[ $push_choice =~ ^[Yy]$ ]]; then
        if git push origin "$NEW_VERSION"; then
            echo -e "${GREEN}✅ Тег отправлен в удаленный репозиторий${NC}"
        else
            echo -e "${RED}❌ Ошибка отправки тега${NC}"
            exit 1
        fi
    fi
    
    echo ""
    echo -e "${GREEN}🎉 Тег $NEW_VERSION успешно создан!${NC}"
    echo -e "${BLUE}📋 Информация о теге:${NC}"
    echo "   Версия: $NEW_VERSION"
    echo "   Сообщение: $TAG_MESSAGE"
    echo "   Коммит: $(git rev-parse --short HEAD)"
    echo "   Дата: $(git log -1 --format=%cd --date=short)"
    echo ""
    echo -e "${GREEN}🌐 Версия будет отображаться в подвале всех страниц приложения${NC}"
else
    echo -e "${RED}❌ Ошибка создания тега${NC}"
    exit 1
fi
