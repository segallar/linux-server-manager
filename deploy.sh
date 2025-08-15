#!/bin/bash

# Конфигурация
REPO_URL="https://github.com/your-username/linux-server-manager.git"
DEPLOY_PATH="/var/www/html/linux-server-manager"
BACKUP_PATH="/var/backups/linux-server-manager"
DOMAIN="your-domain.com"

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 Начинаем развертывание Linux Server Manager...${NC}"

# Проверяем, что мы root или используем sudo
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}❌ Этот скрипт должен выполняться с правами sudo${NC}"
    exit 1
fi

# Создаем резервную копию
if [ -d "$DEPLOY_PATH" ]; then
    echo -e "${YELLOW}📦 Создаем резервную копию...${NC}"
    mkdir -p $BACKUP_PATH
    cp -r $DEPLOY_PATH $BACKUP_PATH/backup-$(date +%Y%m%d-%H%M%S)
    echo -e "${GREEN}✅ Резервная копия создана${NC}"
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

# Проверяем, что git команда выполнилась успешно
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Ошибка при работе с git${NC}"
    exit 1
fi

# Устанавливаем зависимости
echo -e "${YELLOW}📦 Устанавливаем зависимости...${NC}"
composer install --no-dev --optimize-autoloader

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Ошибка при установке зависимостей${NC}"
    exit 1
fi

# Создаем папку для логов если её нет
mkdir -p $DEPLOY_PATH/logs

# Настраиваем права доступа
echo -e "${YELLOW}🔐 Настраиваем права доступа...${NC}"
chown -R www-data:www-data $DEPLOY_PATH
chmod -R 755 $DEPLOY_PATH
chmod -R 777 $DEPLOY_PATH/logs

# Создаем .env файл если его нет
if [ ! -f "$DEPLOY_PATH/.env" ]; then
    echo -e "${YELLOW}⚙️ Создаем файл .env...${NC}"
    cat > $DEPLOY_PATH/.env << EOF
APP_ENV=production
APP_DEBUG=false
APP_URL=http://$DOMAIN
APP_KEY=$(openssl rand -base64 32)
EOF
    chown www-data:www-data $DEPLOY_PATH/.env
fi

# Настраиваем Nginx (если установлен)
if command -v nginx &> /dev/null; then
    echo -e "${YELLOW}🌐 Настраиваем Nginx...${NC}"
    
    # Создаем конфигурацию Nginx
    cat > /etc/nginx/sites-available/linux-server-manager << EOF
server {
    listen 80;
    server_name $DOMAIN;
    root $DEPLOY_PATH/public;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

    # Включаем сайт
    ln -sf /etc/nginx/sites-available/linux-server-manager /etc/nginx/sites-enabled/
    nginx -t && systemctl reload nginx
    
    echo -e "${GREEN}✅ Nginx настроен${NC}"
fi

# Настраиваем Nginx (если установлен)
if command -v nginx &> /dev/null; then
    echo -e "${YELLOW}🌐 Настраиваем Nginx...${NC}"
    
    # Создаем конфигурацию Nginx
    cat > /etc/nginx/sites-available/linux-server-manager << EOF
server {
    listen 80;
    server_name $DOMAIN;
    root $DEPLOY_PATH/public;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

    # Включаем сайт
    ln -sf /etc/nginx/sites-available/linux-server-manager /etc/nginx/sites-enabled/
    nginx -t && systemctl reload nginx
    
    echo -e "${GREEN}✅ Nginx настроен${NC}"
fi

# Настраиваем SSL с Let's Encrypt (если установлен certbot)
if command -v certbot &> /dev/null; then
    echo -e "${YELLOW}🔒 Настраиваем SSL...${NC}"
    if [ -f "/etc/nginx/sites-available/linux-server-manager" ]; then
        certbot --nginx -d $DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN
    fi
    echo -e "${GREEN}✅ SSL настроен${NC}"
fi

# Настраиваем файрвол
if command -v ufw &> /dev/null; then
    echo -e "${YELLOW}🔥 Настраиваем файрвол...${NC}"
    ufw allow ssh
    ufw allow 'Nginx Full'
    ufw --force enable
    echo -e "${GREEN}✅ Файрвол настроен${NC}"
fi

# Создаем скрипт для автоматических обновлений
cat > /usr/local/bin/update-linux-server-manager.sh << 'EOF'
#!/bin/bash
cd /var/www/html/linux-server-manager
git pull origin main
composer install --no-dev --optimize-autoloader
chown -R www-data:www-data /var/www/html/linux-server-manager
systemctl reload nginx
EOF

chmod +x /usr/local/bin/update-linux-server-manager.sh

echo -e "${GREEN}✅ Развертывание завершено!${NC}"
echo -e "${BLUE}🌐 Приложение доступно по адресу: http://$DOMAIN${NC}"
echo -e "${BLUE}📝 Для обновления используйте: /usr/local/bin/update-linux-server-manager.sh${NC}"
echo -e "${BLUE}📊 Логи доступны в: /var/log/nginx/${NC}"
