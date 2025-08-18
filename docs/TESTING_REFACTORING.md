# 🧪 Тестирование рефакторинга на сервере

## 📋 Обзор

Этот документ описывает процесс тестирования Linux Server Manager после рефакторинга на сервере.

---

## 🎯 Цель тестирования

Проверить, что все компоненты после рефакторинга работают корректно на сервере:

- ✅ Загрузка всех классов и интерфейсов
- ✅ Создание экземпляров контроллеров и сервисов
- ✅ Работа роутера с новыми контроллерами
- ✅ Доступность всех маршрутов
- ✅ Функциональность всех API endpoints

---

## 🚀 Быстрое тестирование

### 1. Автоматическое тестирование

```bash
# Запуск полного тестирования
./test-on-server.sh
```

### 2. Ручное тестирование

```bash
# Проверка PHP компонентов
php test-server.php

# Проверка синтаксиса
find src -name "*.php" -exec php -l {} \;

# Проверка автозагрузчика
composer dump-autoload --no-dev
```

---

## 📊 Что тестируется

### 🔧 Основные компоненты

1. **Автозагрузчик** - загрузка всех классов
2. **Основные классы** - Application, Router, Controller, Request, Response
3. **Новые контроллеры** - все специализированные контроллеры
4. **Новые сервисы** - все разделенные сервисы
5. **Интерфейсы** - все созданные интерфейсы
6. **Абстрактные классы** - BaseService
7. **Исключения** - ServiceException, ValidationException

### 🌐 Веб-компоненты

1. **Веб-сервер** - Nginx/Apache
2. **PHP-FPM** - обработка PHP
3. **Права доступа** - директории logs, cache
4. **Конфигурация** - .env файл
5. **Шаблоны** - все PHP шаблоны
6. **Статические файлы** - CSS, JS, изображения

### 📈 Статистика

- **Контроллеры**: 12 файлов
- **Сервисы**: 18 файлов  
- **Интерфейсы**: 18 файлов
- **Исключения**: 2 файла
- **Абстракции**: 1 файл

---

## 🔍 Детальное тестирование

### 1. Тестирование контроллеров

```bash
# Проверка загрузки контроллеров
php -r "
require 'vendor/autoload.php';
echo 'NetworkViewController: ' . (class_exists('App\\Controllers\\Network\\NetworkViewController') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'SSHTunnelApiController: ' . (class_exists('App\\Controllers\\Network\\SSHTunnelApiController') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'PortForwardingApiController: ' . (class_exists('App\\Controllers\\Network\\PortForwardingApiController') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'WireGuardController: ' . (class_exists('App\\Controllers\\Network\\WireGuardController') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'CloudflareController: ' . (class_exists('App\\Controllers\\Network\\CloudflareController') ? 'OK' : 'FAIL') . PHP_EOL;
"
```

### 2. Тестирование сервисов

```bash
# Проверка загрузки сервисов
php -r "
require 'vendor/autoload.php';
echo 'NetworkService: ' . (class_exists('App\\Services\\Network\\NetworkService') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'NetworkRoutingService: ' . (class_exists('App\\Services\\Network\\NetworkRoutingService') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'NetworkMonitoringService: ' . (class_exists('App\\Services\\Network\\NetworkMonitoringService') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'SSHTunnelService: ' . (class_exists('App\\Services\\Network\\SSHTunnelService') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'PortForwardingService: ' . (class_exists('App\\Services\\Network\\PortForwardingService') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'WireGuardService: ' . (class_exists('App\\Services\\WireGuard\\WireGuardService') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'CloudflareService: ' . (class_exists('App\\Services\\Cloudflare\\CloudflareService') ? 'OK' : 'FAIL') . PHP_EOL;
"
```

### 3. Тестирование интерфейсов

```bash
# Проверка загрузки интерфейсов
php -r "
require 'vendor/autoload.php';
echo 'NetworkViewControllerInterface: ' . (interface_exists('App\\Interfaces\\NetworkViewControllerInterface') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'NetworkRoutingServiceInterface: ' . (interface_exists('App\\Interfaces\\NetworkRoutingServiceInterface') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'NetworkMonitoringServiceInterface: ' . (interface_exists('App\\Interfaces\\NetworkMonitoringServiceInterface') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'SSHTunnelServiceInterface: ' . (interface_exists('App\\Interfaces\\SSHTunnelServiceInterface') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'PortForwardingServiceInterface: ' . (interface_exists('App\\Interfaces\\PortForwardingServiceInterface') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'WireGuardServiceInterface: ' . (interface_exists('App\\Interfaces\\WireGuardServiceInterface') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'CloudflareServiceInterface: ' . (interface_exists('App\\Interfaces\\CloudflareServiceInterface') ? 'OK' : 'FAIL') . PHP_EOL;
"
```

---

## 🚨 Устранение проблем

### Проблема: Класс не найден

```bash
# Решение: Обновить автозагрузчик
composer dump-autoload --no-dev

# Проверить namespace
grep -r "namespace" src/Controllers/Network/
```

### Проблема: Ошибка синтаксиса

```bash
# Решение: Проверить синтаксис
php -l src/Controllers/Network/NetworkViewController.php

# Исправить ошибки и повторить
```

### Проблема: Права доступа

```bash
# Решение: Настроить права
chmod -R 755 src/
chmod -R 777 logs/
chmod -R 777 cache/
chown -R www-data:www-data .
```

### Проблема: Зависимости не установлены

```bash
# Решение: Установить зависимости
composer install --no-dev --optimize-autoloader
```

---

## 📝 Логи тестирования

### Локальные логи

```bash
# Просмотр логов тестирования
tail -f logs/test.log

# Очистка логов
rm logs/test.log
```

### Системные логи

```bash
# Логи Nginx
tail -f /var/log/nginx/error.log

# Логи PHP-FPM
tail -f /var/log/php*-fpm.log

# Логи системы
journalctl -u nginx -f
journalctl -u php*-fpm -f
```

---

## 🎯 Критерии успеха

### ✅ Обязательные проверки

- [ ] Все классы загружаются без ошибок
- [ ] Все интерфейсы загружаются без ошибок
- [ ] Создание экземпляров работает
- [ ] Синтаксис всех файлов корректен
- [ ] Автозагрузчик работает
- [ ] Права доступа настроены
- [ ] Зависимости установлены

### ✅ Дополнительные проверки

- [ ] Веб-сервер работает
- [ ] PHP-FPM работает
- [ ] Приложение доступно
- [ ] Все маршруты работают
- [ ] API endpoints отвечают
- [ ] Версия отображается корректно

---

## 🔄 Автоматическое тестирование

### Настройка cron

```bash
# Добавить в crontab для автоматического тестирования
# Каждые 5 минут
*/5 * * * * /var/www/html/linux-server-manager/test-on-server.sh >> /var/www/html/linux-server-manager/logs/test.log 2>&1
```

### Мониторинг

```bash
# Проверка статуса тестирования
tail -f /var/www/html/linux-server-manager/logs/test.log

# Проверка последних результатов
grep "🎉" /var/www/html/linux-server-manager/logs/test.log | tail -5
```

---

## 📞 Поддержка

### Полезные команды

```bash
# Полная диагностика
./test-on-server.sh

# Быстрая проверка
php test-server.php

# Проверка версии
git describe --tags --abbrev=0

# Проверка статуса
git status
```

### Контакты

- **Документация**: [docs/](docs/)
- **Проблемы**: Создать issue в GitHub
- **Обновления**: [auto-update.sh](auto-update.sh)

---

**📅 Последнее обновление**: 2025-01-16  
**🏷️ Версия документа**: 1.0.0  
**📝 Автор**: Команда Linux Server Manager
