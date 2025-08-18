#!/bin/bash

# Скрипт для автоматического обновления Linux Server Manager
# Запускается через cron каждую минуту и проверяет флаг приостановки
# Обновлен для работы с автоматическим версионированием

echo "🤖 Автоматическое обновление Linux Server Manager"
echo "================================================"

WEB_ROOT="/var/www/html/linux-server-manager"
FLAG_FILE="$WEB_ROOT/.pause-auto-update"
LOG_FILE="$WEB_ROOT/logs/auto-update.log"
LOCK_FILE="$WEB_ROOT/.auto-update.lock"

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

# Функция для логирования
log_message() {
    local message="$1"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$timestamp] $message" | tee -a "$LOG_FILE"
}

# Создаем директорию для логов
mkdir -p "$(dirname "$LOG_FILE")"

# Проверяем блокировку (предотвращаем одновременные запуски)
if [ -f "$LOCK_FILE" ]; then
    PID=$(cat "$LOCK_FILE" 2>/dev/null)
    if [ -n "$PID" ] && kill -0 "$PID" 2>/dev/null; then
        log_message "⚠️ Обновление уже выполняется (PID: $PID)"
        exit 1
    else
        log_message "🧹 Удаляем устаревший lock файл"
        rm -f "$LOCK_FILE"
    fi
fi

# Создаем lock файл
echo $$ > "$LOCK_FILE"

# Функция очистки при выходе
cleanup() {
    rm -f "$LOCK_FILE"
    exit 0
}

trap cleanup EXIT INT TERM

# Проверяем флаг приостановки
if [ -f "$FLAG_FILE" ]; then
    log_message "⏸️ Автоматическое обновление приостановлено (флаг: $FLAG_FILE)"
    log_message "💡 Для возобновления удалите файл: rm $FLAG_FILE"
    exit 0
fi

# Проверяем директорию приложения
if [ ! -d "$WEB_ROOT" ]; then
    log_message "❌ Директория $WEB_ROOT не найдена"
    exit 1
fi

cd "$WEB_ROOT"

log_message "📁 Переходим в директорию: $(pwd)"

# Проверяем текущую версию
CURRENT_VERSION=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")
log_message "🏷️ Текущая версия: $CURRENT_VERSION"

# Получаем последние изменения и теги
log_message "📥 Получаем последние изменения и теги..."
git fetch origin --tags

# Проверяем, есть ли обновления
if [ "$(git log HEAD..origin/main --oneline | wc -l)" -eq 0 ]; then
    log_message "ℹ️ Обновлений нет"
    exit 0
fi

# Показываем доступные обновления
log_message "🔄 Доступные обновления:"
git log HEAD..origin/main --oneline | while read line; do
    log_message "   $line"
done

# Проверяем, есть ли локальные изменения
if [ -n "$(git status --porcelain)" ]; then
    log_message "💾 Сохраняем локальные изменения..."
    git stash push -m "Auto-stash before update $(date)"
    STASHED=true
else
    STASHED=false
fi

# Выполняем обновление
log_message "🔄 Выполняем обновление..."
if git reset --hard origin/main; then
    log_message "✅ Обновление кода выполнено успешно"
    
    # Проверяем, нужно ли создать серверный тег
    NEW_VERSION=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")
    log_message "🏷️ Новая версия: $NEW_VERSION"
    
    # Если версия не изменилась, но есть новые коммиты, создаем серверный тег
    if [ "$CURRENT_VERSION" = "$NEW_VERSION" ]; then
        log_message "🔄 Создание серверного тега для обновления..."
        
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
                git tag -a "$SERVER_VERSION" -m "Server auto-update: $SERVER_VERSION - $(date)"
                log_message "✅ Серверный тег создан: $SERVER_VERSION"
                
                # Отправляем тег в удаленный репозиторий
                if git push origin "$SERVER_VERSION" > /dev/null 2>&1; then
                    log_message "✅ Серверный тег отправлен в GitHub"
                else
                    log_message "⚠️ Не удалось отправить тег в GitHub"
                fi
            else
                log_message "⚠️ Тег $SERVER_VERSION уже существует"
            fi
        fi
    else
        log_message "✅ Версия обновлена: $CURRENT_VERSION → $NEW_VERSION"
    fi
else
    log_message "❌ Ошибка при обновлении кода"
    exit 1
fi

# Восстанавливаем сохраненные изменения (если были)
if [ "$STASHED" = true ]; then
    log_message "🔄 Восстанавливаем сохраненные изменения..."
    if git stash pop > /dev/null 2>&1; then
        log_message "✅ Изменения восстановлены"
    else
        log_message "⚠️ Конфликты при восстановлении изменений"
    fi
fi

# Обновляем Composer
log_message "📦 Обновляем Composer..."
if [ -f "composer.json" ]; then
    if composer dump-autoload --no-dev; then
        log_message "✅ Composer обновлен"
    else
        log_message "⚠️ Ошибка при обновлении Composer"
    fi
else
    log_message "⚠️ composer.json не найден"
fi

# Создаем директорию кэша
log_message "🗄️ Проверяем директорию кэша..."
if [ ! -d "cache" ]; then
    mkdir -p cache
    chown www-data:www-data cache
    chmod 755 cache
    log_message "✅ Директория кэша создана"
else
    log_message "ℹ️ Директория кэша уже существует"
fi

# Делаем скрипты исполняемыми
log_message "🔧 Обновляем права на скрипты..."
chmod +x *.sh 2>/dev/null

# Проверяем финальную версию
FINAL_VERSION=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")
log_message "🏷️ Финальная версия: $FINAL_VERSION"

# Проверяем доступность приложения
log_message "🌐 Проверяем доступность приложения..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:81/ 2>/dev/null || echo "000")
if [ "$HTTP_CODE" = "200" ]; then
    log_message "✅ Приложение доступно (HTTP $HTTP_CODE)"
else
    log_message "⚠️ Приложение недоступно (HTTP $HTTP_CODE)"
fi

# Проверяем версию в приложении
VERSION_IN_APP=$(curl -s http://localhost:81/ 2>/dev/null | grep -o "v[0-9]\+\.[0-9]\+\.[0-9]\+" | head -1 || echo "")
if [ -n "$VERSION_IN_APP" ]; then
    log_message "✅ Версия в приложении: $VERSION_IN_APP"
else
    log_message "⚠️ Версия в приложении не найдена"
fi

log_message "🎯 Автоматическое обновление завершено успешно!"

# Отправляем уведомление (опционально)
if [ -n "$VERSION_IN_APP" ] && [ "$VERSION_IN_APP" != "$CURRENT_VERSION" ]; then
    log_message "🎉 Обновление с $CURRENT_VERSION до $VERSION_IN_APP выполнено!"
fi
