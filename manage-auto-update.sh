#!/bin/bash

# Скрипт для управления автоматическим обновлением

echo "⚙️ Управление автоматическим обновлением"
echo "======================================="

WEB_ROOT="/var/www/html/linux-server-manager"
FLAG_FILE="$WEB_ROOT/.pause-auto-update"
LOG_FILE="$WEB_ROOT/logs/auto-update.log"
CRON_FILE="/etc/cron.d/linux-server-manager-auto-update"

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

# Функция для показа статуса
show_status() {
    echo "📊 Статус автоматического обновления:"
    echo "====================================="
    
    # Проверяем флаг приостановки
    if [ -f "$FLAG_FILE" ]; then
        echo "⏸️ Статус: ПРИОСТАНОВЛЕНО"
        echo "📅 Флаг создан: $(stat -c %y "$FLAG_FILE" 2>/dev/null || echo 'неизвестно')"
    else
        echo "🔄 Статус: АКТИВНО"
    fi
    
    # Проверяем cron задачу
    if [ -f "$CRON_FILE" ]; then
        echo "⏰ Cron задача: УСТАНОВЛЕНА"
        echo "📋 Расписание:"
        cat "$CRON_FILE" | grep -v "^#" | grep -v "^$"
    else
        echo "⏰ Cron задача: НЕ УСТАНОВЛЕНА"
    fi
    
    # Показываем последние логи
    if [ -f "$LOG_FILE" ]; then
        echo ""
        echo "📝 Последние записи в логе:"
        echo "---------------------------"
        tail -10 "$LOG_FILE" 2>/dev/null || echo "Лог пуст"
    else
        echo ""
        echo "📝 Лог файл не найден"
    fi
}

# Функция для установки cron задачи
install_cron() {
    echo "⏰ Установка cron задачи..."
    
    # Создаем cron файл
    cat > "$CRON_FILE" << EOF
# Автоматическое обновление Linux Server Manager
# Проверка каждую минуту
* * * * * root /var/www/html/linux-server-manager/auto-update.sh > /dev/null 2>&1

# Очистка старых логов каждые 24 часа
0 2 * * * root find /var/www/html/linux-server-manager/logs -name "*.log" -mtime +7 -delete > /dev/null 2>&1
EOF
    
    # Устанавливаем права
    chmod 644 "$CRON_FILE"
    
    # Перезагружаем cron
    systemctl reload cron 2>/dev/null || systemctl reload crond 2>/dev/null
    
    echo "✅ Cron задача установлена"
    echo "📋 Расписание: каждую минуту"
}

# Функция для удаления cron задачи
remove_cron() {
    echo "🗑️ Удаление cron задачи..."
    
    if [ -f "$CRON_FILE" ]; then
        rm "$CRON_FILE"
        systemctl reload cron 2>/dev/null || systemctl reload crond 2>/dev/null
        echo "✅ Cron задача удалена"
    else
        echo "ℹ️ Cron задача не найдена"
    fi
}

# Функция для приостановки обновлений
pause_updates() {
    echo "⏸️ Приостановка автоматического обновления..."
    
    touch "$FLAG_FILE"
    echo "✅ Автоматическое обновление приостановлено"
    echo "💡 Для возобновления выполните: $0 resume"
}

# Функция для возобновления обновлений
resume_updates() {
    echo "🔄 Возобновление автоматического обновления..."
    
    if [ -f "$FLAG_FILE" ]; then
        rm "$FLAG_FILE"
        echo "✅ Автоматическое обновление возобновлено"
    else
        echo "ℹ️ Обновления уже активны"
    fi
}

# Функция для ручного запуска обновления
run_update() {
    echo "🚀 Ручной запуск обновления..."
    
    if [ -f "$WEB_ROOT/auto-update.sh" ]; then
        "$WEB_ROOT/auto-update.sh"
    else
        echo "❌ Скрипт auto-update.sh не найден"
    fi
}

# Функция для очистки логов
clear_logs() {
    echo "🧹 Очистка логов..."
    
    if [ -f "$LOG_FILE" ]; then
        rm "$LOG_FILE"
        echo "✅ Логи очищены"
    else
        echo "ℹ️ Лог файл не найден"
    fi
}

# Главное меню
case "${1:-}" in
    "status")
        show_status
        ;;
    "install")
        install_cron
        ;;
    "remove")
        remove_cron
        ;;
    "pause")
        pause_updates
        ;;
    "resume")
        resume_updates
        ;;
    "run")
        run_update
        ;;
    "clear-logs")
        clear_logs
        ;;
    "help"|"-h"|"--help")
        echo "Использование: $0 [команда]"
        echo ""
        echo "Команды:"
        echo "  status      - Показать статус автоматического обновления"
        echo "  install     - Установить cron задачу (каждые 30 минут)"
        echo "  remove      - Удалить cron задачу"
        echo "  pause       - Приостановить автоматическое обновление"
        echo "  resume      - Возобновить автоматическое обновление"
        echo "  run         - Запустить обновление вручную"
        echo "  clear-logs  - Очистить логи"
        echo "  help        - Показать эту справку"
        echo ""
        echo "Примеры:"
        echo "  sudo $0 install    # Установить автоматическое обновление"
        echo "  sudo $0 pause      # Приостановить обновления"
        echo "  sudo $0 status     # Проверить статус"
        ;;
    *)
        echo "❌ Неизвестная команда: $1"
        echo "💡 Используйте: $0 help"
        exit 1
        ;;
esac
