<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Отладка контроллера</h1>";

try {
    // Подключаем автозагрузчик
    require_once __DIR__ . '/../vendor/autoload.php';
    
    echo "<h2>1. Создание контроллера</h2>";
    $controller = new App\Controllers\TunnelController();
    echo "<p style='color: green;'>✅ Контроллер создан</p>";
    
    echo "<h2>2. Проверка метода wireguard</h2>";
    if (method_exists($controller, 'wireguard')) {
        echo "<p style='color: green;'>✅ Метод wireguard существует</p>";
        
        echo "<h2>3. Выполнение метода wireguard</h2>";
        try {
            $result = $controller->wireguard();
            echo "<p style='color: green;'>✅ Метод выполнен</p>";
            
            echo "<h3>Результат (длина: " . strlen($result) . " символов):</h3>";
            echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9; max-height: 400px; overflow-y: auto;'>";
            echo htmlspecialchars($result);
            echo "</div>";
            
            if (empty($result)) {
                echo "<p style='color: red;'>❌ Результат ПУСТОЙ!</p>";
            } else {
                echo "<p style='color: green;'>✅ Результат НЕ пустой</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Ошибка при выполнении метода: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Метод wireguard НЕ существует</p>";
    }
    
    echo "<h2>4. Проверка WireGuard сервиса</h2>";
    try {
        $service = new App\Services\WireGuardService();
        echo "<p style='color: green;'>✅ WireGuardService создан</p>";
        
        $interfaces = $service->getInterfaces();
        echo "<p><strong>Интерфейсов:</strong> " . count($interfaces) . "</p>";
        
        $stats = $service->getStats();
        echo "<p><strong>Статистика:</strong></p>";
        echo "<pre>" . print_r($stats, true) . "</pre>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Ошибка WireGuardService: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
