<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Route Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔧 Route Debug</h1>";

try {
    // Подключаем автозагрузчик
    require_once __DIR__ . '/../vendor/autoload.php';
    
    echo "<h2>1. Проверка автозагрузчика</h2>";
    if (class_exists('App\Core\Application')) {
        echo "<p class='success'>✅ Application класс найден</p>";
    } else {
        echo "<p class='error'>❌ Application класс НЕ найден</p>";
        exit;
    }
    
    echo "<h2>2. Создание приложения</h2>";
    $app = new App\Core\Application(__DIR__ . '/..');
    echo "<p class='success'>✅ Приложение создано</p>";
    
    echo "<h2>3. Проверка маршрутов</h2>";
    echo "<pre>";
    print_r($app->router->routes);
    echo "</pre>";
    
    echo "<h2>4. Проверка текущего пути</h2>";
    $currentPath = $app->request->getPath();
    echo "<p><strong>Текущий путь:</strong> $currentPath</p>";
    
    echo "<h2>5. Проверка метода</h2>";
    $method = $app->request->method();
    echo "<p><strong>Метод:</strong> $method</p>";
    
    echo "<h2>6. Тест маршрута /tunnels/wireguard</h2>";
    
    // Симулируем запрос к /tunnels/wireguard
    $_SERVER['REQUEST_URI'] = '/tunnels/wireguard';
    $app = new App\Core\Application(__DIR__ . '/..');
    
    // Регистрируем маршрут
    $app->router->get('/tunnels/wireguard', [App\Controllers\TunnelController::class, 'wireguard']);
    
    echo "<p class='success'>✅ Маршрут зарегистрирован</p>";
    
    // Проверяем, есть ли маршрут
    $callback = $app->router->routes['get']['/tunnels/wireguard'] ?? false;
    if ($callback) {
        echo "<p class='success'>✅ Маршрут найден</p>";
        echo "<pre>";
        print_r($callback);
        echo "</pre>";
    } else {
        echo "<p class='error'>❌ Маршрут НЕ найден</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</body></html>";
?>
