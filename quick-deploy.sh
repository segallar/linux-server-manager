#!/bin/bash

# Конфигурация
REPO_URL="https://github.com/your-username/linux-server-manager.git"
DEPLOY_PATH="/var/www/html/linux-server-manager"
DOMAIN="your-domain.com"

# Цвета для вывода
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🚀 Быстрое развертывание Linux Server Manager...${NC}"

# Проверяем, что мы root или используем sudo
if [ "$EUID" -ne 0 ]; then
    echo -e "${YELLOW}⚠️ Рекомендуется запускать с sudo для настройки веб-сервера${NC}"
fi

# Проверяем наличие необходимых команд
if ! command -v git &> /dev/null; then
    echo "❌ Git не установлен. Установите: sudo apt install git"
    exit 1
fi

if ! command -v composer &> /dev/null; then
    echo "❌ Composer не установлен. Установите: sudo apt install composer"
    exit 1
fi

# Клонируем/обновляем код
if [ -d "$DEPLOY_PATH" ]; then
    echo -e "${YELLOW}🔄 Обновляем код...${NC}"
    cd $DEPLOY_PATH
    git pull origin main
else
    echo -e "${YELLOW}📥 Клонируем репозиторий...${NC}"
    cd /var/www/html
    git clone $REPO_URL linux-server-manager
    cd $DEPLOY_PATH
fi

# Устанавливаем зависимости
echo -e "${YELLOW}📦 Устанавливаем зависимости...${NC}"
composer install --no-dev --optimize-autoloader

# Создаем папку для логов
mkdir -p $DEPLOY_PATH/logs

# Настраиваем права доступа
echo -e "${YELLOW}🔐 Настраиваем права доступа...${NC}"
if [ "$EUID" -eq 0 ]; then
    chown -R www-data:www-data $DEPLOY_PATH
    chmod -R 755 $DEPLOY_PATH
    chmod -R 777 $DEPLOY_PATH/logs
else
    echo -e "${YELLOW}⚠️ Запустите с sudo для настройки прав доступа${NC}"
fi

# Создаем .env файл если его нет
if [ ! -f "$DEPLOY_PATH/.env" ]; then
    echo -e "${YELLOW}⚙️ Создаем файл .env...${NC}"
    cat > $DEPLOY_PATH/.env << EOF
APP_ENV=production
APP_DEBUG=false
APP_URL=http://$DOMAIN
APP_KEY=$(openssl rand -base64 32)
EOF
    if [ "$EUID" -eq 0 ]; then
        chown www-data:www-data $DEPLOY_PATH/.env
    fi
fi

echo -e "${GREEN}✅ Развертывание завершено!${NC}"
echo -e "${BLUE}📝 Следующие шаги:${NC}"
echo -e "${BLUE}1. Настройте Nginx (см. QUICK_DEPLOY.md)${NC}"
echo -e "${BLUE}2. Укажите root: $DEPLOY_PATH/public${NC}"
echo -e "${BLUE}3. Настройте домен: $DOMAIN${NC}"
echo -e "${BLUE}4. Для обновления: cd $DEPLOY_PATH && git pull && composer install${NC}"
