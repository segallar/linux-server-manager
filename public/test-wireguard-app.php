<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <title>WireGuard App Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔧 WireGuard App Test</h1>";

try {
    // Подключаем автозагрузчик
    require_once __DIR__ . '/../vendor/autoload.php';
    
    echo "<div class='section'>
        <h2>1. Проверка автозагрузчика</h2>";
    
    if (class_exists('App\Services\WireGuardService')) {
        echo "<p class='success'>✅ Класс WireGuardService найден</p>";
    } else {
        echo "<p class='error'>❌ Класс WireGuardService НЕ найден</p>";
        exit;
    }
    
    echo "</div><div class='section'>
        <h2>2. Создание экземпляра сервиса</h2>";
    
    $service = new App\Services\WireGuardService();
    echo "<p class='success'>✅ Экземпляр WireGuardService создан</p>";
    
    echo "</div><div class='section'>
        <h2>3. Проверка установки</h2>";
    
    $isInstalled = $service->isInstalled();
    echo "<p><strong>isInstalled():</strong> " . ($isInstalled ? 'ДА' : 'НЕТ') . "</p>";
    
    echo "</div><div class='section'>
        <h2>4. Получение интерфейсов</h2>";
    
    $interfaces = $service->getInterfaces();
    echo "<p><strong>getInterfaces():</strong> " . count($interfaces) . " интерфейсов</p>";
    
    if (!empty($interfaces)) {
        echo "<h3>Детали интерфейсов:</h3>";
        foreach ($interfaces as $interface) {
            echo "<div style='border: 1px solid #ccc; margin: 10px 0; padding: 10px;'>";
            echo "<h4>" . htmlspecialchars($interface['name']) . "</h4>";
            echo "<p><strong>Статус:</strong> " . htmlspecialchars($interface['status']) . "</p>";
            echo "<p><strong>Адрес:</strong> " . htmlspecialchars($interface['address']) . "</p>";
            echo "<p><strong>Порт:</strong> " . htmlspecialchars($interface['port']) . "</p>";
            echo "<p><strong>Пиров:</strong> " . count($interface['peers']) . "</p>";
            
            if (!empty($interface['peers'])) {
                echo "<h5>Пиры:</h5>";
                foreach ($interface['peers'] as $peer) {
                    echo "<div style='margin-left: 20px; padding: 5px; background: #f9f9f9;'>";
                    echo "<p><strong>Ключ:</strong> " . substr(htmlspecialchars($peer['public_key']), 0, 20) . "...</p>";
                    echo "<p><strong>Статус:</strong> " . htmlspecialchars($peer['status']) . "</p>";
                    echo "<p><strong>Endpoint:</strong> " . htmlspecialchars($peer['endpoint']) . "</p>";
                    echo "<p><strong>Allowed IPs:</strong> " . implode(', ', $peer['allowed_ips']) . "</p>";
                    echo "</div>";
                }
            }
            echo "</div>";
        }
    }
    
    echo "</div><div class='section'>
        <h2>5. Получение статистики</h2>";
    
    $stats = $service->getStats();
    echo "<h3>Статистика:</h3>";
    echo "<pre>" . print_r($stats, true) . "</pre>";
    
    echo "</div><div class='section'>
        <h2>6. Тест рендеринга шаблона</h2>";
    
    // Проверяем, что шаблон существует
    $templatePath = __DIR__ . '/../templates/tunnels/wireguard.php';
    if (file_exists($templatePath)) {
        echo "<p class='success'>✅ Шаблон wireguard.php найден</p>";
        
        // Пробуем рендерить данные как в контроллере
        $title = 'WireGuard';
        $currentPage = 'tunnels';
        
        echo "<h3>Данные для шаблона:</h3>";
        echo "<pre>";
        echo "title: $title\n";
        echo "currentPage: $currentPage\n";
        echo "interfaces: " . count($interfaces) . "\n";
        echo "stats: " . json_encode($stats) . "\n";
        echo "isInstalled: " . ($isInstalled ? 'true' : 'false') . "\n";
        echo "</pre>";
        
    } else {
        echo "<p class='error'>❌ Шаблон wireguard.php НЕ найден</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</div>
    <div class='section'>
        <h2>7. Проверка маршрутизации</h2>";
    
// Проверяем, что маршрут зарегистрирован
echo "<p>Проверьте, что маршрут /tunnels/wireguard зарегистрирован в public/index.php</p>";
echo "<p>Проверьте, что TunnelController::wireguard() метод существует</p>";

echo "</div>";

echo "</body></html>";
?>
