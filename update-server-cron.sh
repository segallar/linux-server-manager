#!/bin/bash

# Скрипт для автоматического обновления сервера через cron
# Работает без интерактивности и правильно обрабатывает версионирование

# Конфигурация
PROJECT_PATH="/var/www/html/linux-server-manager"
WEB_SERVER="nginx"  # или "apache2"
BACKUP_DIR="/var/backups/linux-server-manager"
LOG_FILE="/var/log/linux-server-manager-update.log"

# Функция для логирования
log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "$LOG_FILE"
}

log "🔄 Начало автоматического обновления Linux Server Manager"

# Проверяем, что мы в правильной папке
if [ ! -d "$PROJECT_PATH" ]; then
    log "❌ Папка проекта не найдена: $PROJECT_PATH"
    exit 1
fi

# Переходим в папку проекта
cd "$PROJECT_PATH"

# Создаем резервную копию
log "📦 Создание резервной копии..."
BACKUP_FILE="$BACKUP_DIR/backup-$(date +%Y%m%d-%H%M%S).tar.gz"
mkdir -p "$BACKUP_DIR"
tar -czf "$BACKUP_FILE" --exclude='.git' --exclude='vendor' . > /dev/null 2>&1
log "✅ Резервная копия создана: $BACKUP_FILE"

# Проверяем статус Git
log "🔍 Проверка статуса Git..."
if [ -d ".git" ]; then
    # Сохраняем локальные изменения автоматически
    if ! git diff --quiet; then
        log "⚠️ Обнаружены локальные изменения, сохраняем в stash..."
        git stash > /dev/null 2>&1
        STASHED=true
    else
        STASHED=false
    fi
    
    # Получаем последние изменения
    log "📥 Получение последних изменений с GitHub..."
    git fetch origin --tags > /dev/null 2>&1
    
    # Переключаемся на основную ветку
    git checkout main > /dev/null 2>&1
    
    # Получаем текущую версию до обновления
    OLD_VERSION=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")
    log "📋 Текущая версия: $OLD_VERSION"
    
    # Обновляем код
    log "🔄 Обновление кода..."
    if git pull origin main > /dev/null 2>&1; then
        log "✅ Код обновлен"
        
        # Проверяем, нужно ли создать новый тег для сервера
        NEW_VERSION=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")
        log "📋 Новая версия: $NEW_VERSION"
        
        # Если версия не изменилась, но есть новые коммиты, создаем серверный тег
        if [ "$OLD_VERSION" = "$NEW_VERSION" ]; then
            log "🔄 Создание серверного тега для обновления..."
            
            # Извлекаем компоненты версии
            if [[ $NEW_VERSION =~ v([0-9]+)\.([0-9]+)\.([0-9]+) ]]; then
                MAJOR=${BASH_REMATCH[1]}
                MINOR=${BASH_REMATCH[2]}
                PATCH=${BASH_REMATCH[3]}
                
                # Создаем серверный тег
                SERVER_PATCH=$((PATCH + 1))
                SERVER_VERSION="v${MAJOR}.${MINOR}.${SERVER_PATCH}"
                
                # Проверяем, существует ли тег
                if ! git tag -l "$SERVER_VERSION" | grep -q "$SERVER_VERSION"; then
                    git tag -a "$SERVER_VERSION" -m "Server update: $SERVER_VERSION - $(date)" > /dev/null 2>&1
                    log "✅ Серверный тег создан: $SERVER_VERSION"
                    
                    # Отправляем тег в удаленный репозиторий
                    if git push origin "$SERVER_VERSION" > /dev/null 2>&1; then
                        log "✅ Серверный тег отправлен в GitHub"
                    else
                        log "⚠️ Не удалось отправить тег в GitHub"
                    fi
                else
                    log "⚠️ Тег $SERVER_VERSION уже существует"
                fi
            fi
        else
            log "✅ Версия обновлена: $OLD_VERSION → $NEW_VERSION"
        fi
    else
        log "❌ Ошибка при обновлении кода, попытка принудительного обновления..."
        git reset --hard origin/main > /dev/null 2>&1
        log "✅ Код обновлен принудительно"
    fi
    
    # Восстанавливаем сохраненные изменения (если были)
    if [ "$STASHED" = true ]; then
        log "🔄 Восстановление сохраненных изменений..."
        if git stash pop > /dev/null 2>&1; then
            log "✅ Изменения восстановлены"
        else
            log "⚠️ Конфликты при восстановлении изменений"
        fi
    fi
else
    log "❌ Git репозиторий не найден"
    exit 1
fi

# Обновляем зависимости Composer
log "📦 Обновление зависимостей Composer..."
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader --quiet
    log "✅ Зависимости обновлены"
else
    log "❌ Composer не установлен"
    exit 1
fi

# Проверяем файл .env
log "⚙️ Проверка конфигурации..."
if [ ! -f ".env" ]; then
    log "⚠️ Файл .env не найден, создаем из примера..."
    cp env.example .env
    log "✅ Файл .env создан"
fi

# Устанавливаем права доступа
log "🔐 Установка прав доступа..."
chown -R www-data:www-data "$PROJECT_PATH"
chmod -R 755 "$PROJECT_PATH"
chmod 644 .env
log "✅ Права доступа установлены"

# Перезапускаем веб-сервер
log "🔄 Перезапуск веб-сервера..."
if [ "$WEB_SERVER" = "nginx" ]; then
    systemctl reload nginx > /dev/null 2>&1
    log "✅ Nginx перезапущен"
elif [ "$WEB_SERVER" = "apache2" ]; then
    systemctl reload apache2 > /dev/null 2>&1
    log "✅ Apache перезапущен"
else
    log "❌ Неизвестный веб-сервер: $WEB_SERVER"
fi

# Проверяем статус веб-сервера
log "🔍 Проверка статуса веб-сервера..."
if systemctl is-active --quiet "$WEB_SERVER"; then
    log "✅ Веб-сервер работает"
else
    log "❌ Веб-сервер не работает!"
fi

# Очищаем кэш (если есть)
log "🧹 Очистка кэша..."
if [ -d "cache" ]; then
    rm -rf cache/*
    log "✅ Кэш очищен"
fi

# Проверяем логи на ошибки
log "📋 Проверка логов на ошибки..."
if [ "$WEB_SERVER" = "nginx" ]; then
    ERROR_LOG="/var/log/nginx/error.log"
else
    ERROR_LOG="/var/log/apache2/error.log"
fi

if [ -f "$ERROR_LOG" ]; then
    log "📋 Последние ошибки в логах:"
    tail -3 "$ERROR_LOG" | while read line; do
        log "   $line"
    done
fi

# Информация о версии
CURRENT_VERSION=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")
log "✅ Обновление завершено! Текущая версия: $CURRENT_VERSION"
log "📦 Резервная копия: $BACKUP_FILE"

# Очищаем старые резервные копии (оставляем только последние 5)
log "🧹 Очистка старых резервных копий..."
cd "$BACKUP_DIR"
ls -t backup-*.tar.gz | tail -n +6 | xargs -r rm
log "✅ Старые резервные копии удалены"

log "🎉 Автоматическое обновление завершено успешно!"
