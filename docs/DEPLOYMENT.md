# Инструкция по развертыванию Linux Server Manager

## 🚀 Быстрое развертывание

### Вариант 1: Простой PHP сервер (для тестирования)

```bash
# 1. Клонируйте репозиторий
git clone <your-repo-url>
cd linux-server-manager

# 2. Установите зависимости
composer install

# 3. Запустите встроенный сервер
composer start
# или
php -S localhost:8000 -t public
```

### Вариант 2: Apache/Nginx (продакшн)

#### Требования к серверу:
- PHP 7.4 или выше
- Composer
- Apache или Nginx
- Git

#### Пошаговая инструкция:

```bash
# 1. Подключитесь к серверу
ssh user@your-server.com

# 2. Перейдите в папку веб-сервера
cd /var/www/html

# 3. Клонируйте проект
git clone <your-repo-url> linux-server-manager
cd linux-server-manager

# 4. Установите зависимости
composer install --no-dev --optimize-autoloader

# 5. Настройте права доступа
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 777 logs/
```

#### Настройка Apache:

Создайте файл `/etc/apache2/sites-available/linux-server-manager.conf`:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/linux-server-manager/public
    
    <Directory /var/www/html/linux-server-manager/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/linux-server-manager_error.log
    CustomLog ${APACHE_LOG_DIR}/linux-server-manager_access.log combined
</VirtualHost>
```

```bash
# Включите сайт
sudo a2ensite linux-server-manager
sudo systemctl reload apache2
```

#### Настройка Nginx:

Создайте файл `/etc/nginx/sites-available/linux-server-manager`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/linux-server-manager/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

```bash
# Включите сайт
sudo ln -s /etc/nginx/sites-available/linux-server-manager /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Вариант 3: Автоматический скрипт развертывания

Создайте файл `deploy.sh`:

```bash
#!/bin/bash

# Конфигурация
REPO_URL="your-git-repo-url"
DEPLOY_PATH="/var/www/html/linux-server-manager"
BACKUP_PATH="/var/backups/linux-server-manager"

echo "🚀 Начинаем развертывание Linux Server Manager..."

# Создаем резервную копию
if [ -d "$DEPLOY_PATH" ]; then
    echo "📦 Создаем резервную копию..."
    sudo mkdir -p $BACKUP_PATH
    sudo cp -r $DEPLOY_PATH $BACKUP_PATH/backup-$(date +%Y%m%d-%H%M%S)
fi

# Клонируем/обновляем код
if [ -d "$DEPLOY_PATH" ]; then
    echo "🔄 Обновляем код..."
    cd $DEPLOY_PATH
    git pull origin main
else
    echo "📥 Клонируем репозиторий..."
    sudo git clone $REPO_URL $DEPLOY_PATH
    cd $DEPLOY_PATH
fi

# Устанавливаем зависимости
echo "📦 Устанавливаем зависимости..."
composer install --no-dev --optimize-autoloader

# Настраиваем права доступа
echo "🔐 Настраиваем права доступа..."
sudo chown -R www-data:www-data $DEPLOY_PATH
sudo chmod -R 755 $DEPLOY_PATH
sudo chmod -R 777 $DEPLOY_PATH/logs

# Перезапускаем веб-сервер
echo "🔄 Перезапускаем веб-сервер..."
sudo systemctl reload apache2

echo "✅ Развертывание завершено!"
echo "🌐 Приложение доступно по адресу: http://your-domain.com"
```

Сделайте скрипт исполняемым:

```bash
chmod +x deploy.sh
./deploy.sh
```

## 🔧 Настройка окружения

### 1. Создайте файл .env

```bash
cp .env.example .env
```

Отредактируйте `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

# Настройки базы данных (если используется)
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=linux_server_manager
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Настройки безопасности
APP_KEY=your-secret-key-here
```

### 2. Настройте SSL (HTTPS)

```bash
# Установите Certbot
sudo apt install certbot python3-certbot-apache

# Получите SSL сертификат
sudo certbot --apache -d your-domain.com

# Автоматическое обновление
sudo crontab -e
# Добавьте строку:
# 0 12 * * * /usr/bin/certbot renew --quiet
```

## 📊 Мониторинг и логи

### Настройка логирования:

```bash
# Создайте папку для логов
sudo mkdir -p /var/log/linux-server-manager
sudo chown www-data:www-data /var/log/linux-server-manager

# Настройте ротацию логов
sudo nano /etc/logrotate.d/linux-server-manager
```

Содержимое `/etc/logrotate.d/linux-server-manager`:

```
/var/log/linux-server-manager/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

## 🔒 Безопасность

### 1. Настройте файрвол:

```bash
# Установите UFW
sudo apt install ufw

# Настройте правила
sudo ufw allow ssh
sudo ufw allow 'Apache Full'
sudo ufw enable
```

### 2. Настройте fail2ban:

```bash
# Установите fail2ban
sudo apt install fail2ban

# Создайте конфигурацию
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sudo nano /etc/fail2ban/jail.local
```

### 3. Регулярные обновления:

```bash
# Создайте скрипт обновления
sudo nano /usr/local/bin/update-server.sh
```

```bash
#!/bin/bash
apt update && apt upgrade -y
apt autoremove -y
```

```bash
chmod +x /usr/local/bin/update-server.sh

# Добавьте в cron
sudo crontab -e
# Добавьте строку:
# 0 2 * * 0 /usr/local/bin/update-server.sh
```

## 🚨 Устранение неполадок

### Частые проблемы:

1. **Ошибка 500**: Проверьте права доступа и логи
```bash
sudo tail -f /var/log/apache2/error.log
```

2. **Composer ошибки**: Обновите Composer
```bash
composer self-update
```

3. **Проблемы с правами доступа**:
```bash
sudo chown -R www-data:www-data /var/www/html/linux-server-manager
sudo chmod -R 755 /var/www/html/linux-server-manager
```

4. **SSL ошибки**: Проверьте сертификат
```bash
sudo certbot certificates
```

## 📞 Поддержка

Если возникли проблемы:
1. Проверьте логи в `/var/log/`
2. Убедитесь, что все зависимости установлены
3. Проверьте права доступа к файлам
4. Убедитесь, что веб-сервер настроен правильно

## 🎯 Рекомендации

- Настройте автоматические резервные копии
- Регулярно обновляйте систему и зависимости
- Мониторьте использование ресурсов
- Настройте SSL для безопасности
- Используйте автоматические скрипты развертывания
