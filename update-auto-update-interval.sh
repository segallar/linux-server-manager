#!/bin/bash

# Скрипт для обновления интервала автоматического обновления на сервере

echo "⚙️ Обновление интервала автоматического обновления"
echo "================================================="

WEB_ROOT="/var/www/html/linux-server-manager"
CRON_FILE="/etc/cron.d/linux-server-manager-auto-update"

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

# Проверяем директорию приложения
if [ ! -d "$WEB_ROOT" ]; then
    echo "❌ Директория $WEB_ROOT не найдена"
    exit 1
fi

cd "$WEB_ROOT"

echo "📁 Текущая директория: $(pwd)"

# Проверяем текущий статус cron задачи
echo "📊 Текущий статус cron задачи:"
if [ -f "$CRON_FILE" ]; then
    echo "✅ Cron файл найден: $CRON_FILE"
    echo "📋 Текущее расписание:"
    cat "$CRON_FILE" | grep -v "^#" | grep -v "^$" || echo "Расписание не найдено"
else
    echo "⚠️ Cron файл не найден"
fi

echo ""
echo "🔄 Обновляем интервал на каждую минуту..."

# Создаем новый cron файл с интервалом в 1 минуту
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
echo "🔄 Перезагружаем cron демон..."
if systemctl reload cron 2>/dev/null; then
    echo "✅ Cron демон перезагружен (systemctl)"
elif systemctl reload crond 2>/dev/null; then
    echo "✅ Cron демон перезагружен (crond)"
else
    echo "⚠️ Не удалось перезагрузить cron демон автоматически"
    echo "💡 Выполните вручную: systemctl reload cron"
fi

echo ""
echo "✅ Интервал автоматического обновления обновлен!"
echo "📋 Новое расписание: каждую минуту"
echo ""
echo "📊 Проверьте статус:"
echo "   sudo ./manage-auto-update.sh status"
echo ""
echo "⏸️ Для приостановки:"
echo "   sudo ./manage-auto-update.sh pause"
echo ""
echo "🔄 Для возобновления:"
echo "   sudo ./manage-auto-update.sh resume"
