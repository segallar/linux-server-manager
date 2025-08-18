# ⏰ Настройка автоматического обновления через Cron

## Обзор

Для автоматического обновления сервера через cron используйте специальный скрипт `update-server-cron.sh`, который работает без интерактивности и правильно обрабатывает версионирование.

## Проблема с версионированием в cron

### ❌ Проблема
При обновлении через cron Git hooks не выполняются, поэтому:
- Patch версии не увеличиваются автоматически
- Версии на сервере не соответствуют локальным версиям
- Система версионирования работает некорректно

### ✅ Решение
Создан специальный скрипт `update-server-cron.sh`, который:
- Работает без интерактивности
- Автоматически создает серверные теги
- Правильно обрабатывает версионирование
- Логирует все операции

## Настройка Cron

### 1. Скопируйте скрипт на сервер
```bash
# На локальной машине
scp update-server-cron.sh user@your-server:/var/www/html/linux-server-manager/

# На сервере
chmod +x /var/www/html/linux-server-manager/update-server-cron.sh
```

### 2. Настройте cron задачу
```bash
# Откройте crontab для редактирования
crontab -e

# Добавьте одну из следующих строк:
```

#### Варианты расписания:

**Ежедневно в 2:00 утра:**
```bash
0 2 * * * /var/www/html/linux-server-manager/update-server-cron.sh
```

**Каждые 6 часов:**
```bash
0 */6 * * * /var/www/html/linux-server-manager/update-server-cron.sh
```

**Каждые 2 часа:**
```bash
0 */2 * * * /var/www/html/linux-server-manager/update-server-cron.sh
```

**Каждый час:**
```bash
0 * * * * /var/www/html/linux-server-manager/update-server-cron.sh
```

**Каждые 30 минут:**
```bash
*/30 * * * * /var/www/html/linux-server-manager/update-server-cron.sh
```

### 3. Проверьте настройки
```bash
# Просмотр текущих cron задач
crontab -l

# Проверка логов cron
tail -f /var/log/cron
```

## Логирование

### Лог файл
Скрипт создает лог файл: `/var/log/linux-server-manager-update.log`

### Просмотр логов
```bash
# Последние записи
tail -f /var/log/linux-server-manager-update.log

# Последние 50 записей
tail -50 /var/log/linux-server-manager-update.log

# Поиск ошибок
grep "❌" /var/log/linux-server-manager-update.log
```

### Пример лога
```
2025-08-18 14:00:01 - 🔄 Начало автоматического обновления Linux Server Manager
2025-08-18 14:00:02 - 📦 Создание резервной копии...
2025-08-18 14:00:03 - ✅ Резервная копия создана: /var/backups/linux-server-manager/backup-20250818-140003.tar.gz
2025-08-18 14:00:04 - 🔍 Проверка статуса Git...
2025-08-18 14:00:05 - 📥 Получение последних изменений с GitHub...
2025-08-18 14:00:06 - 📋 Текущая версия: v1.14.19
2025-08-18 14:00:07 - 🔄 Обновление кода...
2025-08-18 14:00:08 - ✅ Код обновлен
2025-08-18 14:00:09 - 📋 Новая версия: v1.14.20
2025-08-18 14:00:10 - ✅ Версия обновлена: v1.14.19 → v1.14.20
2025-08-18 14:00:11 - 📦 Обновление зависимостей Composer...
2025-08-18 14:00:12 - ✅ Зависимости обновлены
2025-08-18 14:00:13 - 🔐 Установка прав доступа...
2025-08-18 14:00:14 - ✅ Права доступа установлены
2025-08-18 14:00:15 - 🔄 Перезапуск веб-сервера...
2025-08-18 14:00:16 - ✅ Nginx перезапущен
2025-08-18 14:00:17 - ✅ Обновление завершено! Текущая версия: v1.14.20
```

## Конфигурация

### Настройка путей
Отредактируйте скрипт `update-server-cron.sh` под ваши нужды:

```bash
# Конфигурация
PROJECT_PATH="/var/www/html/linux-server-manager"  # Путь к проекту
WEB_SERVER="nginx"  # или "apache2"
BACKUP_DIR="/var/backups/linux-server-manager"     # Папка для резервных копий
LOG_FILE="/var/log/linux-server-manager-update.log" # Лог файл
```

### Права доступа
```bash
# Убедитесь, что скрипт имеет права на выполнение
chmod +x /var/www/html/linux-server-manager/update-server-cron.sh

# Убедитесь, что пользователь cron имеет доступ к проекту
chown -R www-data:www-data /var/www/html/linux-server-manager
```

## Тестирование

### Ручной запуск
```bash
# Запустите скрипт вручную для тестирования
/var/www/html/linux-server-manager/update-server-cron.sh

# Проверьте лог
tail -f /var/log/linux-server-manager-update.log
```

### Проверка версии
```bash
# Проверьте текущую версию на сервере
cd /var/www/html/linux-server-manager
git describe --tags --abbrev=0
```

## Мониторинг

### Проверка статуса cron
```bash
# Статус cron службы
systemctl status cron

# Активные cron задачи
crontab -l

# Логи cron
tail -f /var/log/cron
```

### Проверка обновлений
```bash
# Последние обновления
tail -20 /var/log/linux-server-manager-update.log

# Статистика обновлений
grep "✅ Обновление завершено" /var/log/linux-server-manager-update.log | wc -l
```

## Устранение проблем

### Проблема: Скрипт не выполняется
```bash
# Проверьте права доступа
ls -la /var/www/html/linux-server-manager/update-server-cron.sh

# Проверьте cron логи
tail -f /var/log/cron

# Запустите вручную для диагностики
/var/www/html/linux-server-manager/update-server-cron.sh
```

### Проблема: Git ошибки
```bash
# Проверьте настройки Git на сервере
cd /var/www/html/linux-server-manager
git config --list

# Проверьте доступ к GitHub
git fetch origin --tags
```

### Проблема: Права доступа
```bash
# Установите правильные права
chown -R www-data:www-data /var/www/html/linux-server-manager
chmod -R 755 /var/www/html/linux-server-manager
chmod +x /var/www/html/linux-server-manager/update-server-cron.sh
```

## Рекомендации

### Частота обновлений
- **Разработка**: каждые 30 минут - 1 час
- **Тестирование**: каждые 2-4 часа
- **Продакшен**: ежедневно в нерабочее время

### Мониторинг
- Регулярно проверяйте логи обновлений
- Настройте уведомления об ошибках
- Мониторьте использование диска (резервные копии)

### Безопасность
- Используйте SSH ключи для доступа к GitHub
- Ограничьте права доступа к скрипту
- Регулярно обновляйте резервные копии

## Пример полной настройки

```bash
# 1. Скопируйте скрипт
scp update-server-cron.sh user@server:/var/www/html/linux-server-manager/

# 2. Настройте права
ssh user@server
cd /var/www/html/linux-server-manager
chmod +x update-server-cron.sh
chown www-data:www-data update-server-cron.sh

# 3. Настройте cron
crontab -e
# Добавьте: 0 */2 * * * /var/www/html/linux-server-manager/update-server-cron.sh

# 4. Протестируйте
./update-server-cron.sh
tail -f /var/log/linux-server-manager-update.log
```

Теперь ваш сервер будет автоматически обновляться с правильным версионированием! 🎉
