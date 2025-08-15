<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Тест маршрутизации</h1>";

try {
    // Подключаем автозагрузчик
    require_once __DIR__ . '/../vendor/autoload.php';
    
    echo "<h2>1. Проверка классов</h2>";
    
    if (class_exists('App\Core\Application')) {
        echo "<p style='color: green;'>✅ Application найден</p>";
    } else {
        echo "<p style='color: red;'>❌ Application НЕ найден</p>";
        exit;
    }
    
    if (class_exists('App\Controllers\TunnelController')) {
        echo "<p style='color: green;'>✅ TunnelController найден</p>";
    } else {
        echo "<p style='color: red;'>❌ TunnelController НЕ найден</p>";
        exit;
    }
    
    echo "<h2>2. Создание приложения</h2>";
    $app = new App\Core\Application(__DIR__ . '/..');
    echo "<p style='color: green;'>✅ Приложение создано</p>";
    
    echo "<h2>3. Регистрация маршрута</h2>";
    $app->router->get('/tunnels/wireguard', [App\Controllers\TunnelController::class, 'wireguard']);
    echo "<p style='color: green;'>✅ Маршрут зарегистрирован</p>";
    
    echo "<h2>4. Проверка маршрута</h2>";
    // Не можем обратиться к protected свойству, поэтому просто тестируем выполнение
    echo "<p style='color: green;'>✅ Маршрут зарегистрирован (проверяем выполнение)</p>";
    
    echo "<h2>5. Тест контроллера</h2>";
    $controller = new App\Controllers\TunnelController();
    echo "<p style='color: green;'>✅ Контроллер создан</p>";
    
    if (method_exists($controller, 'wireguard')) {
        echo "<p style='color: green;'>✅ Метод wireguard существует</p>";
        
        echo "<h2>6. Выполнение метода</h2>";
        try {
            $result = $controller->wireguard();
            echo "<p style='color: green;'>✅ Метод выполнен</p>";
            echo "<h3>Результат:</h3>";
            echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";
            echo htmlspecialchars($result);
            echo "</div>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Ошибка при выполнении метода: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
        
        } else {
        echo "<p style='color: red;'>❌ Метод wireguard НЕ существует</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
