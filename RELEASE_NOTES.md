# 🚀 Release Notes - Linux Server Manager

## 🎉 Что нового в версии 1.14.0 (Текущий)

### 🌐 Этап 2: Реализация сетевых функций

#### 🔐 SSH туннели API
- **GET /api/ssh/tunnels** - получение списка SSH туннелей
- **POST /api/ssh/tunnel/create** - создание SSH туннеля
- **POST /api/ssh/tunnel/{id}/start** - запуск SSH туннеля
- **POST /api/ssh/tunnel/{id}/stop** - остановка SSH туннеля
- **DELETE /api/ssh/tunnel/{id}** - удаление SSH туннеля

#### ☁️ Cloudflare туннели API
- **GET /api/cloudflare/tunnels** - получение списка Cloudflare туннелей
- **POST /api/cloudflare/tunnel/create** - создание Cloudflare туннеля
- **POST /api/cloudflare/tunnel/{id}/start** - запуск Cloudflare туннеля
- **POST /api/cloudflare/tunnel/{id}/stop** - остановка Cloudflare туннеля
- **DELETE /api/cloudflare/tunnel/{id}** - удаление Cloudflare туннеля

#### 🔌 Проброс портов API
- **GET /api/port-forwarding/rules** - получение правил проброса портов
- **POST /api/port-forwarding/rule/add** - добавление правила проброса портов
- **DELETE /api/port-forwarding/rule/{id}** - удаление правила проброса портов

#### 🛠️ Технические улучшения
- **Поддержка DELETE метода** - добавлен в роутер и Request класс
- **Улучшенная обработка параметров** - поддержка {id} и {name} в URL
- **Конфигурационные файлы** - INI файлы для SSH туннелей и проброса портов
- **iptables интеграция** - автоматическое управление правилами проброса портов
- **Cloudflared интеграция** - полное управление Cloudflare туннелями

#### 📁 Обновленные файлы
- `public/index.php` - добавлены новые API маршруты
- `src/Core/Router.php` - поддержка DELETE метода и параметров в URL
- `src/Core/Request.php` - метод isDelete() и setParam()
- `src/Controllers/NetworkController.php` - API методы для всех сетевых функций
- `src/Services/NetworkService.php` - SSH туннели и проброс портов
- `src/Services/CloudflareService.php` - управление Cloudflare туннелями

### 📊 Статистика изменений
- **Добавлено API эндпоинтов**: 15
- **Новых строк кода**: 1144
- **Новых функций**: 25+
- **Версия**: v1.14.0

### 🎯 Что теперь работает
- ✅ Полное управление SSH туннелями через API
- ✅ Создание и управление Cloudflare туннелями
- ✅ Настройка проброса портов через iptables
- ✅ Конфигурационные файлы для всех сетевых функций
- ✅ DELETE метод в роутере
- ✅ Параметры в URL маршрутах

---

## 🎉 Что нового в версии 1.13.0 (Текущий)

### 🚀 Этап 1: Реализация критических API

#### 🔧 Системные API эндпоинты
- **GET /api/system/info** - получение системной информации
- **GET /api/system/stats** - статистика системы в реальном времени
- **GET /api/system/processes** - список процессов
- **POST /api/system/processes/{id}/kill** - завершение процесса

#### 🌐 WireGuard API эндпоинты
- **GET /api/wireguard/interfaces** - список интерфейсов WireGuard
- **GET /api/wireguard/interface/{name}** - информация об интерфейсе
- **POST /api/wireguard/interface/{name}/up** - запуск интерфейса
- **POST /api/wireguard/interface/{name}/down** - остановка интерфейса
- **POST /api/wireguard/interface/{name}/restart** - перезапуск интерфейса
- **GET /api/wireguard/interface/{name}/config** - получение конфигурации
- **POST /api/wireguard/interface/{name}/config** - обновление конфигурации

#### 🛠️ Технические улучшения
- **Улучшенный роутер** - поддержка параметров в URL типа {name} и {id}
- **Обработка ошибок** - улучшенная обработка ошибок для всех API
- **JavaScript функции** - включены отключенные функции для WireGuard и системной информации
- **Валидация параметров** - проверка обязательных параметров в API

#### 📁 Обновленные файлы
- `public/index.php` - добавлены новые API маршруты
- `src/Controllers/SystemController.php` - API методы для системной информации
- `src/Controllers/ProcessController.php` - API методы для управления процессами
- `src/Controllers/NetworkController.php` - API методы для WireGuard
- `src/Core/Router.php` - поддержка параметров в URL
- `src/Core/Request.php` - метод setParam для параметров
- `public/assets/js/app.js` - включены отключенные функции

### 📊 Статистика изменений
- **Добавлено API эндпоинтов**: 12
- **Новых строк кода**: 465
- **Улучшенных функций**: 8
- **Версия**: v1.13.0

### 🎯 Что теперь работает
- ✅ Системная информация через API
- ✅ Статистика в реальном времени
- ✅ Управление процессами через API
- ✅ Полное управление WireGuard интерфейсами
- ✅ Параметры в URL маршрутах
- ✅ Обработка ошибок API

---

## 🎉 Что нового в версии 1.12.2 (Текущий)

### 🧹 Очистка и оптимизация

#### 🗑️ Удаление отладочных файлов
- **Очистка проекта** - удалены все отладочные и тестовые файлы
- **Удаленные файлы**: 16 отладочных скриптов и тестовых PHP файлов
- **Результат**: Чистый код, готовый к продакшену

#### 📁 Удаленные отладочные файлы:
- `analyze-slow-pages.sh` - анализ медленных страниц
- `check-update.sh` - проверка обновлений
- `cleanup-debug.sh` - скрипт очистки (самоудален)
- `diagnose.sh` - диагностика проблем
- `fix-application.sh` - исправление приложения
- `fix-blank-screen.sh`