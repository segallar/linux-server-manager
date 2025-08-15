# Быстрое развертывание Linux Server Manager

## 🚀 Самый простой способ

### 1. Подготовка сервера

Убедитесь, что на сервере установлены:
- PHP 7.4 или выше
- Composer
- Git
- Apache или Nginx

```bash
# Установка на Ubuntu/Debian
sudo apt update
sudo apt install php php-cli php-mbstring php-xml php-zip php-fpm composer git nginx
```

### 2. Развертывание

```bash
# Подключитесь к серверу
ssh user@your-server.com

# Перейдите в папку веб-сервера
cd /var/www/html

# Клонируйте проект
git clone https://github.com/your-username/linux-server-manager.git
cd linux-server-manager

# Установите зависимости
composer install --no-dev --optimize-autoloader

# Создайте папку для логов
mkdir -p logs

# Настройте права доступа
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 777 logs/
```

### 3. Настройка Nginx

```bash
# Создайте конфигурацию
sudo nano /etc/nginx/sites-available/linux-server-manager
```

Содержимое файла:
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

### 4. Настройка Apache (альтернатива)

```bash
# Создайте конфигурацию
sudo nano /etc/nginx/sites-available/linux-server-manager
```

Содержимое файла:
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
}
```

```bash
# Включите сайт
sudo ln -s /etc/nginx/sites-available/linux-server-manager /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 🔧 Дополнительные настройки

### SSL сертификат (Let's Encrypt)

```bash
# Установите Certbot
sudo apt install certbot python3-certbot-nginx

# Получите сертификат
sudo certbot --nginx -d your-domain.com
```

### Файрвол

```bash
# Установите UFW
sudo apt install ufw

# Настройте правила
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

## 📝 Обновление приложения

```bash
# Перейдите в папку проекта
cd /var/www/html/linux-server-manager

# Обновите код
git pull origin main

# Обновите зависимости
composer install --no-dev --optimize-autoloader

# Настройте права доступа
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 777 logs/

# Перезапустите веб-сервер
sudo systemctl reload nginx
```

## 🚨 Устранение неполадок

### Ошибка 500
```bash
# Проверьте логи
sudo tail -f /var/log/nginx/error.log
```

### Проблемы с правами доступа
```bash
sudo chown -R www-data:www-data /var/www/html/linux-server-manager
sudo chmod -R 755 /var/www/html/linux-server-manager
```

### Composer ошибки
```bash
composer self-update
composer install --no-dev --optimize-autoloader
```

## ✅ Готово!

Ваше приложение теперь доступно по адресу:
- HTTP: `http://your-domain.com`
- HTTPS: `https://your-domain.com` (после настройки SSL)

## 🔄 Автоматическое обновление

Создайте скрипт для автоматического обновления:

```bash
sudo nano /usr/local/bin/update-lsm.sh
```

```bash
#!/bin/bash
cd /var/www/html/linux-server-manager
git pull origin main
composer install --no-dev --optimize-autoloader
sudo chown -R www-data:www-data .
sudo systemctl reload nginx
echo "Обновление завершено: $(date)"
```

```bash
sudo chmod +x /usr/local/bin/update-lsm.sh

# Добавьте в cron для автоматического обновления
sudo crontab -e
# Добавьте строку:
# 0 2 * * 0 /usr/local/bin/update-lsm.sh
```
