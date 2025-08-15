<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Main App Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔧 Main App Debug</h1>";

try {
    // Подключаем автозагрузчик
    require_once __DIR__ . '/../vendor/autoload.php';
    
    echo "<div class='section'>
        <h2>1. Проверка основных классов</h2>";
    
    $classes = [
        'App\Core\Application',
        'App\Core\Router',
        'App\Core\Request',
        'App\Core\Response',
        'App\Core\Controller',
        'App\Controllers\TunnelController',
        'App\Services\WireGuardService'
    ];
    
    foreach ($classes as $class) {
        if (class_exists($class)) {
            echo "<p class='success'>✅ $class</p>";
        } else {
            echo "<p class='error'>❌ $class</p>";
        }
    }
    
    echo "</div><div class='section'>
        <h2>2. Создание приложения</h2>";
    
    $app = new App\Core\Application(__DIR__ . '/..');
    echo "<p class='success'>✅ Приложение создано</p>";
    
    echo "</div><div class='section'>
        <h2>3. Регистрация маршрутов</h2>";
    
    // Регистрируем маршруты как в index.php
    $app->router->get('/', [App\Controllers\DashboardController::class, 'index']);
    $app->router->get('/system', [App\Controllers\SystemController::class, 'index']);
    $app->router->get('/processes', [App\Controllers\ProcessController::class, 'index']);
    $app->router->get('/services', [App\Controllers\ServiceController::class, 'index']);
    $app->router->get('/tunnels/ssh', [App\Controllers\TunnelController::class, 'ssh']);
    $app->router->get('/tunnels/port-forwarding', [App\Controllers\TunnelController::class, 'portForwarding']);
    $app->router->get('/tunnels/wireguard', [App\Controllers\TunnelController::class, 'wireguard']);
    $app->router->get('/tunnels/cloudflare', [App\Controllers\TunnelController::class, 'cloudflare']);
    
    echo "<p class='success'>✅ Маршруты зарегистрированы</p>";
    
    echo "</div><div class='section'>
        <h2>4. Проверка маршрута /tunnels/wireguard</h2>";
    
    // Симулируем запрос к /tunnels/wireguard
    $_SERVER['REQUEST_URI'] = '/tunnels/wireguard';
    $app = new App\Core\Application(__DIR__ . '/..');
    
    // Регистрируем маршрут
    $app->router->get('/tunnels/wireguard', [App\Controllers\TunnelController::class, 'wireguard']);
    
    $callback = $app->router->routes['get']['/tunnels/wireguard'] ?? false;
    if ($callback) {
        echo "<p class='success'>✅ Маршрут найден</p>";
        echo "<pre>";
        print_r($callback);
        echo "</pre>";
        
        echo "<h3>Тест выполнения контроллера:</h3>";
        try {
            $controller = new $callback[0]();
            $method = $callback[1];
            
            if (method_exists($controller, $method)) {
                echo "<p class='success'>✅ Метод $method существует</p>";
                
                // Пробуем выполнить метод
                $result = $controller->$method();
                echo "<p class='success'>✅ Метод выполнен успешно</p>";
                echo "<h4>Результат:</h4>";
                echo "<pre>" . htmlspecialchars($result) . "</pre>";
                
            } else {
                echo "<p class='error'>❌ Метод $method НЕ существует</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Ошибка при выполнении контроллера: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
        
    } else {
        echo "<p class='error'>❌ Маршрут НЕ найден</p>";
    }
    
    echo "</div><div class='section'>
        <h2>5. Проверка WireGuard сервиса</h2>";
    
    try {
        $service = new App\Services\WireGuardService();
        echo "<p class='success'>✅ WireGuardService создан</p>";
        
        $interfaces = $service->getInterfaces();
        echo "<p><strong>Интерфейсов:</strong> " . count($interfaces) . "</p>";
        
        $stats = $service->getStats();
        echo "<p><strong>Статистика:</strong></p>";
        echo "<pre>" . print_r($stats, true) . "</pre>";
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Ошибка WireGuardService: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</div>";

echo "</body></html>";
?>
