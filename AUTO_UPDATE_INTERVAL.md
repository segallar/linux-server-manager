# ⚙️ Обновление интервала автоматического обновления

## 📋 Описание изменений

Интервал автоматического обновления Linux Server Manager изменен с **30 минут** на **1 минуту**.

### 🔄 Что изменилось

- **Было**: `*/30 * * * *` (каждые 30 минут)
- **Стало**: `* * * * *` (каждую минуту)

### 📁 Обновленные файлы

- `manage-auto-update.sh` - обновлен интервал в функции `install_cron()`
- `auto-update.sh` - обновлен комментарий
- `update-auto-update-interval.sh` - новый скрипт для быстрого обновления

## 🚀 Применение изменений на сервере

### Вариант 1: Автоматическое обновление (рекомендуется)

Если на сервере уже настроено автоматическое обновление, изменения применятся автоматически при следующем обновлении.

### Вариант 2: Ручное применение

#### Шаг 1: Подключитесь к серверу
```bash
ssh user@your-server
```

#### Шаг 2: Перейдите в директорию проекта
```bash
cd /var/www/html/linux-server-manager
```

#### Шаг 3: Запустите скрипт обновления интервала
```bash
sudo ./update-auto-update-interval.sh
```

#### Шаг 4: Проверьте статус
```bash
sudo ./manage-auto-update.sh status
```

### Вариант 3: Полная переустановка cron задачи

#### Шаг 1: Удалите старую cron задачу
```bash
sudo ./manage-auto-update.sh remove
```

#### Шаг 2: Установите новую cron задачу
```bash
sudo ./manage-auto-update.sh install
```

#### Шаг 3: Проверьте статус
```bash
sudo ./manage-auto-update.sh status
```

## 📊 Проверка работы

### Проверка cron задачи
```bash
# Просмотр cron файла
cat /etc/cron.d/linux-server-manager-auto-update

# Проверка активных cron задач
crontab -l

# Просмотр логов cron
tail -f /var/log/cron
```

### Проверка логов обновления
```bash
# Просмотр логов автоматического обновления
tail -f /var/www/html/linux-server-manager/logs/auto-update.log

# Проверка последних обновлений
sudo ./manage-auto-update.sh status
```

## ⚠️ Важные замечания

### 🔄 Частота обновлений
- **Каждую минуту** - очень частые обновления
- Может увеличить нагрузку на сервер
- Рекомендуется мониторить логи

### ⏸️ Приостановка обновлений
Если нужно приостановить автоматические обновления:
```bash
sudo ./manage-auto-update.sh pause
```

### 🔄 Возобновление обновлений
Для возобновления автоматических обновлений:
```bash
sudo ./manage-auto-update.sh resume
```

### 🧹 Очистка логов
Для очистки старых логов:
```bash
sudo ./manage-auto-update.sh clear-logs
```

## 📈 Мониторинг

### Проверка активности обновлений
```bash
# Последние 20 записей в логе
tail -20 /var/www/html/linux-server-manager/logs/auto-update.log

# Поиск ошибок
grep "ERROR\|FAILED" /var/www/html/linux-server-manager/logs/auto-update.log

# Поиск успешных обновлений
grep "Обновление с.*до.*выполнено" /var/www/html/linux-server-manager/logs/auto-update.log
```

### Проверка нагрузки
```bash
# Мониторинг нагрузки cron
watch -n 5 "ps aux | grep cron"

# Проверка использования CPU
top -p $(pgrep cron)
```

## 🎯 Результат

После применения изменений:
- ✅ Автоматическое обновление будет происходить каждую минуту
- ✅ Новые версии будут применяться быстрее
- ✅ Логи будут обновляться чаще
- ✅ Мониторинг будет более актуальным

## 📞 Поддержка

При возникновении проблем:
1. Проверьте логи: `tail -f /var/www/html/linux-server-manager/logs/auto-update.log`
2. Проверьте статус: `sudo ./manage-auto-update.sh status`
3. При необходимости приостановите: `sudo ./manage-auto-update.sh pause`
