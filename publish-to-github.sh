#!/bin/bash

# Конфигурация
GITHUB_USERNAME="segallar"
REPO_NAME="linux-server-manager"
REPO_DESCRIPTION="Web application for Linux server management with SSH tunnels, port forwarding, WireGuard and Cloudflare"

# Цвета для вывода
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}🚀 Публикация Linux Server Manager на GitHub...${NC}"

# Проверяем, что мы в правильной папке
if [ ! -f "composer.json" ]; then
    echo -e "${RED}❌ composer.json не найден. Убедитесь, что вы в папке проекта.${NC}"
    exit 1
fi

# Проверяем наличие Git
if ! command -v git &> /dev/null; then
    echo -e "${RED}❌ Git не установлен. Установите: sudo apt install git${NC}"
    exit 1
fi

# Проверяем, что Git не инициализирован
if [ -d ".git" ]; then
    echo -e "${YELLOW}⚠️ Git уже инициализирован в этой папке${NC}"
    read -p "Продолжить? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Обновляем README с правильным именем пользователя
echo -e "${YELLOW}📝 Обновляем README.md...${NC}"
sed -i "s/your-username/$GITHUB_USERNAME/g" README.md

# Обновляем скрипты развертывания
echo -e "${YELLOW}📝 Обновляем скрипты развертывания...${NC}"
sed -i "s/your-username/$GITHUB_USERNAME/g" deploy.sh
sed -i "s/your-username/$GITHUB_USERNAME/g" quick-deploy.sh

# Инициализируем Git
echo -e "${YELLOW}🔧 Инициализируем Git...${NC}"
git init

# Добавляем все файлы
echo -e "${YELLOW}📦 Добавляем файлы в Git...${NC}"
git add .

# Создаем первый коммит
echo -e "${YELLOW}💾 Создаем первый коммит...${NC}"
git commit -m "Initial commit: Linux Server Manager

Features:
- SSH tunnel management
- Port forwarding
- WireGuard configuration
- Cloudflare tunnel support
- Modern web interface with jQuery
- Responsive design with Bootstrap 5"

# Добавляем удаленный репозиторий
echo -e "${YELLOW}🔗 Добавляем удаленный репозиторий...${NC}"
git remote add origin https://github.com/$GITHUB_USERNAME/$REPO_NAME.git

# Отправляем код на GitHub
echo -e "${YELLOW}📤 Отправляем код на GitHub...${NC}"
git branch -M main
git push -u origin main

# Создаем тег для первого релиза
echo -e "${YELLOW}🏷️ Создаем тег v1.0.0...${NC}"
git tag -a v1.0.0 -m "First release: Linux Server Manager v1.0.0"
git push origin v1.0.0

echo -e "${GREEN}✅ Проект успешно опубликован на GitHub!${NC}"
echo -e "${BLUE}🌐 Ссылка на репозиторий: https://github.com/$GITHUB_USERNAME/$REPO_NAME${NC}"
echo -e "${BLUE}📋 Следующие шаги:${NC}"
echo -e "${BLUE}1. Перейдите на GitHub и настройте описание репозитория${NC}"
echo -e "${BLUE}2. Создайте Issues для планирования новых функций${NC}"
echo -e "${BLUE}3. Настройте GitHub Pages (опционально)${NC}"
echo -e "${BLUE}4. Добавьте секреты для GitHub Actions (если нужно)${NC}"
echo -e "${BLUE}5. Поделитесь ссылкой с сообществом${NC}"

# Создаем файл с инструкциями
cat > GITHUB_NEXT_STEPS.md << EOF
# Следующие шаги после публикации на GitHub

## 🎯 Что нужно сделать:

### 1. Настройте репозиторий
- Перейдите на https://github.com/$GITHUB_USERNAME/$REPO_NAME
- Добавьте описание в профиль репозитория
- Настройте темы и метки

### 2. Создайте Issues
- Bug reports
- Feature requests
- Documentation improvements

### 3. Настройте GitHub Pages (опционально)
- Settings → Pages
- Source: Deploy from a branch
- Branch: main, folder: /docs

### 4. Настройте Actions (опционально)
- Создайте .github/workflows/deploy.yml
- Добавьте секреты в Settings → Secrets

### 5. Создайте релиз
- Releases → Create a new release
- Tag: v1.0.0
- Title: Linux Server Manager v1.0.0

## 🔗 Полезные ссылки:
- Репозиторий: https://github.com/$GITHUB_USERNAME/$REPO_NAME
- Issues: https://github.com/$GITHUB_USERNAME/$REPO_NAME/issues
- Releases: https://github.com/$GITHUB_USERNAME/$REPO_NAME/releases
EOF

echo -e "${GREEN}📄 Создан файл GITHUB_NEXT_STEPS.md с инструкциями${NC}"
