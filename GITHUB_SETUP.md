# Публикация на GitHub

## 🚀 Пошаговая инструкция

### 1. Создайте репозиторий на GitHub

1. Перейдите на [github.com](https://github.com)
2. Нажмите кнопку **"New repository"** (зеленая кнопка)
3. Заполните форму:
   - **Repository name**: `linux-server-manager`
   - **Description**: `Web application for Linux server management with SSH tunnels, port forwarding, WireGuard and Cloudflare`
   - **Visibility**: Public (или Private)
   - **Initialize with**: НЕ ставьте галочки (у нас уже есть файлы)
4. Нажмите **"Create repository"**

### 2. Инициализируйте Git в вашем проекте

```bash
# Перейдите в папку проекта
cd /path/to/linux-server-manager

# Инициализируйте Git
git init

# Добавьте все файлы
git add .

# Создайте первый коммит
git commit -m "Initial commit: Linux Server Manager"

# Добавьте удаленный репозиторий (замените YOUR_USERNAME на ваше имя пользователя)
git remote add origin https://github.com/YOUR_USERNAME/linux-server-manager.git

# Отправьте код на GitHub
git branch -M main
git push -u origin main
```

### 3. Настройте репозиторий

#### Добавьте описание в README
Обновите `README.md`, заменив `your-username` на ваше реальное имя пользователя GitHub.

#### Настройте теги релизов
```bash
# Создайте тег для первого релиза
git tag -a v1.0.0 -m "First release"
git push origin v1.0.0
```

### 4. Настройте GitHub Pages (опционально)

Если хотите создать сайт-демонстрацию:

1. Перейдите в **Settings** → **Pages**
2. В **Source** выберите **Deploy from a branch**
3. Выберите ветку **main** и папку **/docs**
4. Нажмите **Save**

### 5. Настройте Actions (опционально)

Создайте файл `.github/workflows/deploy.yml` для автоматического развертывания:

```yaml
name: Deploy to Server

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v2
    
    - name: Deploy to server
      uses: appleboy/ssh-action@v0.1.4
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

### 6. Обновите скрипты развертывания

Обновите URL репозитория в файлах:
- `deploy.sh` (строка 3)
- `quick-deploy.sh` (строка 3)

Замените:
```bash
REPO_URL="https://github.com/your-username/linux-server-manager.git"
```

На:
```bash
REPO_URL="https://github.com/YOUR_USERNAME/linux-server-manager.git"
```

### 7. Добавьте файлы для GitHub

#### Создайте CONTRIBUTING.md
```markdown
# Contributing to Linux Server Manager

## How to contribute

1. Fork the repository
2. Create a feature branch: `git checkout -b feature-name`
3. Make your changes
4. Commit your changes: `git commit -m 'Add feature'`
5. Push to the branch: `git push origin feature-name`
6. Submit a pull request

## Code style

- Follow PSR-4 autoloading standard
- Use meaningful variable and function names
- Add comments for complex logic
- Test your changes before submitting
```

#### Создайте ISSUE_TEMPLATE.md
```markdown
## Bug Report

**Describe the bug**
A clear description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '....'
3. See error

**Expected behavior**
A clear description of what you expected to happen.

**Environment:**
- OS: [e.g. Ubuntu 20.04]
- PHP Version: [e.g. 8.1]
- Browser: [e.g. Chrome, Firefox]

**Additional context**
Add any other context about the problem here.
```

### 8. Настройте секреты (для Actions)

Если используете GitHub Actions, добавьте секреты в **Settings** → **Secrets**:

- `HOST` - IP адрес вашего сервера
- `USERNAME` - имя пользователя на сервере
- `KEY` - приватный SSH ключ

### 9. Создайте релиз

1. Перейдите в **Releases**
2. Нажмите **"Create a new release"**
3. Заполните:
   - **Tag version**: `v1.0.0`
   - **Release title**: `Linux Server Manager v1.0.0`
   - **Description**: Описание функций и изменений
4. Нажмите **"Publish release"**

## 📋 Чек-лист перед публикацией

- [ ] Все файлы добавлены в `.gitignore`
- [ ] Нет конфиденциальных данных в коде
- [ ] README.md обновлен с правильными ссылками
- [ ] Скрипты развертывания обновлены
- [ ] Код протестирован
- [ ] Создан первый коммит
- [ ] Репозиторий создан на GitHub
- [ ] Код отправлен на GitHub

## 🎯 После публикации

1. **Поделитесь ссылкой** на репозиторий
2. **Добавьте описание** в профиль GitHub
3. **Создайте Issues** для планирования новых функций
4. **Настройте уведомления** для новых Issues и Pull Requests

## 🔗 Полезные ссылки

- [GitHub Guides](https://guides.github.com/)
- [GitHub Pages](https://pages.github.com/)
- [GitHub Actions](https://github.com/features/actions)
- [GitHub Security](https://github.com/security)
