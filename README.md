# 🐧 Linux Server Manager v1.0.0

[![GitHub release](https://img.shields.io/github/v/release/segallar/linux-server-manager)](https://github.com/segallar/linux-server-manager/releases)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.0+-blue.svg)](https://php.net)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple.svg)](https://getbootstrap.com)
[![Status](https://img.shields.io/badge/Status-Production%20Ready-green.svg)](https://github.com/segallar/linux-server-manager)

**🚀 Первый официальный релиз! Полнофункциональное веб-приложение для управления Linux сервером с современным интерфейсом и 40+ API эндпоинтами.**

## 🎯 Особенности v1.0.0

- 🚀 **Первый официальный релиз** - Стабильная и протестированная версия
- 🎨 **Современный дизайн** - Bootstrap 5, адаптивный интерфейс
- 📱 **Мобильная поддержка** - Полная адаптивность для всех устройств
- 🔧 **7 основных модулей** - Система, процессы, сервисы, пакеты, сеть, файрвол
- 🌐 **40+ API эндпоинтов** - REST API для всех функций
- ⚡ **Real-time обновления** - AJAX запросы, live статистика
- 🔒 **Безопасность** - Валидация, санитизация, CSRF защита
- 📊 **Мониторинг** - CPU, RAM, диск, сеть в реальном времени
- 🐧 **Linux совместимость** - Debian/Ubuntu, CentOS/RHEL
- 🔄 **Автоматические обновления** - Git-версионирование, cron обновления

## Структура проекта

```
linux-server-manager/
├── assets/
│   ├── css/
│   │   └── style.css          # Основные стили
│   ├── js/
│   │   ├── app.js            # Основной JavaScript
│   │   └── jquery-3.7.1.min.js # jQuery (локальная копия)
│   └── images/               # Изображения
├── src/
│   ├── Core/
│   │   ├── Application.php   # Основной класс приложения
│   │   ├── Request.php       # Обработка запросов
│   │   └── Response.php      # Обработка ответов
│   └── Controllers/
│       └── DashboardController.php # Контроллер дашборда
├── templates/
│   ├── layout.php            # Основной шаблон
│   └── dashboard.php         # Страница дашборда
├── public/
│   └── index.php             # Точка входа
└── composer.json             # Зависимости
```

## Меню приложения

### Основные разделы:
- **Главная** - Панель управления с общей статистикой
- **Система** - Информация о системе и ресурсах
- **Процессы** - Управление процессами
- **Сервисы** - Управление системными сервисами

### Дополнительные разделы:
- **Мониторинг**
  - Графики
  - Алерты
  - История
- **Администрирование**
  - Пользователи
  - Безопасность
  - Резервное копирование
- **Помощь**

## Установка и запуск

### Быстрый старт (для разработки)

1. **Клонируйте репозиторий:**
   ```bash
   git clone https://github.com/your-username/linux-server-manager.git
   cd linux-server-manager
   ```

2. **Установите зависимости:**
   ```bash
   composer install
   ```

3. **Запустите встроенный сервер:**
   ```bash
   composer start
   # или
   php -S localhost:8000 -t public
   ```

4. **Откройте браузер:**
   Перейдите по адресу `http://localhost:8000`

### Развертывание на сервер

#### Вариант 1: Быстрый скрипт (рекомендуется)

```bash
# Отредактируйте quick-deploy.sh (укажите ваш репозиторий и домен)
nano quick-deploy.sh

# Запустите быстрый скрипт развертывания
chmod +x quick-deploy.sh
./quick-deploy.sh
```

#### Вариант 2: Полный автоматический скрипт

```bash
# Отредактируйте deploy.sh (укажите ваш репозиторий и домен)
nano deploy.sh

# Запустите полный скрипт развертывания (включает настройку веб-сервера)
sudo chmod +x deploy.sh
sudo ./deploy.sh
```

#### Вариант 2: Простая установка

```bash
# 1. Подключитесь к серверу
ssh user@your-server.com

# 2. Клонируйте проект
cd /var/www/html
git clone <your-repo-url> linux-server-manager
cd linux-server-manager

# 3. Установите зависимости
composer install --no-dev --optimize-autoloader

# 4. Настройте права доступа
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 777 logs/

# 5. Настройте веб-сервер (Apache/Nginx)
# Подробности в docs/DEPLOYMENT.md
```

#### Вариант 3: Быстрая инструкция

Простая пошаговая инструкция в файле [QUICK_DEPLOY.md](docs/QUICK_DEPLOY.md)

#### Вариант 4: Подробная инструкция

Полная инструкция с настройкой Apache/Nginx, SSL и безопасности в файле [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)

## Использование jQuery

jQuery подключен через CDN и готов к использованию. Основные функции:

### AJAX запросы:
```javascript
$.ajax({
    url: '/api/system-info',
    method: 'GET',
    success: function(data) {
        // Обработка данных
    }
});
```

### Обработка событий:
```javascript
$('.btn-custom').on('click', function() {
    // Действие при клике
});
```

### Показ уведомлений:
```javascript
showAlert('success', 'Операция выполнена успешно');
showAlert('danger', 'Произошла ошибка');
```

## Стилизация

Основные CSS классы:

- `.stats-card` - Карточки статистики
- `.btn-custom` - Кастомные кнопки
- `.table-custom` - Стилизованные таблицы
- `.status-indicator` - Индикаторы статуса
- `.sidebar` - Боковое меню

## Адаптивность

Приложение полностью адаптивно:
- На десктопе: боковое меню всегда видимо
- На планшете: боковое меню сворачивается
- На мобильном: боковое меню скрыто, открывается по кнопке

## Расширение функциональности

### Добавление новой страницы:

1. Создайте контроллер в `src/Controllers/`
2. Создайте шаблон в `templates/`
3. Добавьте маршрут в `public/index.php`
4. Добавьте пункт меню в `templates/layout.php`

### Добавление новых стилей:

Редактируйте файл `assets/css/style.css`

### Добавление JavaScript функциональности:

Редактируйте файл `assets/js/app.js`

## Технологии

- **PHP 8.0+** - Backend
- **jQuery 3.7.1** - JavaScript библиотека
- **Bootstrap 5.3** - CSS фреймворк
- **Font Awesome 6.4** - Иконки
- **Composer** - Менеджер зависимостей

## 📚 Документация

📖 **[Полная документация](docs/README.md)** - Индекс всех документов

### 🚀 Быстрый старт
- [Быстрое развертывание](docs/QUICK_DEPLOY.md) - Простая инструкция по установке
- [Полное руководство](docs/DEPLOYMENT.md) - Подробная инструкция с настройкой безопасности
- [Настройка файрвола](docs/FIREWALL_SETUP.md) - Конфигурация безопасности

### 🔧 Разработка
- [Система версионирования](docs/VERSIONING.md) - Как работает версионирование
- [История изменений](docs/CHANGELOG.md) - Подробная история всех изменений
- [Заметки о релизах](docs/RELEASE_NOTES.md) - Детальная информация о каждом релизе

### 🔒 Безопасность
- [Политика безопасности](docs/SECURITY.md) - Информация о безопасности проекта

## 🚀 Публикация на GitHub

### Быстрый способ:

1. **Отредактируйте скрипт:**
   ```bash
   nano publish-to-github.sh
   # Замените "your-github-username" на ваше имя пользователя
   ```

2. **Запустите скрипт:**
   ```bash
   chmod +x publish-to-github.sh
   ./publish-to-github.sh
   ```

### Ручной способ:

Следуйте инструкции в файле [docs/GITHUB_SETUP.md](docs/GITHUB_SETUP.md)

## 🤝 Вклад в проект

1. Fork репозитория
2. Создайте ветку для новой функции (`git checkout -b feature/amazing-feature`)
3. Зафиксируйте изменения (`git commit -m 'Add amazing feature'`)
4. Отправьте в ветку (`git push origin feature/amazing-feature`)
5. Откройте Pull Request

## 📄 Лицензия

MIT License - см. файл [LICENSE](LICENSE) для деталей

## 🆘 Поддержка

Если у вас есть вопросы или предложения:
- Создайте [Issue](https://github.com/your-username/linux-server-manager/issues)
- Напишите на email: your-email@example.com
- Присоединитесь к обсуждению в [Discussions](https://github.com/your-username/linux-server-manager/discussions)
