<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Тест основного приложения</h1>";

try {
    // Подключаем автозагрузчик
    require_once __DIR__ . '/../vendor/autoload.php';
    
    echo "<h2>1. Симуляция основного приложения</h2>";
    
    // Симулируем запрос к /tunnels/wireguard
    $_SERVER['REQUEST_URI'] = '/tunnels/wireguard';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    // Создаем приложение как в index.php
    $app = new App\Core\Application(__DIR__ . '/..');
    
    // Регистрируем маршруты как в index.php
    $app->router->get('/', [App\Controllers\DashboardController::class, 'index']);
    $app->router->get('/system', [App\Controllers\SystemController::class, 'index']);
    $app->router->get('/processes', [App\Controllers\ProcessController::class, 'index']);
    $app->router->get('/services', [App\Controllers\ServiceController::class, 'index']);
    $app->router->get('/tunnels/ssh', [App\Controllers\TunnelController::class, 'ssh']);
    $app->router->get('/tunnels/port-forwarding', [App\Controllers\TunnelController::class, 'portForwarding']);
    $app->router->get('/tunnels/wireguard', [App\Controllers\TunnelController::class, 'wireguard']);
    $app->router->get('/tunnels/cloudflare', [App\Controllers\TunnelController::class, 'cloudflare']);
    
    echo "<p style='color: green;'>✅ Приложение создано и маршруты зарегистрированы</p>";
    
    echo "<h2>2. Тест маршрутизации</h2>";
    
    try {
        $result = $app->router->resolve();
        echo "<p style='color: green;'>✅ Маршрутизация выполнена успешно</p>";
        
        echo "<h3>Результат:</h3>";
        echo "<div style='border: 2px solid #ccc; padding: 20px; margin: 10px 0; background: #f9f9f9;'>";
        echo htmlspecialchars($result);
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Ошибка маршрутизации: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
