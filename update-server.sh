#!/bin/bash

# Конфигурация
PROJECT_PATH="/var/www/html/linux-server-manager"
WEB_SERVER="nginx"  # или "apache2"
BACKUP_DIR="/var/backups/linux-server-manager"

# Цвета для вывода
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}🔄 Обновление Linux Server Manager на сервере...${NC}"

# Проверяем, что мы в правильной папке
if [ ! -d "$PROJECT_PATH" ]; then
    echo -e "${RED}❌ Папка проекта не найдена: $PROJECT_PATH${NC}"
    exit 1
fi

# Переходим в папку проекта
cd "$PROJECT_PATH"

# Создаем резервную копию
echo -e "${YELLOW}📦 Создание резервной копии...${NC}"
BACKUP_FILE="$BACKUP_DIR/backup-$(date +%Y%m%d-%H%M%S).tar.gz"
sudo mkdir -p "$BACKUP_DIR"
sudo tar -czf "$BACKUP_FILE" --exclude='.git' --exclude='vendor' .
echo -e "${GREEN}✅ Резервная копия создана: $BACKUP_FILE${NC}"

# Проверяем статус Git
echo -e "${YELLOW}🔍 Проверка статуса Git...${NC}"
if [ -d ".git" ]; then
    # Сохраняем локальные изменения
    if ! git diff --quiet; then
        echo -e "${YELLOW}⚠️ Обнаружены локальные изменения...${NC}"
        echo -e "${BLUE}Измененные файлы:${NC}"
        git status --porcelain
        
        read -p "Сохранить локальные изменения? (y/n): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            echo -e "${YELLOW}💾 Сохраняем изменения в stash...${NC}"
            git stash
            STASHED=true
        else
            echo -e "${YELLOW}🗑️ Отменяем локальные изменения...${NC}"
            git reset --hard HEAD
            STASHED=false
        fi
    else
        STASHED=false
    fi
    
    # Получаем последние изменения
    echo -e "${YELLOW}📥 Получение последних изменений с GitHub...${NC}"
    git fetch origin --tags
    
    # Переключаемся на основную ветку
    git checkout main
    
    # Получаем текущую версию до обновления
    OLD_VERSION=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")
    echo -e "${BLUE}📋 Текущая версия: $OLD_VERSION${NC}"
    
    # Обновляем код
    echo -e "${YELLOW}🔄 Обновление кода...${NC}"
    if git pull origin main; then
        echo -e "${GREEN}✅ Код обновлен${NC}"
        
        # Проверяем, нужно ли создать новый тег для сервера
        NEW_VERSION=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")
        echo -e "${BLUE}📋 Новая версия: $NEW_VERSION${NC}"
        
        # Если версия не изменилась, но есть новые коммиты, создаем серверный тег
        if [ "$OLD_VERSION" = "$NEW_VERSION" ]; then
            echo -e "${YELLOW}🔄 Создание серверного тега для обновления...${NC}"
            
            # Извлекаем компоненты версии
            if [[ $NEW_VERSION =~ v([0-9]+)\.([0-9]+)\.([0-9]+) ]]; then
                MAJOR=${BASH_REMATCH[1]}
                MINOR=${BASH_REMATCH[2]}
                PATCH=${BASH_REMATCH[3]}
                
                # Создаем серверный тег с суффиксом
                SERVER_PATCH=$((PATCH + 1))
                SERVER_VERSION="v${MAJOR}.${MINOR}.${SERVER_PATCH}"
                
                # Проверяем, существует ли тег
                if ! git tag -l "$SERVER_VERSION" | grep -q "$SERVER_VERSION"; then
                    git tag -a "$SERVER_VERSION" -m "Server update: $SERVER_VERSION - $(date)"
                    echo -e "${GREEN}✅ Серверный тег создан: $SERVER_VERSION${NC}"
                    
                    # Отправляем тег в удаленный репозиторий
                    if git push origin "$SERVER_VERSION"; then
                        echo -e "${GREEN}✅ Серверный тег отправлен в GitHub${NC}"
                    else
                        echo -e "${YELLOW}⚠️ Не удалось отправить тег в GitHub${NC}"
                    fi
                else
                    echo -e "${YELLOW}⚠️ Тег $SERVER_VERSION уже существует${NC}"
                fi
            fi
        else
            echo -e "${GREEN}✅ Версия обновлена: $OLD_VERSION → $NEW_VERSION${NC}"
        fi
    else
        echo -e "${RED}❌ Ошибка при обновлении кода${NC}"
        echo -e "${YELLOW}🔄 Попытка принудительного обновления...${NC}"
        git reset --hard origin/main
        echo -e "${GREEN}✅ Код обновлен принудительно${NC}"
    fi
    
    # Восстанавливаем сохраненные изменения (если были)
    if [ "$STASHED" = true ]; then
        echo -e "${YELLOW}🔄 Восстановление сохраненных изменений...${NC}"
        if git stash pop; then
            echo -e "${GREEN}✅ Изменения восстановлены${NC}"
        else
            echo -e "${YELLOW}⚠️ Конфликты при восстановлении изменений${NC}"
            echo -e "${BLUE}Используйте 'git stash list' для просмотра сохраненных изменений${NC}"
        fi
    fi
else
    echo -e "${RED}❌ Git репозиторий не найден${NC}"
    exit 1
fi

# Обновляем зависимости Composer
echo -e "${YELLOW}📦 Обновление зависимостей Composer...${NC}"
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader
    echo -e "${GREEN}✅ Зависимости обновлены${NC}"
else
    echo -e "${RED}❌ Composer не установлен${NC}"
    exit 1
fi

# Проверяем файл .env
echo -e "${YELLOW}⚙️ Проверка конфигурации...${NC}"
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚠️ Файл .env не найден, создаем из примера...${NC}"
    cp env.example .env
    echo -e "${GREEN}✅ Файл .env создан${NC}"
    echo -e "${YELLOW}⚠️ Не забудьте отредактировать .env файл!${NC}"
fi

# Устанавливаем права доступа
echo -e "${YELLOW}🔐 Установка прав доступа...${NC}"
sudo chown -R www-data:www-data "$PROJECT_PATH"
sudo chmod -R 755 "$PROJECT_PATH"
sudo chmod 644 .env
echo -e "${GREEN}✅ Права доступа установлены${NC}"

# Перезапускаем веб-сервер
echo -e "${YELLOW}🔄 Перезапуск веб-сервера...${NC}"
if [ "$WEB_SERVER" = "nginx" ]; then
    sudo systemctl reload nginx
    echo -e "${GREEN}✅ Nginx перезапущен${NC}"
elif [ "$WEB_SERVER" = "apache2" ]; then
    sudo systemctl reload apache2
    echo -e "${GREEN}✅ Apache перезапущен${NC}"
else
    echo -e "${RED}❌ Неизвестный веб-сервер: $WEB_SERVER${NC}"
fi

# Проверяем статус веб-сервера
echo -e "${YELLOW}🔍 Проверка статуса веб-сервера...${NC}"
if systemctl is-active --quiet "$WEB_SERVER"; then
    echo -e "${GREEN}✅ Веб-сервер работает${NC}"
else
    echo -e "${RED}❌ Веб-сервер не работает!${NC}"
    sudo systemctl status "$WEB_SERVER"
fi

# Очищаем кэш (если есть)
echo -e "${YELLOW}🧹 Очистка кэша...${NC}"
if [ -d "cache" ]; then
    sudo rm -rf cache/*
    echo -e "${GREEN}✅ Кэш очищен${NC}"
fi

# Проверяем логи на ошибки
echo -e "${YELLOW}📋 Проверка логов на ошибки...${NC}"
if [ "$WEB_SERVER" = "nginx" ]; then
    ERROR_LOG="/var/log/nginx/error.log"
else
    ERROR_LOG="/var/log/apache2/error.log"
fi

if [ -f "$ERROR_LOG" ]; then
    echo -e "${BLUE}Последние ошибки в логах:${NC}"
    sudo tail -5 "$ERROR_LOG"
fi

echo -e "${GREEN}🎉 Обновление завершено успешно!${NC}"
echo -e "${BLUE}🌐 Проверьте приложение: http://your-server-ip/${NC}"
echo -e "${BLUE}📦 Резервная копия: $BACKUP_FILE${NC}"

# Информация о версии
echo -e "${BLUE}📋 Информация о версии:${NC}"
git log --oneline -1
CURRENT_VERSION=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")
echo -e "${GREEN}✅ Текущая версия: $CURRENT_VERSION${NC}"
