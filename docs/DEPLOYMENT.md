# 🚀 Развертывание Linux Server Manager

## 📋 Краткое описание
Полное руководство по развертыванию Linux Server Manager на различных платформах и окружениях.

## 🎯 Цель документа
Для системных администраторов и разработчиков, которые хотят развернуть Linux Server Manager на сервере или локальной машине.

---

## 📋 Содержание
- [Требования](#требования)
- [Варианты развертывания](#варианты-развертывания)
- [Настройка веб-сервера](#настройка-веб-сервера)
- [Проверка установки](#проверка-установки)
- [Устранение неполадок](#устранение-неполадок)

---

## 📝 Основное содержание

### Требования

#### 🔧 Системные требования
- **PHP**: 8.0 или выше
- **Composer**: Последняя версия
- **Git**: Для клонирования репозитория
- **Веб-сервер**: Apache 2.4+ или Nginx 1.18+
- **Операционная система**: Linux (Ubuntu 20.04+, CentOS 8+, Debian 11+)

#### 📦 PHP расширения
```bash
# Обязательные расширения
php-xml
php-curl
php-mbstring
php-json
php-openssl

# Для Ubuntu/Debian
sudo apt-get install php8.0-xml php8.0-curl php8.0-mbstring php8.0-json php8.0-openssl

# Для CentOS/RHEL
sudo yum install php-xml php-curl php-mbstring php-json php-openssl
```

### Варианты развертывания

#### 🚀 Вариант 1: Простой PHP сервер (для тестирования)

**Назначение**: Быстрое развертывание для разработки и тестирования.

```bash
# 1. Клонируйте репозиторий
git clone https://github.com/segallar/linux-server-manager.git
cd linux-server-manager

# 2. Установите зависимости
composer install

# 3. Запустите встроенный сервер
composer start
# или
php -S localhost:8000 -t public
```

**Результат**: Приложение доступно по адресу `http://localhost:8000`

#### 🌐 Вариант 2: Apache/Nginx (продакшн)

**Назначение**: Полноценное развертывание для продакшн среды.

```bash
# 1. Подключитесь к серверу
ssh user@your-server.com

# 2. Перейдите в папку веб-сервера
cd /var/www/html

# 3. Клонируйте проект
git clone https://github.com/segallar/linux-server-manager.git linux-server-manager
cd linux-server-manager

# 4. Установите зависимости
composer install --no-dev --optimize-autoloader

# 5. Настройте права доступа
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 777 logs/
```

#### ⚡ Вариант 3: Автоматический скрипт развертывания

**Назначение**: Автоматизированное развертывание с минимальным вмешательством.

```bash
# 1. Скачайте скрипт развертывания
wget https://raw.githubusercontent.com/segallar/linux-server-manager/main/deploy.sh

# 2. Сделайте скрипт исполняемым
chmod +x deploy.sh

# 3. Запустите развертывание
./deploy.sh
```

### Настройка веб-сервера

#### 🌐 Apache

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

#### 🚀 Nginx

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
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
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

### Проверка установки

#### ✅ Основные проверки

1. **Доступность веб-интерфейса**
   ```bash
   curl -I http://your-domain.com
   # Должен вернуть HTTP 200
   ```

2. **Проверка PHP**
   ```bash
   php -v
   # Должен показать версию PHP 8.0+
   ```

3. **Проверка зависимостей**
   ```bash
   composer check-platform-reqs
   # Все требования должны быть выполнены
   ```

4. **Проверка прав доступа**
   ```bash
   ls -la logs/
   # Папка logs должна быть доступна для записи
   ```

#### 🔍 Проверка функциональности

1. Откройте веб-интерфейс в браузере
2. Проверьте раздел "Dashboard" - должна отображаться информация о системе
3. Проверьте раздел "System" - должна быть доступна системная информация
4. Проверьте раздел "Services" - должен отображаться список сервисов

### Устранение неполадок

#### 🚨 Частые проблемы

**Проблема**: Ошибка 500 Internal Server Error
```bash
# Решение: Проверьте логи ошибок
sudo tail -f /var/log/apache2/error.log
# или
sudo tail -f /var/log/nginx/error.log
```

**Проблема**: Ошибка "Permission denied"
```bash
# Решение: Настройте права доступа
sudo chown -R www-data:www-data /var/www/html/linux-server-manager
sudo chmod -R 755 /var/www/html/linux-server-manager
sudo chmod -R 777 /var/www/html/linux-server-manager/logs
```

**Проблема**: PHP расширения не найдены
```bash
# Решение: Установите недостающие расширения
sudo apt-get install php8.0-xml php8.0-curl php8.0-mbstring
# или
sudo yum install php-xml php-curl php-mbstring
```

**Проблема**: Composer не найден
```bash
# Решение: Установите Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### 📊 Диагностика

```bash
# Проверка системных ресурсов
free -h
df -h
top

# Проверка сетевых соединений
netstat -tulpn | grep :80
netstat -tulpn | grep :443

# Проверка конфигурации PHP
php -m
php --ini
```

---

## 🔗 Связанные документы
- **[Основная документация](README.md)** - Индекс всех документов
- **[Настройка файрвола](FIREWALL_SETUP.md)** - Конфигурация безопасности
- **[Политика безопасности](SECURITY.md)** - Информация о безопасности
- **[Статус проекта](PROJECT_STATUS.md)** - Что реализовано и что планируется

## 📞 Поддержка
См. [основную документацию](README.md#📞-поддержка).

---

**📅 Последнее обновление**: 2025-01-16  
**🏷️ Версия документа**: 1.0.0  
**📝 Автор**: Команда Linux Server Manager
