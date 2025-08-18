#!/bin/bash

echo "🔧 Исправление проблем с autoloader..."

# Переходим в директорию проекта
cd /var/www/html/linux-server-manager

# Перегенерируем autoloader
echo "📦 Перегенерируем autoloader..."
composer dump-autoload --optimize

# Проверяем права доступа
echo "🔐 Проверяем права доступа..."
chown -R www-data:www-data /var/www/html/linux-server-manager
chmod -R 755 /var/www/html/linux-server-manager

# Очищаем кэш PHP
echo "🧹 Очищаем кэш PHP..."
php -r "opcache_reset();" 2>/dev/null || echo "OPcache не доступен"

# Проверяем, что файлы существуют
echo "📁 Проверяем наличие файлов..."
if [ -f "src/Services/Cloudflare/CloudflareService.php" ]; then
    echo "✅ CloudflareService.php найден"
else
    echo "❌ CloudflareService.php не найден"
fi

if [ -f "vendor/autoload.php" ]; then
    echo "✅ autoload.php найден"
else
    echo "❌ autoload.php не найден"
fi

echo "✅ Исправление завершено!"
echo "🔄 Перезапустите веб-сервер если необходимо:"
echo "   sudo systemctl reload nginx"
echo "   sudo systemctl reload php8.3-fpm"
