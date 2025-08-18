# 🌐 Публикация Linux Server Manager на GitHub

## 📋 Краткое описание
Пошаговая инструкция по публикации Linux Server Manager на GitHub, включая настройку репозитория, тегов и автоматизации.

## 🎯 Цель документа
Для разработчиков, которые хотят опубликовать Linux Server Manager на GitHub и настроить все необходимые компоненты для распространения проекта.

---

## 📋 Содержание
- [Создание репозитория](#создание-репозитория)
- [Инициализация Git](#инициализация-git)
- [Настройка репозитория](#настройка-репозитория)
- [GitHub Pages](#github-pages)
- [GitHub Actions](#github-actions)
- [Обновление скриптов](#обновление-скриптов)

---

## 📝 Основное содержание

### Создание репозитория

#### 🚀 Пошаговая инструкция

**Шаг 1: Создание репозитория на GitHub**

1. Перейдите на [github.com](https://github.com)
2. Нажмите кнопку **"New repository"** (зеленая кнопка)
3. Заполните форму:
   - **Repository name**: `linux-server-manager`
   - **Description**: `Web application for Linux server management with SSH tunnels, port forwarding, WireGuard and Cloudflare`
   - **Visibility**: Public (или Private)
   - **Initialize with**: НЕ ставьте галочки (у нас уже есть файлы)
4. Нажмите **"Create repository"**

**Шаг 2: Настройка репозитория**

После создания репозитория GitHub покажет инструкции. Скопируйте URL вашего репозитория для дальнейшего использования.

---

### Инициализация Git

#### 🔧 Настройка локального репозитория

```bash
# Перейдите в папку проекта
cd /path/to/linux-server-manager

# Инициализируйте Git (если еще не инициализирован)
git init

# Добавьте все файлы
git add .

# Создайте первый коммит
git commit -m "Initial commit: Linux Server Manager v1.0.0"

# Добавьте удаленный репозиторий (замените YOUR_USERNAME на ваше имя пользователя)
git remote add origin https://github.com/YOUR_USERNAME/linux-server-manager.git

# Отправьте код на GitHub
git branch -M main
git push -u origin main
```

#### ✅ Проверка настройки

```bash
# Проверьте удаленный репозиторий
git remote -v

# Проверьте статус
git status

# Проверьте ветки
git branch -a
```

---

### Настройка репозитория

#### 📝 Обновление README

Обновите `README.md`, заменив `your-username` на ваше реальное имя пользователя GitHub:

```markdown
# Linux Server Manager v1.0.0

[![Status](https://img.shields.io/badge/status-ready-green.svg)](https://github.com/YOUR_USERNAME/linux-server-manager)

...

## 🚀 Быстрый старт

```bash
git clone https://github.com/YOUR_USERNAME/linux-server-manager.git
cd linux-server-manager
composer install
php -S localhost:8000 -t public
```
```

#### 🏷️ Настройка тегов релизов

```bash
# Создайте тег для первого релиза
git tag -a v1.0.0 -m "First release: Linux Server Manager v1.0.0"

# Отправьте тег на GitHub
git push origin v1.0.0

# Проверьте теги
git tag --list
```

#### 📋 Настройка описания репозитория

В настройках репозитория GitHub:

1. Перейдите в **Settings** → **General**
2. Обновите **Description**:
   ```
   Web application for Linux server management with SSH tunnels, port forwarding, WireGuard and Cloudflare
   ```
3. Добавьте **Website** (если настроите GitHub Pages):
   ```
   https://YOUR_USERNAME.github.io/linux-server-manager
   ```
4. Добавьте **Topics**:
   ```
   linux, server-management, ssh, wireguard, cloudflare, php, web-application
   ```

---

### GitHub Pages

#### 🌐 Настройка сайта-демонстрации

Если хотите создать сайт-демонстрацию:

**Шаг 1: Настройка Pages**
1. Перейдите в **Settings** → **Pages**
2. В **Source** выберите **Deploy from a branch**
3. Выберите ветку **main** и папку **/docs**
4. Нажмите **Save**

**Шаг 2: Создание index.html**
Создайте файл `docs/index.html` для демонстрации:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Linux Server Manager - Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Linux Server Manager</h1>
        <p>Web application for Linux server management</p>
        <a href="https://github.com/YOUR_USERNAME/linux-server-manager" class="btn btn-primary">View on GitHub</a>
    </div>
</body>
</html>
```

**Шаг 3: Проверка**
Через несколько минут сайт будет доступен по адресу:
```
https://YOUR_USERNAME.github.io/linux-server-manager
```

---

### GitHub Actions

#### 🤖 Автоматическое развертывание

Создайте файл `.github/workflows/deploy.yml` для автоматического развертывания:

```yaml
name: Deploy to Server

on:
  push:
    branches: [ main ]
  release:
    types: [ published ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v3
    
    - name: Deploy to server
      uses: appleboy/ssh-action@v1.0.0
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.KEY }}
        script: |
          cd /var/www/html/linux-server-manager
          git pull origin main
          composer install --no-dev --optimize-autoloader
          sudo chown -R www-data:www-data .
          sudo systemctl reload nginx
```

#### 🔐 Настройка секретов

В настройках репозитория **Settings** → **Secrets and variables** → **Actions**:

1. **HOST** - IP адрес вашего сервера
2. **USERNAME** - имя пользователя на сервере
3. **KEY** - приватный SSH ключ для доступа к серверу

#### 📊 Дополнительные Actions

**Проверка кода:**
```yaml
name: Code Quality

on: [push, pull_request]

jobs:
  phpstan:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v3
    - uses: shivammathur/setup-php@v2
      with:
        php-version: '8.0'
    - run: composer install
    - run: vendor/bin/phpstan analyse src/
```

---

### Обновление скриптов

#### 🔧 Обновление URL репозитория

Обновите URL репозитория в файлах:

**В файле `deploy.sh` (строка 3):**
```bash
# Замените
REPO_URL="https://github.com/your-username/linux-server-manager.git"

# На
REPO_URL="https://github.com/YOUR_USERNAME/linux-server-manager.git"
```

**В файле `quick-deploy.sh` (строка 3):**
```bash
# Замените
REPO_URL="https://github.com/your-username/linux-server-manager.git"

# На
REPO_URL="https://github.com/YOUR_USERNAME/linux-server-manager.git"
```

#### 📝 Обновление документации

Обновите все ссылки в документации:

```bash
# Замените все вхождения
find docs/ -name "*.md" -exec sed -i 's/your-username/YOUR_USERNAME/g' {} \;
find . -name "*.md" -exec sed -i 's/your-username/YOUR_USERNAME/g' {} \;
```

---

## 📊 Статистика настройки

### ✅ Чек-лист готовности
- [x] **Репозиторий создан** - на GitHub
- [x] **Код загружен** - все файлы отправлены
- [x] **README обновлен** - с правильными ссылками
- [x] **Теги созданы** - v1.0.0 готов
- [x] **GitHub Pages** - настроен (опционально)
- [x] **GitHub Actions** - настроен (опционально)
- [x] **Скрипты обновлены** - с правильными URL

### 🎯 Следующие шаги
1. **Создать GitHub Release** - для v1.0.0
2. **Настроить Issues** - для обратной связи
3. **Настроить Discussions** - для обсуждений
4. **Добавить в каталоги** - Awesome Lists, etc.

---

## 🔗 Связанные документы
- **[Основная документация](README.md)** - Индекс всех документов
- **[Следующие шаги](GITHUB_NEXT_STEPS.md)** - Что делать после публикации
- **[Чек-лист готовности](RELEASE_CHECKLIST.md)** - Проверка готовности к релизу
- **[Статус проекта](PROJECT_STATUS.md)** - Что реализовано и что планируется

## 📞 Поддержка
См. [основную документацию](README.md#📞-поддержка).

---

**📅 Последнее обновление**: 2025-01-16  
**🏷️ Версия документа**: 1.0.0  
**📝 Автор**: Команда Linux Server Manager
