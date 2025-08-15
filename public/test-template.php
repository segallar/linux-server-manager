<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Тест шаблона WireGuard</h1>";

try {
    // Подключаем автозагрузчик
    require_once __DIR__ . '/../vendor/autoload.php';
    
    echo "<h2>1. Получение данных WireGuard</h2>";
    $service = new App\Services\WireGuardService();
    $interfaces = $service->getInterfaces();
    $stats = $service->getStats();
    $isInstalled = $service->isInstalled();
    
    echo "<p style='color: green;'>✅ Данные получены</p>";
    echo "<p><strong>Интерфейсов:</strong> " . count($interfaces) . "</p>";
    echo "<p><strong>Установлен:</strong> " . ($isInstalled ? 'ДА' : 'НЕТ') . "</p>";
    
    echo "<h2>2. Проверка шаблона</h2>";
    $templatePath = __DIR__ . '/../templates/tunnels/wireguard.php';
    if (file_exists($templatePath)) {
        echo "<p style='color: green;'>✅ Шаблон найден</p>";
        
        echo "<h2>3. Рендеринг шаблона</h2>";
        echo "<div style='border: 2px solid #ccc; padding: 20px; margin: 10px 0; background: #f9f9f9;'>";
        
        // Включаем шаблон с переменными
        include $templatePath;
        
        echo "</div>";
        
    } else {
        echo "<p style='color: red;'>❌ Шаблон НЕ найден</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
