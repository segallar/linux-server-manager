<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\CloudflareService;

echo "<h1>🔧 Cloudflare Debug Information</h1>";

try {
    echo "<h2>1. Проверка установки cloudflared</h2>";
    $service = new CloudflareService();
    $isInstalled = $service->isInstalled();
    echo "<p><strong>cloudflared установлен:</strong> " . ($isInstalled ? 'ДА' : 'НЕТ') . "</p>";
    
    if ($isInstalled) {
        echo "<h2>2. Проверка команд cloudflared</h2>";
        
        // Проверяем версию
        $version = shell_exec('cloudflared version 2>&1');
        echo "<p><strong>Версия:</strong></p>";
        echo "<pre>" . htmlspecialchars($version ?: 'Нет вывода') . "</pre>";
        
        // Проверяем список туннелей
        $tunnelList = shell_exec('cloudflared tunnel list 2>&1');
        echo "<p><strong>Список туннелей:</strong></p>";
        echo "<pre>" . htmlspecialchars($tunnelList ?: 'Нет вывода') . "</pre>";
        
        echo "<h2>3. Тест PHP сервиса</h2>";
        echo "<p><strong>Класс CloudflareService найден:</strong> ДА</p>";
        
        $tunnels = $service->getTunnels();
        echo "<p><strong>getTunnels():</strong> " . count($tunnels) . " туннелей</p>";
        
        if (!empty($tunnels)) {
            echo "<p><strong>Детали туннелей:</strong></p>";
            foreach ($tunnels as $tunnel) {
                echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
                echo "<p><strong>ID:</strong> " . htmlspecialchars($tunnel['id']) . "</p>";
                echo "<p><strong>Название:</strong> " . htmlspecialchars($tunnel['name']) . "</p>";
                echo "<p><strong>Статус:</strong> " . htmlspecialchars($tunnel['status']) . "</p>";
                echo "<p><strong>Создан:</strong> " . htmlspecialchars($tunnel['created']) . "</p>";
                echo "<p><strong>Соединения:</strong> " . count($tunnel['connections']) . "</p>";
                echo "<p><strong>Маршруты:</strong> " . count($tunnel['routes']) . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p style='color: red;'>❌ Туннели не найдены</p>";
        }
        
        $stats = $service->getStats();
        echo "<p><strong>Статистика:</strong></p>";
        echo "<pre>" . print_r($stats, true) . "</pre>";
        
    } else {
        echo "<p style='color: red;'>❌ cloudflared не установлен</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<h2>4. Последние логи PHP</h2>";
$logFile = '/var/log/php/error.log';
if (file_exists($logFile)) {
    $logs = shell_exec("tail -20 $logFile 2>/dev/null");
    echo "<pre>" . htmlspecialchars($logs ?: 'Нет логов') . "</pre>";
} else {
    echo "<p>Файл логов не найден: $logFile</p>";
}

echo "<h2>5. Рекомендации</h2>";
if (!$isInstalled) {
    echo "<p>Установите cloudflared:</p>";
    echo "<code>curl -L https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64 -o cloudflared</code><br>";
    echo "<code>chmod +x cloudflared</code><br>";
    echo "<code>sudo mv cloudflared /usr/local/bin/</code>";
} else {
    echo "<p>cloudflared установлен, но туннели не найдены. Проверьте:</p>";
    echo "<ul>";
    echo "<li>Выполните <code>cloudflared tunnel list</code> в терминале</li>";
    echo "<li>Проверьте права доступа к cloudflared</li>";
    echo "<li>Убедитесь, что вы авторизованы в Cloudflare</li>";
    echo "</ul>";
}
?>
