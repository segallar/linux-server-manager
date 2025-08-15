<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <title>WireGuard Template Debug</title>
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
    <h1>🔧 WireGuard Template Debug</h1>";

try {
    // Подключаем автозагрузчик
    require_once __DIR__ . '/../vendor/autoload.php';
    
    echo "<div class='section'>
        <h2>1. Симуляция контроллера</h2>";
    
    // Симулируем работу контроллера
    $wireguardService = new App\Services\WireGuardService();
    
    $interfaces = $wireguardService->getInterfaces();
    $stats = $wireguardService->getStats();
    $isInstalled = $wireguardService->isInstalled();
    
    echo "<p class='success'>✅ Данные получены из сервиса</p>";
    
    echo "</div><div class='section'>
        <h2>2. Переменные для шаблона</h2>";
    
    echo "<h3>isInstalled:</h3>";
    echo "<pre>" . var_export($isInstalled, true) . "</pre>";
    
    echo "<h3>stats:</h3>";
    echo "<pre>" . print_r($stats, true) . "</pre>";
    
    echo "<h3>interfaces:</h3>";
    echo "<pre>" . print_r($interfaces, true) . "</pre>";
    
    echo "</div><div class='section'>
        <h2>3. Проверка условий в шаблоне</h2>";
    
    echo "<p><strong>!isInstalled:</strong> " . (!$isInstalled ? 'true' : 'false') . "</p>";
    echo "<p><strong>empty(interfaces):</strong> " . (empty($interfaces) ? 'true' : 'false') . "</p>";
    echo "<p><strong>count(interfaces):</strong> " . count($interfaces) . "</p>";
    
    if (!empty($interfaces)) {
        echo "<h3>Первый интерфейс:</h3>";
        $firstInterface = $interfaces[0];
        echo "<p><strong>name:</strong> " . htmlspecialchars($firstInterface['name']) . "</p>";
        echo "<p><strong>status:</strong> " . htmlspecialchars($firstInterface['status']) . "</p>";
        echo "<p><strong>address:</strong> " . htmlspecialchars($firstInterface['address']) . "</p>";
        echo "<p><strong>port:</strong> " . htmlspecialchars($firstInterface['port']) . "</p>";
        echo "<p><strong>peers count:</strong> " . count($firstInterface['peers']) . "</p>";
    }
    
    echo "</div><div class='section'>
        <h2>4. Тест рендеринга шаблона</h2>";
    
    // Проверяем, что шаблон существует
    $templatePath = __DIR__ . '/../templates/tunnels/wireguard.php';
    if (file_exists($templatePath)) {
        echo "<p class='success'>✅ Шаблон wireguard.php найден</p>";
        
        // Пробуем включить шаблон напрямую
        echo "<h3>Рендеринг шаблона:</h3>";
        echo "<div style='border: 2px solid #ccc; padding: 20px; margin: 10px 0;'>";
        
        // Включаем шаблон с нашими переменными
        include $templatePath;
        
        echo "</div>";
        
    } else {
        echo "<p class='error'>❌ Шаблон wireguard.php НЕ найден</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</div>";

echo "</body></html>";
?>
